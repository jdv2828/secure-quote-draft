# Product Brief — Demo of the secure AI quote flow

## 01 · Problem

- **What is the problem?** An AI assistant builds quotes and can invent SKUs/prices, self-approve, and self-send, bypassing the catalog and human review.
- **Why is it a problem?** An unsafe draft can reach the customer with wrong prices or nonexistent products, and the AI would send without human approval.
- **Who has this problem?** Sales teams and reviewers who trust AI-generated quotes.
- **What solution does this system offer?** A demo showing the secure flow: the AI proposes, the system validates against the catalog, the human approves.
- **How does it improve/help?** It makes the BEFORE (broken) vs AFTER (corrected) visible and proves with tests that the rules hold.

## 02 · User

The reviewer/evaluator watching the demo on a shared screen.

- **Context**: a technical-test presentation, screen shared.
- **What I want it to do**: see how the unsafe draft turns into a safe one.
- **What they see on entry**: the broken draft (`FR-2436` $99 `approved`/`send`).
- **What frustrates them today**: AI quotes that reach the customer without human review.

## 03 · MVP

### Step 1 — Brainstorming (unbounded)

- Editable catalog, sales-note input, JSON output, checks, charts, buttons, a real approval UI, a frontend.

### Step 2 — Prioritization (3 filters)

1. **CORE**: show broken → corrected.
2. **Value**: make the AI-proposes / system-validates / human-approves separation clear.
3. **Feasible**: Laravel (PHP 8.3 + Composer) + SQLite + PHPUnit.

**Final MVP:**

- Load the trusted catalog + sales note (including the injected note)
- Reproduce the broken draft
- Run the correction layer → safe draft
- Run the test suite (29 tests)

## 04 · How it works (main flow)

```
POST /api/quotes/draft with the broken draft
→ the server corrects (SKU/price from catalog, status draft, tax/shipping/total UNVERIFIED)
→ POST /api/quotes/{id}/approve (human-only, 403 without the approver role)
→ php artisan test (29 tests green)
```

## 05 · Construction

- **Architecture**: hexagonal (ports & adapters) + dependency injection. Framework-agnostic domain (`CatalogPort`, `CatalogProduct`, `QuoteDraftService`); `ConfigCatalogAdapter`; binding in `CatalogServiceProvider`.
- **Entities**: `CatalogProduct` (value object), `Quote` (Eloquent, SQLite).
- **Information needed**: SKU, description, price, quantity, status, next action.
- **Endpoints**: `POST /api/quotes/draft`, `POST /api/quotes/{id}/approve`.
- **Data**: catalog in `config/catalog.php`, drafts in SQLite.
- **What can break**: unknown SKU, out-of-catalog price, `approved` status, injection in the note.

## 06 · Functional and non-functional requirements

### Functional

- [ ] The system must reject a SKU that is not in the catalog.
- [ ] The system must source price/description from the catalog, never from the AI or the customer.
- [ ] The system must not allow `status: approved` in the draft.
- [ ] The system must mark tax/shipping/total as unverified.
- [ ] The system must treat the customer note as untrusted data.
- [ ] The system must approve only when the caller is a human approver.

### Non-functional

- **Determinism**: same input → same output (no AI inside).
- **Verifiable**: `php artisan test` green (exit 0).
- **No frontend**: API-only, no extra packages beyond the Laravel core.

## 07 · Tech stack

Laravel 13 (PHP 8.3) + SQLite + PHPUnit. REST API in `routes/api.php`. No frontend.

## 08 · Important constraints

- Hardcoded catalog in `config/catalog.php` as the source of truth.
- No extra packages beyond the Laravel core.
- Deterministic (the AI is simulated, not a component).

## 09 · Out of scope

- Real tax-exemption and rush-availability verification (only `UNVERIFIED`).
- Full authentication/authorization (the demo uses a demo-grade header middleware).
- A frontend / approval UI.
- The customer-send pipeline.
