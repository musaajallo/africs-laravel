<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clients are individuals, organisations (NGO, CBO, private company, …)
     * or government bodies. `type` is the first-class grouping; `category`
     * is the sub-label within a type.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('category')->nullable()->after('type');
        });

        DB::table('clients')->where('type', 'company')->update(['type' => 'organisation']);

        Schema::table('clients', function (Blueprint $table) {
            $table->string('type')->default('organisation')->change();
        });
    }

    public function down(): void
    {
        DB::table('clients')->where('type', 'organisation')->update(['type' => 'company']);

        Schema::table('clients', function (Blueprint $table) {
            $table->string('type')->default('company')->change();
            $table->dropColumn('category');
        });
    }
};
