<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Enums\Gender;
use App\Enums\Sex;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Usuarios';

    public static function getNavigationBadge(): ?string
    {
        return number_format(static::getModel()::count(), 0, ',', '.');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->numeric(),
                Forms\Components\Toggle::make('active')
                    ->required(),
                Forms\Components\TextInput::make('text')
                    ->maxLength(255),
                Forms\Components\TextInput::make('given')
                    ->maxLength(255),
                Forms\Components\TextInput::make('fathers_family')
                    ->maxLength(255),
                Forms\Components\TextInput::make('mothers_family')
                    ->maxLength(255),
                Forms\Components\Select::make('sex')
                    ->options(Sex::class),
                Forms\Components\Select::make('gender')
                    ->options(Gender::class),
                Forms\Components\DatePicker::make('birthday'),
                Forms\Components\DateTimePicker::make('deceased_datetime'),
                Forms\Components\TextInput::make('cod_con_marital_id')
                    ->numeric(),
                Forms\Components\TextInput::make('multiple_birth')
                    ->numeric(),
                Forms\Components\Select::make('nationality_id')
                    ->relationship('nationality', 'name'),
                Forms\Components\TextInput::make('team')
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(255),
                Forms\Components\Toggle::make('claveunica'),
                Forms\Components\TextInput::make('fhir_id')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('text')
                    ->searchable(),
                Tables\Columns\TextColumn::make('given')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fathers_family')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mothers_family')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sex'),
                Tables\Columns\TextColumn::make('gender'),
                Tables\Columns\TextColumn::make('birthday')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deceased_datetime')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cod_con_marital_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('multiple_birth')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nationality.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('team')
                    ->searchable(),
                Tables\Columns\IconColumn::make('claveunica')
                    ->boolean(),
                Tables\Columns\TextColumn::make('fhir_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
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
            RelationManagers\IdentifiersRelationManager::class,
            RelationManagers\AddressRelationManager::class
        ];

    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
