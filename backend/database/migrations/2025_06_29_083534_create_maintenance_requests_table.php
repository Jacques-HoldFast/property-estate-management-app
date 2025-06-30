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
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('resident_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['plumbing', 'electrical', 'hvac', 'appliances', 'structural', 'painting', 'landscaping', 'security', 'cleaning', 'other']);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent']);
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'on_hold', 'completed', 'cancelled']);
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->string('assigned_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('reported_at');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
