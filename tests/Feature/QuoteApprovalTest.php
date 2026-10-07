<?php

namespace Tests\Feature;

use App\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function createDraft(): Quote
    {
        return Quote::create([
            'customer_id' => 'ACCT-1842',
            'items' => [['sku' => 'KRP-150FR-2436', 'qty' => 4, 'unit_price' => 428.0]],
            'subtotal' => 1712.0,
            'tax' => 'UNVERIFIED',
            'shipping' => 'UNVERIFIED',
            'total' => 'UNDETERMINED',
            'status' => 'draft',
            'next_action' => 'await_human_approval',
        ]);
    }

    /** Rule 2 — approval is human-only: the system/AI can never approve. */
    public function test_approve_requires_human_approver(): void
    {
        $quote = $this->createDraft();

        $denied = $this->postJson("/api/quotes/{$quote->id}/approve");
        $denied->assertStatus(403);
        $this->assertSame('draft', $quote->fresh()->status);

        $approved = $this->withHeader('X-Role', 'approver')
            ->postJson("/api/quotes/{$quote->id}/approve");
        $approved->assertStatus(200);
        $this->assertSame('approved', $quote->fresh()->status);
    }

    public function test_approve_non_existent_quote_returns_404(): void
    {
        $response = $this->withHeader('X-Role', 'approver')
            ->postJson('/api/quotes/9999/approve');

        $response->assertStatus(404);
    }

    public function test_body_role_is_not_accepted(): void
    {
        $quote = $this->createDraft();

        $response = $this->postJson("/api/quotes/{$quote->id}/approve", ['role' => 'approver']);

        $response->assertStatus(403);
        $this->assertSame('draft', $quote->fresh()->status);
    }
}
