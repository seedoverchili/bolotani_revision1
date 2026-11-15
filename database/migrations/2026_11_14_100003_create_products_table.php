<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            return;
        }
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('category', 30);
            $table->json('categories')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku', 30)->nullable();
            $table->string('subcategory')->nullable();
            $table->string('tags')->nullable();
            $table->unsignedInteger('price');
            $table->string('unit', 40);
            $table->unsignedInteger('stock')->default(0);
            $table->string('status', 20)->default('pending');
            $table->text('description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('badge')->nullable();
            $table->unsignedTinyInteger('badge_tone')->default(0);
            $table->string('group')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};