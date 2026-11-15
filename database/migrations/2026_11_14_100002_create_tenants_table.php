<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenants')) {
            return;
        }
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
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};