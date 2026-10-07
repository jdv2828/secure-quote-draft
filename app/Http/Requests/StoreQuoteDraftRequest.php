<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The incoming draft is untrusted data. `status`, `next_action` and
     * `unit_price` are accepted but never trusted: the service overrides them.
     * `customer_note` is accepted but ignored — it is data, not an instruction.
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'string'],
            'requirement' => ['nullable', 'string'],
            'customer_note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku' => ['required', 'string'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:10000'],
            'items.*.unit_price' => ['nullable', 'numeric'],
            'status' => ['nullable', 'string'],
            'next_action' => ['nullable', 'string'],
        ];
    }
}
