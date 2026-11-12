<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Subject;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubjectPolicy
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
        return $authUser->can('ViewAny:Subject') || $authUser->hasRole(['super_admin', 'guru']);
    }

    public function view(AuthUser $authUser, Subject $subject): bool
    {
        return $authUser->can('View:Subject') || $authUser->hasRole(['super_admin', 'guru']);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function update(AuthUser $authUser, Subject $subject): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function delete(AuthUser $authUser, Subject $subject): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function restore(AuthUser $authUser, Subject $subject): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function forceDelete(AuthUser $authUser, Subject $subject): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function replicate(AuthUser $authUser, Subject $subject): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }
}
