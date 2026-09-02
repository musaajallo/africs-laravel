<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('company'); // company | individual
            $table->string('status')->default('active'); // active | inactive
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('tax_number')->nullable();
            // Default billing currency for this client; null falls back to the
            // configured base currency (Settings, added later in Phase 0).
            $table->char('currency', 3)->nullable();
            $table->text('billing_address')->nullable();
            $table->string('city')->nullable();
            $table->char('country', 2)->nullable(); // ISO 3166-1 alpha-2
            $table->text('notes')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
