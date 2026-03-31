<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotKnowledgeBase extends Model
{
    use HasFactory;

    protected $table = 'chatbot_knowledge_base';

    protected $fillable = [
        'source_file',
        'title',
        'content_chunk',
        'embedding', // Stored as JSON array or Vector type
        'category',
        'is_active',
        'last_trained_at'
    ];

    protected $casts = [
        'embedding' => 'array',
        'is_active' => 'boolean',
        'last_trained_at' => 'datetime',
    ];

    /**
     * Helper to compute cosine similarity (Basic PHP implementation if Vector DB extension is unavailable)
     */
    public static function cosineSimilarity(array $vec1, array $vec2): float
    {
        $dotProduct = 0;
        $norm1 = 0;
        $norm2 = 0;

        foreach ($vec1 as $i => $v1) {
            $v2 = $vec2[$i] ?? 0;
            $dotProduct += $v1 * $v2;
            $norm1 += $v1 * $v1;
            $norm2 += $v2 * $v2;
        }

        if ($norm1 == 0 || $norm2 == 0) return 0;
        return $dotProduct / (sqrt($norm1) * sqrt($norm2));
    }
}
