<?php

use App\Http\Controllers\QuoteApprovalController;
use App\Http\Controllers\QuoteDraftController;
use App\Http\Middleware\EnsureApprover;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| The secure quote-draft flow:
|   - POST /quotes/draft  -> correct an untrusted AI draft (server-side)
|   - POST /quotes/{quote}/approve -> human-only approval gate
|
*/

Route::post('/quotes/draft', [QuoteDraftController::class, 'store']);
Route::post('/quotes/{quote}/approve', [QuoteApprovalController::class, 'update'])
    ->middleware(EnsureApprover::class);
