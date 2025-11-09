<?php

namespace App\Filament\App\Widgets;

use App\Enums\CalendarEventsType;
use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\Reservations\Schemas\ReservationForm;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\User;
use App\Queries\Holidays;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Guava\Calendar\Concerns\CalendarAction;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\Actions\CreateAction;
use Guava\Calendar\Filament\Actions\ViewAction;
use Guava\Calendar\Filament\CalendarWidget as BaseCalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

class CalendarWidget extends BaseCalendarWidget
{
    use CalendarAction;

    protected ?string $locale = 'hr';

    protected bool $noEventsClickEnabled = true;

    protected bool $dateSelectEnabled = true;

    protected ?string $defaultEventClickAction = 'view';

    protected string|HtmlString|null|bool $heading = 'Calendar';

    protected bool $eventClickEnabled = true;

    protected CalendarViewType $calendarView = CalendarViewType::ResourceTimeGridDay;

    protected bool $dateClickEnabled = true;

    protected bool $datesSetEnabled = true;

    protected bool $dayMaxEvents = false;

    public ?Collection $selectedUsers;

    public ?int $selectedClient = null;

    public ?int $calendarEventsType = 1;

    public function getHeaderActions(): array
    {
        return [
            Action::make('filter')
                ->slideOver()
                ->hiddenLabel()
                ->button()
                ->icon(Heroicon::Cog)
                ->label('Filter')
                ->fillForm(function ($data) {
                    $data['users'] = $this->selectedUsers;
                    $data['clients'] = $this->selectedClient;
                    $data['type'] = $this->calendarEventsType;

                    return $data;
                })
                ->schema([
                    ToggleButtons::make('type')
                        ->grouped()
                        ->label('View type')
                        ->columnSpanFull()
                        ->default(CalendarEventsType::All)
                        ->options(CalendarEventsType::class),

                    CheckboxList::make('users')
                        ->columnSpanFull()
                        ->hint('Show only selected users')
                        ->label('Users')
                        ->options(User::all()->pluck('name', 'id')),

                    Select::make('clients')
                        ->label('Client')
                        ->hint('Show only client reservations')
                        ->options(Client::all()->pluck('full_name', 'id'))
                ])
                ->action(function (array $data) {
                    if (Arr::get($data, 'users')) {
                        $this->selectedUsers = collect($data['users']);
                    }
                    $this->calendarEventsType = $data['type']->value;
                    $this->selectedClient = $data['clients'];

                    $this->refreshResources();
                    $this->refreshRecords();
                })
        ];
    }

    public function mount(): void
    {
        $this->calendarEventsType = 1;
        $this->selectedClient = null;
        $this->selectedUsers = User::all()->pluck('id');
    }

    public function getHeading(): null|string|HtmlString
    {
        return null;
    }

    public function getReservations(FetchInfo $info)
    {
        return Reservation::query()
            ->with(['client', 'serviceProvider', 'service'])
            ->when($this->selectedClient, fn($q) => $q->where('client_id', $this->selectedClient))
            ->whereDate('to', '>=', $info->start)
            ->whereDate('from', '<=', $info->end);
    }

    public function getHolidays(FetchInfo $info, mixed $org)
    {
        $rangeStart = Carbon::parse($info->start)->toDateString();
        $rangeEnd = Carbon::parse($info->end)->toDateString();
        $cacheKey = "org:{$org->id}:holidays:{$rangeStart}:{$rangeEnd}";

        $holidays = Cache::remember($cacheKey, now()->addHours(6), function () use ($rangeStart, $rangeEnd) {
            return app(Holidays::class)->forRange(
                Carbon::parse($rangeStart),
                Carbon::parse($rangeEnd)
            );
        });

        $resourceIds = $this->getResources()->get()->pluck('id');

        return $holidays->map(function ($h) use ($resourceIds) {
            $start = $h->date instanceof Carbon ? $h->date : Carbon::parse($h->date);

            return CalendarEvent::make()
                ->key($h->id)
                ->title($h->getAttribute('name') ?? 'Holiday')
                ->start($start->toDateString())
                ->end($start->toDateString())
                ->allDay()
                ->resourceIds(array_unique($resourceIds->toArray()))
                ->editable(false)
                ->backgroundColor('#e91e63');
        });
    }

    protected function resourceLabelContent(): HtmlString|string
    {
        return view('calendar.resource');
    }

    /*protected function eventContent(): HtmlString|string
    {
        return view('calendar.event');
    }*/

    public function getOptions(): array
    {
        return [
            'headerToolbar' => [
                'start' => 'prev,next today',
                'center' => 'title',
                'end' => 'resourceTimeGridDay,resourceTimelineWeek,dayGridMonth,listMonth'
            ],
            'buttonText' => [
                'today' => 'Today',
                'resourceTimeGridDay' => 'Daily',
                'resourceTimelineWeek' => 'Weekly',
                'dayGridMonth' => 'Monthly',
                'listMonth' => 'List'
            ],
            'slotDuration' => '00:15:00',
            'slotLabelInterval' => '00:15:00',
            'slotHeight' => 50,
            'slotMinTime' => '08:00:00',
            'slotMaxTime' => '20:00:00',
        ];
    }

    protected function getDateSelectContextMenuActions(): array
    {
        return [
            CreateAction::make('createBlockTime')
                ->color('danger')
                ->modalIcon(Heroicon::MinusCircle)
                ->icon(Heroicon::MinusCircle)
                ->label('Unavailable')
                ->model(Reservation::class)
                ->modalHeading('Unavailable time')
                ->mountUsing(function ($schema, $arguments) {
                    $date = data_get($arguments, 'data.dateStr');
                    $resourceId = data_get($arguments, 'data.resource.id');

                    $schema->fill([
                        'service_provider_id' => $resourceId,
                        'from' => Carbon::make($date),
                    ]);
                }),
        ];
    }

    protected function getDateClickContextMenuActions(): array
    {
        return [
            CreateAction::make('createAppointment')
                ->modalIcon(Heroicon::Calendar)
                ->icon(Heroicon::Calendar)
                ->label('New reservation')
                ->model(Reservation::class)
                ->modalHeading('New reservation')
                ->mountUsing(function ($schema, $arguments) {
                    $date = data_get($arguments, 'data.dateStr');
                    $resourceId = data_get($arguments, 'data.resource.id');

                    $schema->fill([
                        'service_provider_id' => $resourceId,
                        'from' => Carbon::make($date),
                    ]);
                }),
        ];
    }

    protected function getEventClickContextMenuActions(): array
    {
        return [
            $this->viewAction(),
            $this->editAction(),
            Action::make('cancel-reservation')
                ->label('Cancel reservation')
                ->icon(PhosphorIcons::CalendarX)
                ->modalWidth(Width::Large)
                ->color('danger')
                ->modalSubmitActionLabel('Cancel reservation')
                ->modalIcon(PhosphorIcons::CalendarX)
                ->modalHeading('Cancel reservation')
                ->schema([
                    Textarea::make('reason')
                        ->label('Cancel reason')
                        ->placeholder('Enter cancel reason')
                        ->required()
                        ->rows(4),

                    Toggle::make('send_email')
                        ->hint('Send email to client about cancellation')
                        ->label('Send email')

                ])
                ->model(Reservation::class)
                ->mountUsing(function ($schema, $arguments) {

                })
        ];
    }

    public function defaultSchema(Schema $schema): Schema
    {
        return ReservationForm::configure($schema)
            ->columns(2);
    }

    public function viewAction(): ViewAction
    {
        return ViewAction::make($this->view)
            ->label('Open');
    }

    protected function createAction(string $model, ?string $name = null): CreateAction
    {
        return parent::createAction($model, $name)
            ->label('New reservation')
            ->modalHeading('New reservation')
            ->modalIcon(Heroicon::Calendar)
            ->icon(Heroicon::Calendar);
    }

    protected function getResources(): Collection|array|Builder
    {
        if ($this->selectedUsers) {
            return User::query()->whereIn('id', $this->selectedUsers);
        }

        return User::query();
    }

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        $org = auth()->user()->organisation;

        $reservations = $this->getReservations($info);
        $holidayEvents = $this->getHolidays($info, $org);
        $weekendBackgrounds = $this->makeWeekendBackgroundEvents($info);

        return collect()
            ->push(...$weekendBackgrounds)
            ->push(...$reservations->get())
            ->push(...$holidayEvents);
    }

    protected function makeWeekendBackgroundEvents(FetchInfo $info): Collection
    {
        $events = collect();

        $from = Carbon::parse($info->start)->startOfDay();
        $to   = Carbon::parse($info->end)->endOfDay();

        $resourceIds = $this->getResources()->get()->pluck('id');

        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $dayOfWeek = $cursor->dayOfWeekIso; // 6 = Saturday, 7 = Sunday

            if ($dayOfWeek === 6 || $dayOfWeek === 7) {
                $events->push(
                    CalendarEvent::make()
                        ->key('weekend-'.$cursor->toDateString())
                        ->title($dayOfWeek === 6 ? 'Saturday' : 'Sunday')
                        ->start($cursor->toDateString())
                        ->end($cursor->toDateString())
                        ->resourceIds(array_values($resourceIds->toArray()))
                        ->allDay()
                        ->displayBackground()
                        ->backgroundColor($dayOfWeek === 6 ? '#FFF9C4' : '#FFCDD2')
                        ->editable(false)
                );
            }

            $cursor->addDay();
        }

        return $events;
    }
}
