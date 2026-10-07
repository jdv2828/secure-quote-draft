<?php

namespace App\Domain\Quote;

/**
 * A product as described by the trusted catalog.
 */
final readonly class CatalogProduct
{
    public function __construct(
        public string $sku,
        public string $size,
        public string $rating,
        public string $description,
        public float $unitPrice,
    ) {
    }
}
