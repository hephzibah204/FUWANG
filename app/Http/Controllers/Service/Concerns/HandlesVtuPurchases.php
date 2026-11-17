<?php
namespace App\Http\Controllers\Service\Concerns;

use App\Models\CustomApi;
use App\Models\VtuTransaction;
use App\Services\EpinCatalogService;
use App\Services\VtuCatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait HandlesVtuPurchases
{
    private function purchasePage(string $type,string $route) {
        return view('services.vtu.purchase',['title'=>config('vtu_services.service_types.'.$type.'.label'),'type'=>$type,'purchaseRoute'=>$route]);
    }
    private function providerTypes(string $type):array {
        return match($type){'vtu_cable_tv'=>['vtu_cable_tv','cable_tv'],'vtu_electricity'=>['vtu_electricity','electricity_bills'],default=>[$type]};
    }
    private function datasubSupports(string $type):bool {
        return in_array($type,['vtu_airtime','vtu_data','vtu_cable_tv','vtu_electricity','vtu_epin','vtu_bulksms','vtu_data_card','vtu_recharge_card'],true);
    }
    private function purchaseProviders(Request $request,string $serviceType) {
        abort_unless(isset(config('vtu_services.service_types', [])[$serviceType]), 404);
        return response()->json(['status' => true, 'providers' => app(\App\Services\VtuProviderService::class)->listing($serviceType)]);
    }
    public function catalog(Request $request,string $serviceType) {
        abort_unless(isset(config('vtu_services.service_types',[])[$serviceType]),404);
        $id=$request->integer('provider_id') ?: (int) app(\App\Services\VtuProviderService::class)->providers($serviceType)->first()?->id;$products=[];
        if ($id && !app(\App\Services\VtuProviderService::class)->providers($serviceType)->contains('id', $id)) {
            return response()->json(['status' => false, 'message' => 'Only Datasub247 is available.', 'products' => []], 422);
        }
        if($serviceType==='vtu_data'){
            $products=app(VtuCatalogService::class)->dataPlans($id ?: null)
                ->map(fn($p)=>['code'=>(string)$p->id,'network'=>$p->network,'label'=>$p->data_plan,'validity'=>$p->validate,'amount'=>(float)$p->amount])->all();
        }elseif($serviceType==='vtu_epin'){
            $provider=$id?CustomApi::find($id):null;
            $datasub=$provider?->provider_identifier==='datasub247' || (!$provider && $this->vtuHub->getDatasub247()->getApiKey());
            foreach(app(EpinCatalogService::class)->all() as $p){
                if($datasub && !in_array($p['service_id'],['waec','neco','nabteb'],true))continue;
                $products[]=['code'=>$p['variation_code'],'service_id'=>$p['service_id'],'label'=>$p['label'],'amount'=>$p['amount']];
            }
        }elseif($serviceType!=='vtu_data'){
            $products=array_values(array_filter(app(VtuCatalogService::class)->products($serviceType),fn($p)=>empty($p['provider_id']) || (int)$p['provider_id']===$id));
        }
        return response()->json(['status'=>true,'products'=>$products]);
    }
    public function purchasePayload(Request $request,string $type):array {return $request->only(array_keys((array)config('vtu_services.service_types.'.$type.'.rules',[])));}
    public function quote(Request $request,string $serviceType){
        $result=$this->vtuHub->processRequest(['service_type'=>$serviceType,'quote_only'=>true,'provider_id'=>$request->input('provider_id'),'amount'=>(float)$request->input('amount'),'payload'=>$this->purchasePayload($request,$serviceType)]);
        return response()->json($result,($result['status']??false)?200:422);
    }
    private function purchaseResponse(Request $request,array $result){
        if($request->expectsJson() || $request->ajax())return response()->json($result,($result['pending']??false)?202:(($result['status']??false)?200:422));
        return back()->with(($result['status']??false)?'success':(($result['pending']??false)?'warning':'error'),$result['message'])->with('vtu_result',$result)->withInput();
    }
    private function purchase(Request $request,string $type){
        if($request->is('api/*') && !$request->filled('request_id') && !$request->header('Idempotency-Key'))return $this->purchaseResponse($request,['status'=>false,'message'=>'Provide a unique Idempotency-Key header or request_id for this purchase.']);
        $request->validate((array)config('vtu_services.service_types.'.$type.'.rules',[])+['provider_id'=>'nullable|integer|exists:custom_apis,id','request_id'=>'nullable|string|max:100']);
        if(in_array($type,['vtu_electricity','vtu_cable_tv'],true) && !$this->purchaseVerified($request,$type))return $this->purchaseResponse($request,['status'=>false,'message'=>'Verify this customer and band before payment.']);
        return $this->purchaseResponse($request,$this->vtuHub->processRequest(['service_type'=>$type,'provider_id'=>$request->input('provider_id'),'amount'=>(float)$request->input('amount'),'payload'=>$this->purchasePayload($request,$type)]));
    }
    private function verificationData(Request $request,string $type):array{
        $keys=$type==='vtu_electricity'?['serviceID','variation_code','meter_number']:['serviceID','smart_card_number'];
        return $request->only($keys)+['provider_id'=>(int)$request->input('provider_id',0),'user_id'=>(int)$request->user()->id];
    }
    private function purchaseVerified(Request $request,string $type):bool{
        $token=(string)$request->input('validation_token','');
        return $token!=='' && Cache::get('vtu-verify:'.$type.':'.$token)===$this->verificationData($request,$type);
    }
    private function verifyPurchaseCustomer(Request $request,string $type){
        $rules=(array)config('vtu_services.service_types.'.$type.'.validation_rules');$request->validate($rules+['provider_id'=>'nullable|integer|exists:custom_apis,id']);
        $result=$this->vtuHub->validateCustomer($type,$request->only(array_keys($rules)),$request->input('provider_id'));
        if(!($result['status']??false))return response()->json($result,422);
        $token=bin2hex(random_bytes(16));Cache::put('vtu-verify:'.$type.':'.$token,$this->verificationData($request,$type),900);
        return response()->json(['status'=>true,'message'=>'Customer verified.','validation_token'=>$token,'customer'=>$result['customer']??null]);
    }
    private function purchaseStatus(Request $request){
        $request->validate(['order_id'=>'required|string']);
        $tx=VtuTransaction::where('user_id',$request->user()->id)->where('transaction_id',$request->order_id)->firstOrFail();
        if($tx->created_at->lt(now()->subMinutes(2)))$tx=app(\App\Services\VtuReconciliationService::class)->reconcile($tx);
        return response()->json($this->vtuHub->transactionResult($tx));
    }
    private function purchaseWebhook(Request $request){
        if(!\App\Support\DatasubWebhookAuth::valid($request))return response()->json(['status'=>false,'message'=>'Invalid webhook credentials.'],401);
        $result=$this->vtuHub->getDatasub247()->handleWebhook($request->json()->all());return response()->json($result,($result['status']??false)?200:400);
    }
}
