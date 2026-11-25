<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Service\Concerns\HandlesVtuPurchases;
use Illuminate\Http\Request;

/** Shared purchase flows; the parent retains the education and hub entry points. */
class VtuPurchaseController extends VTUController
{
    use HandlesVtuPurchases;

    public function airtimeIndex() { return $this->purchasePage('vtu_airtime', 'services.vtu.airtime.buy'); }
    public function dataIndex() { return $this->purchasePage('vtu_data', 'services.vtu.data.buy'); }
    public function cableIndex() { return $this->purchasePage('vtu_cable_tv', 'services.vtu.cable.buy'); }
    public function electricityIndex() { return $this->purchasePage('vtu_electricity', 'services.vtu.electricity.buy'); }
    public function internetIndex() { return $this->purchasePage('vtu_internet', 'services.vtu.internet.buy'); }
    public function bettingIndex() { return $this->purchasePage('vtu_betting', 'services.vtu.betting.fund'); }
    public function airtimeToCashIndex() { return $this->purchasePage('vtu_airtime_to_cash', 'services.vtu.airtime_to_cash.submit'); }
    public function bulkSmsIndex() { return $this->purchasePage('vtu_bulksms', 'services.vtu.bulksms.send'); }
    public function rechargePrintingIndex()
    {
        return $this->purchasePage(request()->query('type') === 'data' ? 'vtu_data_card' : 'vtu_recharge_card', 'services.vtu.recharge_printing.generate');
    }
    public function buyAirtime(Request $request) { return $this->purchase($request, 'vtu_airtime'); }
    public function buyData(Request $request) { return $this->purchase($request, 'vtu_data'); }
    public function buyCable(Request $request) { return $this->purchase($request, 'vtu_cable_tv'); }
    public function buyElectricity(Request $request) { return $this->purchase($request, 'vtu_electricity'); }
    public function buyInternet(Request $request) { return $this->purchase($request, 'vtu_internet'); }
    public function fundBetting(Request $request) { return $this->purchase($request, 'vtu_betting'); }
    public function submitAirtimeToCash(Request $request) { return $this->purchase($request, 'vtu_airtime_to_cash'); }
    public function sendBulkSms(Request $request) { return $this->purchase($request, 'vtu_bulksms'); }
    public function generateRechargePins(Request $request)
    {
        $request->validate(['pin_type' => 'required|in:airtime,data']);
        $request->merge(['card_name' => (string) ($request->user()->username ?: config('app.name'))]);
        return $this->purchase($request, $request->pin_type === 'data' ? 'vtu_data_card' : 'vtu_recharge_card');
    }
    public function validateCable(Request $request) { return $this->verifyPurchaseCustomer($request, 'vtu_cable_tv'); }
    public function validateElectricity(Request $request) { return $this->verifyPurchaseCustomer($request, 'vtu_electricity'); }
    public function providers(Request $request, string $serviceType) { return $this->purchaseProviders($request, $serviceType); }
    public function queryRechargeOrder(Request $request) { return $this->purchaseStatus($request); }
    public function webhookDatasub(Request $request) { return $this->purchaseWebhook($request); }
    public function fetchDatabundlePlans()
    {
        return response()->json(['status' => true, 'data' => app(\App\Services\VtuCatalogService::class)->products('vtu_data_card')]);
    }
    protected function respondWithVtuResult(Request $request, $result, ?string $fallbackRoute = null, int $statusHttp = 200)
    {
        return $this->purchaseResponse($request, (array) $result);
    }
}
