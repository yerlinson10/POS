<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Policies\ProductPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\InvoicePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Category::class => CategoryPolicy::class,
        Customer::class => CustomerPolicy::class,
        Invoice::class => InvoicePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
