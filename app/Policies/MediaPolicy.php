<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MediaPolicy
{
    /**
     * Determine whether the user can view the media gallery.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific media details.
     */
    public function view(User $user, Media $media): bool
    {
        return $media->is_active;
    }

    /**
     * Determine whether the user can request a secure streaming token/URL.
     */
    public function stream(User $user, Media $media): Response
    {
        if (! $media->is_active) {
            return Response::deny('The requested media is inactive or currently unavailable.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Media $media): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Media $media): bool
    {
        return false;
    }
}
