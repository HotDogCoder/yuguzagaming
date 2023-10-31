<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Listen to the User deleting event.
     *
     * @param User $user
     * @return void
     */
    public function deleting(User $user): void
    {
        //
    }
}
