<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LearningMaterial;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LearningMaterialPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LearningMaterial');
    }

    public function view(AuthUser $authUser, LearningMaterial $learningMaterial): bool
    {
        if (! $authUser->can('View:LearningMaterial')) {
            return false;
        }

        if ($authUser instanceof User && $authUser->hasRole('guru') && ! $authUser->hasAnyRole(['super_admin', 'operator_sekolah'])) {
            if ((int) $learningMaterial->created_by === (int) $authUser->id) {
                return true;
            }

            return $authUser->canAccessSubjectContent(
                $learningMaterial->subject_id,
                $learningMaterial->grade_level,
                $learningMaterial->target_classes
            );
        }

        return true;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LearningMaterial');
    }

    public function update(AuthUser $authUser, LearningMaterial $learningMaterial): bool
    {
        if (!$authUser->can('Update:LearningMaterial')) {
            return false;
        }

        if ($authUser->hasAnyRole(['super_admin', 'operator_sekolah'])) {
            return true;
        }

        if ((int) $learningMaterial->created_by === (int) $authUser->id) {
            return true;
        }

        return $authUser instanceof User && $authUser->canAccessSubjectContent(
            $learningMaterial->subject_id,
            $learningMaterial->grade_level,
            $learningMaterial->target_classes
        );
    }

    public function delete(AuthUser $authUser, LearningMaterial $learningMaterial): bool
    {
        if (!$authUser->can('Delete:LearningMaterial')) {
            return false;
        }

        if ($authUser->hasAnyRole(['super_admin', 'operator_sekolah'])) {
            return true;
        }

        if ((int) $learningMaterial->created_by === (int) $authUser->id) {
            return true;
        }

        return $authUser instanceof User && $authUser->canAccessSubjectContent(
            $learningMaterial->subject_id,
            $learningMaterial->grade_level,
            $learningMaterial->target_classes
        );
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LearningMaterial');
    }

    public function restore(AuthUser $authUser, LearningMaterial $learningMaterial): bool
    {
        if (!$authUser->can('Restore:LearningMaterial')) {
            return false;
        }

        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return (int) $learningMaterial->created_by === (int) $authUser->id;
        }

        return true;
    }

    public function forceDelete(AuthUser $authUser, LearningMaterial $learningMaterial): bool
    {
        if (!$authUser->can('ForceDelete:LearningMaterial')) {
            return false;
        }

        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return (int) $learningMaterial->created_by === (int) $authUser->id;
        }

        return true;
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LearningMaterial');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LearningMaterial');
    }

    public function replicate(AuthUser $authUser, LearningMaterial $learningMaterial): bool
    {
        if (!$authUser->can('Replicate:LearningMaterial')) {
            return false;
        }

        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return (int) $learningMaterial->created_by === (int) $authUser->id;
        }

        return true;
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LearningMaterial');
    }

}