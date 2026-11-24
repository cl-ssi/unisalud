<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * What the successful rows of an upload did, kept per import because the
     * file is processed in separate queued chunks. Filled by
     * DemandManagementWaitlistImporter and shown in its completion notification.
     */
    public function up(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->unsignedInteger('created_rows')->default(0)->after('successful_rows');
            $table->unsignedInteger('updated_rows')->default(0)->after('created_rows');
            $table->unsignedInteger('unchanged_rows')->default(0)->after('updated_rows');
        });
    }

    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->dropColumn(['created_rows', 'updated_rows', 'unchanged_rows']);
        });
    }
};
