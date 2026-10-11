<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BookReservation;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookReservationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BookReservation');
    }

    public function view(AuthUser $authUser, BookReservation $bookReservation): bool
    {
        return $authUser->can('View:BookReservation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BookReservation');
    }

    public function update(AuthUser $authUser, BookReservation $bookReservation): bool
    {
        return $authUser->can('Update:BookReservation');
    }

    public function delete(AuthUser $authUser, BookReservation $bookReservation): bool
    {
        return $authUser->can('Delete:BookReservation');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BookReservation');
    }

    public function restore(AuthUser $authUser, BookReservation $bookReservation): bool
    {
        return $authUser->can('Restore:BookReservation');
    }

    public function forceDelete(AuthUser $authUser, BookReservation $bookReservation): bool
    {
        return $authUser->can('ForceDelete:BookReservation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BookReservation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BookReservation');
    }

    public function replicate(AuthUser $authUser, BookReservation $bookReservation): bool
    {
        return $authUser->can('Replicate:BookReservation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BookReservation');
    }

}