<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The 87 GES health problems and the CIE-10 codes of each, from MINSAL's
     * "GES - 87 Problemas de Salud.xlsx" (database/data/ges_health_problems.json).
     * Codes are stored normalized: no dot, no †/* marks, no X filler (C23X → C23).
     */
    public function up(): void
    {
        Schema::create('ges_health_problems', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->primary();  // MINSAL problem number, 1-87
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('ges_health_problem_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('ges_health_problem_id');
            $table->foreign('ges_health_problem_id')->references('id')->on('ges_health_problems')->cascadeOnDelete();
            $table->string('code', 5)->index();
            $table->unique(['ges_health_problem_id', 'code']);
        });

        $data = json_decode(file_get_contents(database_path('data/ges_health_problems.json')), true);

        foreach ($data['problems'] as $problem) {
            DB::table('ges_health_problems')->insert([
                'id'         => $problem['id'],
                'name'       => $problem['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('ges_health_problem_codes')->insert(array_map(fn ($code) => [
                'ges_health_problem_id' => $problem['id'],
                'code'                  => $code,
            ], $problem['codes']));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ges_health_problem_codes');
        Schema::dropIfExists('ges_health_problems');
    }
};
