<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // users: soft-delete column is unindexed, making onlyTrashed() and withTrashed() scans slow
        Schema::table('users', function (Blueprint $table) {
            $table->index('deleted_at');
        });

        // org_structure_trees: user_id has no FK/index; reports_to_id is a plain bigInteger with no index
        // but is heavily queried for hierarchy traversal; deleted_at also unindexed
        Schema::table('org_structure_trees', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('reports_to_id');
            $table->index('deleted_at');
        });

        // messages: compound index for the common "get unread messages for a user ordered by time" query
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['receiver_id', 'is_read', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('org_structure_trees', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['reports_to_id']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['receiver_id', 'is_read', 'created_at']);
        });
    }
};
