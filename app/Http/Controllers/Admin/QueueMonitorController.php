<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\SmsCampaign;
use Illuminate\Support\Facades\DB;

class QueueMonitorController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
    }

    public function index()
    {
        $jobQueues = DB::table('jobs')
            ->selectRaw('queue, COUNT(*) as total, MIN(created_at) as oldest_created_at')
            ->groupBy('queue')
            ->orderByDesc('total')
            ->get();

        $failedQueues = DB::table('failed_jobs')
            ->selectRaw('queue, COUNT(*) as total, MAX(failed_at) as last_failed_at')
            ->groupBy('queue')
            ->orderByDesc('total')
            ->get();

        $recentFailed = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->limit(25)
            ->get(['id', 'uuid', 'queue', 'failed_at']);

        $batches = DB::table('job_batches')
            ->orderByDesc('created_at')
            ->limit(25)
            ->get();

        $emailCampaigns = EmailCampaign::query()->orderByDesc('id')->limit(10)->get(['id', 'name', 'status', 'batch_id', 'recipient_count', 'delivered_count', 'failed_count', 'sent_at']);
        $smsCampaigns = SmsCampaign::query()->orderByDesc('id')->limit(10)->get(['id', 'name', 'status', 'batch_id', 'recipient_count', 'delivered_count', 'failed_count', 'sent_at']);

        return view('admin.queue.index', compact('jobQueues', 'failedQueues', 'recentFailed', 'batches', 'emailCampaigns', 'smsCampaigns'));
    }
}

