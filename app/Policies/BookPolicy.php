<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Book;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class BookPolicy
{
    use HandlesAuthorization;

    public function before(AuthUser $user, string $ability): ?bool
    {
        if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Book') || $authUser->can('manage_library') || $authUser->can('view_admin_panel');
    }

    public function view(AuthUser $authUser, Book $book): bool
    {
        return $authUser->can('View:Book') || $authUser->can('manage_library') || $authUser->can('view_admin_panel');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Book') || $authUser->can('manage_library') || $authUser->can('view_admin_panel');
    }

    public function update(AuthUser $authUser, Book $book): bool
    {
        return $authUser->can('Update:Book') || $authUser->can('manage_library') || $authUser->can('view_admin_panel');
    }

    public function delete(AuthUser $authUser, Book $book): bool
    {
        return $authUser->can('Delete:Book') || $authUser->can('manage_library') || $authUser->can('view_admin_panel');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Book') || $authUser->can('manage_library');
    }

    public function restore(AuthUser $authUser, Book $book): bool
    {
        return $authUser->can('Restore:Book') || $authUser->can('manage_library');
    }

    public function forceDelete(AuthUser $authUser, Book $book): bool
    {
        return $authUser->can('ForceDelete:Book') || $authUser->can('manage_library');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Book') || $authUser->can('manage_library');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Book') || $authUser->can('manage_library');
    }

    public function replicate(AuthUser $authUser, Book $book): bool
    {
        return $authUser->can('Replicate:Book') || $authUser->can('manage_library');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Book') || $authUser->can('manage_library');
    }
}

