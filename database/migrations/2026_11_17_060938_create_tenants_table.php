<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('owner_name');
            $table->string('whatsapp', 20);
            $table->string('email');
            $table->json('categories')->nullable();
            $table->string('main_category')->nullable();
            $table->string('kecamatan', 80);
            $table->string('desa', 80)->nullable();
            $table->text('address');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->text('description')->nullable();
            $table->string('avatar')->nullable();
            $table->string('cover')->nullable();
            $table->unsignedTinyInteger('cover_pos')->default(50);
            $table->string('status', 20)->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->unsignedInteger('wa_clicks')->default(0);
            $table->timestamps();
        });

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
            $table->text('description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('badge')->nullable();
            $table->unsignedTinyInteger('badge_tone')->default(0);
            $table->string('group')->nullable();
            $table->timestamps();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('label')->nullable();
            $table->unsignedTinyInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('password_otps', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('purpose', 20)->default('login');
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_otps');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('tenants');
    }
};