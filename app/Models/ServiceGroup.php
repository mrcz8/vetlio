<?php

namespace App\Models;

use App\Traits\Organisationable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceGroup extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceGroupFactory> */
    use HasFactory, SoftDeletes, Organisationable;

    protected $fillable = [
        'name',
        'color',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'service_group_id');
    }
}
