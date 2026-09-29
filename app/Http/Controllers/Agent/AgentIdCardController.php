<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentAgent;
use App\Support\QrCodeDataUri;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentIdCardController extends Controller
{
    /**
     * Preview the official Enrollment Agent Digital ID Card
     */
    public function show(Request $request)
    {
        $agent = Auth::user()?->enrollmentAgent;

        if (!$agent || !$agent->isApproved()) {
            abort(403, 'Active Enrollment Agent authorization required.');
        }

        $code = $agent->company_agent_code ?: ('AG-' . str_pad((string)$agent->id, 5, '0', STR_PAD_LEFT));
        $verifyUrl = route('agent.id_card.verify', ['code' => $code]);
        $qrCode = QrCodeDataUri::make($verifyUrl, 160);

        $issueDate = $agent->approved_at ? $agent->approved_at->format('d/m/Y') : now()->format('d/m/Y');
        $expiryDate = $agent->approved_at ? $agent->approved_at->copy()->addYear()->format('d/m/Y') : now()->addYear()->format('d/m/Y');

        return view('agent.id_card', compact('agent', 'code', 'verifyUrl', 'qrCode', 'issueDate', 'expiryDate'));
    }

    /**
     * Download the official printable PDF ID Card
     */
    public function downloadPdf(Request $request)
    {
        $agent = Auth::user()?->enrollmentAgent;

        if (!$agent || !$agent->isApproved()) {
            abort(403, 'Active Enrollment Agent authorization required.');
        }

        $code = $agent->company_agent_code ?: ('AG-' . str_pad((string)$agent->id, 5, '0', STR_PAD_LEFT));
        $verifyUrl = route('agent.id_card.verify', ['code' => $code]);
        $qrCode = QrCodeDataUri::make($verifyUrl, 160);

        $issueDate = $agent->approved_at ? $agent->approved_at->format('d/m/Y') : now()->format('d/m/Y');
        $expiryDate = $agent->approved_at ? $agent->approved_at->copy()->addYear()->format('d/m/Y') : now()->addYear()->format('d/m/Y');

        $pdf = Pdf::loadView('agent.id_card_pdf', compact('agent', 'code', 'verifyUrl', 'qrCode', 'issueDate', 'expiryDate'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("Agent_ID_Card_{$code}.pdf");
    }

    /**
     * Public QR Code Verification endpoint for field law enforcement or customers
     */
    public function verify(string $code)
    {
        $agent = EnrollmentAgent::where('company_agent_code', $code)
            ->orWhere('id', is_numeric($code) ? (int)$code : 0)
            ->first();

        $isValid = $agent && $agent->isApproved();

        return view('agent.verify_card', compact('agent', 'code', 'isValid'));
    }
}
