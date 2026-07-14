<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sigte_surgical_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('desde')->nullable();
            $table->date('hasta')->nullable();
            $table->unsignedInteger('patients_count')->default(0);
            $table->timestamps();
        });

        Schema::create('sigte_surgical_export_waitlist', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sigte_surgical_export_id');
            $table->unsignedBigInteger('sigte_surgical_waitlist_id');
            $table->timestamp('created_at')->nullable();

            $table->foreign('sigte_surgical_export_id', 'ssew_export_id_foreign')
                ->references('id')->on('sigte_surgical_exports')->cascadeOnDelete();
            $table->foreign('sigte_surgical_waitlist_id', 'ssew_waitlist_id_foreign')
                ->references('id')->on('sigte_surgical_waitlists')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sigte_surgical_export_waitlist');
        Schema::dropIfExists('sigte_surgical_exports');
    }
};
