<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `source` becomes the lead's channel. When the channel is a referral,
     * the referrer is either an existing client or a free-text name (a
     * partner company, a contact, …).
     */
    public function up(): void
    {
        Schema::table('contact_submissions', function (Blueprint $table) {
            $table->foreignId('referred_by_client_id')->nullable()->after('source')
                ->constrained('clients')->nullOnDelete();
            $table->string('referral_source')->nullable()->after('referred_by_client_id');
        });
    }

    public function down(): void
    {
        Schema::table('contact_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by_client_id');
            $table->dropColumn('referral_source');
        });
    }
};
