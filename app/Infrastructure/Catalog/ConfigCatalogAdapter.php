<?php

namespace App\Infrastructure\Catalog;

use App\Domain\Quote\CatalogPort;
use App\Domain\Quote\CatalogProduct;

/**
 * Adapter that reads the trusted catalog from config/catalog.php.
 */
final class ConfigCatalogAdapter implements CatalogPort
{
    public function find(string $sku): ?CatalogProduct
    {
        $catalog = config('catalog');

        $entry = $catalog[$sku] ?? null;

        if (! is_array($entry)) {
            return null;
        }

        return new CatalogProduct(
            sku: $sku,
            size: $entry['size'],
            rating: $entry['rating'],
            description: $entry['description'],
            unitPrice: $entry['unit_price'],
        );
    }

    public function resolveSku(?string $requirement): ?string
    {
        if ($requirement === null) {
            return null;
        }

        if (str_contains($requirement, 'fire-rated')) {
            return 'KRP-150FR-2436';
        }

        if (str_contains($requirement, 'general')) {
            return 'KDW-2436';
        }

        return null;
    }
}
