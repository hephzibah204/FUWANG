<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogisticsRequest;
use App\Models\NotaryRequest;
use App\Models\ServiceInvoice;
use Illuminate\Http\Request;

class AdminOperationsController extends Controller
{
    public function invoices(Request $request)
    {
        $query = ServiceInvoice::query()->with(['user:id,fullname,email']);

        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('invoice_number', 'like', '%' . $q . '%')
                    ->orWhere('client_name', 'like', '%' . $q . '%')
                    ->orWhere('client_email', 'like', '%' . $q . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->paginate(20)->withQueryString();

        return view('admin.operations.invoices', compact('invoices'));
    }

    public function updateInvoiceStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:draft,sent,paid,overdue,cancelled'],
        ]);

        $invoice = ServiceInvoice::findOrFail($id);
        $invoice->status = $request->status;
        $invoice->save();

        return back()->with('success', 'Invoice status updated.');
    }

    public function logistics(Request $request)
    {
        $query = LogisticsRequest::query()->with(['user:id,fullname,email']);

        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('tracking_id', 'like', '%' . $q . '%')
                    ->orWhere('sender_name', 'like', '%' . $q . '%')
                    ->orWhere('recipient_name', 'like', '%' . $q . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $shipments = $query->latest()->paginate(20)->withQueryString();

        return view('admin.operations.logistics', compact('shipments'));
    }

    public function updateLogisticsStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:processing,in_transit,delivered,completed,cancelled'],
        ]);

        $shipment = LogisticsRequest::findOrFail($id);
        $shipment->status = $request->status;
        $shipment->save();

        return back()->with('success', 'Shipment status updated.');
    }

    public function notary(Request $request)
    {
        $query = NotaryRequest::query()->with(['user:id,fullname,email']);

        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('reference', 'like', '%' . $q . '%')
                    ->orWhere('document_type', 'like', '%' . $q . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(20)->withQueryString();

        return view('admin.operations.notary', compact('requests'));
    }

    public function updateNotaryStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:draft,pending_stamp,completed,cancelled'],
        ]);

        $notary = NotaryRequest::findOrFail($id);
        $notary->status = $request->status;
        $notary->save();

        return back()->with('success', 'Notary status updated.');
    }
}

