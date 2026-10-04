<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ElectionCandidate;
use Illuminate\Auth\Access\HandlesAuthorization;

class ElectionCandidatePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ElectionCandidate');
    }

    public function view(AuthUser $authUser, ElectionCandidate $electionCandidate): bool
    {
        return $authUser->can('View:ElectionCandidate');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ElectionCandidate');
    }

    public function update(AuthUser $authUser, ElectionCandidate $electionCandidate): bool
    {
        return $authUser->can('Update:ElectionCandidate');
    }

    public function delete(AuthUser $authUser, ElectionCandidate $electionCandidate): bool
    {
        return $authUser->can('Delete:ElectionCandidate');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ElectionCandidate');
    }

    public function restore(AuthUser $authUser, ElectionCandidate $electionCandidate): bool
    {
        return $authUser->can('Restore:ElectionCandidate');
    }

    public function forceDelete(AuthUser $authUser, ElectionCandidate $electionCandidate): bool
    {
        return $authUser->can('ForceDelete:ElectionCandidate');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ElectionCandidate');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ElectionCandidate');
    }

    public function replicate(AuthUser $authUser, ElectionCandidate $electionCandidate): bool
    {
        return $authUser->can('Replicate:ElectionCandidate');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ElectionCandidate');
    }

}