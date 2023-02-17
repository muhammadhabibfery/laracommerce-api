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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->onDelete('cascade');
            $table->foreignId('merchant_account_id')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('slug', 150)->unique()->index();
            $table->longText('description');
            $table->unsignedInteger('price');
            $table->unsignedInteger('weight');
            $table->unsignedInteger('stock')->default(1);
            $table->unsignedInteger('sold')->default(0);
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
        Schema::dropIfExists('products');
    }
};
