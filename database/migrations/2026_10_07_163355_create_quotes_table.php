<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id')->nullable();
            $table->json('items');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->string('tax')->nullable();
            $table->string('shipping')->nullable();
            $table->string('total')->nullable();
            $table->string('status')->default('draft');
            $table->string('next_action')->default('await_human_approval');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
