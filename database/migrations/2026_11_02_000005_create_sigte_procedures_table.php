<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SIGTE procedure codes (PRESTA_MIN when TIPO_PREST = 3), from MINSAL's
     * "Mantenedor Procediminento de SIGTE.xlsx" (database/data/sigte_procedures.json),
     * and the PRESTA_MIN / PRESTA_MIN_SALIDA procedure links of both waitlists.
     */
    public function up(): void
    {
        Schema::create('sigte_procedures', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->unsignedSmallInteger('type_code');  // Código de Tipo de Procedimiento
            $table->string('type_name');
            $table->timestamps();
        });

        $data = json_decode(file_get_contents(database_path('data/sigte_procedures.json')), true);
        $types = array_column($data['types'], 'name', 'id');

        foreach (array_chunk($data['procedures'], 500) as $chunk) {
            DB::table('sigte_procedures')->insert(array_map(fn ($procedure) => [
                'code'       => $procedure['code'],
                'name'       => $procedure['name'],
                'type_code'  => $procedure['type_id'],
                'type_name'  => $types[$procedure['type_id']],
                'created_at' => now(),
                'updated_at' => now(),
            ], $chunk));
        }

        foreach (['demand_management_waitlists', 'odontology_waitlists'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('sigte_procedure_id')->nullable()->after('minsal_specialty_id')->constrained('sigte_procedures');
                $table->foreignId('exit_sigte_procedure_id')->nullable()->after('exit_minsal_specialty_id')->constrained('sigte_procedures');
            });
        }
    }

    public function down(): void
    {
        foreach (['demand_management_waitlists', 'odontology_waitlists'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('sigte_procedure_id');
                $table->dropConstrainedForeignId('exit_sigte_procedure_id');
            });
        }

        Schema::dropIfExists('sigte_procedures');
    }
};
