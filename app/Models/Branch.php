<?php

namespace App\Models;

use App\Observers\BranchObserver;
use App\Traits\Organisationable;
use Filament\Models\Contracts\HasCurrentTenantLabel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Zap\Models\Concerns\HasSchedules;

#[ObservedBy(BranchObserver::class)]
class Branch extends Model implements HasCurrentTenantLabel
{
    /** @use HasFactory<\Database\Factories\BranchFactory> */
    use SoftDeletes, HasFactory, Organisationable, HasSchedules;

    protected $fillable = [
        'name',
        'address',
        'city',
        'work_schedule',
        'branch_mark',
        'postal_code',
        'active',
        'price_list_id'
    ];

    protected $casts = [
        'work_schedule' => 'array',
    ];

    public function fullAddress(): Attribute
    {
        return Attribute::make(function () {
            return implode(', ', [$this->address, $this->city, $this->zip_code]);
        });
    }

    public function getCurrentTenantLabel(): string
    {
        return 'Current branch';
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class, 'price_list_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'link_user_branches');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function appointmentRequests(): HasMany
    {
        return $this->hasMany(AppointmentRequest::class, 'branch_id');
    }
}
