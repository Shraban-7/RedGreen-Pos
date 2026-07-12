<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Brand\BrandRepositoryInterface::class,
            \App\Repositories\Brand\BrandRepository::class    
        );
        $this->app->bind(
            \App\Repositories\Product\ProductRepositoryInterface::class,
            \App\Repositories\Product\ProductRepository::class
        );
        $this->app->bind(
            \App\Repositories\Supplier\SupplierRepositoryInterface::class,
            \App\Repositories\Supplier\SupplierRepository::class
        );
        $this->app->bind(
            \App\Repositories\Customer\CustomerRepositoryInterface::class,
            \App\Repositories\Customer\CustomerRepository::class
        );
        $this->app->bind(
            \App\Repositories\Sale\SaleRepositoryInterface::class,
            \App\Repositories\Sale\SaleRepository::class
        );
        $this->app->bind(
            \App\Repositories\Expense\ExpenseRepositoryInterface::class,
            \App\Repositories\Expense\ExpenseRepository::class
        );
        $this->app->bind(
            \App\Repositories\ExpenseCategory\ExpenseCategoryRepositoryInterface::class,
            \App\Repositories\ExpenseCategory\ExpenseCategoryRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
