<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vault_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('vault_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->nullable()->constrained('vault_folders')->nullOnDelete();
            // Optional forward link to a Phase 5 subscription — no FK yet.
            $table->unsignedBigInteger('related_subscription_id')->nullable();
            $table->string('title');
            $table->string('username')->nullable();
            $table->text('password')->nullable();   // encrypted
            $table->string('url')->nullable();
            $table->text('notes')->nullable();       // encrypted
            $table->text('totp_secret')->nullable(); // encrypted
            $table->text('custom_fields')->nullable(); // encrypted JSON: [{label, value, secret}]
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('folder_id');
            $table->index('related_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vault_entries');
        Schema::dropIfExists('vault_folders');
    }
};
