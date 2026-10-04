<?php

namespace App\Filament\Resources\StudentStatistics\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentStatisticForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('academic_year')
                    ->required(),
                TextInput::make('grade_level')
                    ->required()
                    ->numeric(),
                TextInput::make('class_name')
                    ->required(),
                TextInput::make('male_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('female_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_count')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
