<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->string('type')->nullable()->after('description');
            $table->string('source')->nullable()->after('type');
            $table->string('destination')->nullable()->after('source');
            $table->string('priority')->default('medium')->after('destination');
        });
    }

    public function down(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropColumn(['type', 'source', 'destination', 'priority']);
        });
    }
};