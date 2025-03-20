<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        JobListing::class => \App\Policies\JobListingPolicy::class,
        Application::class => \App\Policies\ApplicationPolicy::class,
        User::class => \App\Policies\UserPolicy::class,
        Notification::class => \App\Policies\NotificationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define role-based gates
        Gate::define('admin-access', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('recruiter-access', function (User $user) {
            return $user->role === 'recruiter' || $user->role === 'admin';
        });

        Gate::define('candidate-access', function (User $user) {
            return $user->role === 'candidate' || $user->role === 'admin';
        });

        // Stats access gates
        Gate::define('view-recruiter-stats', function (User $user) {
            return $user->role === 'recruiter' || $user->role === 'admin';
        });

        Gate::define('view-global-stats', function (User $user) {
            return $user->role === 'admin';
        });
    }
}