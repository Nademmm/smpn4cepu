<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\StudentStatistic;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentStatisticPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StudentStatistic');
    }

    public function view(AuthUser $authUser, StudentStatistic $studentStatistic): bool
    {
        return $authUser->can('View:StudentStatistic');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StudentStatistic');
    }

    public function update(AuthUser $authUser, StudentStatistic $studentStatistic): bool
    {
        return $authUser->can('Update:StudentStatistic');
    }

    public function delete(AuthUser $authUser, StudentStatistic $studentStatistic): bool
    {
        return $authUser->can('Delete:StudentStatistic');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StudentStatistic');
    }

    public function restore(AuthUser $authUser, StudentStatistic $studentStatistic): bool
    {
        return $authUser->can('Restore:StudentStatistic');
    }

    public function forceDelete(AuthUser $authUser, StudentStatistic $studentStatistic): bool
    {
        return $authUser->can('ForceDelete:StudentStatistic');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StudentStatistic');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StudentStatistic');
    }

    public function replicate(AuthUser $authUser, StudentStatistic $studentStatistic): bool
    {
        return $authUser->can('Replicate:StudentStatistic');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StudentStatistic');
    }

}