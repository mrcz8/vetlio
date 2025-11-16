<?php

namespace App\Models;

use App\Contracts\TaskRelated;
use App\Observers\MedicalDocumentObserver;
use App\Traits\AddedByCurrentUser;
use App\Traits\Organisationable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Swindon\FilamentHashids\Traits\HasHashid;

#[ObservedBy(MedicalDocumentObserver::class)]
class MedicalDocument extends Model implements HasMedia, TaskRelated
{
    /** @use HasFactory<\Database\Factories\MedicalDocumentFactory> */
    use HasFactory, SoftDeletes, Organisationable, AddedByCurrentUser, InteractsWithMedia, HasHashid;

    protected $fillable = [
        'code',
        'sequence',
        'reservation_id',
        'price_list_id',
        'patient_id',
        'client_id',
        'diagnosis',
        'content',
        'locked_at',
        'locked_user_id',
        'reason_for_coming',
        'service_provider_id',
    ];

    protected $casts = [
        'diagnosis' => 'array',
        'locked_at' => 'datetime',
        'sequence' => 'array'
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (MedicalDocument $medicalDocument) {
            $medicalDocument->price_list_id = 1;
            $medicalDocument->uuid = Str::orderedUuid();
        });
    }

    public function isPayed(): bool
    {
        return !$this->items()->whereDoesntHave('invoice')->exists();
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function totalAmountToPay(): Attribute
    {
        return Attribute::make(function () {
            return $this->items()
                ->with('invoice')
                ->get()
                ->pluck('invoice')
                ->filter()
                ->unique('id')
                ->sum('total');
        });
    }

    public function isPaid(): bool
    {
        $invoices = $this->items()
            ->with('invoice.payments')
            ->get()
            ->pluck('invoice')
            ->unique('id')
            ->filter()
            ->values();

        foreach ($invoices as $invoice) {
            if (! $invoice->payed) {
                return false;
            }
        }

        return $invoices->isNotEmpty();
    }

    public function userLocked()
    {
        return $this->belongsTo(User::class, 'locked_user_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'related')->latest();
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'related')->latest();
    }

    public function givenServices(): HasManyThrough
    {
        return $this->hasManyThrough(Service::class, MedicalDocumentItem::class, 'medical_document_id', 'id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function pastMedicalDocuments()
    {
        return $this->hasMany(
            self::class,
            'patient_id',   // foreign key na drugom modelu
            'patient_id'    // lokalni key
        )->whereKeyNot($this->getKey())
            ->latest();
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class, 'price_list_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MedicalDocumentItem::class);
    }

    public function relatedValue()
    {
        return $this->code;
    }

    public function relatedLabel()
    {
        return 'Nalaz';
    }
}
