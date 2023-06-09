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
        Schema::table('users', function (Blueprint $table) {
            $table->string('etsy_access_token')->nullable();
            $table->string('etsy_refresh_token')->nullable();
            $table->string('etsy_nonce')->nullable();
            $table->string('etsy_verifier')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('etsy_access_token');
            $table->dropColumn('etsy_refresh_token');
            $table->dropColumn('etsy_nonce');
            $table->dropColumn('etsy_verifier');
        });
    }
};
