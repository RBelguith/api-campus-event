<?php

namespace App\Policies;

use App\Models\CampusEvent;
use App\Models\User;

class CampusEventPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CampusEvent $event): bool
    {
        return $user->id === $event->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CampusEvent $event): bool
    {
        return $this->update($user, $event);
    }
}
