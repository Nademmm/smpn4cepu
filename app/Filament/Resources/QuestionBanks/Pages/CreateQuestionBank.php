<?php

namespace App\Filament\Resources\QuestionBanks\Pages;

use App\Filament\Resources\QuestionBanks\QuestionBankResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestionBank extends CreateRecord
{
    protected static string $resource = QuestionBankResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] ??= auth()->id();
        return $data;
    }
}

