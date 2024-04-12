<?php

namespace App\Http\Guards;

use App\Models\Admin;
use Illuminate\Support\Facades\Gate;

/**
 * Bootstrap any application services.
 */
class DeleteUser
{

    public function boot(): void
    {
        Gate::define('delete-user', function (Admin $admin) {
            return str_contains('delete-user', $admin->access);
        });
    }
}