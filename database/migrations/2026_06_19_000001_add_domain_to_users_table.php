<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One install serves multiple front doors. `domain` is the user's home XMPP vhost
 * (their JID suffix, e.g. kewlchats.net or ready2.im), set at signup from the door
 * they used. `xmpp_username` stays globally unique (added in the xmpp-columns
 * migration), so the localpart namespace is shared across the whole community.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('domain')->nullable()->after('xmpp_username')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('domain');
        });
    }
};
