<?php

namespace App\Filament\App\Clusters\Settings\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Clusters\Settings\SettingsCluster;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use UnitEnum;

class BranchWorkSchedule extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.app.clusters.settings.pages.branch-work-schedule';

    protected static ?string $cluster = SettingsCluster::class;

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::Clock;

    protected static ?string $navigationLabel = 'Work schedule';

    protected static ?string $title = 'Work schedule';

    protected static string | UnitEnum | null $navigationGroup = 'Clinic';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Filament::getTenant()->work_schedule ?? []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
                Form::make([
                    Section::make('Configure work schedule')
                        ->icon(PhosphorIcons::Clock)
                        ->schema(collect(self::getDays())->flatMap(function ($label, $key) {
                            return [
                                Grid::make(3)
                                    ->schema([
                                        Toggle::make("{$key}_working")
                                            ->columnSpan(1)
                                            ->live()
                                            ->label($label)
                                            ->afterStateUpdated(function ($state, Set $set) use ($key) {
                                                if ($state) {
                                                    $set("{$key}", [
                                                        [
                                                            'from' => '08:00',
                                                            'to' => '17:00',
                                                        ]
                                                    ]);
                                                }
                                            }),

                                        TextEntry::make("{$key}_off")
                                            ->inlineLabel()
                                            ->live()
                                            ->state(new HtmlString(Blade::render("@svg('heroicon-o-moon', 'w-5 h-5')")))
                                            ->label('Closed')
                                            ->columnSpan(1)
                                            ->hidden(function (Get $get) use ($key) {
                                                return $get("{$key}_working");
                                            }),

                                        Repeater::make("{$key}")
                                            ->hiddenLabel()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) use ($key) {
                                                if (empty($state)) {
                                                    $set("{$key}_working", null);
                                                }
                                            })
                                            ->hidden(function (Get $get) use ($key) {
                                                return !$get("{$key}_working");
                                            })
                                            ->addActionLabel("Add to {$label}")
                                            ->reorderable(false)
                                            ->columnSpan(2)
                                            ->table([
                                                TableColumn::make('From')->hiddenHeaderLabel(),
                                                TableColumn::make('To')->hiddenHeaderLabel()->markAsRequired(),
                                            ])
                                            ->schema([
                                                TimePicker::make('from')
                                                    ->hiddenLabel()
                                                    ->seconds(false)
                                                    ->prefix("from"),
                                                TimePicker::make('to')
                                                    ->seconds(false)
                                                    ->hiddenLabel()
                                                    ->prefix("to")
                                            ])
                                    ])
                            ];
                        })->toArray())
                ])->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Save')
                                ->icon(PhosphorIcons::Check)
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ]);

    }

    public function save(): void
    {
        $data = $this->form->getState();

        Filament::getTenant()->update([
            'work_schedule' => $data
        ]);

        Notification::make()
            ->success()
            ->title('Saved')
            ->send();
    }

    private static function getDays(): array
    {
        $carbon = Carbon::now();

        return collect(range(0, 6))
            ->mapWithKeys(function ($i) use ($carbon) {
                $date = $carbon->startOfWeek()->addDays($i);

                $key = strtolower($date->englishDayOfWeek);
                $value = $date->isoFormat('dddd');

                return [$key => $value];
            })
            ->toArray();
    }
}
