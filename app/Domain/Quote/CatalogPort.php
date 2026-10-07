<?php

namespace App\Domain\Quote;

/**
 * Port for the trusted catalog — the single source of truth for product
 * identifiers, descriptions and prices (business rule #1).
 *
 * The domain depends on this port only; the concrete catalog (config, DB,
 * external API) is an adapter.
 */
interface CatalogPort
{
    public function find(string $sku): ?CatalogProduct;

    public function resolveSku(?string $requirement): ?string;
}
