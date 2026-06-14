<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admin-managed persistent public channels (MUC rooms). The localpart maps to
        // localpart@conference.<domain>; the room itself lives in ejabberd.
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('localpart')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
