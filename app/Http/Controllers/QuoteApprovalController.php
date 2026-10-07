<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteApprovalController extends Controller
{
    /**
     * Human-only approval. Reaching this handler means the EnsureApprover
     * middleware already confirmed the caller is a human approver.
     */
    public function update(Request $request, Quote $quote): JsonResponse
    {
        $quote->update(['status' => 'approved']);

        return response()->json([
            'quote' => $quote->fresh(),
        ]);
    }
}
