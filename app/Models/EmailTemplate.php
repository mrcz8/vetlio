<?php

namespace App\Models;

use App\Traits\Organisationable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use Organisationable, SoftDeletes;

    protected $fillable = [
        'name',
        'active',
        'organisation_id',
        'type_id',
        'group_id',
    ];

    protected static function booted()
    {
        parent::booted();

        static::deleted(function ($model) {
            $model->emailTemplateContents()->delete();
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function emailTemplateContents(): HasMany|EmailTemplate
    {
        return $this->hasMany(EmailTemplateContent::class);
    }
}
