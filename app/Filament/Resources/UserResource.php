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
use App\Filament\Imports\ConditionImporter;
use Filament\Tables\Actions\ImportAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

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
                    ->label('Nombre Completo')
                    ->maxLength(255),
                Forms\Components\TextInput::make('given')
                    ->label('Nombre')
                    ->maxLength(255),
                Forms\Components\TextInput::make('fathers_family')
                    ->label('Apellido Paterno')
                    ->maxLength(255),
                Forms\Components\TextInput::make('mothers_family')
                    ->label('Apellido Materno')
                    ->maxLength(255),
                Forms\Components\Select::make('sex')
                    ->label('Sexo')
                    ->options(Sex::class),
                Forms\Components\Select::make('gender')
                    ->label('Género')
                    ->options(Gender::class),
                Forms\Components\DatePicker::make('birthday')
                    ->label('Fecha Nacimiento'),
                Forms\Components\DateTimePicker::make('deceased_datetime')
                    ->label('Fecha Deceso'),
                Forms\Components\Select::make('cod_con_marital_id')
                    ->label('Estado Civil')
                    ->relationship('codConMarital', 'text'),
                Forms\Components\Select::make('nationality_id')
                    ->label('Nacionalidad')
                    ->relationship('nationality', 'name'),
                Forms\Components\TextInput::make('team')
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(255),
                Forms\Components\Toggle::make('claveunica'),
                Forms\Components\TextInput::make('fhir_id')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label('Fecha de Verificación Email'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                /*
                ImportAction::make()
                    ->importer(userImporter::class)
                    ->label('Importar Usuarios'),
                */
                ImportAction::make()
                    ->importer(ConditionImporter::class)
                    ->label('Importar Condición de Usuarios')
            ])
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Estado')
                    ->boolean(),
                Tables\Columns\TextColumn::make('text')
                    ->label('Nombre Completo')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('given')
                //     ->label('Nombre')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('fathers_family')
                //     ->label('Apellido Paterno')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('mothers_family')
                //     ->label('Apellido Materno')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('sex')
                    ->label('Sexo')
                    ->toggleable(),
                // Tables\Columns\TextColumn::make('gender')
                //     ->label('Genero'),
                Tables\Columns\TextColumn::make('birthday')
                    ->label('Fecha Nacimiento')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                // Tables\Columns\TextColumn::make('deceased_datetime')
                //     ->label('Fecha Deceso')
                //     ->dateTime()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('cod_con_marital_id')
                //     ->label('Estado Civil')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('nationality.name')
                //     ->label('Nacionalidad')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\IconColumn::make('claveunica')
                //     ->boolean(),
                // Tables\Columns\TextColumn::make('fhir_id')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('email_verified_at')
                //     ->label('Fecha de Verificación Email')
                //     ->dateTime()
                //     ->sortable(),
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
                \STS\FilamentImpersonate\Tables\Actions\Impersonate::make()
                    ->redirectTo(route('filament.admin.pages.dashboard')),
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
            RelationManagers\AddressRelationManager::class,
            RelationManagers\RolesRelationManager::class,
            RelationManagers\PermissionsRelationManager::class,
            RelationManagers\ConditionsRelationManager::class,
            // RelationManagers\SexesRelationManager::class,
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

    public static function getLabel(): string
    {
        return 'Usuario';
    }

    public static function getPluralLabel(): string
    {
        return 'Usuarios';
    }
}
