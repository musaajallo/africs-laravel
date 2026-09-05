<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();          // INV-2026-0001
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('proforma_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('draft');   // draft|sent|partially_paid|paid|overdue|void

            $table->char('currency', 3);
            $table->decimal('fx_rate', 20, 10)->default(1);

            $table->date('issue_date');
            $table->date('due_date')->nullable();

            $table->string('tax_label')->default('VAT');
            $table->decimal('tax_rate', 6, 3)->default(0);

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('base_total', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0); // maintained by Phase 4

            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'status']);
            $table->index('issue_date');
        });

        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        // Close the two-way link now that the invoices table exists.
        Schema::table('proformas', function (Blueprint $table) {
            $table->foreign('converted_invoice_id')->references('id')->on('invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->dropForeign(['converted_invoice_id']);
        });

        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
    }
};
