<?php

namespace App\Filament\Resources\OdontologyWaitlistResource\RelationManagers;

use App\Filament\Resources\OdontologyWaitlistResource;
use App\Models\OdontologyWaitlist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    protected static ?string $title = 'Contactos';

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
                    ->live()
                    ->options([
                        'primer llamado'         => 'Primer Llamado',
                        'segundo llamado'        => 'Segundo Llamado',
                        'tercer llamado'         => 'Tercer Llamado',
                        'en visita domiciliaria' => 'En Visita Domiciliaria',
                        'citado'                 => 'Citado',
                        'atendido praps'         => 'Atendido PRAPS',
                        'atendido hetg'          => 'Atendido HETG',
                        'atendido sst'           => 'Atendido SST',
                        'atendido hah'           => 'Atendido HAH',
                        'fallecido'              => 'Fallecido',
                        'egresado'               => 'Egresado',
                    ])
                    ->disableOptionWhen(function ($value) {
                        return $this->ownerRecord->events()->where('status', $value)->exists();
                    }),
                Forms\Components\DatePicker::make('exit_date')
                    ->label('Fecha Egreso')
                    ->native(false)
                    ->displayFormat('d-m-Y')
                    ->visible(fn (callable $get) => $get('status') === 'egresado'),
                Forms\Components\Select::make('exit_code')
                    ->label('Causal Salida')
                    ->options(OdontologyWaitlist::EXIT_CODE_LABELS)
                    ->searchable()
                    ->visible(fn (callable $get) => $get('status') === 'egresado'),
                Forms\Components\FileUpload::make('file')
                    ->label('Adjuntar Archivo')
                    ->directory('ionline/odontology/events')
                    ->preserveFilenames()
                    ->downloadable()
                    ->nullable(),
                Forms\Components\DatePicker::make('registered_date')
                    ->label('Fecha')
                    ->native(false)
                    ->displayFormat('d-m-Y')
                    ->required(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('text')
            ->columns([
                Tables\Columns\TextColumn::make('text')
                    ->label('Observaciones')
                    ->wrap(),
                Tables\Columns\TextColumn::make('user.text')
                    ->label('Registrado Por'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Registro')
                    ->dateTime('d-m-Y H:i'),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nuevo Contacto')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Crear Nuevo Contacto')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['register_user_id'] = auth()->id();

                        return $data;
                    })
                    ->after(function ($record, $data, $livewire) {
                        $waitlistData = ['status' => $data['status']];

                        if ($data['status'] === 'egresado') {
                            $waitlistData['exit_date'] = $data['exit_date'] ?? null;
                            $waitlistData['exit_code'] = $data['exit_code'] ?? null;
                        }

                        $livewire->ownerRecord->update($waitlistData);
                    })
                    ->visible(fn () => ! auth()->user()->hasRole(OdontologyWaitlistResource::MUNICIPALITY_ROLE)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => ! auth()->user()->hasRole(OdontologyWaitlistResource::MUNICIPALITY_ROLE)),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => ! auth()->user()->hasRole(OdontologyWaitlistResource::MUNICIPALITY_ROLE)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->visible(fn () => ! auth()->user()->hasRole(OdontologyWaitlistResource::MUNICIPALITY_ROLE)),
            ]);
    }
}
