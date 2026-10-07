<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Trusted Catalog
    |--------------------------------------------------------------------------
    |
    | The single source of truth for product identifiers, descriptions and
    | prices. The model (AI) and the customer are never allowed to set these
    | values: the server resolves them from here only.
    |
    */
    'KRP-150FR-2436' => [
        'size' => '24 x 36',
        'rating' => 'Fire-rated',
        'description' => 'KRP-150FR access panel',
        'unit_price' => 428.00,
    ],
    'KDW-2436' => [
        'size' => '24 x 36',
        'rating' => 'General',
        'description' => 'KDW general-purpose panel',
        'unit_price' => 186.00,
    ],
];
