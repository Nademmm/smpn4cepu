<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SchoolDocument;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchoolDocumentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SchoolDocument');
    }

    public function view(AuthUser $authUser, SchoolDocument $schoolDocument): bool
    {
        return $authUser->can('View:SchoolDocument');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SchoolDocument');
    }

    public function update(AuthUser $authUser, SchoolDocument $schoolDocument): bool
    {
        return $authUser->can('Update:SchoolDocument');
    }

    public function delete(AuthUser $authUser, SchoolDocument $schoolDocument): bool
    {
        return $authUser->can('Delete:SchoolDocument');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SchoolDocument');
    }

    public function restore(AuthUser $authUser, SchoolDocument $schoolDocument): bool
    {
        return $authUser->can('Restore:SchoolDocument');
    }

    public function forceDelete(AuthUser $authUser, SchoolDocument $schoolDocument): bool
    {
        return $authUser->can('ForceDelete:SchoolDocument');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SchoolDocument');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SchoolDocument');
    }

    public function replicate(AuthUser $authUser, SchoolDocument $schoolDocument): bool
    {
        return $authUser->can('Replicate:SchoolDocument');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SchoolDocument');
    }

}