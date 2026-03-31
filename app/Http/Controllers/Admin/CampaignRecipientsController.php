<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\Campaigns\RetryEmailCampaignFailedJob;
use App\Jobs\Campaigns\RetrySmsCampaignFailedJob;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\SmsCampaign;
use App\Models\SmsCampaignRecipient;
use Illuminate\Http\Request;

class CampaignRecipientsController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
    }

    public function emailRecipients(EmailCampaign $emailCampaign, Request $request)
    {
        $status = (string) $request->query('status', '');
        $q = trim((string) $request->query('q', ''));

        $recipients = EmailCampaignRecipient::query()
            ->where('email_campaign_id', $emailCampaign->id)
            ->when($status !== '', fn ($qq) => $qq->where('status', $status))
            ->when($q !== '', fn ($qq) => $qq->where('email', 'like', '%' . $q . '%'))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $counts = EmailCampaignRecipient::query()
            ->selectRaw("status, COUNT(*) as c")
            ->where('email_campaign_id', $emailCampaign->id)
            ->groupBy('status')
            ->pluck('c', 'status')
            ->toArray();

        return view('admin.email_campaigns.recipients', compact('emailCampaign', 'recipients', 'status', 'q', 'counts'));
    }

    public function emailRecipientsExport(EmailCampaign $emailCampaign, Request $request)
    {
        $status = (string) $request->query('status', '');
        $q = trim((string) $request->query('q', ''));

        $query = EmailCampaignRecipient::query()
            ->where('email_campaign_id', $emailCampaign->id)
            ->when($status !== '', fn ($qq) => $qq->where('status', $status))
            ->when($q !== '', fn ($qq) => $qq->where('email', 'like', '%' . $q . '%'))
            ->orderBy('id');

        $filename = 'email_campaign_' . $emailCampaign->id . '_recipients.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'email', 'status', 'attempts', 'sent_at', 'provider_message_id', 'provider_response']);
            $query->chunkById(1000, function ($chunk) use ($out) {
                foreach ($chunk as $r) {
                    fputcsv($out, [
                        $r->id,
                        $r->email,
                        $r->status,
                        $r->attempts,
                        optional($r->sent_at)->format('Y-m-d H:i:s'),
                        $r->provider_message_id,
                        is_string($r->provider_response) ? preg_replace("/\r\n|\r|\n/", ' ', $r->provider_response) : '',
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function retryEmailFailed(EmailCampaign $emailCampaign)
    {
        RetryEmailCampaignFailedJob::dispatch($emailCampaign->id);
        return back()->with('success', 'Retry queued for failed recipients.');
    }

    public function smsRecipients(SmsCampaign $smsCampaign, Request $request)
    {
        $status = (string) $request->query('status', '');
        $q = trim((string) $request->query('q', ''));

        $recipients = SmsCampaignRecipient::query()
            ->where('sms_campaign_id', $smsCampaign->id)
            ->when($status !== '', fn ($qq) => $qq->where('status', $status))
            ->when($q !== '', fn ($qq) => $qq->where('phone', 'like', '%' . $q . '%'))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $counts = SmsCampaignRecipient::query()
            ->selectRaw("status, COUNT(*) as c")
            ->where('sms_campaign_id', $smsCampaign->id)
            ->groupBy('status')
            ->pluck('c', 'status')
            ->toArray();

        return view('admin.sms_campaigns.recipients', compact('smsCampaign', 'recipients', 'status', 'q', 'counts'));
    }

    public function smsRecipientsExport(SmsCampaign $smsCampaign, Request $request)
    {
        $status = (string) $request->query('status', '');
        $q = trim((string) $request->query('q', ''));

        $query = SmsCampaignRecipient::query()
            ->where('sms_campaign_id', $smsCampaign->id)
            ->when($status !== '', fn ($qq) => $qq->where('status', $status))
            ->when($q !== '', fn ($qq) => $qq->where('phone', 'like', '%' . $q . '%'))
            ->orderBy('id');

        $filename = 'sms_campaign_' . $smsCampaign->id . '_recipients.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'phone', 'status', 'attempts', 'sent_at', 'provider_message_id', 'provider_response']);
            $query->chunkById(1000, function ($chunk) use ($out) {
                foreach ($chunk as $r) {
                    fputcsv($out, [
                        $r->id,
                        $r->phone,
                        $r->status,
                        $r->attempts,
                        optional($r->sent_at)->format('Y-m-d H:i:s'),
                        $r->provider_message_id,
                        is_string($r->provider_response) ? preg_replace("/\r\n|\r|\n/", ' ', $r->provider_response) : '',
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function retrySmsFailed(SmsCampaign $smsCampaign)
    {
        RetrySmsCampaignFailedJob::dispatch($smsCampaign->id);
        return back()->with('success', 'Retry queued for failed recipients.');
    }
}
