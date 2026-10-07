<?php

namespace App\Providers;

use App\Domain\Quote\CatalogPort;
use App\Infrastructure\Catalog\ConfigCatalogAdapter;
use Illuminate\Support\ServiceProvider;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CatalogPort::class, ConfigCatalogAdapter::class);
    }
}
