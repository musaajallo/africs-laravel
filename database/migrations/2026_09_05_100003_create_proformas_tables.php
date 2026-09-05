<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proformas', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();          // PRO-2026-0001
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('draft');   // draft|sent|accepted|declined|expired|converted

            $table->char('currency', 3);
            // Snapshot of currency -> reporting-base rate at issue time.
            $table->decimal('fx_rate', 20, 10)->default(1);

            $table->date('issue_date');
            $table->date('valid_until')->nullable();

            $table->string('tax_label')->default('VAT');
            $table->decimal('tax_rate', 6, 3)->default(0); // percentage

            // Derived totals, in the document currency (plus the base equivalent).
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('base_total', 15, 2)->default(0);

            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            $table->foreignId('converted_invoice_id')->nullable(); // FK added with the invoices table
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'status']);
            $table->index('issue_date');
        });

        Schema::create('proforma_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proforma_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proforma_lines');
        Schema::dropIfExists('proformas');
    }
};
