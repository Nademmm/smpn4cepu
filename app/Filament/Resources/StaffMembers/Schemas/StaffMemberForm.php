<?php

namespace App\Filament\Resources\StaffMembers\Schemas;

use App\Enums\EmploymentStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StaffMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nip'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('position')
                    ->required(),
                Select::make('employment_status')
                    ->options(EmploymentStatus::class)
                    ->default('PNS')
                    ->required(),
                TextInput::make('photo_path'),
                TextInput::make('display_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
