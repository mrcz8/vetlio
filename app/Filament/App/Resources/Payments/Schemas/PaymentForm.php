<?php

namespace App\Filament\App\Resources\Payments\Schemas;

use App\Enums\Icons\PhosphorIcons;
use App\Enums\PaymentMethod;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Payment Code')
                    ->disabled(),

                Select::make('branch_id')
                    ->disabled()
                    ->relationship('branch', 'name')
                    ->default(Filament::getTenant()->id)
                    ->required()
                    ->label('Branch'),

                DateTimePicker::make('payment_at')
                    ->default(now())
                    ->seconds(false)
                    ->label('Payment Date')
                    ->required(),

                TextInput::make('amount')
                    ->required()
                    ->formatStateUsing(function ($state) {
                        return Number::format($state, 2);
                    })
                    ->label('Amount')
                    ->suffixIcon(PhosphorIcons::CurrencyEur),

                ToggleButtons::make('payment_method_id')
                    ->grouped()
                    ->label('Payment Method')
                    ->required()
                    ->options(PaymentMethod::class),

                Select::make('client_id')
                    ->relationship('client', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                    ->required()
                    ->label('Client'),

                Textarea::make('note')
                    ->label('Note')
                    ->columnSpanFull(),
            ]);
    }
}
