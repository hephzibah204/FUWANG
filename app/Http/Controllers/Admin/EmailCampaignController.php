<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\EmailCampaignMail;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\User;
use App\Services\UserTargeting\UserSegment;
use App\Services\UserTargeting\UserSegmentValidator;
use App\Jobs\Campaigns\StartEmailCampaignJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EmailCampaignController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
    }

    public function index()
    {
        $campaigns = EmailCampaign::query()->latest()->paginate(30);
        return view('admin.email_campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('admin.email_campaigns.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'html' => ['required', 'string'],
            'audience_type' => ['required', 'in:all,emails,user_ids,segment'],
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

        EmailCampaign::create([
            'admin_id' => Auth::guard('admin')->id(),
            'name' => $request->name,
            'subject' => $request->subject,
            'html' => $request->html,
            'audience_type' => $request->audience_type,
            'audience' => $audience,
            'status' => 'draft',
        ]);

        return redirect()->route('admin.email_campaigns.index')->with('success', 'Email campaign created.');
    }

    public function send(EmailCampaign $emailCampaign)
    {
        if ($emailCampaign->status === 'sent') {
            return back()->with('error', 'Campaign already sent.');
        }
        if (!in_array($emailCampaign->status, ['draft', 'failed'], true)) {
            return back()->with('error', 'Campaign is already processing.');
        }

        StartEmailCampaignJob::dispatch($emailCampaign->id);
        $emailCampaign->update(['status' => 'queued']);

        return back()->with('success', 'Email campaign queued.');
    }
}
