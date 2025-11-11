<?php

namespace App\Filament\Public\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Entries\PlaceholderEntry;
use App\Models\Reservation;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Locked;

class ConfirmAppointmentArrival extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.public.pages.confirm-appointment-arrival';

    protected static ?string $slug = 'appointment/confirm/{uuid}';

    protected ?string $subheading = 'Please confirm that you plan to arrive at the appointment.';

    #[Locked]
    public ?Reservation $appointment;

    public ?array $data = [];

    protected Width|string|null $maxContentWidth = Width::ThreeExtraLarge;

    protected array $extraBodyAttributes = [
        'class' => 'bg-white'
    ];

    public function getTitle(): string|Htmlable
    {
        return $this?->appointment?->organisation->name . ' - Confirm Appointment Arrival';
    }

    public function mount(): void
    {
        $this->appointment = $this->resolveRecord();
    }

    public function appointmentInformation(Schema $schema): Schema
    {
        return $schema
            ->record($this->appointment)
            ->schema([
                ImageEntry::make('organisation.logo')
                    ->hiddenLabel()
                    ->circular()
                    ->alignCenter(),

                Grid::make(1)
                    ->gap(false)
                    ->schema([
                        TextEntry::make('organisation.name')
                            ->hiddenLabel()
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->alignCenter(),

                        TextEntry::make('branch.name')
                            ->size(TextSize::Large)
                            ->hiddenLabel()
                            ->alignCenter(),

                        TextEntry::make('branch.full_address')
                            ->weight(FontWeight::SemiBold)
                            ->alignCenter()
                            ->icon(PhosphorIcons::MapPin)
                            ->hiddenLabel()
                            ->size(TextSize::Small),

                        PlaceholderEntry::make('divider')
                            ->extraAttributes([
                                'class' => 'mt-8 mb-3 border-gray-200'
                            ]),
                    ]),

                Grid::make(2)
                    ->schema([
                        TextEntry::make('from')
                            ->alignBetween()
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->label('Appointment at')
                            ->icon(PhosphorIcons::Clock)
                            ->dateTime('d.m.Y H:i'),

                        TextEntry::make('service.name')
                            ->alignBetween()
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->label('Service')
                            ->icon(PhosphorIcons::Hand),

                        TextEntry::make('patient.name')
                            ->alignBetween()
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->label('Pet')
                            ->icon(PhosphorIcons::Dog),

                        TextEntry::make('serviceProvider.full_name')
                            ->alignBetween()
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->label('Vet')
                            ->icon(PhosphorIcons::User)
                    ]),

                PlaceholderEntry::make('divider2')
                    ->extraAttributes([
                        'class' => 'mt-3 mb-3 border-gray-200'
                    ]),

                ActionGroup::make([
                    Action::make('confirm')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update([
                                'confirmed_status_id' => 1,
                                'confirmed_at' => now()
                            ]);

                        })
                        ->record($this->appointment)
                        ->label('I confirm arrival')
                        ->icon(PhosphorIcons::CheckCircleBold),

                    Action::make('reject')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update([
                                'confirmed_status_id' => 2,
                                'confirmed_at' => now()
                            ]);
                        })
                        ->record($this->appointment)
                        ->color('danger')
                        ->label('I reject arrival')
                        ->icon(PhosphorIcons::CheckCircleBold)
                ])->buttonGroup(),
            ]);
    }

    public function resolveRecord(): ?Reservation
    {
        return Reservation::whereUuid(request('uuid'))
            ->canceled(false)
            ->ordered()
            ->confirmed(false)
            ->first();
    }
}
