<?php

namespace App\Filament\Resources\OdontologyWaitlistResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WaitlistEventRelationManager extends RelationManager
{
    protected static string $relationship = 'events';

    protected static ?string $title = 'Cargas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('text')
                    ->label('Descripción')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->required()
                    ->options([
                        'primer llamado'        => 'Primer Llamado',
                        'segundo llamado'       => 'Segundo Llamado',
                        'tercer llamado'        => 'Tercer Llamado',
                        'en visita domiciliaria' => 'En Visita Domiciliaria',
                        'citado'                => 'Citado',
                        'atendido praps'              => 'Atendido PRAPS',
                        'atendido hetg'              => 'Atendido HETG',
                        'atendido hah'               => 'Atendido HAH',
                        'fallecido'                 => 'Fallecido'

                    ])
                    ->disableOptionWhen(function ($value, callable $get) {
                        $waitlist = $this->ownerRecord;

                        $alreadyUsed = $waitlist->events()->where('status', $value)->exists();

                        return $alreadyUsed;
                    }),
                Forms\Components\FileUpload::make('file')
                    ->label('Adjuntar Archivo')
                    ->directory('ionline/odontology/events')
                    ->preserveFilenames()
                    ->downloadable()
                    ->nullable(),
                Forms\Components\DatePicker::make('registered_date')
                    ->label('Fecha')
                    ->native(false)
                    ->required()
                    ->displayFormat('d-m-Y')
                    ->required(false)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Eventos')
            ->columns([
                Tables\Columns\TextColumn::make('registered_at')
                    ->label('Fecha Registro')
                    ->formatStateUsing(function ($state) {
                        return \Carbon\Carbon::parse($state)->format('d-m-Y');
                    }),
                Tables\Columns\TextColumn::make('text')
                    ->label('Observaciones')
                    ->wrap()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.text')
                    ->label('Registrado Por'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->wrap()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                ]),
            ]);
    }
}
