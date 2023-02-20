<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->nullable()->onDelete('cascade');
            $table->string('name', 90);
            $table->string('username', 35)->unique()->index();
            $table->string('email')->unique()->index();
            $table->string('phone', 13)->unique();
            $table->string('nik', 16)->nullable()->unique();
            $table->string('role', 15);
            $table->unsignedBigInteger('balance')->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE']);
            $table->text('address')->nullable();
            $table->string('avatar', 120)->nullable();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
