<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PostPolicy
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
        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return false;
        }

        return $authUser->can('ViewAny:Post') || $authUser->hasRole('super_admin');
    }

    public function view(AuthUser $authUser, Post $post): bool
    {
        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return false;
        }

        return $authUser->can('View:Post') || $authUser->hasRole('super_admin');
    }

    public function create(AuthUser $authUser): bool
    {
        // Berita dikelola khusus oleh Administrator Utama
        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return false;
        }

        return $authUser->can('Create:Post') || $authUser->can('manage_posts');
    }

    public function update(AuthUser $authUser, Post $post): bool
    {
        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return false;
        }

        return $authUser->can('Update:Post') || $authUser->can('manage_posts');
    }

    public function delete(AuthUser $authUser, Post $post): bool
    {
        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return false;
        }

        return $authUser->can('Delete:Post') || $authUser->can('manage_posts');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return false;
        }

        return $authUser->can('DeleteAny:Post') || $authUser->can('manage_posts');
    }

    public function restore(AuthUser $authUser, Post $post): bool
    {
        return $authUser->can('Restore:Post');
    }

    public function forceDelete(AuthUser $authUser, Post $post): bool
    {
        return $authUser->can('ForceDelete:Post');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Post');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Post');
    }

    public function replicate(AuthUser $authUser, Post $post): bool
    {
        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return false;
        }

        return $authUser->can('Replicate:Post');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Post');
    }
}
