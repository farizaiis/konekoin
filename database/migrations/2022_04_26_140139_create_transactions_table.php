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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('app')->nullable();
            $table->unsignedBigInteger('konekita_order_id')->nullable();
            $table->unsignedBigInteger('konekios_order_id')->nullable();
            $table->string('konekoin_balance_id')->nullable();
            $table->string('type')->nullable();
            $table->string('durianpay_id')->unique();
            $table->string('access_token')->nullable();
            $table->string('customer_id')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('signature')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
