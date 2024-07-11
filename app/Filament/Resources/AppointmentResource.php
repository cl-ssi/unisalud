<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;

use App\Filament\Imports\AppointmentImporter;
use Filament\Tables\Actions\ImportAction;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-c-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options(AppointmentStatus::class),
                Forms\Components\TextInput::make('cod_con_cancel_reason_id')
                    ->label('Motivo Cancelación')
                    ->numeric(),
                Forms\Components\Select::make('cod_con_appointment_type_id')
                    ->label('Tipo')
                    ->relationship('appointmentType', 'text'),
                    // ->options(AppointmentType::class),
                Forms\Components\TextInput::make('priority')
                    ->label('Prioridad')
                    ->numeric(),
                Forms\Components\TextInput::make('description')
                    ->label('Descripción')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('start')
                    ->label('Inicio'),
                Forms\Components\DateTimePicker::make('end')
                    ->label('Fin'),
                Forms\Components\DateTimePicker::make('created')
                    ->label('Fecha Creación'),
                Forms\Components\TextInput::make('comment')
                    ->label('Comentario')
                    ->maxLength(255),
                Forms\Components\TextInput::make('patient_instruction')
                    ->label('Información de Paciente')
                    ->maxLength(255)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado'),
                Tables\Columns\TextColumn::make('appointmentType.text')
                    ->label('Motivo Cancelación')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cod_con_appointment_type_id')
                    ->label('Tipo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioridad')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start')
                    ->label('Inicio')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end')
                    ->label('Fin')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created')
                    ->label('Fecha Creación')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('comment')
                    ->label('Comentario')
                    ->searchable(),
                Tables\Columns\TextColumn::make('patient_instruction')
                    ->label('Información de Paciente')
                    ->searchable(),
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
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(AppointmentImporter::class)
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ParticipantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return 'Cita';
    }

    public static function getPluralLabel(): string
    {
        return 'Citas';
    }
}
