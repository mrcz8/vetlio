<?php

namespace App\Models;

use App\Enums\PatientGender;
use App\Traits\Organisationable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Swindon\FilamentHashids\Traits\HasHashid;

class Patient extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory, SoftDeletes, Organisationable,HasHashid;

    protected $fillable = [
        'name',
        'photo',
        'color',
        'date_of_birth',
        'client_id',
        'gender_id',
        'species_id',
        'breed_id',
        'dangerous',
        'dangerous_note',
        'remarks',
        'allergies',
        'archived_at',
        'archived_by',
        'organisation_id',
        'archived_note',
    ];

    protected $casts = [
        'gender_id' => PatientGender::class,
        'date_of_birth' => 'date',
    ];

    public function description(): Attribute
    {
        return Attribute::make(function () {
            $this->loadMissing('species', 'breed');

            $age = $this->date_of_birth ? $this->date_of_birth->age . ' god.' : null;
            $dangerous = $this->dangerous ? 'opasan' : null;

            return implode(', ', [$this->name, $this->species->name, $this->breed->name, $age, $dangerous]);
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function medicalDocuments(): HasMany
    {
        return $this->hasMany(MedicalDocument::class, 'patient_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'related')->latest();
    }

    public function reminders(): MorphMany
    {
        return $this->morphMany(Reminder::class, 'related')->latest();
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'patient_id');
    }

    public function breed(): BelongsTo
    {
        return $this->belongsTo(Breed::class);
    }
}
