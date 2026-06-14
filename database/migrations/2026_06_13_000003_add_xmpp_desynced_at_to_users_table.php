<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Set when an XMPP sync/ban job fails terminally: Laravel state changed
            // but ejabberd didn't. `reason` distinguishes the re-drivable kinds
            // (ban/unban — reconcile fixes) from password (no plaintext to re-push).
            $table->timestamp('xmpp_desynced_at')->nullable();
            $table->string('xmpp_desync_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['xmpp_desynced_at', 'xmpp_desync_reason']);
        });
    }
};
