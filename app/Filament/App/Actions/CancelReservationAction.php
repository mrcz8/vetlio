<?php

namespace App\Filament\App\Actions;

use App\Enums\EmailTemplateType;
use App\Enums\Icons\PhosphorIcons;
use App\Models\CancelReason;
use App\Models\Reservation;
use App\Services\EmailTemplate\EmailTemplateService;
use App\Services\ReservationService;
use CodeWithDennis\SimpleAlert\Components\SimpleAlert;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Support\Enums\Width;

class CancelReservationAction extends Action
{
    private bool $hasEmailTemplate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hasEmailTemplate = $this->checkEmailTemplateExists();
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
            Select::make('cancel_reason_id')
                ->label('Reason for cancellation')
                ->options(CancelReason::whereActive(true)->get()->pluck('name', 'id'))
                ->required(),

            SimpleAlert::make('no-template')
                ->color('warning')
                ->icon(PhosphorIcons::Warning)
                ->columnSpanFull()
                ->border()
                ->visible(!$this->hasEmailTemplate)
                ->title('No email template found')
                ->description('Please create an email template for this action'),

            Toggle::make('send_email')
                ->visible(function () {
                    return !auth()->guard('portal')->check();
                })
                ->disabled(function ($record) {
                    return !$this->hasEmailTemplate || $record->client->email == null;
                })
                ->hint('Send email to client about cancellation')
                ->label('Send email')

        ]);
        $this->action(function (array $data, $record) {
            app(ReservationService::class)->cancel($record, $data['cancel_reason_id'], $data['send_email'] ?? false);
        });
    }

    public static function getDefaultName(): ?string
    {
        return 'cancel-reservation';
    }

    private function checkEmailTemplateExists()
    {
        if (auth()->guard('portal')->check()) return false;

        return app(EmailTemplateService::class)->getTemplateContent(Filament::getTenant()->id, EmailTemplateType::CancelAppointment->value) != null;
    }
}
