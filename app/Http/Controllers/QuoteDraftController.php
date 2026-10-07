<?php

namespace App\Http\Controllers;

use App\Domain\Quote\QuoteDraftService;
use App\Http\Requests\StoreQuoteDraftRequest;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;

class QuoteDraftController extends Controller
{
    public function __construct(
        private readonly QuoteDraftService $service,
    ) {
    }

    public function store(StoreQuoteDraftRequest $request): JsonResponse
    {
        $input = $request->validated();

        $result = $this->service->correct($input, $input['requirement'] ?? null);

        if ($result['corrected']['items'] === []) {
            return response()->json([
                'message' => 'No valid items could be resolved from the trusted catalog.',
            ], 422);
        }

        $quote = Quote::create($result['corrected']);

        return response()->json([
            'quote' => $quote,
            'findings' => $result['findings'],
        ], 201);
    }
}
