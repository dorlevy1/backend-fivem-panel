<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class DiscordProvider extends EloquentUserProvider
{

    /**
     * Validate a user against the given credentials.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array $credentials
     *
     */
    public function validateCredentials(UserContract $user, array $credentials): array|bool
    {

        if ($user->discord_id === $credentials['discord_id']) {
            return true;
        }

        return false;
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
}