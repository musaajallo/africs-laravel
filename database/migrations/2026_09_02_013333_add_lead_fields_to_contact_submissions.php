<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Contact-form submissions become leads the team triages: an owner,
     * a status pipeline, notes, and a link to the client they converted into.
     */
    public function up(): void
    {
        Schema::table('contact_submissions', function (Blueprint $table) {
            $table->string('source')->default('website')->after('message');
            $table->foreignId('owner_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->foreignId('converted_client_id')->nullable()->after('owner_id')->constrained('clients')->nullOnDelete();
            $table->text('notes')->nullable()->after('converted_client_id');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('contact_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
            $table->dropConstrainedForeignId('converted_client_id');
            $table->dropColumn(['source', 'notes']);
        });
    }
};
