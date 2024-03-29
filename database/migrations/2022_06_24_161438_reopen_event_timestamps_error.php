<?php

use App\Models\Samu\Event;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReopenEventTimestampsError extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Disable auditing from this point on
        Event::disableAuditing();

        $ids = '8, 12, 34, 51, 58, 69, 103, 109, 226, 232, 257, 329, 372, 440, 486, 544, 560, 629, 689, 709, 729, 744, 779, 801, 805, 827, 840, 862, 916, 925, 980, 1036, 1068, 1117, 1162, 1163, 1210, 1241, 1317, 1318, 1410, 1469, 1510, 1524, 1582, 1691, 1708, 1711, 1732, 1763, 1774, 1775, 1870, 1886, 1900, 2115, 2181, 2193, 2231, 2249, 2256, 2281, 2369, 2378, 2379, 2407, 2408, 2417, 2571, 2622, 2636, 2688, 2711, 2750, 2815, 2821, 2826, 2834, 2869, 3056, 3057, 3088, 3106, 3239, 3315, 3349, 3407, 3505, 3531, 3539, 3604, 3654, 3691, 3702, 3703, 3715, 3747, 3762, 3766, 3787, 3804, 3835, 3853, 3864, 3921, 3976, 3980, 4007, 4180, 4184, 4194, 4214, 4225, 4446, 4510, 4526, 4577, 4591, 4600, 4601, 4674, 4697, 4710, 4736, 4752, 4766, 4806, 4824, 4858, 4887, 4888, 4923, 4933, 4966, 4983, 4988, 5062, 5076, 5086, 5088, 5089, 5188, 5196, 5216, 5265, 5287, 5290, 5294, 5309, 5367, 5392, 5528, 5560, 5566, 5591, 5636, 5653, 5683, 5754, 5810, 5827, 5896, 5912, 6004, 6010, 6053, 6063, 6066, 6077, 6119, 6152, 6184, 6232, 6261, 6413, 6417, 6422, 6463, 6506, 6675, 6742, 6762, 6794, 6810, 6813, 6858, 6970, 6982, 7005, 7047, 7070, 7131, 7169, 7197, 7344, 7350, 7356, 7367, 7378, 7412, 7482, 7546, 7563, 7571, 7607, 7611, 7620, 7681, 7696, 7729, 7783, 7870, 7875, 7910, 7956';
        DB::update("UPDATE samu_events SET `status` = 1 WHERE `id` in ($ids)");

        // Re-enable auditing
        Event::enableAuditing();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
