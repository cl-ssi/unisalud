<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddressResource\Pages;
use App\Filament\Resources\AddressResource\RelationManagers;
use App\Models\Address;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Services\GeocodingService;

use App\Enums\AddressUse;
use App\Enums\AddressType;

use App\Models\Commune;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
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
                    ->required(),
                Forms\Components\TextInput::make('longitude')
                    ->label('Longitud')
                    ->required(),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('address_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('period_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('use'),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('text')
                    ->searchable(),
                Tables\Columns\TextColumn::make('line')
                    ->searchable(),
                Tables\Columns\TextColumn::make('apartment')
                    ->searchable(),
                Tables\Columns\TextColumn::make('suburb')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('commune.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('postal_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('region.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('actually')
                    ->boolean(),
                Tables\Columns\TextColumn::make('organization_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('practitioner_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
        ];
    }
}
