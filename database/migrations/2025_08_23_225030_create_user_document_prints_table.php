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
        Schema::create('user_document_prints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->integer('print_count')->default(0);
            $table->timestamp('last_printed_at')->nullable();
            $table->timestamps();
            
            // Ensure unique combination of user and document
            $table->unique(['user_id', 'document_id']);
            
            // Add indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['document_id', 'print_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_document_prints');
    }
};
