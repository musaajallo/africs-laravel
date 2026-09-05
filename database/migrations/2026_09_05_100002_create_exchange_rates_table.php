<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->char('base_currency', 3);   // reporting base, e.g. GMD
            $table->char('quote_currency', 3);  // foreign currency, e.g. USD
            // Value of 1 unit of quote_currency expressed in base_currency.
            $table->decimal('rate', 20, 10);
            $table->date('rate_date');
            $table->string('source')->default('manual'); // manual | exchangerate.host
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['base_currency', 'quote_currency', 'rate_date']);
            $table->index(['quote_currency', 'rate_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
