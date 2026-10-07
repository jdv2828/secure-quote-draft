<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteDraftValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_items_field_is_required(): void
    {
        $response = $this->postJson('/api/quotes/draft', ['customer_id' => 'ACCT-1842']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items');
    }

    public function test_items_must_not_be_empty(): void
    {
        $response = $this->postJson('/api/quotes/draft', [
            'customer_id' => 'ACCT-1842',
            'items' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items');
    }

    public function test_customer_id_is_required(): void
    {
        $response = $this->postJson('/api/quotes/draft', [
            'items' => [['sku' => 'KRP-150FR-2436', 'qty' => 1]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('customer_id');
    }

    public function test_qty_must_be_at_least_one(): void
    {
        $response = $this->postJson('/api/quotes/draft', [
            'customer_id' => 'ACCT-1842',
            'items' => [['sku' => 'KRP-150FR-2436', 'qty' => 0]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items.0.qty');
    }

    public function test_negative_qty_is_rejected(): void
    {
        $response = $this->postJson('/api/quotes/draft', [
            'customer_id' => 'ACCT-1842',
            'items' => [['sku' => 'KRP-150FR-2436', 'qty' => -1]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items.0.qty');
    }

    public function test_unit_price_must_be_numeric(): void
    {
        $response = $this->postJson('/api/quotes/draft', [
            'customer_id' => 'ACCT-1842',
            'items' => [['sku' => 'KRP-150FR-2436', 'qty' => 1, 'unit_price' => 'not-a-number']],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items.0.unit_price');
    }

    public function test_mixed_items_are_corrected_server_side(): void
    {
        $response = $this->postJson('/api/quotes/draft', [
            'customer_id' => 'ACCT-1842',
            'items' => [
                ['sku' => 'KRP-150FR-2436', 'qty' => 1],
                ['sku' => 'GHOST-000', 'qty' => 5],
            ],
        ]);

        $response->assertStatus(201);
        $this->assertSame(['KRP-150FR-2436'], array_column($response->json('quote.items'), 'sku'));
    }

    public function test_all_unknown_skus_are_rejected(): void
    {
        $response = $this->postJson('/api/quotes/draft', [
            'customer_id' => 'ACCT-1842',
            'items' => [['sku' => 'GHOST-000', 'qty' => 1]],
        ]);

        $response->assertStatus(422);
    }

    public function test_qty_above_max_is_rejected(): void
    {
        $response = $this->postJson('/api/quotes/draft', [
            'customer_id' => 'ACCT-1842',
            'items' => [['sku' => 'KRP-150FR-2436', 'qty' => 10001]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items.0.qty');
    }
}
