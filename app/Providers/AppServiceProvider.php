<?php

namespace App\Providers;

use App\Contracts\HolidayProvider;
use App\Enums\Icons\PhosphorIcons;
use App\Models\Invoice;
use App\Services\Holidays\NagerDateHolidayProvider;
use Awcodes\Palette\Forms\Components\ColorPicker;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(HolidayProvider::class, fn() => new NagerDateHolidayProvider());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Number::useCurrency('EUR');

        $this->initGate();

        //Filament
        $this->configureFilamentActions();
        $this->configureFilamentComponents();
    }

    public function configureFilamentComponents(): void
    {
        Table::configureUsing(function (Table $table) {
            $table->striped(true);
            $table->emptyStateHeading('No data');
            $table->emptyStateDescription('Currently there is no data to display.');
            $table->emptyStateIcon(PhosphorIcons::MinusCircle);
            $table->emptyStateActions([CreateAction::make()]);
        });

        Select::configureUsing(function (Select $select) {
            $select->native(false);
        });

        ColorPicker::configureUsing(function (ColorPicker $colorPicker) {
            $colorPicker->size('sm');
            $colorPicker->required();
            $colorPicker->storeAsKey();
            $colorPicker->colors([
                '#28B8DA' => Color::hex('#28B8DA'),
                '#03a9f4' => Color::hex('#03a9f4'),
                '#c53da9' => Color::hex('#c53da9'),
                '#8e24aa' => Color::hex('#8e24aa'),
                '#d81b60' => Color::hex('#d81b60'),
                '#7cb342' => Color::hex('#7cb342'),
                '#fb8c00' => Color::hex('#fb8c00')
            ]);
        });
    }

    public function configureFilamentActions(): void
    {
        CreateAction::configureUsing(function (CreateAction $action) {
            $action->label('Add');
            $action->modalHeading('Add new item');
            $action->icon(Heroicon::Plus);
            $action->slideOver();
            $action->modalIcon(Heroicon::Plus);
        });

        EditAction::configureUsing(function (EditAction $action) {
            $action->hiddenLabel();
            $action->modalHeading('Edit item');
            $action->icon(Heroicon::Pencil);
            $action->slideOver();
            $action->modalIcon(Heroicon::Pencil);
        });

        DeleteAction::configureUsing(function (DeleteAction $action) {
            $action->hiddenLabel();
            $action->modalHeading('Delete item');
            $action->icon(Heroicon::Trash);
            $action->modalIcon(Heroicon::Trash);
        });

        ViewAction::configureUsing(function (ViewAction $action) {
            $action->hiddenLabel();
            $action->modalHeading('View');
            $action->icon(Heroicon::Eye);
            $action->slideOver();
            $action->modalIcon(Heroicon::Eye);
        });
    }

    private function initGate(): void
    {
        Gate::before(function (Authenticatable $user, string $ability) {
            if ($user->administrator) {
                return true;
            }

            return null;
        });
    }
}
