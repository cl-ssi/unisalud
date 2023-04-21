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

            //Datos examen Directo
            $table->string('direct_exam_result')->nullable()->after('chagas_result_confirmation_file');
            $table->datetime('direct_exam_at')->nullable()->after('direct_exam_result');
            $table->string('direct_exam_file')->nullable()->after('direct_exam_at');

            //Datos Primera PCR
            $table->string('pcr_first_result')->nullable()->after('direct_exam_file');
            $table->datetime('pcr_first_at')->nullable()->after('pcr_first_result');
            $table->string('pcr_first_file')->nullable()->after('pcr_first_at');


            //Datos Segunda PCR
            $table->string('pcr_second_result')->nullable()->after('pcr_first_file');
            $table->datetime('pcr_second_at')->nullable()->after('pcr_second_result');
            $table->string('pcr_second_file')->nullable()->after('pcr_second_at');


            //Datos Tercera PCR
            $table->string('pcr_third_result')->nullable()->after('pcr_second_file');
            $table->datetime('pcr_third_at')->nullable()->after('pcr_third_result');
            $table->string('pcr_third_file')->nullable()->after('pcr_third_at');
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
            $table->dropColumn('direct_exam_result');
            $table->dropColumn('direct_exam_at');
            $table->dropColumn('direct_exam_file');
    
            $table->dropColumn('pcr_first_result');
            $table->dropColumn('pcr_first_at');
            $table->dropColumn('pcr_first_file');
    
            $table->dropColumn('pcr_second_result');
            $table->dropColumn('pcr_second_at');
            $table->dropColumn('pcr_second_file');
    
            $table->dropColumn('pcr_third_result');
            $table->dropColumn('pcr_third_at');
            $table->dropColumn('pcr_third_file');
        });
    }
};