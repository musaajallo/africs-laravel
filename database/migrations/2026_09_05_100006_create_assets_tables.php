<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('other'); // laptop|desktop|monitor|printer|phone|network|peripheral|other
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('asset_tag')->nullable(); // internal inventory label
            $table->string('status')->default('spare'); // in_use|spare|repair|retired|lost
            $table->string('condition')->nullable();   // new|good|fair|poor

            $table->date('purchased_on')->nullable();
            $table->decimal('purchase_cost', 15, 2)->nullable();
            $table->char('purchase_currency', 3)->nullable();
            $table->string('supplier')->nullable();
            $table->date('warranty_until')->nullable();

            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('assigned_on')->nullable();
            $table->string('location')->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('serial_number');
            $table->unique('asset_tag');
            $table->index(['status', 'category']);
            $table->index('assigned_to');
        });

        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('assigned_on');
            $table->date('returned_on')->nullable(); // null = currently held
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['asset_id', 'returned_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
        Schema::dropIfExists('assets');
    }
};
