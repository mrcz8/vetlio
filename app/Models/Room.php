<?php

namespace App\Models;

use App\Traits\Organisationable;
use Guava\Calendar\Contracts\Resourceable;
use Guava\Calendar\ValueObjects\CalendarResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Zap\Models\Concerns\HasSchedules;

class Room extends Model implements Resourceable
{
    /** @use HasFactory<\Database\Factories\RoomFactory> */
    use HasFactory, SoftDeletes, Organisationable, HasSchedules;

    protected $fillable = [
        'name',
        'code',
        'color',
        'branch_id',
        'active',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'link_service_rooms');
    }

    public function toCalendarResource(): CalendarResource
    {
        return CalendarResource::make($this->id)
            ->extendedProps([
                'avatar' => $this->avatar != null ? asset($this->avatar) : null,
                'title' => $this->code ?? '-',
                'name' => $this->name
            ])
            ->title($this->name);
    }
}
