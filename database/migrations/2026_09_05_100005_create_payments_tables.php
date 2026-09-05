<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();          // RCT-2026-0001
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->char('currency', 3);
            $table->decimal('fx_rate', 20, 10)->default(1); // currency -> reporting base
            $table->decimal('amount', 15, 2);               // total received
            $table->decimal('allocated_amount', 15, 2)->default(0); // sum of allocations
            $table->string('method');                       // from Settings billing.payment_methods
            $table->string('reference')->nullable();        // transfer id, cheque no, etc.
            $table->date('paid_on');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'paid_on']);
        });

        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            // Amount of this payment applied to this invoice, in the shared currency.
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->unique(['payment_id', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments');
    }
};
