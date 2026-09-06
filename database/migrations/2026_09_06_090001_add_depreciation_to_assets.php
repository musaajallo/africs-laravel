<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->date('manufactured_on')->nullable()->after('model');
            $table->date('in_service_on')->nullable()->after('purchased_on'); // depreciation start
            $table->string('depreciation_method')->default('none')->after('warranty_until'); // none|straight_line|reducing_balance
            $table->unsignedInteger('useful_life_months')->nullable()->after('depreciation_method');
            $table->decimal('depreciation_rate', 6, 3)->nullable()->after('useful_life_months'); // annual %
            $table->decimal('salvage_value', 15, 2)->nullable()->after('depreciation_rate');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'manufactured_on', 'in_service_on', 'depreciation_method',
                'useful_life_months', 'depreciation_rate', 'salvage_value',
            ]);
        });
    }
};
