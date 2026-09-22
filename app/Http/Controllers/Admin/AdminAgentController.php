<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentAgent;
use Illuminate\Http\Request;

class AdminAgentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = EnrollmentAgent::with('user')->latest();

        if (in_array($status, ['pending', 'approved', 'rejected', 'suspended'], true)) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('nin', 'like', "%{$search}%")
                  ->orWhere('bvn', 'like', "%{$search}%")
                  ->orWhere('machine_imei', 'like', "%{$search}%");
            });
        }

        $agents = $query->paginate(20)->withQueryString();

        $counts = [
            'total' => EnrollmentAgent::count(),
            'pending' => EnrollmentAgent::where('status', 'pending')->count(),
            'approved' => EnrollmentAgent::where('status', 'approved')->count(),
            'rejected' => EnrollmentAgent::where('status', 'rejected')->count(),
            'suspended' => EnrollmentAgent::where('status', 'suspended')->count(),
        ];

        return view('admin.agents.index', compact('agents', 'counts', 'status'));
    }

    public function show($id)
    {
        $agent = EnrollmentAgent::with('user')->findOrFail($id);

        return view('admin.agents.show', compact('agent'));
    }

    public function approve($id)
    {
        $agent = EnrollmentAgent::findOrFail($id);
        $agent->update([
            'status' => 'approved',
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', "Agent {$agent->full_name} has been approved successfully.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $agent = EnrollmentAgent::findOrFail($id);
        $agent->update([
            'status' => 'rejected',
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        return back()->with('success', "Agent {$agent->full_name} application has been rejected.");
    }

    public function suspend($id)
    {
        $agent = EnrollmentAgent::findOrFail($id);
        $agent->update(['status' => 'suspended']);

        return back()->with('success', "Agent {$agent->full_name} has been suspended.");
    }

    public function reactivate($id)
    {
        $agent = EnrollmentAgent::findOrFail($id);
        $agent->update(['status' => 'approved']);

        return back()->with('success', "Agent {$agent->full_name} status reactivated to approved.");
    }

    public function leaderboard()
    {
        $approvedAgents = EnrollmentAgent::where('status', 'approved')
            ->orderBy('monthly_enrollments', 'desc')
            ->orderBy('total_enrollments', 'desc')
            ->get();

        $currentMva = EnrollmentAgent::where('is_mva_of_month', true)->first();

        return view('admin.agents.leaderboard', compact('approvedAgents', 'currentMva'));
    }

    public function publishLeaderboard(Request $request)
    {
        $request->validate([
            'mva_agent_id' => ['nullable', 'exists:enrollment_agents,id'],
        ]);

        // Reset all MVA flags
        EnrollmentAgent::query()->update(['is_mva_of_month' => false]);

        if ($mvaId = $request->input('mva_agent_id')) {
            $mvaAgent = EnrollmentAgent::find($mvaId);
            if ($mvaAgent) {
                $mvaAgent->update(['is_mva_of_month' => true]);
            }
        }

        return back()->with('success', 'Agent Leaderboard & Most Valuable Agent (MVA) of the Month updated and published!');
    }

    public function showUploadPreApproved()
    {
        $preApprovedCount = \App\Models\PreApprovedAgent::count();
        $claimedCount = \App\Models\PreApprovedAgent::where('is_claimed', true)->count();
        $unclaimedCount = \App\Models\PreApprovedAgent::where('is_claimed', false)->count();

        $recent = \App\Models\PreApprovedAgent::latest()->take(20)->get();

        return view('admin.agents.upload_preapproved', compact('preApprovedCount', 'claimedCount', 'unclaimedCount', 'recent'));
    }

    public function processUploadPreApproved(Request $request)
    {
        $request->validate([
            'agent_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $file = $request->file('agent_file');
        $path = $file->getRealPath();

        try {
            $pythonScript = <<<PYTHON
import openpyxl, json, sys, csv

filePath = r"{$path}"
output = []

if filePath.endswith('.csv'):
    with open(filePath, mode='r', encoding='utf-8-sig') as f:
        reader = csv.reader(f)
        rows = list(reader)
else:
    wb = openpyxl.load_workbook(filePath)
    sheet = wb.active
    rows = list(sheet.iter_rows(values_only=True))

header_idx = -1
for idx, row in enumerate(rows[:10]):
    row_str = " ".join([str(c) for c in row if c is not None]).lower()
    if 'agent code' in row_str or 'phone' in row_str or 'email' in row_str:
        header_idx = idx
        break

start_row = header_idx + 1 if header_idx != -1 else 0

for row in rows[start_row:]:
    if not row or len(row) < 3:
        continue
    
    first_name = str(row[1]).strip() if len(row) > 1 and row[1] else ''
    last_name = str(row[2]).strip() if len(row) > 2 and row[2] else ''
    email = str(row[3]).strip().lower() if len(row) > 3 and row[3] else ''
    phone = str(row[4]).strip() if len(row) > 4 and row[4] else ''
    code = str(row[5]).strip() if len(row) > 5 and row[5] else ''

    if code and code.lower() != 'agent code':
        output.append({
            'first_name': first_name,
            'last_name': last_name,
            'email': email,
            'phone_number': phone,
            'agent_code': code
        })

print(json.dumps(output))
PYTHON;

            $tmpScript = sys_get_temp_dir() . '/parse_upload_' . time() . '.py';
            file_put_contents($tmpScript, $pythonScript);

            $output = shell_exec("python " . escapeshellarg($tmpScript));
            @unlink($tmpScript);

            $data = json_decode($output, true);

            if (!is_array($data) || empty($data)) {
                return back()->with('error', 'Could not parse records from uploaded file. Please check Excel headers.');
            }

            $count = 0;
            foreach ($data as $item) {
                $fullName = trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''));

                \App\Models\PreApprovedAgent::updateOrCreate(
                    ['agent_code' => $item['agent_code']],
                    [
                        'first_name' => $item['first_name'],
                        'last_name' => $item['last_name'],
                        'full_name' => $fullName,
                        'email' => $item['email'],
                        'phone_number' => $item['phone_number'],
                    ]
                );
                $count++;
            }

            return back()->with('success', "Successfully imported {$count} pre-approved existing enrollment agents.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }
}
