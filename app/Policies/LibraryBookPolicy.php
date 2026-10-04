<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LibraryBook;
use Illuminate\Auth\Access\HandlesAuthorization;

class LibraryBookPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LibraryBook');
    }

    public function view(AuthUser $authUser, LibraryBook $libraryBook): bool
    {
        return $authUser->can('View:LibraryBook');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LibraryBook');
    }

    public function update(AuthUser $authUser, LibraryBook $libraryBook): bool
    {
        return $authUser->can('Update:LibraryBook');
    }

    public function delete(AuthUser $authUser, LibraryBook $libraryBook): bool
    {
        return $authUser->can('Delete:LibraryBook');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LibraryBook');
    }

    public function restore(AuthUser $authUser, LibraryBook $libraryBook): bool
    {
        return $authUser->can('Restore:LibraryBook');
    }

    public function forceDelete(AuthUser $authUser, LibraryBook $libraryBook): bool
    {
        return $authUser->can('ForceDelete:LibraryBook');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LibraryBook');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LibraryBook');
    }

    public function replicate(AuthUser $authUser, LibraryBook $libraryBook): bool
    {
        return $authUser->can('Replicate:LibraryBook');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LibraryBook');
    }

}