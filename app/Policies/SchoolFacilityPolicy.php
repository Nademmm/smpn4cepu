<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SchoolFacility;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchoolFacilityPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SchoolFacility');
    }

    public function view(AuthUser $authUser, SchoolFacility $schoolFacility): bool
    {
        return $authUser->can('View:SchoolFacility');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SchoolFacility');
    }

    public function update(AuthUser $authUser, SchoolFacility $schoolFacility): bool
    {
        return $authUser->can('Update:SchoolFacility');
    }

    public function delete(AuthUser $authUser, SchoolFacility $schoolFacility): bool
    {
        return $authUser->can('Delete:SchoolFacility');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SchoolFacility');
    }

    public function restore(AuthUser $authUser, SchoolFacility $schoolFacility): bool
    {
        return $authUser->can('Restore:SchoolFacility');
    }

    public function forceDelete(AuthUser $authUser, SchoolFacility $schoolFacility): bool
    {
        return $authUser->can('ForceDelete:SchoolFacility');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SchoolFacility');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SchoolFacility');
    }

    public function replicate(AuthUser $authUser, SchoolFacility $schoolFacility): bool
    {
        return $authUser->can('Replicate:SchoolFacility');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SchoolFacility');
    }

}