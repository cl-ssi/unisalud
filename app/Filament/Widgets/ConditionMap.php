<?php

namespace App\Filament\Widgets;

use App\Models\User;

use Cheesegrits\FilamentGoogleMaps\Widgets\MapWidget;

class ConditionMap extends MapWidget
{
    protected static ?string $heading = 'Map';

    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = null;

    protected static ?bool $clustering = true;

    protected static ?bool $fitToBounds = true;

    protected static ?int $zoom = 12;

    public $condition_id = null;
    
    public $user_id = null;

    protected function getData(): array
    {
    	/**
    	 * You can use whatever query you want here, as long as it produces a set of records with your
    	 * lat and lng fields in them.
    	 */
        $query = User::query();
        if($this->condition_id != null){
            $query->whereHas('dependentUser', function ($query) {
                $query->whereHas('dependentConditions', function ($query) {
                    $query->where('condition_id', '=', $this->condition_id);
                });
            });
            if($this->user_id != null){
                $query->where('id', '=', $this->user_id);

            } 
        } else{
            $query->where('id', '=', $this->id);
        }
        $query->with(['address', 'address.location', 'address.commune', 'dependentUser']);
        $users = $query->all()->limit(500);
        
        // $locations = \App\Models\Location::all()->limit(500);


        $data = [];

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
                    'lat' => $user->address->location->latitude  ? round(floatval($user->address->location->latitude), static::$precision) : 0,
                    'lng' => $user->address->location->longitude ? round(floatval($user->address->location->longitude), static::$precision) : 0,
                ],

                'label'     => $user->address->location->latitude . ',' . $user->address->location->longitude,
                
                'id' => $user->address->location->getKey(),

				/**
				 * Optionally you can provide custom icons for the map markers,
				 * either as scalable SVG's, or PNG, which doesn't support scaling.
				 * If you don't provide icons, the map will use the standard Google marker pin.
				 */
				'icon' => [
					'url' => url('images/dealership.svg'),
					'type' => 'svg',
					'scale' => [35,35],
				],
            ];
        }

        return $data;
    }
}