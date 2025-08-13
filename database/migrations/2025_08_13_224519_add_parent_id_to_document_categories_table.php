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
        Schema::table('document_categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('document_categories')->onDelete('cascade');
            $table->enum('type', ['category', 'folder'])->default('category');
            $table->string('path')->nullable(); // Store full path for easy navigation
            $table->integer('depth')->default(0); // Track nesting level
            
            $table->index(['parent_id', 'type']);
            $table->index('path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['parent_id', 'type']);
            $table->dropIndex(['path']);
            $table->dropColumn(['parent_id', 'type', 'path', 'depth']);
        });
    }
};
