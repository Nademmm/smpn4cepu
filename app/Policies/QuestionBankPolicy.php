<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\QuestionBank;
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
        return $authUser->can('View:QuestionBank');
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

        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return (int) $questionBank->created_by === (int) $authUser->id;
    }

    public function delete(AuthUser $authUser, QuestionBank $questionBank): bool
    {
        if (!$authUser->can('Delete:QuestionBank')) {
            return false;
        }

        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return (int) $questionBank->created_by === (int) $authUser->id;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:QuestionBank');
    }

    public function restore(AuthUser $authUser, QuestionBank $questionBank): bool
    {
        return $authUser->can('Restore:QuestionBank');
    }

    public function forceDelete(AuthUser $authUser, QuestionBank $questionBank): bool
    {
        return $authUser->can('ForceDelete:QuestionBank');
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
        return $authUser->can('Replicate:QuestionBank');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:QuestionBank');
    }

}