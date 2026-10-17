<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Post;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Post');
    }

    public function view(AuthUser $authUser, Post $post): bool
    {
        return $authUser->can('View:Post');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Post');
    }

    public function update(AuthUser $authUser, Post $post): bool
    {
        if (!$authUser->can('Update:Post')) {
            return false;
        }

        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return (int) $post->author_id === (int) $authUser->id;
        }

        return true;
    }

    public function delete(AuthUser $authUser, Post $post): bool
    {
        if (!$authUser->can('Delete:Post')) {
            return false;
        }

        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return (int) $post->author_id === (int) $authUser->id;
        }

        return true;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Post');
    }

    public function restore(AuthUser $authUser, Post $post): bool
    {
        if (!$authUser->can('Restore:Post')) {
            return false;
        }

        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return (int) $post->author_id === (int) $authUser->id;
        }

        return true;
    }

    public function forceDelete(AuthUser $authUser, Post $post): bool
    {
        if (!$authUser->can('ForceDelete:Post')) {
            return false;
        }

        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return (int) $post->author_id === (int) $authUser->id;
        }

        return true;
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
        if (!$authUser->can('Replicate:Post')) {
            return false;
        }

        if ($authUser->hasRole('guru') && !$authUser->hasRole('super_admin')) {
            return (int) $post->author_id === (int) $authUser->id;
        }

        return true;
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Post');
    }

}