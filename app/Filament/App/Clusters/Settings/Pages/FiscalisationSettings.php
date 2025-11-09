<?php

namespace App\Filament\App\Clusters\Settings\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Clusters\Settings\SettingsCluster;
use App\Models\Organisation;
use App\Services\FiscalisationService;
use BackedEnum;
use Carbon\Carbon;
use CodeWithDennis\SimpleAlert\Components\SimpleAlert;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use UnitEnum;

class FiscalisationSettings extends Page
{
    protected string $view = 'filament.app.clusters.settings.pages.fiscalisation-settings';

    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'Fiscalisation';

    protected static ?string $title = 'Fiscalisation details';

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::Invoice;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    public ?array $data = [];

    protected static ?int $navigationSort = 5;

    public function mount(): void
    {
        $this->form->fill($this->getRecord()?->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Fiscalisation')
                        ->columns(2)
                        ->headerActions([
                            Action::make('test-connection')
                                ->disabled(function (Get $get) {
                                    return !$get('certificate_path') || !$get('certificate_password');
                                })
                                ->label('Test certificate')
                                ->link()
                                ->icon(PhosphorIcons::Check)
                                ->successNotificationTitle('The certificate has been successfully validated.')
                                ->failureNotificationTitle('Error testing the certificate.')
                                ->action(function (Action $action, Get $get, Set $set) {
                                    if (!$get('certificate_path') || !$get('certificate_password')) {
                                        $action->failure();
                                        return;
                                    }

                                    $certificatePath = array_values($get('certificate_path'))[0];
                                    $certificatePassword = $get('certificate_password');

                                    $response = $this->getCertificateValidResponse($certificatePath, $certificatePassword);

                                    if (Arr::has($response, 'error')) {
                                        $action->failureNotificationTitle($response['error']);
                                        $action->failure();
                                    }

                                    if (Arr::has($response, 'success') && $response['success'] === true) {
                                        $validTo = $response['details']['valid_to'];

                                        $set('certificate_valid_to', Carbon::parse($validTo)->format('Y-m-d'));
                                        $set('certificate_details', $this->setCertificateDetailsAttribute($response['details']));
                                    }
                                }),
                        ])
                        ->icon(PhosphorIcons::Globe)
                        ->schema([
                            Toggle::make('fiscalization_enabled')
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    if (!$state) {
                                        $set('certificate_path', null);
                                        $set('certificate_password', null);
                                        $set('certificate_details', null);
                                        $set('certificate_valid_to', null);
                                    }
                                })
                                ->inline(false)
                                ->live(true)
                                ->label('Enable fiscalisation'),

                            ToggleButtons::make('fiscalization_demo')
                                ->label('Demo fiscalisation')
                                ->inline()
                                ->boolean()
                                ->default(true)
                                ->required(fn($get) => $get('fiscalization_enabled'))
                                ->disabled(fn($get) => !$get('fiscalization_enabled')),

                            FileUpload::make('certificate_path')
                                ->directory(fn() => 'certs/' . $this->getRecord()->subdomain)
                                ->acceptedFileTypes([
                                    'application/x-pkcs12',
                                    'application/x-pkcs12-certificates',
                                    '.p12',
                                    '.pfx',
                                ])
                                ->moveFiles()
                                ->storeFileNamesIn('certificate_path')
                                ->preserveFilenames()
                                ->required(fn($get) => $get('fiscalization_enabled'))
                                ->label('Certificate'),

                            TextInput::make('certificate_password')
                                ->password()
                                ->live()
                                ->required(fn($get) => $get('fiscalization_enabled'))
                                ->readOnly(fn($get) => !$get('fiscalization_enabled'))
                                ->label('Password'),

                            Select::make('sequence_mark')
                                ->label('Sequence mark')
                                ->required(fn($get) => $get('fiscalization_enabled'))
                                ->visible(fn($get) => $get('fiscalization_enabled'))
                                ->options([
                                    'P' => 'P - Business location',
                                    'N' => 'N - Register device',
                                ]),

                            Fieldset::make('Certificate details')
                                ->columnSpanFull()
                                ->columns(1)
                                ->visible(fn($get) => $get('fiscalization_enabled'))
                                ->schema([
                                    SimpleAlert::make('certificate-info')
                                        ->columnSpanFull()
                                        ->title('Certificate information')
                                        ->border()
                                        ->icon(PhosphorIcons::Info)
                                        ->description(function ($get) {
                                            $validTo = $get('certificate_valid_to') ? Carbon::parse($get('certificate_valid_to')) : null;

                                            if ($validTo) {
                                                return "The certificate is valid until {$validTo->format('d.m.Y')}";
                                            }

                                            return "The certificate is not valid";
                                        })
                                        ->color(function ($get) {
                                            $validTo = $get('certificate_valid_to') ? Carbon::parse($get('certificate_valid_to')) : null;
                                            return $validTo ? 'success' : 'danger';
                                        }),

                                    DatePicker::make('certificate_valid_to')
                                        ->readOnly()
                                        ->format('d.m.Y')
                                        ->label('Expiration date'),

                                    KeyValue::make('certificate_details')
                                        ->deletable(false)
                                        ->live()
                                        ->addable(false)
                                        ->editableValues(false)
                                        ->editableKeys(false)
                                        ->label('Certificate details'),
                                ]),
                        ]),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Save')
                                ->icon(PhosphorIcons::Check)
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ])
            ->record($this->getRecord())
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if (is_array($data['certificate_path'])) {
            $data['certificate_path'] = array_values($data['certificate_path'])[0] ?? null;
        }

        if (!($data['fiscalization_enabled'])) {
            $data['certificate_path'] = null;
            $data['certificate_password'] = null;
            $data['certificate_valid_to'] = null;
            $data['certificate_details'] = null;
        }

        $this->getRecord()->update($data);

        Notification::make()
            ->success()
            ->title('Saved')
            ->send();
    }

    private function getCertificateValidResponse($certificateName, $password): array
    {
        return (new FiscalisationService())->testCertificate(storage_path("app/private/{$certificateName}"), $password);
    }

    public function getRecord(): ?Organisation
    {
        return auth()->user()->organisation()->first();
    }

    public function setCertificateDetailsAttribute($value): array
    {
        if (is_array($value)) {
            return collect($value)->flatMap(function ($v, $k) {
                return is_array($v)
                    ? collect($v)->mapWithKeys(fn($vv, $kk) => ["{$k}_{$kk}" => $vv])
                    : [$k => $v];
            })->toArray();
        }

        return [];
    }
}
