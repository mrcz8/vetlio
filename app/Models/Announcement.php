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
        'for_users',
        'for_clients',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'active' => 'boolean',
        'for_users' => 'boolean',
        'for_clients' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'reader', 'announcement_reads')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    public function clients(): MorphToMany
    {
        return $this->morphedByMany(Client::class, 'reader', 'announcement_reads')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        $now = now();

        return $query
            ->where('active', true)
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
