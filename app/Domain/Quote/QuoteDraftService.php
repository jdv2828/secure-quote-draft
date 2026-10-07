<?php

namespace App\Domain\Quote;

/**
 * Domain service: correct an untrusted AI quote draft against the business
 * rules. Pure logic — no framework, no HTTP, no Eloquent. The catalog is
 * reached through the CatalogPort, so the rules are testable in isolation.
 */
final class QuoteDraftService
{
    public function __construct(private readonly CatalogPort $catalog)
    {
    }

    /**
     * @param  array<string, mixed>  $draft  the untrusted AI draft
     * @param  string|null  $requirement  the sales-note request text, used only
     *                                    to resolve an invalid SKU to a real one
     * @return array{corrected: array<string, mixed>, findings: list<string>}
     */
    public function correct(array $draft, ?string $requirement = null): array
    {
        $findings = [];
        $items = [];

        foreach ($draft['items'] ?? [] as $item) {
            $sku = $item['sku'] ?? null;
            $qty = $item['qty'] ?? 0;

            $product = $sku !== null ? $this->catalog->find($sku) : null;

            if ($product === null) {
                $resolved = $this->catalog->resolveSku($requirement);
                if ($resolved !== null) {
                    $findings[] = "SKU '{$sku}' not in catalog -> resolved to '{$resolved}' from requirement";
                    $product = $this->catalog->find($resolved);
                } else {
                    $findings[] = "SKU '{$sku}' not in trusted catalog -> dropped";
                    continue;
                }
            }

            if (array_key_exists('unit_price', $item) && $item['unit_price'] != $product->unitPrice) {
                $findings[] = sprintf(
                    "unit_price $%s for '%s' overridden with catalog price $%s",
                    $item['unit_price'],
                    $product->sku,
                    $product->unitPrice
                );
            }

            $items[] = [
                'sku' => $product->sku,
                'description' => $product->description,
                'size' => $product->size,
                'rating' => $product->rating,
                'qty' => $qty,
                'unit_price' => $product->unitPrice,
            ];
        }

        $subtotal = round(array_sum(array_map(
            fn (array $i) => $i['unit_price'] * $i['qty'],
            $items
        )), 2);

        $status = $draft['status'] ?? null;
        if ($status !== null && $status !== 'draft') {
            $findings[] = "status '{$status}' forced to 'draft' (model may not approve)";
        }

        $nextAction = $draft['next_action'] ?? null;
        if ($nextAction !== null && $nextAction !== 'await_human_approval') {
            $findings[] = "next_action '{$nextAction}' forced to 'await_human_approval' (model may not send)";
        }

        return [
            'corrected' => [
                'customer_id' => $draft['customer_id'] ?? null,
                'items' => $items,
                'subtotal' => $subtotal,
                'tax' => 'UNVERIFIED - tax-exempt status not yet confirmed in company systems',
                'shipping' => 'UNVERIFIED - rush-delivery availability and cost not yet confirmed',
                'total' => 'UNDETERMINED - pending tax and shipping verification',
                'status' => 'draft',
                'next_action' => 'await_human_approval',
            ],
            'findings' => $findings,
        ];
    }
}
