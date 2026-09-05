<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequences', function (Blueprint $table) {
            $table->id();
            $table->string('key');          // e.g. proforma | invoice
            $table->string('period');       // e.g. 2026 — the counter resets per period
            $table->unsignedBigInteger('value')->default(0);
            $table->timestamps();

            $table->unique(['key', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequences');
    }
};
