<?php

// database/migrations/2026_08_06_000001_add_category_to_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Broad category used for search, filtering, and the recommendation
            // engine (similar-category items = candidate recommendations).
            $table->string('category')->default('other')->after('description');

            $table->index(['category', 'university_id']);
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex(['category', 'university_id']);
            $table->dropColumn('category');
        });
    }
};