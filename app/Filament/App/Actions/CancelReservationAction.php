<?php

namespace App\Filament\App\Actions;

use App\Enums\Icons\PhosphorIcons;
use App\Models\Reservation;
use App\Services\ReservationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Support\Enums\Width;

class CancelReservationAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Cancel reservation');
        $this->icon(PhosphorIcons::CalendarX);
        $this->modalWidth(Width::Large);
        $this->color('danger');
        $this->model(Reservation::class);
        $this->modalSubmitActionLabel('Cancel reservation');
        $this->modalIcon(PhosphorIcons::CalendarX);
        $this->modalHeading('Cancel reservation');
        $this->visible(function ($record) {
            return !$record->is_canceled && $record->status_id->isOrdered();
        });
        $this->successNotificationTitle('Reservation canceled successfully');
        $this->failureNotificationTitle('Error canceling reservation');
        $this->schema([
            Textarea::make('reason')
                ->label('Cancel reason')
                ->placeholder('Enter cancel reason')
                ->required()
                ->rows(4),

            Toggle::make('send_email')
                ->visible(function () {
                    return !auth()->guard('portal')->check();
                })
                ->hint('Send email to client about cancellation')
                ->label('Send email')

        ]);
        $this->action(function (array $data, $record) {
            app(ReservationService::class)->cancel($record, $data['reason'], $data['send_email'] ?? false);
        });
    }

    public static function getDefaultName(): ?string
    {
        return 'cancel-reservation';
    }
}
