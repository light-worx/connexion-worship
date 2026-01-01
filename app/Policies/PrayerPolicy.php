<?php

declare(strict_types=1);

namespace Modules\Worship\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Worship\Models\Prayer;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrayerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Prayer');
    }

    public function view(AuthUser $authUser, Prayer $prayer): bool
    {
        return $authUser->can('View:Prayer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Prayer');
    }

    public function update(AuthUser $authUser, Prayer $prayer): bool
    {
        return $authUser->can('Update:Prayer');
    }

    public function delete(AuthUser $authUser, Prayer $prayer): bool
    {
        return $authUser->can('Delete:Prayer');
    }

    public function restore(AuthUser $authUser, Prayer $prayer): bool
    {
        return $authUser->can('Restore:Prayer');
    }

    public function forceDelete(AuthUser $authUser, Prayer $prayer): bool
    {
        return $authUser->can('ForceDelete:Prayer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Prayer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Prayer');
    }

    public function replicate(AuthUser $authUser, Prayer $prayer): bool
    {
        return $authUser->can('Replicate:Prayer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Prayer');
    }

}