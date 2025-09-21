<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pin extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'label',
        'body',
        'banner',
        'moderated',
        'icon',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'moderated' => 'boolean',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
