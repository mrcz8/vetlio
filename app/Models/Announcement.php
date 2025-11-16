<?php

namespace App\Models;

use App\Traits\Organisationable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    /** @use HasFactory<\Database\Factories\AnnouncementFactory> */
    use HasFactory, Organisationable, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'for_users',
        'for_clients',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'for_users' => 'boolean',
        'for_clients' => 'boolean',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($announcement) {
            $announcement->user_id = auth()->id();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dismissedByUsers(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'dismissable', 'dismissed_announcements')
            ->withPivot(['read_at'])
            ->withTimestamps();
    }

    public function dismissedByClients(): MorphToMany
    {
        return $this->morphedByMany(Client::class, 'dismissable', 'dismissed_announcements')
            ->withPivot(['read_at'])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        $now = now();

        return $query
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    public function scopeForUsers($query)
    {
        return $query->where('for_users', true);
    }

    public function scopeForClients($query)
    {
        return $query->where('for_clients', true);
    }
}
