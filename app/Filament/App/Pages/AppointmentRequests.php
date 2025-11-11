<?php

namespace App\Filament\App\Pages;

use App\Enums\AppointmentRequestStatus;
use App\Enums\Icons\PhosphorIcons;
use App\Filament\Shared\Columns\CreatedAtColumn;
use App\Models\AppointmentRequest;
use App\Services\ReservationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class AppointmentRequests extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.app.pages.appointment-requests';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Appointment Requests';

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::CalendarPlus;

    public function getSubheading(): string|Htmlable|null
    {
        return 'View all pending, approved and rejected appointment requests';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(AppointmentRequest::query()->where('branch_id', Filament::getTenant()->id))
            ->columns([
                TextColumn::make('client.full_name')
                    ->description(function ($record) {
                        return $record->client->email;
                    })
                    ->label('Client'),

                TextColumn::make('patient.name')
                    ->description(function ($record) {
                        return $record->patient->description;
                    })
                    ->label('Patient'),

                TextColumn::make('service.name')
                    ->description(function ($record) {
                        return $record->reason_for_coming;
                    })
                    ->label('Service'),

                TextColumn::make('service_provider.full_name')
                    ->label('Service Provider'),

                TextColumn::make('date')
                    ->date()
                    ->description(function ($record) {
                        return $record->from->format('H:i') . ' - ' . $record->to->format('H:i');
                    })
                    ->label('Date'),

                TextColumn::make('approval_status_id')
                    ->badge()
                    ->description(function ($record) {
                        return $record?->approvalBy ? "Approval by {$record->approvalBy->full_name}" : null;
                    })
                    ->label('Status'),

                TextColumn::make('note')
                    ->label('Note'),

                CreatedAtColumn::make(),
            ])
            ->filters([
                SelectFilter::make('approval_status_id')
                    ->label('Status')
                    ->native(false)
                    ->multiple()
                    ->options(AppointmentRequestStatus::class)
                    ->default(AppointmentRequestStatus::Pending->value)
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->requiresConfirmation()
                    ->visible(function ($record) {
                        return !$record->approval_at;
                    })
                    ->action(function ($record, $data) {
                        app(ReservationService::class)->approveRequest($record, $data['note']);
                    })
                    ->modalHeading('Approve this request')
                    ->modalDescription('Are you sure you want to approve this request? This will create a new reservation for the client.')
                    ->schema([
                        Textarea::make('note')
                            ->label('Note')
                            ->hint('Add a note for this request')
                    ])
                    ->successNotificationTitle('Appointment request approved')
                    ->icon(PhosphorIcons::CheckCircle)
                    ->color('success'),

                Action::make('deny')
                    ->requiresConfirmation()
                    ->label('Deny')
                    ->visible(function ($record) {
                        return !$record->approval_at;
                    })
                    ->modalHeading('Deny this request')
                    ->modalDescription('Are you sure you want to deny this request?')
                    ->schema([
                        Textarea::make('note')
                            ->label('Note')
                            ->hint('Add a note for this request')
                    ])
                    ->action(function ($record, $data) {
                        app(ReservationService::class)->denyRequest($record, $data['note']);
                    })
                    ->icon(PhosphorIcons::XCircleBold)
                    ->color('danger'),
            ]);
    }
}
