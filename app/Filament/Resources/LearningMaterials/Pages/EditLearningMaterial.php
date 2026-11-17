<?php

namespace App\Filament\Resources\LearningMaterials\Pages;

use App\Filament\Resources\LearningMaterials\LearningMaterialResource;
use App\Support\SubjectPermissionFormHelper;
use App\Support\TemplateImportUiHelper;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditLearningMaterial extends EditRecord
{
    protected static string $resource = LearningMaterialResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return SubjectPermissionFormHelper::validateAndNormalizePayload(
            TemplateImportUiHelper::stripTransientKeys($data),
            isCreate: false
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
