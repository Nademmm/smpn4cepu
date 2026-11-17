<?php

namespace App\Filament\Resources\LearningMaterials\Pages;

use App\Filament\Resources\LearningMaterials\LearningMaterialResource;
use App\Support\SubjectPermissionFormHelper;
use App\Support\TemplateImportUiHelper;
use Filament\Resources\Pages\CreateRecord;

class CreateLearningMaterial extends CreateRecord
{
    protected static string $resource = LearningMaterialResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return SubjectPermissionFormHelper::validateAndNormalizePayload(
            TemplateImportUiHelper::stripTransientKeys($data),
            isCreate: true
        );
    }
}

