<?php

namespace App\Filament\Portal\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\Portal\Widgets\ClientStats;
use BackedEnum;
use CodeWithDennis\SimpleAlert\Components\SimpleAlert;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.portal.pages.dashboard';

    protected static ?string $title = 'Dashboard';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Home;

    public $defaultAction = 'unreadAnnouncements';

    public function getSubheading(): string|Htmlable|null
    {
        return 'Welcome back, ' . auth()->user()->full_name . '.';
    }

    public function announcementsAction(): Action
    {
        return Action::make('unreadAnnouncements')
            ->modalWidth(Width::ExtraLarge)
            ->color('warning')
            ->extraAttributes([
                'style' => 'display:none;'
            ])
            ->label('New announcements')
            ->visible(fn() => auth()->user()->unreadAnnouncements()->exists())
            ->icon(PhosphorIcons::Bell)
            ->modalHeading(fn($record) => $record?->title ?? 'No new announcements')
            ->modalDescription(fn($record) => $record ? 'Announcement from: ' . $record->user->full_name : null)
            ->modalIcon(PhosphorIcons::Bell)
            ->formWrapper(false)
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->record(function () {
                return auth()->user()->nextUnreadAnnouncement();
            })
            ->modalCloseButton(fn($record) => !filled($record))
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->extraModalFooterActions(function ($action) {
                $record = $action->getRecord();

                if (!$record) {
                    return [
                        Action::make('close')
                            ->label('Zatvori ')
                            ->color('gray')
                            ->close()
                            ->icon(PhosphorIcons::X),
                    ];
                }
                return [
                    Action::make('next')
                        ->label('Ok, I read it!')
                        ->link()
                        ->visible(fn($record) => filled($record))
                        ->icon(PhosphorIcons::Check)
                        ->color('success')
                        ->action(function ($record, Action $action) {
                            $user = auth()->user();

                            $user->markAnnouncementAsRead($record);

                            $next = $user->nextUnreadAnnouncement();

                            if ($next) {
                                $action->record($next);
                                $action->getRecord()->refresh();
                            } else {
                                $action->record(null);

                            }
                        }),
                ];
            })
            ->schema([
                TextEntry::make('content')
                    ->hiddenLabel()
                    ->html()
                    ->visible(fn($record) => filled($record)),

                SimpleAlert::make('announcement-banner')
                    ->warning()
                    ->visible(fn($record) => blank($record))
                    ->border()
                    ->columnSpanFull()
                    ->icon(PhosphorIcons::CheckCircleBold)
                    ->title('You have no unread announcements')
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [

            $this->announcementsAction(),

            Action::make('new-appointment')
                ->icon(PhosphorIcons::CalendarPlus)
                ->color('success')
                ->label('New appointment')
        ];
    }

    public function beforeConsultationBanner(Schema $schema)
    {
        return $schema
            ->columns(4)
            ->components([
                SimpleAlert::make('vaccination-banner')
                    ->warning()
                    ->icon(PhosphorIcons::Virus)
                    ->info()
                    ->title('Vaccination reminder for Rex')
                    ->actions([
                        Action::make('view-vaccination')
                            ->link()
                            ->color('info')
                            ->label('Schedule vaccination')
                            ->icon(PhosphorIcons::CalendarPlus)
                    ])
                    ->description(function () {
                        $fullName = auth()->user()->first_name;

                        return "Hi, {$fullName}, vaccination for you pet Rex is due on 20.03.2025";
                    })->columnSpanFull(),

                Section::make('How to prepare your pet for an examination?')
                    ->columnSpan(2)
                    ->icon(PhosphorIcons::Dog)
                    ->schema([
                        TextEntry::make('preparation-list')
                            ->hiddenLabel()
                            ->bulleted()
                            ->state([
                                'Bring your pet’s health booklet or any previous medical records – if you have them at home.',
                                'Avoid feeding your pet 2–3 hours before the appointment, unless your veterinarian advises otherwise.',
                                'Take your pet for a short walk before arriving, to help them relax.',
                                'If your pet is anxious, bring their favorite blanket or treat 🧸',
                            ]),
                        TextEntry::make('preparation-list-2')
                            ->state('View more tips')
                            ->hiddenLabel()
                            ->url('/portal/tips')
                            ->icon(PhosphorIcons::ArrowRight)
                            ->weight(FontWeight::SemiBold)
                    ])
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ClientStats::make()
        ];
    }
}
