<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gestión Demanda specialty catalog: the Tipo 4 (intervención quirúrgica)
     * rows of "Prestaciones_c-fechavigencia.xlsx", hoja "prest total"
     * (database/data/demand_management_specialities.json). code = Código MAI,
     * which is PRESTA_MIN when TIPO_PREST = 4; text = Especialidad; name = Glosa.
     *
     * Also adds exit_specialty_id for PRESTA_MIN_SALIDA of TIPO_PREST 4 rows.
     */
    public function up(): void
    {
        Schema::table('demand_management_specialities', function (Blueprint $table) {
            $table->text('name')->nullable()->after('text');
            $table->index('code');
        });

        Schema::table('demand_management_waitlists', function (Blueprint $table) {
            $table->foreignId('exit_specialty_id')->nullable()->after('specialty_id')->constrained('demand_management_specialities');
        });

        $data = json_decode(file_get_contents(database_path('data/demand_management_specialities.json')), true);

        foreach ($data['specialities'] as $speciality) {
            DB::table('demand_management_specialities')->updateOrInsert(
                ['code' => $speciality['code']],
                [
                    'text'       => $speciality['specialty'],
                    'name'       => $speciality['name'],
                    'deleted_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::table('demand_management_waitlists', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exit_specialty_id');
        });

        $codes = array_column(
            json_decode(file_get_contents(database_path('data/demand_management_specialities.json')), true)['specialities'],
            'code'
        );
        // Keep rows a waitlist still points to; they lose only their code.
        $used = DB::table('demand_management_waitlists')->whereNotNull('specialty_id')->distinct()->pluck('specialty_id');
        DB::table('demand_management_specialities')->whereIn('code', $codes)->whereNotIn('id', $used)->delete();
        DB::table('demand_management_specialities')->whereIn('code', $codes)->update(['code' => null]);

        Schema::table('demand_management_specialities', function (Blueprint $table) {
            $table->dropIndex(['code']);
            $table->dropColumn('name');
        });
    }
};
