<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enums\AddressUse;
use App\Enums\AddressType;

use App\Services\GeocodingService;
use App\Models\Commune;

class AddressRelationManager extends RelationManager
{
    protected static string $relationship = 'address';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('use')
                    ->label('Uso')
                    ->options(AddressUse::class),
                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->options(AddressType::class),
                Forms\Components\TextInput::make('text')
                    ->label('Calle')->reactive()
                    ->reactive()
                    ->debounce(2000)
                    /*
                    ->afterStateUpdated(function ($state, callable $set) {
                        $geocodingService = app(GeocodingService::class);
                        $coordinates = $geocodingService->getCoordinates($state);

                        if ($coordinates) {
                            $set('latitude', $coordinates['lat']);
                            $set('longitude', $coordinates['lng']);
                        }
                    })
                    */
                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::calculateCoordinates($get, $set)),
                Forms\Components\TextInput::make('latitude')
                    ->required(),
                Forms\Components\TextInput::make('longitude')
                    ->required(),
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
                    ->relationship(
                        name: 'country',
                        titleAttribute: 'name'
                    ),
                Forms\Components\Select::make('commune_id')
                    ->label('Comuna')
                    ->debounce(2000)
                    ->reactive()
                    ->relationship('commune','name')
                    /*
                    ->relationship(
                        name: 'commune',
                        titleAttribute: 'name'
                    )*/
                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::calculateCoordinates($get, $set)),
                Forms\Components\TextInput::make('postal_code')
                    ->label('Código Postal')
                    ->maxLength(255),
                Forms\Components\Select::make('region_id')
                    ->label('Región')
                    ->relationship(
                        name: 'region',
                        titleAttribute: 'name'
                    ),
                Forms\Components\Toggle::make('actually')
                    ->required(),
                Forms\Components\TextInput::make('organization_id')
                    ->numeric(),
                Forms\Components\TextInput::make('practitioner_id')
                    ->numeric(),
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
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
