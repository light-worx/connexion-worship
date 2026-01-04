<?php

declare(strict_types=1);

namespace Modules\Worship\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Worship\Models\Chord;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChordPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Chord');
    }

    public function view(AuthUser $authUser, Chord $chord): bool
    {
        return $authUser->can('View:Chord');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Chord');
    }

    public function update(AuthUser $authUser, Chord $chord): bool
    {
        return $authUser->can('Update:Chord');
    }

    public function delete(AuthUser $authUser, Chord $chord): bool
    {
        return $authUser->can('Delete:Chord');
    }

    public function restore(AuthUser $authUser, Chord $chord): bool
    {
        return $authUser->can('Restore:Chord');
    }

    public function forceDelete(AuthUser $authUser, Chord $chord): bool
    {
        return $authUser->can('ForceDelete:Chord');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Chord');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Chord');
    }

    public function replicate(AuthUser $authUser, Chord $chord): bool
    {
        return $authUser->can('Replicate:Chord');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Chord');
    }

}