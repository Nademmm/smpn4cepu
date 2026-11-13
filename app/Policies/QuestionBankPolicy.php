<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\QuestionBank;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionBankPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:QuestionBank');
    }

    public function view(AuthUser $authUser, QuestionBank $questionBank): bool
    {
        if (! $authUser->can('View:QuestionBank')) {
            return false;
        }

        if ($authUser instanceof User && $authUser->hasRole('guru') && ! $authUser->hasAnyRole(['super_admin', 'operator_sekolah'])) {
            if ((int) $questionBank->created_by === (int) $authUser->id) {
                return true;
            }

            return $authUser->canAccessSubjectContent(
                $questionBank->subject_id,
                $questionBank->grade_level,
                $questionBank->target_classes
            );
        }

        return true;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:QuestionBank');
    }

    public function update(AuthUser $authUser, QuestionBank $questionBank): bool
    {
        if (!$authUser->can('Update:QuestionBank')) {
            return false;
        }

        if ($authUser->hasAnyRole(['super_admin', 'operator_sekolah'])) {
            return true;
        }

        if ((int) $questionBank->created_by === (int) $authUser->id) {
            return true;
        }

        return $authUser instanceof User && $authUser->canAccessSubjectContent(
            $questionBank->subject_id,
            $questionBank->grade_level,
            $questionBank->target_classes
        );
    }

    public function delete(AuthUser $authUser, QuestionBank $questionBank): bool
    {
        if (!$authUser->can('Delete:QuestionBank')) {
            return false;
        }

        if ($authUser->hasAnyRole(['super_admin', 'operator_sekolah'])) {
            return true;
        }

        if ((int) $questionBank->created_by === (int) $authUser->id) {
            return true;
        }

        return $authUser instanceof User && $authUser->canAccessSubjectContent(
            $questionBank->subject_id,
            $questionBank->grade_level,
            $questionBank->target_classes
        );
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:QuestionBank');
    }

    public function restore(AuthUser $authUser, QuestionBank $questionBank): bool
    {
        if (!$authUser->can('Restore:QuestionBank')) {
            return false;
        }

        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return (int) $questionBank->created_by === (int) $authUser->id;
        }

        return true;
    }

    public function forceDelete(AuthUser $authUser, QuestionBank $questionBank): bool
    {
        if (!$authUser->can('ForceDelete:QuestionBank')) {
            return false;
        }

        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return (int) $questionBank->created_by === (int) $authUser->id;
        }

        return true;
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:QuestionBank');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:QuestionBank');
    }

    public function replicate(AuthUser $authUser, QuestionBank $questionBank): bool
    {
        if (!$authUser->can('Replicate:QuestionBank')) {
            return false;
        }

        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return (int) $questionBank->created_by === (int) $authUser->id;
        }

        return true;
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:QuestionBank');
    }

}