<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFilesToEpiSuspectCasesTable extends Migration
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
            $table->string('chagas_result_screening_file')->nullable()->after('chagas_result_screening_at');
            $table->string('chagas_result_confirmation_file')->nullable()->after('chagas_result_confirmation_at');
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
            $table->dropColumn('chagas_result_screening_file');
            $table->dropColumn('chagas_result_confirmation_file');
        });
    }
}
