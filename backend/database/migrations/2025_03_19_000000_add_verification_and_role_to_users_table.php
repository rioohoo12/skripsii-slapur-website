<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add is_verified if it doesn't exist
            if (!Schema::hasColumn('users', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('email_verified_at');
            }

            // Add role_id if it doesn't exist (for relationship)
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete()->after('role');
            }

            // Ensure full_name is there by using existing 'name' field or add it
            if (!Schema::hasColumn('users', 'full_name')) {
                $table->string('full_name')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_verified')) {
                $table->dropColumn('is_verified');
            }
            if (Schema::hasColumn('users', 'role_id')) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            }
            if (Schema::hasColumn('users', 'full_name')) {
                $table->dropColumn('full_name');
            }
        });
    }
};
