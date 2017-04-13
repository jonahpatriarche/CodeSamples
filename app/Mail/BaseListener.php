<?php

namespace App\Listeners;

use App\Role
use App\User;
use Illuminate\Support\Facades\Log;

abstract class BaseListener
{
    /**
     * If user with the specific staff email was not found in the database, fall back to the main email
     *  - if the main email is also not found, send to the first super user
     *
     * @return \App\User
     */
    protected function getFallbackEmail()
    {
        try {
            $staff = User::where('email', env('DEFAULT_EMAIL'))
                ->firstOrFail();
        }
        catch (\Exception $e) {
            $staff = User::where('role_id', Role::SUPER_USER)
                ->first();

            Log::warning(
                env('DEFAULT_EMAIL') . ' is set as default staff email, but user was not found in the database. ' .
                'Email was sent to the first user with super privileges: ' . $staff->email
            );
        }

        return $staff;
    }
}
