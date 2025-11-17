<?php

namespace App\Filament\App\Widgets;

use App\Enums\CalendarEventsType;
use App\Filament\App\Actions\CancelReservationAction;
use App\Filament\App\Resources\Reservations\Schemas\ReservationForm;
use App\Filament\App\Resources\Reservations\Schemas\ReservationInfolist;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\User;
use App\Queries\Holidays;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Guava\Calendar\Concerns\CalendarAction;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\Actions\CreateAction;
use Guava\Calendar\Filament\Actions\ViewAction;
use Guava\Calendar\Filament\CalendarWidget as BaseCalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\DatesSetInfo;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;
use Spatie\Period\Precision;

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

    protected bool $useFilamentTimezone = true;

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

    protected function onDatesSet(DatesSetInfo $info): void
    {
        $workingTimes = $this->getEarliestWorkingStartForDate($info->start);
        $this->setOption('slotMinTime', $workingTimes['min']);
        $this->setOption('slotMaxTime', $workingTimes['max']);
    }

    protected function getEarliestWorkingStartForDate(CarbonInterface|string $date): array
    {
        if (! $date instanceof CarbonInterface) {
            $date = Carbon::parse($date);
        }

        $schedule = Filament::getTenant()->work_schedule ?? [];

        $dayName = strtolower($date->format('l'));
        $flagKey = "{$dayName}_working";

        $isWorking = $schedule[$flagKey] ?? false;
        $intervals = $schedule[$dayName] ?? [];

        if (! $isWorking || empty($intervals)) {
            return [
                'min' => '06:00',
                'max' => '22:00',
            ];
        }

        $collection = collect($intervals);

        $earliestFrom = $collection
            ->pluck('from')
            ->filter()
            ->sort()
            ->first();

        $latestTo = $collection
            ->pluck('to')
            ->filter()
            ->sortDesc()
            ->first();

        $min = $earliestFrom ?: '06:00';
        $max = $latestTo ?: ($earliestFrom ?: '06:00');

        return [
            'min' => $min,
            'max' => $max,
        ];
    }

    public function getHeading(): null|string|HtmlString
    {
        return null;
    }

    public function getReservations(FetchInfo $info)
    {
        return Reservation::query()
            ->canceled(false)
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
            'slotMinTime' => '06:00:00',
            'slotMaxTime' => '20:00:00',
            'nowIndicator' => true,
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
            CancelReservationAction::make()
                ->record(function ($livewire) {
                    return $livewire->getEventRecord();
                })->after(function () {
                    $this->refreshRecords();
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
            ->record(function ($livewire) {
                return $livewire->getEventRecord();
            })
            ->schema(function ($schema, $record) {
                return ReservationInfolist::configure($schema)
                    ->record($record);
            })
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

    protected function nonWorkingPeriods(FetchInfo $fetchInfo): Collection
    {
        $events = [];
        $schedule = Filament::getTenant()->work_schedule ?? [];

        // Guava daje CarbonImmutable start/end
        $start = $fetchInfo->start->copy()->startOfDay();
        $end   = $fetchInfo->end->copy()->subSecond()->startOfDay();

        $currentDate = $start->copy();

        while ($currentDate->lte($end)) {
            $dayName = strtolower($currentDate->format('l'));

            $flagKey = "{$dayName}_working";

            $isWorking = $schedule[$flagKey] ?? false;
            $intervals = $schedule[$dayName] ?? [];

            if (! $isWorking || empty($intervals)) {
                $full = Period::make(
                    $currentDate->startOfDay(),
                    $currentDate->endOfDay(),
                    Precision::SECOND()
                );

                $events[] = [
                    'start'      => CarbonImmutable::instance($full->start()),
                    'end'        => CarbonImmutable::instance($full->end()),
                    'classNames' => ['non-working'],
                ];

                $currentDate = $currentDate->addDay();
                continue;
            }

            $fullDay = Period::make(
                $currentDate->startOfDay(),
                $currentDate->endOfDay(),
                Precision::SECOND()
            );

            $workingPeriods = collect($intervals)
                ->filter(fn ($i) => ! empty($i['from']) && ! empty($i['to'] ))
                ->map(function($i) use ($currentDate){
                    return Period::make(
                        $currentDate->setTimeFromTimeString($i['from']),
                        $currentDate->setTimeFromTimeString($i['to']),
                        Precision::SECOND()
                    );
                })
                ->values()
                ->all();

            if (empty($workingPeriods)) {
                $full = Period::make(
                    $currentDate->startOfDay(),
                    $currentDate->endOfDay(),
                    Precision::SECOND()
                );

                $events[] = [
                    'start'      => CarbonImmutable::instance($full->start()),
                    'end'        => CarbonImmutable::instance($full->end()),
                    'classNames' => ['non-working'],
                ];

                $currentDate = $currentDate->addDay();
                continue;
            }

            $nonWorking = $fullDay->subtract(...$workingPeriods);

            foreach ($nonWorking as $period) {
                //dump($period->start(), $period->end());
                $events[] = [
                    'start'      => CarbonImmutable::instance($period->start()),
                    'end'        => CarbonImmutable::instance($period->end()),
                    'classNames' => ['non-working'],
                ];
            }

            $currentDate = $currentDate->addDay();
        }

        // Map u Guava CalendarEvent
        return collect($events)->map(function ($event) {
            return CalendarEvent::make()
                ->key(Str::uuid())
                ->title('NON WORKING')
                ->start($event['start'])
                ->end($event['end'])
                //->backgroundColor('red')
                ->displayBackground()
                ->classes([
                    "bg-[repeating-linear-gradient(45deg,theme(colors.gray.400)_0,theme(colors.gray.400)_1px,transparent_1px,transparent_3px)]"
                ])
                ->resourceIds(
                    array_values(
                        $this->getResources()->get()->pluck('id')->toArray()
                    )
                );
        });
    }

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        $org = auth()->user()->organisation;

        $nonWorkingPeriods = $this->nonWorkingPeriods($info);
        $reservations = $this->getReservations($info);
        $holidayEvents = $this->getHolidays($info, $org);
        $weekendBackgrounds = $this->makeWeekendBackgroundEvents($info);

        return collect()
            ->push(...$nonWorkingPeriods)
            //->push(...$weekendBackgrounds) //not required, becouse we have non-working periods
            ->push(...$reservations->get())
            ->push(...$holidayEvents);
    }

    protected function makeWeekendBackgroundEvents(FetchInfo $info): Collection
    {
        $events = collect();

        $from = Carbon::parse($info->start)->startOfDay();
        $to = Carbon::parse($info->end)->endOfDay();

        $resourceIds = $this->getResources()->get()->pluck('id');

        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $dayOfWeek = $cursor->dayOfWeekIso; // 6 = Saturday, 7 = Sunday

            if ($dayOfWeek === 6 || $dayOfWeek === 7) {
                $events->push(
                    CalendarEvent::make()
                        ->key('weekend-' . $cursor->toDateString())
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
