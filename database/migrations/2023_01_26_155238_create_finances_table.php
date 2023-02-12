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
        Schema::create('finances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->onDelete('cascade');
            $table->enum('type', ['DEBIT', 'KREDIT'])->index();
            $table->string('order_id', 31)->nullable();
            $table->string('description', 200);
            $table->unsignedInteger('amount');
            $table->enum(
                'status',
                ['PENDING', 'SUCCESS', 'FAILED', 'ACCEPT', 'REJECT']
            )->index();
            $table->unsignedBigInteger('balance');
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
        Schema::dropIfExists('finances');
    }
};
