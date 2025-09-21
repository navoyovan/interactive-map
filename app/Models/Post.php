<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',    
        'body',
        'latitude',
        'longitude',
        'image',
        'user_id',
        'moderated',
        'pin_id',
        'likes_count',
    ];

    public function pin()
    {
        return $this->belongsTo(Pin::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function isLikedBy($user)
    {
        if (!$user) return false;
        
        return $this->likes()->where('user_id', $user->id)->exists();
    }


    public function likes()
    {
        return $this->belongsToMany(User::class, 'likes')->withTimestamps();
    }

    public function toggleLike($user)
    {
        if ($this->likes()->where('user_id', $user->id)->exists()) {
            $this->likes()->detach($user->id);
            return false;
        } else {
            $this->likes()->attach($user->id);
            return true;
        }
    }

        /**
     * Scope to get posts with their counts
     */
    public function scopeWithCounts($query)
    {
        return $query->withCount(['likes', 'comments']);
    }

    /**
     * Scope to get only moderated posts
     */
    public function scopeModerated($query)
    {
        return $query->where('moderated', true);
    }

    /**
     * Scope to sort by latest
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope to sort by oldest
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    /**
     * Scope to sort by most liked
     */
    public function scopeMostLiked($query)
    {
        return $query->withCount('likes')
                    ->orderBy('likes_count', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Scope to sort by most commented
     */
    public function scopeMostCommented($query)
    {
        return $query->withCount(['comments as comments_count' => function ($q) {
                        $q->where('moderated', true);
                    }])
                    ->orderBy('comments_count', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get the moderated comments count
     */
    public function getModeratedCommentsCountAttribute()
    {
        return $this->comments()->where('moderated', true)->count();
    }

}
