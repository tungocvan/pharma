<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

        
                            $table->id();
                            $table->string('name');
                            $table->string('email')->unique();
                            $table->string('phone')->nullable();
                            $table->string('avatar')->nullable();
                            $table->timestamp('email_verified_at')->nullable();
                            $table->string('password')->nullable();
                            $table->boolean('is_active')->default(true);
                            $table->timestamp('last_login_at')->nullable();
                            $table->rememberToken();
                            // Thêm google_id để định danh và google_token để thực hiện các request API nếu cần
                            $table->string('google_id')->nullable()->unique();
                            $table->text('google_token')->nullable();
                            $table->string('google_refresh_token')->nullable();
                            $table->foreignId('affiliate_level_id')->nullable()->constrained('affiliate_levels')->nullOnDelete();
                            $table->timestamps();
                            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};