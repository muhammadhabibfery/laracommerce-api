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
        Schema::create('merchant_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banking_id')->nullable()->onDelete('cascade');
            $table->foreignId('user_id')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('slug', 100)->unique()->index();
            $table->text('address');
            $table->string('bank_account_name', 100)->nullable()->unique();
            $table->string('bank_account_number', 50)->nullable()->unique();
            $table->string('bank_branch_name', 50)->nullable();
            $table->string('image', 100)->nullable();
            $table->unsignedBigInteger('balance')->default(0);
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
        Schema::dropIfExists('merchant_accounts');
    }
};
