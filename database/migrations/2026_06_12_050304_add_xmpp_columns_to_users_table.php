<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // The JID localpart (JID = {xmpp_username}@{config xmpp.domain}). Permanent.
            $table->string('xmpp_username')->nullable()->unique()->after('email');

            // The chosen password, encrypted, held ONLY between signup and email
            // verification, then wiped once the account is provisioned to ejabberd.
            $table->text('xmpp_pending_secret')->nullable()->after('password');

            // pending | active | failed | disabled
            $table->string('xmpp_status')->default('pending')->after('xmpp_pending_secret');

            $table->timestamp('xmpp_provisioned_at')->nullable()->after('xmpp_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'xmpp_username',
                'xmpp_pending_secret',
                'xmpp_status',
                'xmpp_provisioned_at',
            ]);
        });
    }
};
