<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerificationResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AdminVerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = VerificationResult::query()->with(['user:id,fullname,email']);

        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('reference_id', 'like', '%' . $q . '%')
                    ->orWhere('identifier', 'like', '%' . $q . '%')
                    ->orWhere('provider_name', 'like', '%' . $q . '%')
                    ->orWhere('service_type', 'like', '%' . $q . '%');
            });
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $results = $query->latest()->paginate(20)->withQueryString();

        $serviceTypes = VerificationResult::query()
            ->select('service_type')
            ->distinct()
            ->orderBy('service_type')
            ->pluck('service_type');

        return view('admin.verifications.index', compact('results', 'serviceTypes'));
    }

    public function show(int $id)
    {
        $result = VerificationResult::with(['user:id,fullname,email'])->findOrFail($id);
        return view('admin.verifications.show', compact('result'));
    }

    public function report(int $id)
    {
        $result = VerificationResult::with(['user:id,fullname,email'])->findOrFail($id);
        $pdf = Pdf::loadView('pdf.verification_report', compact('result'));
        return $pdf->download('Verification_Report_' . $result->reference_id . '.pdf');
    }
}

