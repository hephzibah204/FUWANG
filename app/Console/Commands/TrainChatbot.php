<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ChatbotKnowledgeBase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use App\Models\CustomApi;
use App\Models\ApiCenter;

class TrainChatbot extends Command
{
    protected $signature = 'chatbot:train';
    protected $description = 'Extract documentation and populate the Chatbot Knowledge Base (Vector DB)';

    public function handle()
    {
        $this->info('Starting Chatbot Training Pipeline...');

        $docsPath = base_path('docs');
        $files = File::glob($docsPath . '/*.md');
        $files[] = base_path('README.md');
        $files[] = base_path('PROJECT_PROPOSAL.md');

        $apiKey = $this->getGeminiApiKey();

        if (!$apiKey) {
            $this->error('Gemini API Key not found. Cannot generate embeddings.');
            return Command::FAILURE;
        }

        foreach ($files as $file) {
            if (!File::exists($file)) continue;

            $this->info("Processing $file...");
            $content = File::get($file);
            $filename = basename($file);
            
            // Basic chunking: split by paragraphs or headings
            $chunks = $this->chunkText($content, 1000);

            foreach ($chunks as $index => $chunk) {
                if (trim($chunk) === '') continue;

                $embedding = $this->generateEmbedding($chunk, $apiKey);

                ChatbotKnowledgeBase::updateOrCreate(
                    [
                        'source_file' => $filename,
                        'title' => "$filename - Chunk $index"
                    ],
                    [
                        'content_chunk' => $chunk,
                        'embedding' => $embedding,
                        'category' => 'documentation',
                        'is_active' => true,
                        'last_trained_at' => now(),
                    ]
                );
            }
        }

        $this->info('Chatbot Training Pipeline completed successfully.');
        return Command::SUCCESS;
    }

    private function chunkText($text, $maxLength = 1000)
    {
        // Simple chunking strategy
        $paragraphs = explode("\n\n", $text);
        $chunks = [];
        $currentChunk = '';

        foreach ($paragraphs as $p) {
            if (strlen($currentChunk) + strlen($p) > $maxLength) {
                $chunks[] = $currentChunk;
                $currentChunk = $p;
            } else {
                $currentChunk .= "\n\n" . $p;
            }
        }
        if ($currentChunk !== '') {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }

    private function generateEmbedding($text, $apiKey)
    {
        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/embedding-001:embedContent?key={$apiKey}", [
                'model' => 'models/embedding-001',
                'content' => [
                    'parts' => [
                        ['text' => $text]
                    ]
                ]
            ]);

            if ($response->successful()) {
                return $response->json()['embedding']['values'] ?? null;
            }
            return null;
        } catch (\Exception $e) {
            $this->error('Failed to generate embedding: ' . $e->getMessage());
            return null;
        }
    }

    private function getGeminiApiKey()
    {
        $customApi = CustomApi::where('service_type', 'gemini_ai')->where('status', true)->first();
        if ($customApi) return $customApi->api_key;

        $apiCenter = ApiCenter::first();
        return $apiCenter->gemini_api_key ?? null;
    }
}
