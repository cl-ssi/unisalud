<?php

namespace App\Filament\Widgets\Condition;

use App\Models\User;

use Cheesegrits\FilamentGoogleMaps\Widgets\MapWidget;

class DependentUserMapWidget extends MapWidget
{
    protected static ?string $heading = '';

    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = null;

    protected static ?bool $clustering = true;

    protected static ?bool $fitToBounds = true;

    protected static ?int $zoom = 8;

    protected static ?string $minHeight = '100vh';

    protected bool $debug = true;

    protected bool $drawingControl = true;

    protected static array $layers = [
        'http://uni.saludtarapaca.gob.cl/kml/2012_iquique.kml', 
        'http://uni.saludtarapaca.gob.cl/kml/linea_seguridad_iquique.kml',
        // 'http://uni.saludtarapaca.gob.cl/kml/cota_30_tarapaca.kml',
    ];

    public array $controls = [
        'mapTypeControl'    => true,
        'scaleControl'      => true,
        'streetViewControl' => false,
        'rotateControl'     => false,
        'fullscreenControl' => true,
        'searchBoxControl'  => false,
        'zoomControl'       => true,
    ];

    public ?int $user_id;
    
    public ?int $condition_id;

    protected function getData(): array
    {
        $data = [];
        $this->condition_id = $this->condition_id??null;

        if($this->condition_id != null){
            $query = User::whereHas('dependentUser', function ($query) {
                $query->whereHas('conditions', function ($query) {
                    $query->where('condition_id', '=', $this->condition_id);
                });
            });
            if($this->user_id != null){
                $query->where('id', '=', $this->user_id);

            } 
            $query->whereHas('address', function ($query) {
                $query->has('location')->whereNotNull('commune_id');
            });
            $query->with(['address', 'address.location', 'address.commune', 'dependentUser']);
            $users = $query->get();

            

            foreach ($users as $user)
            {
                /**
                 * Each element in the returned data must be an array
                 * containing a 'location' array of 'lat' and 'lng',
                 * and a 'label' string (optional but recommended by Google
                 * for accessibility.
                 *
                 * You should also include an 'id' attribute for internal use by this plugin
                 */
                $data[] = [
                    'location'  => [
                        'lat' => $user->address->location->latitude ? round(floatval($user->address->location->latitude), static::$precision) : 0,
                        'lng' => $user->address->location->longitude ? round(floatval($user->address->location->longitude), static::$precision) : 0,
                    ],

                    'label'     => $user->text . ' - ' . $user->dependentUser->diagnosis,
                    
                    'id' => $user->address->location->getKey(),

                    /**
                     * Optionally you can provide custom icons for the map markers,
                     * either as scalable SVG's, or PNG, which doesn't support scaling.
                     * If you don't provide icons, the map will use the standard Google marker pin.
                     */
                    // 'icon' => [
                    // 	'url' => url('images/dealership.svg'),
                    // 	'type' => 'svg',
                    // 	'scale' => [35,35],
                    // ],
                ];
            }
        }
        
        return $data;
    }

    // protected function getLayers(): array
    // {
    //     return [
    //         'unisalud.test\kml\2012_Iquique.kml',
    //         'unisalud.test\kml\cota_30_COMPLETO_TARAPACA.kml',
    //         'unisalud.test\kml\03_02_LineaSeguridadONEMI.kml'
    //     ];
    // }
}