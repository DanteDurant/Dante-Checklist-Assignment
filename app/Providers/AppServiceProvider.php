<?php

namespace App\Providers;

use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use App\Models\Export;
use App\Policies\ChecklistInstancePolicy;
use App\Policies\ChecklistTemplatePolicy;
use App\Policies\ExportPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(ChecklistTemplate::class, ChecklistTemplatePolicy::class);
        Gate::policy(ChecklistInstance::class, ChecklistInstancePolicy::class);
        Gate::policy(Export::class, ExportPolicy::class);
    }
}
