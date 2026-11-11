<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\LegalDrafting\LegalDraftingService;
use App\Services\LegalDrafting\LegalDraftRequest;

class AiController extends Controller
{
    /**
     * Handle incoming chat requests from the frontend AI assistant.
     * Uses Gemini or OpenAI when reachable, with an instant, intelligent local fallback engine.
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $user = auth()->user();
        $message = trim((string) $request->message);

        // 1. Attempt Gemini via GeminiService
        $reply = null;
        try {
            $gemini = new \App\Services\GeminiService();
            $aiResponse = $gemini->siteSupportChat($message);
            if (!empty($aiResponse['status']) && !empty($aiResponse['text'])) {
                $reply = trim((string) $aiResponse['text']);
            }
        } catch (\Throwable $e) {
            Log::info('AI Chat: Gemini service offline/timed out, attempting secondary fallback', ['error' => $e->getMessage()]);
        }

        // 2. Attempt OpenAI if configured and Gemini did not return a response
        if (empty($reply)) {
            $openaiKey = config('services.openai.key');
            if (!empty($openaiKey)) {
                try {
                    $systemPrompt = $this->buildSystemContext($user);
                    $reply = $this->callOpenAI($openaiKey, $systemPrompt, $message);
                } catch (\Throwable $e) {
                    Log::info('AI Chat: OpenAI service offline/timed out, proceeding to smart rule engine', ['error' => $e->getMessage()]);
                }
            }
        }

        // 3. Smart, comprehensive platform rule engine: ALWAYS provides a structured answer with markdown links
        if (empty($reply)) {
            $reply = $this->fallbackResponse($message, $user);
        }

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => $reply,
            'response' => $reply
        ]);
    }

    /**
     * Specialized method to draft legal documents based on provided data.
     */
    public function draftLegalDocument(array $data)
    {
        $type = (string) ($data['document_type'] ?? 'general');
        $category = (string) ($data['category'] ?? 'Miscellaneous');
        $formData = is_array($data['form_data'] ?? null) ? $data['form_data'] : [];

        $svc = app(LegalDraftingService::class);
        $res = $svc->draftHtml(new LegalDraftRequest($type, $category, $formData, 'You are a legal drafting AI specializing in Nigerian Law.'));
        if (!($res['ok'] ?? false)) {
            return "I'm currently experiencing a connection issue with my AI brain. Please try again later or visit the [Support Center](/tickets).";
        }
        return (string) $res['html'];
    }

    /**
     * Builds a comprehensive context about the platform and the current user.
     */
    private function buildSystemContext($user)
    {
        $appName = config('app.name');
        
        $context = "You are FuwaAI, an advanced and helpful assistant baked directly into the $appName platform. ";
        $context .= "Your primary goal is to assist the user with navigating the platform, explaining features, checking their status, and providing guidance.\n\n";

        if ($user) {
            $context .= "### CURRENT USER DATA\n";
            $context .= "- Name: {$user->fullname}\n";
            $context .= "- Email: {$user->email}\n";
            $context .= "- Wallet Balance: ₦" . number_format($user->balance ?? 0, 2) . "\n";
            $context .= "- Account Status: " . ($user->status == 1 ? 'Active' : 'Restricted') . "\n\n";
        }

        $context .= "### PLATFORM FEATURES & NAVIGATION\n";
        $context .= "You MUST direct users to the correct pages using format: [Page Name](/route). Here are the available services:\n";
        $context .= "- **Dashboard**: `/dashboard` (Overview of account)\n";
        $context .= "- **Notary & Legal Services**: `/services/notary` (Get documents legally notarized or drafted online)\n";
        $context .= "  - Categories: Business, Employment, Property, Financial, Personal, Digital/IP, and Education.\n";
        $context .= "- **Airtime Topup**: `/services/airtime`\n";
        $context .= "- **Data Purchase**: `/services/data`\n";
        $context .= "- **NIN Verification**: `/services/nin`\n";
        $context .= "- **BVN Verification**: `/services/bvn`\n";
        $context .= "- **Virtual Cards**: `/services/virtual-card`\n";
        $context .= "- **Agency Banking**: `/services/agency-banking`\n";
        $context .= "- **Auctions**: `/services/auctions`\n";
        $context .= "- **Logistics**: `/services/post-office`\n";
        $context .= "- **Ticketing**: `/services/ticketing`\n";
        $context .= "- **FX Exchange**: `/services/fx`\n";
        $context .= "- **Invoicing**: `/services/invoicing`\n";
        
        $context .= "\n### LEGAL DRAFTING CAPABILITIES\n";
        $context .= "You can help users understand and draft over 40 types of legal documents. ";
        $context .= "If a user asks about a specific document (e.g., 'How do I draft a tenancy agreement?'), explain the process and direct them to the [Notary Page](/services/notary).\n";

        $context .= "### TONE & PERSONALITY\n";
        $context .= "Be extremely professional, concise, and helpful. Use Markdown for formatting.\n";
        
        return $context;
    }

    /**
     * Abstraction to call LLM APIs.
     */
    private function generateResponse($systemPrompt, $userMessage)
    {
        $apiCenter = \App\Models\ApiCenter::first();
        $geminiKey = $apiCenter->gemini_api_key ?? config('services.gemini.key');
        $openaiKey = config('services.openai.key');

        try {
            if ($geminiKey) {
                return $this->callGemini($geminiKey, $systemPrompt, $userMessage);
            } elseif ($openaiKey) {
                return $this->callOpenAI($openaiKey, $systemPrompt, $userMessage);
            } else {
                // Fallback deterministic response if no AI keys are connected.
                return $this->fallbackResponse($userMessage);
            }
        } catch (\Exception $e) {
            Log::error('AI Error: ' . $e->getMessage());
            return "I'm currently experiencing a connection issue with my AI brain. Please try again later or visit the [Support Center](/tickets).";
        }
    }

    private function callGemini($apiKey, $systemPrompt, $userMessage)
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent?key={$apiKey}";
        
        $payload = [
            "system_instruction" => [
                "parts" => [
                    ["text" => $systemPrompt]
                ]
            ],
            "contents" => [
                [
                    "role" => "user",
                    "parts" => [
                        ["text" => $userMessage]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "maxOutputTokens" => 800
            ]
        ];

        $response = Http::timeout(6)->connectTimeout(3)->post($url, $payload);

        if ($response->successful()) {
            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? "Sorry, I couldn't formulate a response.";
        }

        throw new \Exception('Gemini API failed: ' . $response->body());
    }

    private function callOpenAI($apiKey, $systemPrompt, $userMessage)
    {
        $response = Http::timeout(6)->connectTimeout(3)->withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'temperature' => 0.7,
        ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        throw new \Exception('OpenAI API failed: ' . $response->body());
    }

    private function fallbackResponse(string $message, $user = null): string
    {
        $lower = strtolower($message);
        $firstName = $user?->fullname ? explode(' ', trim($user->fullname))[0] : 'there';
        $appName = config('app.name', 'Fuwa.NG');

        // 1. Greetings (checked first so greetings like "hello can you help" greet the user)
        if (str_contains($lower, 'hello') || str_contains($lower, 'hi') || str_contains($lower, 'hey') || str_contains($lower, 'good morning') || str_contains($lower, 'good afternoon') || str_contains($lower, 'good evening')) {
            return "Hello {$firstName}! I am your **{$appName} AI Assistant**.\n\nI can help you navigate the platform and perform tasks quickly:\n- **Identity**: [Verify NIN](/services/identity/nin) or [Verify BVN](/services/identity/bvn)\n- **VTU Services**: [Airtime](/services/vtu/airtime) & [Data](/services/vtu/data)\n- **Cards**: [Virtual Dollar Cards](/services/virtual-card)\n- **Legal**: [Draft Legal Documents](/services/notary)\n- **Wallet**: [Check Balance & Fund](/wallet/fund)\n\nWhat would you like assistance with today?";
        }

        // 2. Balance, Wallet, Funding
        if (str_contains($lower, 'balance') || str_contains($lower, 'wallet') || str_contains($lower, 'fund') || str_contains($lower, 'deposit')) {
            $userBal = 0.0;
            if ($user) {
                $userBal = (float) ($user->wallet_balance ?? ($user->balance?->user_balance ?? (is_numeric($user->balance) ? $user->balance : 0)));
            }
            $bal = number_format($userBal, 2);
            return "Your current wallet balance is **₦{$bal}**.\n\nYou can top up your wallet instantly using your Dedicated Virtual Account or gateway cards by visiting the [Fund Wallet Page](/wallet/fund) or from your [Dashboard](/dashboard).";
        }

        // 2. Airtime, Data, VTU
        if (str_contains($lower, 'airtime') || str_contains($lower, 'data') || str_contains($lower, 'vtu') || str_contains($lower, 'recharge') || str_contains($lower, 'mtn') || str_contains($lower, 'glo') || str_contains($lower, 'airtel')) {
            return "You can purchase instant Airtime and Data subscriptions at discounted agent rates!\n\n- **Airtime Topup**: [Buy Airtime](/services/vtu/airtime)\n- **Internet Data**: [Buy Data Bundles](/services/vtu/data)\n- **Airtime to Cash**: [Convert Airtime](/services/vtu/airtime-to-cash)\n- **Electricity & Bills**: [Pay Utility Bills](/services/vtu/electricity)";
        }

        // 3. NIN Verification & IPE Clearance
        if (str_contains($lower, 'nin') || str_contains($lower, 'nimc') || str_contains($lower, 'ipe') || str_contains($lower, 'slip')) {
            return "We provide official NIMC identification services:\n\n- **NIN Search & Verification**: Search via 11-digit NIN, phone number, or demographic details on the [NIN Verification Page](/services/identity/nin).\n- **IPE Clearance**: Fast-track biometric verification on the [IPE Clearance Page](/services/identity/biometric).\n- Download verified Standard and Premium NIN slips instantly from your transaction history.";
        }

        // 4. BVN Verification
        if (str_contains($lower, 'bvn') || str_contains($lower, 'bank verification')) {
            return "Perform automated BVN lookup and demographic identity matching in real time:\n\n- **BVN Verification**: Head over to the [BVN Verification Page](/services/identity/bvn).\nMake sure your wallet has sufficient balance to cover the query fee.";
        }

        // 5. Virtual Cards (USD / NGN)
        if (str_contains($lower, 'virtual card') || str_contains($lower, 'dollar card') || str_contains($lower, 'card') || str_contains($lower, 'usd')) {
            return "Need to pay for Apple, Netflix, Google, Spotify, or foreign store checkouts?\n\n- We offer instant **USD and NGN Virtual Cards**.\n- Check balances, fund cards, and manage limits directly on the [Virtual Cards Page](/services/virtual-card).";
        }

        // 6. Agents, Enrollment Agent Licensing & Roster
        if (str_contains($lower, 'agent') || str_contains($lower, 'license') || str_contains($lower, 'enrollment') || str_contains($lower, 'roster')) {
            return "Interested in operating as an Enrollment Agent on {$appName}?\n\n- **Register as an Agent**: Apply and submit your business credentials at [Agent Registration](/agent/register).\n- **Agent Management**: Verified agents access subsidized pricing and dedicated verification queues on the [Agent Portal](/agent/dashboard).";
        }

        // 7. Notary & Legal Drafting
        if (str_contains($lower, 'notary') || str_contains($lower, 'legal') || str_contains($lower, 'contract') || str_contains($lower, 'nda') || str_contains($lower, 'tenancy') || str_contains($lower, 'affidavit') || str_contains($lower, 'agreement') || str_contains($lower, 'draft')) {
            return "Our AI Legal Hub drafts and notarizes over 40 types of legally binding Nigerian agreements:\n\n- Non-Disclosure Agreements (NDAs)\n- Tenancy & Lease Agreements\n- Employment Contracts & Affidavits\n- Commercial Sale & Partnership Deeds\n\nVisit the [Notary & Legal Hub](/services/notary) to create and notarize documents with digital security seals.";
        }

        // 8. CAC & Business Verification
        if (str_contains($lower, 'cac') || str_contains($lower, 'company') || str_contains($lower, 'business registration')) {
            return "Verify registered Nigerian corporate entities and verify company directors instantly:\n\n- **Corporate Registry**: [Verify CAC Records](/services/identity/cac).";
        }

        // 9. WAEC & Education
        if (str_contains($lower, 'waec') || str_contains($lower, 'exam') || str_contains($lower, 'scratch card') || str_contains($lower, 'pin')) {
            return "Purchase official educational tokens with instant digital PIN generation:\n\n- **WAEC**: [WAEC Result Checker & Registration PINs](/services/education/waec).";
        }

        // 10. Logistics & Deliveries
        if (str_contains($lower, 'logistics') || str_contains($lower, 'post') || str_contains($lower, 'delivery') || str_contains($lower, 'courier') || str_contains($lower, 'shipping')) {
            return "Need doorstep delivery or inter-state parcels handled?\n\n- Book and track consignments through the [Logistics & Post Office Hub](/services/post-office).";
        }

        // 11. Support, Tickets, Help
        if (str_contains($lower, 'support') || str_contains($lower, 'help') || str_contains($lower, 'human') || str_contains($lower, 'ticket') || str_contains($lower, 'contact') || str_contains($lower, 'issue') || str_contains($lower, 'problem')) {
            return "Our customer support team is available 24/7 to assist you!\n\n- **Submit a Ticket**: Open an inquiry on the [Support Center](/tickets).\n- **Direct Email**: You can reach our team at [support@fuwa.ng](mailto:support@fuwa.ng).";
        }

        // 12. General Catch-All
        return "I am your **{$appName} AI Assistant**. How can I help you?\n\nYou can ask me about:\n- Checking your **[Wallet Balance](/dashboard)** or **[Funding Your Account](/wallet/fund)**\n- Purchasing **[Airtime](/services/vtu/airtime)** or **[Internet Data](/services/vtu/data)**\n- Verifying **[NIN Details](/services/identity/nin)** or **[BVN Match](/services/identity/bvn)**\n- Getting a **[Virtual Dollar Card](/services/virtual-card)**\n- Generating **[Legal & Notary Documents](/services/notary)**\n- Registering as an **[Enrollment Agent](/agent/register)**\n\nOr contact a human representative at our **[Support Center](/tickets)**.";
    }
}
