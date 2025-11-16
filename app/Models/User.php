<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Observers\UserObserver;
use App\Traits\Organisationable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Guava\Calendar\Contracts\Resourceable;
use Guava\Calendar\ValueObjects\CalendarResource;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Zap\Models\Concerns\HasSchedules;

#[ObservedBy(UserObserver::class)]
class User extends Authenticatable implements HasTenants, HasDefaultTenant, FilamentUser, Resourceable, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory,
        Notifiable,
        Organisationable,
        SoftDeletes,
        HasSchedules;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'first_name',
        'last_name',
        'email_verified_at',
        'phone',
        'title',
        'gender_id',
        'date_of_birth',
        'oib',
        'fiscalization_enabled',
        'signature_path',
        'active',
        'administrator',
        'service_provider',
        'primary_branch_id',
        'color',
        'full_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (Model $model) {
            $model->password = bcrypt('12345678');
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function fullName(): Attribute
    {
        return Attribute::make(function () {
            return $this->first_name . ' ' . $this->last_name;
        });
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_image ? Storage::url($this->profile_image) : null;
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'link_user_branches');
    }

    public function primaryBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'primary_branch_id');
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        return $this->branches->first();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if ($this->administrator) {
            return true;
        }

        return $this->branches()->whereKey($tenant)->exists();
    }

    public function getTenants(Panel $panel): array|Collection
    {
        return $this->branches;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function dismissedAnnouncements(): MorphToMany
    {
        return $this->morphToMany(Announcement::class, 'dismissable', 'dismissed_announcements')
            ->withPivot(['read_at'])
            ->withTimestamps();
    }

    public function unreadAnnouncements(): Builder
    {
        return Announcement::query()
            ->forUsers()
            ->active()
            ->where('user_id', '!=', $this->id)
            ->whereDoesntHave('dismissedByUsers', function ($q) {
                $q->where('dismissable_id', $this->id)
                    ->where('dismissable_type', static::class);
            })
            ->orderBy('starts_at');
    }

    public function nextUnreadAnnouncement()
    {
        return $this->unreadAnnouncements()->first();
    }

    public function markAnnouncementAsRead(Announcement $announcement): void
    {
        if (!$announcement->for_users) {
            return;
        }

        $this->dismissedAnnouncements()->syncWithoutDetaching([
            $announcement->getKey() => ['read_at' => now()],
        ]);
    }

    public function toCalendarResource(): CalendarResource
    {
        return CalendarResource::make($this->id)
            ->extendedProps([
                'avatar' => $this->avatar != null ? asset($this->avatar) : null,
                'title' => $this->title ?? '-',
                'name' => $this->first_name
            ])
            ->title($this->full_name);
    }

}
