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
            $table->string('description', 200);
            $table->enum('type', ['DEBIT', 'KREDIT'])->index();
            $table->unsignedInteger('amount');
            $table->enum(
                'status',
                ['PENDING', 'SUCCESS', 'FAILED', 'ACCEPT', 'REJECT']
            )->index();
            $table->unsignedInteger('balance');
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
