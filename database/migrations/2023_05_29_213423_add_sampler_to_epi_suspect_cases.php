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
            $table->datetime('request_at')->nullable()->after('gender');
            $table->foreignId('sampler_id')->nullable()->after('creator_id')->constrained('users');
            $table->renameColumn('creator_id', 'requester_id');
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
            $table->dropColumn('request_at');
            
            $table->dropForeign(['sampler_id']);
            $table->dropColumn('sampler_id');
            
            $table->renameColumn('requester_id', 'creator_id');
            
        });
    }
};
