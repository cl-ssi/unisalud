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
        Schema::table('epi_tracings', function (Blueprint $table) {
            //
            $table->date('result_at')->nullable()->after('patient_id');
            $table->string('result_observation')->nullable()->after('result_at');

            //4o mecanismo de entrega
            $table->date('delivery_of_result_4')->after('observation_of_result_3')->nullable();
            $table->string('mechanism_of_result_4')->after('delivery_of_result_4')->nullable();
            $table->string('observation_of_result_4')->after('mechanism_of_result_4')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('epi_tracings', function (Blueprint $table) {
            //
            $table->dropColumn('result_at');
            $table->dropColumn('result_observation');

            //eliminacion de 4o mecanismo de entrega
            $table->dropColumn('delivery_of_result_4');
            $table->dropColumn('mechanism_of_result_4');
            $table->dropColumn('observation_of_result_4');
            
        });
    }
};
