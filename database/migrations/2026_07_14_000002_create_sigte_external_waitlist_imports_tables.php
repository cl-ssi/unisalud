<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sigte_external_waitlist_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('filename')->nullable();
            $table->unsignedInteger('total_count')->default(0);
            $table->timestamps();
        });

        Schema::create('sigte_external_waitlist_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sigte_external_waitlist_import_id');
            $table->string('run');
            $table->timestamp('created_at')->nullable();

            $table->foreign('sigte_external_waitlist_import_id', 'sewr_import_id_foreign')
                ->references('id')->on('sigte_external_waitlist_imports')->cascadeOnDelete();
            $table->index('run', 'sewr_run_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sigte_external_waitlist_runs');
        Schema::dropIfExists('sigte_external_waitlist_imports');
    }
};
