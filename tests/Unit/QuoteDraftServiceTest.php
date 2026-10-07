<?php

namespace Tests\Unit;

use App\Domain\Quote\CatalogPort;
use App\Domain\Quote\CatalogProduct;
use App\Domain\Quote\QuoteDraftService;
use PHPUnit\Framework\TestCase;

/**
 * Pure domain tests: the correction rules run against an in-memory catalog
 * with no framework, no HTTP and no Eloquent.
 */
class QuoteDraftServiceTest extends TestCase
{
    private function catalog(): CatalogPort
    {
        return new class implements CatalogPort {
            private array $products = [
                'KRP-150FR-2436' => ['24 x 36', 'Fire-rated', 'KRP-150FR access panel', 428.0],
                'KDW-2436' => ['24 x 36', 'General', 'KDW general-purpose panel', 186.0],
            ];

            public function find(string $sku): ?CatalogProduct
            {
                if (! array_key_exists($sku, $this->products)) {
                    return null;
                }

                [$size, $rating, $description, $price] = $this->products[$sku];

                return new CatalogProduct($sku, $size, $rating, $description, $price);
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
        };
    }

    private function service(): QuoteDraftService
    {
        return new QuoteDraftService($this->catalog());
    }

    public function test_resolves_unknown_sku_from_fire_rated_requirement(): void
    {
        $result = $this->service()->correct(
            ['items' => [['sku' => 'FR-2436', 'qty' => 4, 'unit_price' => 99.0]]],
            'four 24 x 36 fire-rated access panels'
        );

        $this->assertSame('KRP-150FR-2436', $result['corrected']['items'][0]['sku']);
    }

    public function test_resolves_unknown_sku_from_general_requirement(): void
    {
        $result = $this->service()->correct(
            ['items' => [['sku' => 'BOGUS', 'qty' => 2]]],
            'general purpose panel'
        );

        $this->assertSame('KDW-2436', $result['corrected']['items'][0]['sku']);
        $this->assertSame(186.0, $result['corrected']['items'][0]['unit_price']);
    }

    public function test_unknown_sku_without_requirement_is_dropped(): void
    {
        $result = $this->service()->correct(
            ['items' => [['sku' => 'XYZ-999', 'qty' => 4]]],
            null
        );

        $this->assertSame([], $result['corrected']['items']);
        $this->assertSame(0.0, $result['corrected']['subtotal']);
    }

    public function test_unknown_sku_with_unmatched_requirement_is_dropped(): void
    {
        $result = $this->service()->correct(
            ['items' => [['sku' => 'XYZ-999', 'qty' => 4]]],
            'something entirely unrelated'
        );

        $this->assertSame([], $result['corrected']['items']);
    }

    public function test_price_and_subtotal_come_from_catalog(): void
    {
        $result = $this->service()->correct(
            ['items' => [['sku' => 'KRP-150FR-2436', 'qty' => 4, 'unit_price' => 99.0]]],
            null
        );

        $this->assertSame(428.0, $result['corrected']['items'][0]['unit_price']);
        $this->assertSame(1712.0, $result['corrected']['subtotal']);
    }

    public function test_correct_price_is_not_reported_as_a_finding(): void
    {
        $result = $this->service()->correct(
            ['items' => [['sku' => 'KRP-150FR-2436', 'qty' => 1, 'unit_price' => 428.0]]],
            null
        );

        $this->assertSame(428.0, $result['corrected']['items'][0]['unit_price']);
        $this->assertStringNotContainsString('overridden', implode("\n", $result['findings']));
    }

    public function test_item_without_price_uses_catalog_price(): void
    {
        $result = $this->service()->correct(
            ['items' => [['sku' => 'KDW-2436', 'qty' => 3]]],
            null
        );

        $this->assertSame(186.0, $result['corrected']['items'][0]['unit_price']);
        $this->assertSame(558.0, $result['corrected']['subtotal']);
    }

    public function test_mixed_items_keep_valid_and_drop_unknown(): void
    {
        $result = $this->service()->correct(
            ['items' => [
                ['sku' => 'KRP-150FR-2436', 'qty' => 1],
                ['sku' => 'GHOST-000', 'qty' => 5],
            ]],
            null
        );

        $this->assertSame(['KRP-150FR-2436'], array_column($result['corrected']['items'], 'sku'));
        $this->assertSame(428.0, $result['corrected']['subtotal']);
    }

    public function test_draft_cannot_be_approved_or_sent(): void
    {
        $result = $this->service()->correct(
            [
                'items' => [['sku' => 'KRP-150FR-2436', 'qty' => 4]],
                'status' => 'approved',
                'next_action' => 'send_to_customer',
            ],
            null
        );

        $this->assertSame('draft', $result['corrected']['status']);
        $this->assertSame('await_human_approval', $result['corrected']['next_action']);
    }

    public function test_absent_status_produces_no_status_finding(): void
    {
        $result = $this->service()->correct(
            ['items' => [['sku' => 'KRP-150FR-2436', 'qty' => 1]]],
            null
        );

        $this->assertStringNotContainsString('status', implode("\n", $result['findings']));
    }
}
