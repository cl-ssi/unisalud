<?php

namespace App\Filament\Resources\WaitlistResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\CreateAction;

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    protected static ?string $title = 'Contactos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                /*
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->placeholder('Selecciona una opción')
                    ->options([
                        'telefonica'            => 'Teléfonica',
                        'visita domiciliaria'   => 'Visita Domiciliaria',
                    ]),
                */
                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->placeholder('Selecciona una opción')
                    ->options(fn () => $this->ownerRecord->status === 'incontactable'
                        ? ['telefonico' => 'Teléfonico', 'visita domiciliaria' => 'Visita Domiciliaria']
                        : ['telefonico' => 'Teléfonico']
                    )
                    ->reactive(),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->placeholder('Selecciona una opción')
                    ->options([
                        'si'    => 'Sí',
                        'no'    => 'No',
                    ])
                ->required(),
                Forms\Components\DateTimePicker::make('contacted_at')
                    ->label('Fecha/Hora de Contacto')         
                    ->required(), // Formato de la fecha (opcional)
                Forms\Components\Textarea::make('text')
                    ->label('Observación') 
                    ->columnSpan('full') 
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(function ($state) {
                        // Convertir los valores internos a nombres amigables
                        return match($state) {
                            'telefonico'            => 'Teléfonico',
                            'visita domiciliaria'   => 'Visita Domiciliaria',
                            default => ucfirst($state),
                        };
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(function ($state) {
                        // Convertir los valores internos a nombres amigables
                        return match($state) {
                            'si' => 'Sí',
                            'no' => 'No',
                            default => ucfirst($state),
                        };
                    }),
                Tables\Columns\TextColumn::make('contacted_at')
                    ->label('Fecha de Contacto'),
                Tables\Columns\TextColumn::make('text')
                    ->label('Observaciones')
                    ->wrap(),
                Tables\Columns\TextColumn::make('user.text')
                    ->label('Registrado Por'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Registro')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nuevo Contacto')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Crear Nuevo Contacto')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Aquí puedes agregar o modificar los datos antes de crear el registro
                        $data['register_user_id'] = auth()->user()->id; // Guardar el ID del usuario autenticado
                        // $data['organization_user_id'] = 123; // Otro dato que no proviene del formulario
                        
                        return $data;
                    })
                    ->after(function (array $data, RelationManager $livewire): void {
                        if ($data['type'] === 'telefonico' && $data['status'] === 'no') {
                            // Contamos cuántos contactos tienen estado 'no'
                            $noContactsCount = $livewire->ownerRecord
                                ->contacts
                                ->where('status', 'no')
                                ->where('type', 'telefonico')
                                ->count();

                            // Si existen 2 o más contactos con estado 'no', actualizamos el estado del Waitlist
                            if ($noContactsCount == 2) {
                                $waitlist = $livewire->ownerRecord;
                                $waitlist->update(['status' => 'incontactable']);

                                // Creamos un nuevo evento en WaitlistEvent con el estado 'incontactable'
                                \App\Models\WaitlistEvent::create([
                                    'status'            => 'incontactable',
                                    'registered_at'     => now(),
                                    'text'              => $data['text'],
                                    'register_user_id'  => auth()->user()->id,
                                    'waitlist_id'       => $waitlist->id,
                                ]);
                            }
                        }
                    }),
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
