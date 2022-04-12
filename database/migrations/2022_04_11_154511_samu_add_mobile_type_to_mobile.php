<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Samu\Mobile;
use App\Models\Samu\MobileInService;
use App\Models\Samu\MobileType;

class SamuAddMobileTypeToMobile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('samu_mobiles', function (Blueprint $table) {
            $table->foreignId('type_id')->after('type')->nullable()->constrained('samu_mobile_types');
        });


        $mobiles = Mobile::withTrashed()->get();
        foreach($mobiles as $mobile)
        {
            if($mobile->type == null) $mobile->type = 'M1';

            $type = MobileType::where('name', $mobile->type)->first();
            $mobile->type_id = $type->id;
            $mobile->save();
        }

        Schema::table('samu_mobiles', function (Blueprint $table) {
            $table->dropColumn('type');
        });




        Schema::table('samu_mobiles_in_service', function (Blueprint $table) {
            $table->foreignId('type_id')->after('type')->nullable()->constrained('samu_mobile_types');
        });

        $mobilesInService = MobileInService::withTrashed()->get();
        foreach($mobilesInService as $mis)
        {
            if($mis->type == null) $mis->type = 'M1';

            $type = MobileType::where('name',$mis->type)->first();
            $mis->type_id = $type->id;
            $mis->save();
        }

        Schema::table('samu_mobiles_in_service', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('samu_mobiles', function (Blueprint $table) {
            $table->string('type')->after('type_id')->nullable();
            $table->dropColumn('type_id');
        });

        Schema::table('samu_mobiles_in_service', function (Blueprint $table) {
            $table->string('type')->after('type_id')->nullable();
            $table->dropColumn('type_id');
        });
    }
}
