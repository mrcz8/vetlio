<?php

namespace App\Traits;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasAnnouncements
{
    public function announcements(): MorphToMany
    {
        return $this->morphToMany(Announcement::class, 'reader', 'announcement_reads')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    public function readAnnouncements(): MorphToMany
    {
        return $this->announcements()->whereNotNull('announcement_reads.read_at');
    }

    public function unreadAnnouncements()
    {
        return Announcement::query()
            ->active()
            ->when(
                $this instanceof User,
                fn($q) => $q->forUsers(),
                fn($q) => $q->forClients(),
            )
            ->where(function ($q) {
                $q->whereDoesntHave('users', function ($q) {
                    $q->where('reader_id', $this->id)
                        ->where('reader_type', static::class);
                })
                    ->whereDoesntHave('clients', function ($q) {
                        $q->where('reader_id', $this->id)
                            ->where('reader_type', static::class);
                    });
            });
    }

    public function markAnnouncementAsRead(Announcement $announcement): void
    {
        $this->announcements()->syncWithoutDetaching([
            $announcement->id => ['read_at' => now()],
        ]);
    }
}
