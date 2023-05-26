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
        Schema::table('epi_suspect_cases', function (Blueprint $table) {
            //
            $table->foreignId('creator_id')->nullable()->after('organization_id')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('epi_suspect_cases', function (Blueprint $table) {
            //
            $table->dropForeign(['creator_id']);
            $table->dropColumn('creator_id');
        });
    }
};
