<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $authUser): bool
    {
        return $authUser->hasRole('super_admin') || $authUser->can('ViewAny:User');
    }

    public function view(User $authUser, User $targetUser): bool
    {
        return $authUser->hasRole('super_admin') || $authUser->can('View:User') || $authUser->id === $targetUser->id;
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasRole('super_admin') || $authUser->can('Create:User');
    }

    public function update(User $authUser, User $targetUser): bool
    {
        return $authUser->hasRole('super_admin') || $authUser->can('Update:User') || $authUser->id === $targetUser->id;
    }

    public function delete(User $authUser, User $targetUser): bool
    {
        // Cegah menghapus akun sendiri
        if ($authUser->id === $targetUser->id) {
            return false;
        }

        return $authUser->hasRole('super_admin') || $authUser->can('Delete:User');
    }

    public function deleteAny(User $authUser): bool
    {
        return $authUser->hasRole('super_admin') || $authUser->can('DeleteAny:User');
    }
}
