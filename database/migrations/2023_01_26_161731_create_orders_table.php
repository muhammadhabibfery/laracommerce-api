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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_account_id')->onDelete('cascade');
            $table->string('invoice_number', 31)->unique()->index();
            $table->unsignedInteger('total_price');
            $table->enum(
                'status',
                ['IN_CART', 'PENDING', 'SUCCES', 'FAILED']
            )->index();
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
        Schema::dropIfExists('orders');
    }
};
