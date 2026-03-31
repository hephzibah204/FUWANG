<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomApi;
use App\Models\SmsCampaign;
use App\Models\SmsCampaignRecipient;
use App\Models\User;
use App\Services\SmsService;
use App\Services\UserTargeting\UserSegment;
use App\Services\UserTargeting\UserSegmentValidator;
use App\Jobs\Campaigns\StartSmsCampaignJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmsCampaignController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
    }

    public function index()
    {
        $campaigns = SmsCampaign::query()->latest()->paginate(30);
        return view('admin.sms_campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $providers = CustomApi::query()
            ->where('service_type', 'sms_gateway')
            ->orderBy('priority')
            ->get();

        return view('admin.sms_campaigns.create', compact('providers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'custom_api_id' => ['nullable', 'exists:custom_apis,id'],
            'sender_id' => ['nullable', 'string', 'max:20'],
            'message' => ['required', 'string', 'max:1000'],
            'audience_type' => ['required', 'in:all,phones,user_ids,segment'],
            'audience_value' => ['nullable', 'string'],
            'segment_json' => ['nullable', 'string'],
        ]);

        $audience = null;
        if ($request->audience_type === 'segment') {
            $audience = UserSegmentValidator::validateAndNormalize($request->segment_json);
        } elseif ($request->audience_type !== 'all') {
            $list = preg_split('/[\s,;]+/', (string) $request->audience_value, -1, PREG_SPLIT_NO_EMPTY);
            $audience = array_values(array_unique($list ?: []));
        }

        SmsCampaign::create([
            'admin_id' => Auth::guard('admin')->id(),
            'custom_api_id' => $request->custom_api_id,
            'name' => $request->name,
            'sender_id' => $request->sender_id,
            'message' => $request->message,
            'audience_type' => $request->audience_type,
            'audience' => $audience,
            'status' => 'draft',
        ]);

        return redirect()->route('admin.sms_campaigns.index')->with('success', 'SMS campaign created.');
    }

    public function send(SmsCampaign $smsCampaign)
    {
        if ($smsCampaign->status === 'sent') {
            return back()->with('error', 'Campaign already sent.');
        }
        if (!in_array($smsCampaign->status, ['draft', 'failed'], true)) {
            return back()->with('error', 'Campaign is already processing.');
        }

        StartSmsCampaignJob::dispatch($smsCampaign->id);
        $smsCampaign->update(['status' => 'queued']);

        return back()->with('success', 'SMS campaign queued.');
    }
}
