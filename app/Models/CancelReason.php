<?php

namespace App\Models;

use App\Traits\Organisationable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CancelReason extends Model
{
    /** @use HasFactory<\Database\Factories\CancelReasonFactory> */
    use HasFactory, Organisationable, SoftDeletes;

    protected $fillable = [
        'name',
        'color',
        'active'
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Reservation::class, 'cancel_reason_id');
    }
}
