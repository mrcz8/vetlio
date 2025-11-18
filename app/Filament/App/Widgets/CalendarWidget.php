<?php

namespace App\Filament\App\Widgets;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Entries\PlaceholderEntry;
use App\Filament\App\Resources\Reservations\Schemas\ReservationForm;
use App\Filament\App\Resources\Reservations\Schemas\ReservationInfolist;
use App\Models\Holiday;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Queries\Holidays;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Guava\Calendar\Attributes\CalendarEventContent;
use Guava\Calendar\Concerns\CalendarAction;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\Actions\CreateAction;
use Guava\Calendar\Filament\Actions\ViewAction;
use Guava\Calendar\Filament\CalendarWidget as BaseCalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\DateClickInfo;
use Guava\Calendar\ValueObjects\DateSelectInfo;
use Guava\Calendar\ValueObjects\DatesSetInfo;
use Guava\Calendar\ValueObjects\EventClickInfo;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Spatie\Period\Period;
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

    protected bool $useFilamentTimezone = true;

    //Resources for header
    public ?Collection $selectedResources = null;
    public ?int $resourceType = 1;

    public function mount(): void
    {
        $this->selectedResources = User::whereHas('branches', function ($query) {
            return $query->where('branch_id', Filament::getTenant()->id);
        })->get();
    }


    public function getHeaderActions(): array
    {
        return [
            Action::make('filter')
                ->slideOver()
                ->modalHeading('Filter')
                ->modalDescription('Filter calendar by date, service or user')
                ->modalIcon(PhosphorIcons::FilesThin)
                ->hiddenLabel()
                ->button()
                ->icon(Heroicon::Cog)
                ->label('Filter')
                ->fillForm(function ($data) {
                    $data['group_by'] = $this->resourceType;

                    if ($this->resourceType == 1) {
                        $data['users'] = $this->selectedResources->pluck('id');
                    } else if ($this->resourceType == 3) {
                        $data['rooms'] = $this->selectedResources->pluck('id');
                    }


                    return $data;
                })
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Radio::make('group_by')
                                ->columnSpanFull()
                                ->hint('Group by events by selected resource.')
                                ->label('Group by')
                                ->default(1)
                                ->afterStateUpdated(function ($state, $set) {
                                    $set('users', []);
                                    $set('rooms', []);
                                })
                                ->inline()
                                ->live(false, 300)
                                ->options([
                                    1 => 'Users',
                                    2 => 'Services',
                                    3 => 'Rooms'
                                ]),

                            PlaceholderEntry::make('divider'),

                            CheckboxList::make('users')
                                ->visible(function ($get) {
                                    return $get('group_by') == 1;
                                })
                                ->required(function ($get) {
                                    return $get('group_by') == 1;
                                })
                                ->bulkToggleable()
                                ->columnSpanFull()
                                ->label('Users')
                                ->options(User::all()->pluck('name', 'id')),

                            CheckboxList::make('rooms')
                                ->visible(function ($get) {
                                    return $get('group_by') == 3;
                                })
                                ->required(function ($get) {
                                    return $get('group_by') == 3;
                                })
                                ->bulkToggleable()
                                ->columnSpanFull()
                                ->label('Rooms')
                                ->options(Room::all()->pluck('name', 'id')),
                        ])
                ])
                ->action(function (array $data) {
                    if ($data['group_by'] == 1) {
                        $this->selectedResources = User::whereIn('id', Arr::get($data, 'users'))->get();
                    } else if ($data['group_by'] == 3) {
                        $this->selectedResources = Room::whereIn('id', Arr::get($data, 'rooms'))->get();
                    }

                    $this->resourceType = Arr::get($data, 'group_by');

                    $this->refreshResources();
                    $this->refreshRecords();
                })
        ];
    }


    protected function onDatesSet(DatesSetInfo $info): void
    {
        $workingTimes = $this->getEarliestWorkingStartForDate($info->start);

        $this->setOption('slotMinTime', $workingTimes['min']);
        //$this->setOption('slotMaxTime', $workingTimes['max']); ?? Buggy, it's not working'
    }

    protected function getEarliestWorkingStartForDate(CarbonInterface|string $date): array
    {
        if (!$date instanceof CarbonInterface) {
            $date = Carbon::parse($date);
        }

        $schedule = Filament::getTenant()->work_schedule ?? [];

        $dayName = strtolower($date->format('l'));
        $flagKey = "{$dayName}_working";

        $isWorking = $schedule[$flagKey] ?? false;
        $intervals = $schedule[$dayName] ?? [];

        if (!$isWorking || empty($intervals)) {
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

    public function getReservations(FetchInfo $info): Collection
    {
        $appointments = Reservation::query()
            ->canceled(false)
            ->with(['client', 'serviceProvider', 'service'])
            ->whereDate('to', '>=', $info->start)
            ->whereDate('from', '<=', $info->end)
            ->get();

        return $appointments->map(function ($appointment) {
            return CalendarEvent::make()
                ->key($appointment)
                ->title($appointment->client->full_name)
                ->extendedProps([
                    'type' => 'appointment',
                    'model' => Reservation::class,
                    'key' => $appointment->getKey(),
                    'start' => $appointment->from->format('H:i'),
                    'end' => $appointment->to->format('H:i'),
                    'client' => $appointment->client->full_name,
                    'patient' => $appointment->patient->name,
                    'service' => $appointment->service->name,
                    'color' => $appointment->service->color ?? '#8bc34a'
                ])
                ->resourceId($appointment->serviceProvider->id)
                ->startEditable()
                ->backgroundColor($appointment->service->color ?? '#8bc34a')
                ->start($appointment->from)
                ->end($appointment->to);
        });

    }

    public function getHolidays(FetchInfo $info)
    {
        $org = auth()->user()->organisation_id;

        $rangeStart = Carbon::parse($info->start)->toDateString();
        $rangeEnd = Carbon::parse($info->end)->toDateString();
        $cacheKey = "org:{$org}:holidays:{$rangeStart}:{$rangeEnd}";

        $holidays = Cache::remember($cacheKey, now()->addHours(6), function () use ($rangeStart, $rangeEnd) {
            return app(Holidays::class)->forRange(
                Carbon::parse($rangeStart),
                Carbon::parse($rangeEnd)
            );
        });

        $resourceIds = collect($this->getResourcesJs())->pluck('id')->all();

        return $holidays->map(function ($h) use ($resourceIds) {
            $start = $h->date instanceof Carbon ? $h->date : Carbon::parse($h->date);

            return CalendarEvent::make()
                ->model(Holiday::class)
                ->key($h->id)
                ->title($h->getAttribute('name') ?? 'Holiday')
                ->start($start->toDateString())
                ->end($start->toDateString())
                ->allDay()
                ->extendedProps([
                    'type' => 'holiday',
                ])
                ->resourceIds($resourceIds)
                ->editable(false)
                ->backgroundColor('#e91e63');
        });
    }

    protected function resourceLabelContent(): HtmlString|string
    {
        return view('calendar.resource');
    }

    #[CalendarEventContent(Reservation::class)]
    protected function reservationEventContent(): HtmlString|string
    {
        return view('calendar.event');
    }

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

    public function createBlockTimeAction(): Action
    {
        return CreateAction::make('createBlockTime')
            ->modalIcon(Heroicon::Calendar)
            ->icon(Heroicon::Calendar)
            ->label('New block time')
            ->model(Reservation::class)
            ->modalHeading('New block time')
            ->mountUsing(function ($schema, $arguments) {
                $date = data_get($arguments, 'data.dateStr');
                $resourceId = data_get($arguments, 'data.resource.id');

                $schema->fill([
                    'service_provider_id' => $resourceId,
                    'from' => Carbon::make($date),
                ]);
            });
    }

    protected function onDateSelect(DateSelectInfo $info): void
    {
        //$this->mountAction('createBlockTime');
    }

    private function isHoliday($date): bool
    {
        return Holiday::whereDate('date', $date)->whereCountryId(auth()->user()->organisation->country_id)->exists();
    }

    protected function onDateClick(DateClickInfo $info): void
    {
        //Check if holiday
        if ($this->isHoliday($info->date)) {
            Notification::make()
                ->danger()
                ->title('This date is a holiday')
                ->body("You cannot book an appointment on holiday.")
                ->send();

            return;
        }

        //Check if working day of a branch
        if (!$this->isWorkingPeriod($info->date)) {
            $date = Carbon::parse($info->date)->format('d.m.Y H:i');

            Notification::make()
                ->danger()
                ->title('Non working time')
                ->body("You cannot book an appointment. Clinic is not working at {$date}")
                ->send();

            return;
        }

        $this->mountAction('createAppointment');
    }

    protected function onEventClick(EventClickInfo $info, Model $event, ?string $action = null): void
    {
        //Dont open rendered holidays
        if ($event instanceof Holiday) return;

        if ($event instanceof Reservation) {
            $this->mountAction('view', ['record' => $event]);
        }
    }

    public function defaultSchema(Schema $schema): Schema
    {
        return ReservationForm::configure($schema)
            ->columns(2);
    }

    #[On('appointment-canceled')]
    public function onAppointmentCanceled(): void
    {
        $this->refreshRecords();
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

    protected function getResources(): Collection|array|Builder
    {
        return $this->selectedResources;
    }

    protected function nonWorkingPeriods(FetchInfo $fetchInfo): Collection
    {
        $events = [];
        $schedule = Filament::getTenant()->work_schedule ?? [];

        $start = $fetchInfo->start->copy()->startOfDay();
        $end = $fetchInfo->end->copy()->subSecond()->startOfDay();

        $currentDate = $start->copy();

        while ($currentDate->lte($end)) {
            $dayName = strtolower($currentDate->format('l'));

            $flagKey = "{$dayName}_working";

            $isWorking = $schedule[$flagKey] ?? false;
            $intervals = $schedule[$dayName] ?? [];

            if (!$isWorking || empty($intervals)) {
                $full = Period::make(
                    $currentDate->startOfDay(),
                    $currentDate->endOfDay(),
                    Precision::SECOND()
                );

                $events[] = [
                    'start' => CarbonImmutable::instance($full->start()),
                    'end' => CarbonImmutable::instance($full->end()),
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
                ->filter(fn($i) => !empty($i['from']) && !empty($i['to']))
                ->map(function ($i) use ($currentDate) {
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
                    'start' => CarbonImmutable::instance($full->start()),
                    'end' => CarbonImmutable::instance($full->end()),
                    'classNames' => ['non-working'],
                ];

                $currentDate = $currentDate->addDay();
                continue;
            }

            $nonWorking = $fullDay->subtract(...$workingPeriods);

            foreach ($nonWorking as $period) {
                $events[] = [
                    'start' => CarbonImmutable::instance($period->start()),
                    'end' => CarbonImmutable::instance($period->end()),
                    'classNames' => ['non-working'],
                ];
            }

            $currentDate = $currentDate->addDay();
        }

        $resourceIds = collect($this->getResourcesJs())->pluck('id')->all();

        // Map u Guava CalendarEvent
        return collect($events)->map(function ($event) use ($resourceIds) {
            return CalendarEvent::make()
                ->key(Str::uuid())
                ->title('NON WORKING')
                ->start($event['start'])
                ->end($event['end'])
                ->displayBackground()
                ->extendedProps([
                    'type' => 'non-working',
                ])
                ->classes([
                    "bg-[repeating-linear-gradient(45deg,theme(colors.gray.400)_0,theme(colors.gray.400)_1px,transparent_1px,transparent_3px)]"
                ])
                ->resourceIds($resourceIds);
        });
    }

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        $nonWorkingPeriods = $this->nonWorkingPeriods($info);
        $reservations = $this->getReservations($info);
        $holidayEvents = $this->getHolidays($info);

        return collect()
            ->push(...$nonWorkingPeriods)
            ->push(...$reservations)
            ->push(...$holidayEvents);
    }

    protected function makeWeekendBackgroundEvents(FetchInfo $info): Collection
    {
        $events = collect();

        $from = Carbon::parse($info->start)->startOfDay();
        $to = Carbon::parse($info->end)->endOfDay();

        $resourceIds = collect($this->getResourcesJs())->pluck('id')->all();

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
                        ->resourceIds($resourceIds)
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

    public function createAppointmentAction(): Action
    {
        return CreateAction::make('createAppointment')
            ->modalIcon(Heroicon::Calendar)
            ->icon(Heroicon::Calendar)
            ->label('New appointment')
            ->model(Reservation::class)
            ->modalHeading('New appointment')
            ->mountUsing(function ($schema, $arguments) {
                $date = data_get($arguments, 'data.dateStr');
                $resourceId = data_get($arguments, 'data.resource.id');

                $schema->fill([
                    'service_provider_id' => $resourceId,
                    'from' => Carbon::make($date),
                ]);
            });
    }

    private function isWorkingPeriod(CarbonInterface $date): bool
    {
        $schedule = Filament::getTenant()->work_schedule ?? [];
        $dayName = strtolower($date->format('l'));
        $flagKey = "{$dayName}_working";

        //Check is working day
        if (isset($schedule[$flagKey]) && !$schedule[$flagKey]) return false;

        $shifts = collect($schedule[$dayName] ?? []);

        $shifts = $shifts->filter(fn($shift) => !empty($shift['from']) && !empty($shift['to']));

        foreach ($shifts as $shift) {
            $from = Carbon::parse($shift['from']);
            $to = Carbon::parse($shift['to']);

            if ($this->isTimeBetween($date->toTimeString(), $from->toTimeString(), $to->toTimeString())) return true;
        }

        return false;
    }

    public function isTimeBetween($timeToCheck, $startTime, $endTime): bool
    {
        $time = Carbon::parse($timeToCheck)->format('H:i:s');
        $start = Carbon::parse($startTime)->format('H:i:s');
        $end = Carbon::parse($endTime)->format('H:i:s');

        return $time >= $start && $time <= $end;
    }
}
