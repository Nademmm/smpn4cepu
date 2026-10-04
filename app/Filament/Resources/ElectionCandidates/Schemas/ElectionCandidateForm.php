<?php

namespace App\Filament\Resources\ElectionCandidates\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ElectionCandidateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('candidate_number')
                    ->required()
                    ->numeric(),
                TextInput::make('candidate_name')
                    ->required(),
                TextInput::make('vice_candidate_name')
                    ->required(),
                Textarea::make('vision')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('mission')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('photo_path'),
                TextInput::make('total_votes_cached')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
