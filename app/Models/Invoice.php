<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\PaymentMethod;
use App\Observers\InvoiceObserver;
use App\Traits\AddedByCurrentUser;
use App\Traits\Organisationable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Swindon\FilamentHashids\Traits\HasHashid;

#[ObservedBy(InvoiceObserver::class)]
class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory, SoftDeletes, Organisationable, InteractsWithMedia, AddedByCurrentUser, HasHashid;

    protected $fillable = [
        'code',
        'client_id',
        'user_id',
        'payment_method_id',
        'invoice_date',
        'price_list_id',
        'client_note',
        'issuer_id',
        'zki',
        'jir',
        'qrcode',
        'fiscalization_at',
        'invoice_due_date',
        'terms_and_conditions',
        'card_id',
        'bank_account_id',
        'total_base_price',
        'total_tax',
        'total',
    ];

    protected $casts = [
        'payment_method_id' => PaymentMethod::class,
        'invoice_date' => 'date',
        'invoice_due_date' => 'date',
        'total' => MoneyCast::class,
        'total_base_price' => MoneyCast::class,
        'total_discount' => MoneyCast::class,
        'total_tax' => MoneyCast::class,
        'fiscalization_at' => 'datetime',
    ];

    public function servicesList(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        $instance = new Service;

        $query = $instance->newQuery()
            ->where('organisation_id', 1);

        return new HasMany(
            $query,
            $this,
            $instance->getTable().'.id',
            $this->getKeyName()
        );
    }
    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($offer) {
            $offer->uuid = (string)\Str::uuid();
            $offer->code = 1;
        });
    }

    public function payed(): Attribute
    {
        return Attribute::make(function () {
            return $this->total <= $this->payments->sum('amount');
        });
    }

    #[Scope]
    public function notPayed(Builder $query)
    {
        return $query->whereRaw('invoices.total > (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.invoice_id = invoices.id)');
    }

    public function reminders(): MorphMany
    {
        return $this->morphMany(Reminder::class, 'related');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'related')->latest();
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_account_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issuer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    public function services(): HasManyThrough
    {
        return $this->hasManyThrough(
            Service::class,
            InvoiceItem::class,
            'invoice_id',
            'id',
            'id',
            'service_id'
        );
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'related');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function canceledInvoice()
    {
        return $this->hasOne(Invoice::class, 'storno_of_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }
}
