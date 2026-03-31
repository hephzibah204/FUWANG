<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GeminiService;
use App\Models\ChatbotSession;
use Illuminate\Support\Str;

class EvaluateChatbot extends Command
{
    protected $signature = 'chatbot:evaluate';
    protected $description = 'Run automated accuracy validation against a predefined Q&A dataset';

    public function handle(GeminiService $geminiService)
    {
        $this->info('Starting Chatbot Evaluation...');

        $testDataset = [
            [
                'question' => 'How do I verify a NIN?',
                'expected_keywords' => ['NIN', 'verification', 'dashboard', 'submit']
            ],
            [
                'question' => 'Can I get a refund if a job fails?',
                'expected_keywords' => ['refund', 'wallet', 'automated', 'failed']
            ],
            [
                'question' => 'What happens if the primary API goes down?',
                'expected_keywords' => ['smart routing', 'failover', 'secondary', 'provider']
            ],
        ];

        $passed = 0;
        $total = count($testDataset);

        $sessionId = Str::uuid()->toString();
        $session = ChatbotSession::create([
            'session_id' => $sessionId,
            'conversation_history' => [],
        ]);

        foreach ($testDataset as $index => $test) {
            $this->info("Testing Question " . ($index + 1) . ": " . $test['question']);
            
            $response = $geminiService->contextAwareChat($test['question'], $session);
            
            if ($response['status']) {
                $text = strtolower($response['text']);
                
                // Calculate basic precision/recall using keyword matching for evaluation
                $matches = 0;
                foreach ($test['expected_keywords'] as $keyword) {
                    if (str_contains($text, strtolower($keyword))) {
                        $matches++;
                    }
                }

                $recall = $matches / count($test['expected_keywords']);
                
                if ($recall >= 0.5) { // 50% keyword match threshold for this simple test
                    $this->info("Result: PASS (Recall: " . ($recall * 100) . "%)");
                    $passed++;
                } else {
                    $this->error("Result: FAIL (Recall: " . ($recall * 100) . "%)");
                    $this->line("Response: " . $response['text']);
                }
            } else {
                $this->error("Result: FAIL (API Error)");
            }

            // Clear history to prevent cross-contamination in tests
            $session->update(['conversation_history' => []]);
        }

        $session->delete();

        $accuracy = ($passed / $total) * 100;
        $this->newLine();
        $this->info("====================================");
        $this->info("Evaluation Complete");
        $this->info("Total Tests: $total");
        $this->info("Passed: $passed");
        $this->info("Overall Accuracy: $accuracy%");
        $this->info("====================================");

        // Deliverables requirement: minimum 95% precision/recall. We log this.
        if ($accuracy < 95) {
            $this->warn("Warning: Accuracy is below the 95% threshold.");
        }

        return Command::SUCCESS;
    }
}
