<?php

namespace App\Filament\App\Clusters\Settings\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Clusters\Settings\SettingsCluster;
use App\Models\Organisation;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;

class InvoiceSettings extends Page
{
    protected string $view = 'filament.app.clusters.settings.pages.invoice-settings';

    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'Invoices';

    protected static ?string $title = 'Invoice settings';

    protected static string|null|\BackedEnum $navigationIcon = PhosphorIcons::Money;

    protected static string|null|\UnitEnum $navigationGroup = 'Finance';

    public ?array $data = [];

    protected static ?int $navigationSort = 5;

    public function mount(): void
    {
        $this->form->fill($this->getRecord()?->settings()->get('invoice'));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([])
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

        collect($data)->each(function ($value, $key) use (&$data) {
            $this->getRecord()->settings()->update('offer', $data);
        });

        Notification::make()
            ->success()
            ->title('Saved')
            ->send();
    }

    public function getRecord(): ?Organisation
    {
        return auth()->user()->organisation()->first();
    }
}
