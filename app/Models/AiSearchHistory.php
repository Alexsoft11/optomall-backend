<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiSearchHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prompt',
        'generated_keywords',
        'items_provider',
        'ai_model',
        'feedback_like',
        'feedback_comment',
        'input_tokens',
        'output_tokens',
        'total_tokens',
    ];

    protected $casts = [
        'generated_keywords' => 'array',
        'feedback_like' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
