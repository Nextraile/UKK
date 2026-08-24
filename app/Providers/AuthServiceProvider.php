<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Identity\Models\User;
use App\Domain\Identity\Policies\UserPolicy;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\KostDocumentRequirement;
use App\Domain\Kost\Models\KostImage;
use App\Domain\Kost\Policies\KostDocumentRequirementPolicy;
use App\Domain\Kost\Policies\KostImagePolicy;
use App\Domain\Kost\Policies\KostPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Service provider for authentication and authorization services.
 *
 * Registers the model-to-policy mappings for the application.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Kost::class => KostPolicy::class,
        KostImage::class => KostImagePolicy::class,
        KostDocumentRequirement::class => KostDocumentRequirementPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
