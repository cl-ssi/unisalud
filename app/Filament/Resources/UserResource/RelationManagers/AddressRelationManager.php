<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Address;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enums\AddressUseValue;
use App\Enums\AddressType;

use App\Services\GeocodingService;
use App\Models\Commune;

use Filament\Pages\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Illuminate\Database\Eloquent\Model;

// use Cheesegrits\FilamentGoogleMaps\Fields\Map;

class AddressRelationManager extends RelationManager
{
    protected static string $relationship = 'address';

    public $setLatitude = null;

    public function form(Form $form): Form
    {
        // dd($form->getModel());

        return $form
            ->schema([
                Forms\Components\Select::make('use')
                    ->label('Uso')
                    ->options(AddressUseValue::class),
                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->options(AddressType::class),
                Forms\Components\TextInput::make('text')
                    ->label('Calle')->reactive()
                    ->reactive()
                    ->debounce(2000)
                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::calculateCoordinates($get, $set)),
                Forms\Components\TextInput::make('line')
                    ->label('Número')
                    ->maxLength(255)
                    ->reactive()
                    ->debounce(2000)
                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::calculateCoordinates($get, $set)),
                Forms\Components\TextInput::make('apartment')
                    ->label('Tipo')
                    ->maxLength(255),
                Forms\Components\TextInput::make('suburb')
                    ->label('Villa / Población')
                    ->maxLength(255),
                Forms\Components\TextInput::make('city')
                    ->label('Ciudad')
                    ->maxLength(255),
                Forms\Components\Select::make('country_id')
                    ->label('País')
                    ->relationship('country','name'),
                Forms\Components\Select::make('commune_id')
                    ->label('Comuna')
                    ->debounce(2000)
                    ->reactive()
                    ->relationship('commune','name')
                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::calculateCoordinates($get, $set)),
                Forms\Components\TextInput::make('postal_code')
                    ->label('Código Postal')
                    ->maxLength(255),
                Forms\Components\Select::make('region_id')
                    ->label('Región')
                    ->relationship('region','name'),
                Forms\Components\TextInput::make('latitude')
                    ->label('Latitud')
                    ->default($this->setLatitude),
                    // ->relationship('address.location.latitude')
                    // ->readonly(),
                Forms\Components\TextInput::make('longitude')
                    ->label('Longitud')
                    ->required(),
                Forms\Components\Toggle::make('actually')
                    ->required(),
                Forms\Components\TextInput::make('organization_id')
                    ->numeric(),
                Forms\Components\TextInput::make('practitioner_id')
                    ->numeric(),
                /*
                Map::make('location')
                ->defaultLocation([39.526610, -107.727261]) // default for new forms,
                */
            ]);
    }

    public static function calculateCoordinates(callable $get, callable $set)
    {
        $address    = $get('text');
        $number     = $get('line');
        $commune_id = $get('commune_id');

        if ($address && $number && $commune_id) {
            $commune = Commune::find($commune_id)->name;

            $geocodingService = app(GeocodingService::class);
            $coordinates = $geocodingService->getCoordinates($address, $number, $commune);

            if ($coordinates) {
                $set('latitude', $coordinates['lat']);
                $set('longitude', $coordinates['lng']);
            } else {
                $set('latitude', null);
                $set('longitude', null);
            }
        }
    }

    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('text')
            ->columns([
                Tables\Columns\TextColumn::make('use')
                    ->label('Uso'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo'),
                Tables\Columns\TextColumn::make('text')
                    ->label('Calle')
                    ->searchable(),
                Tables\Columns\TextColumn::make('line')
                    ->label('Nro')
                    ->searchable(),
                Tables\Columns\TextColumn::make('apartment')
                    ->label('Tipo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('suburb')
                    ->label('Villa / Población')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('Ciudad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('commune.name')
                    ->label('Comuna')
                    ->sortable(),
                Tables\Columns\TextColumn::make('region.name')
                    ->label('Región')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data): Model {
                        $address                    = new Address();
                        $address->user_id           = $this->ownerRecord->id;
                        $address->use               = $data['use'];
                        $address->type              = $data['type'];
                        $address->text              = $data['text'];
                        $address->line              = $data['line'];
                        $address->apartment         = $data['apartment'];
                        $address->suburb            = $data['suburb'];
                        $address->city              = $data['city'];
                        $address->country_id        = $data['country_id'];
                        $address->commune_id        = $data['commune_id'];
                        $address->postal_code       = $data['postal_code'];
                        $address->region_id         = $data['region_id'];
                        $address->actually          = $data['actually'];
                        $address->organization_id   = $data['organization_id'];
                        $address->practitioner_id   = $data['practitioner_id'];

                        $address->save();

                        $address->location()->updateOrCreate(
                            [
                                'latitude'      => $data['latitude'],
                                'longitude'     => $data['longitude'],
                                'address_id'    => $address->id
                            ]
                            
                        );

                        return $address;

                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function (Model $record, array $data): Model {
                        $record->updateOrCreate(
                            [
                                'id' => $record->id ?? null
                            ]
                            ,
                            [
                                'use'               => $data['use'],
                                'type'              => $data['type'],
                                'text'              => $data['text'],
                                'line'              => $data['line'],
                                'apartment'         => $data['apartment'],
                                'suburb'            => $data['suburb'],
                                'city'              => $data['city'],
                                'country_id'        => $data['country_id'],
                                'commune_id'        => $data['commune_id'],
                                'postal_code'       => $data['postal_code'],
                                'region_id'         => $data['region_id'],
                                
                                //'latitude'      => $data['latitude'],
                                //'longitude'     => $data['longitude'],
                                
                                'actually'          => $data['actually'],
                                'organization_id'   => $data['organization_id'],
                                'practitioner_id'   => $data['practitioner_id']
                            ]
                        );

                        $record->location()->updateOrCreate(
                            [
                                'id' => $record->location->id ?? null
                            ],
                            [
                                'latitude'      => $data['latitude'],
                                'longitude'     => $data['longitude'],
                                'address_id'    => $record->id
                            ]
                            
                        );
                
                        return $record;
                    })
                    /*
                    ->action(function (Model $record, array $data) {
                        $this->fill(['latitude' => $record->location->latitude]);
                    }),
                    */
                    ->beforeFormFilled(function (Model $record, array $data) {
                        if($record->location){
                            $this->setLatitude = $record->location->latitude;
                        }
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
