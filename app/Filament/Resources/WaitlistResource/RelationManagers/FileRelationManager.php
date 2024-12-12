<?php

namespace App\Filament\Resources\WaitlistResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Webbingbrasil\FilamentCopyActions\Tables\Actions\CopyAction;

use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FileRelationManager extends RelationManager
{
    public $typeValue;

    protected static string $relationship = 'files';

    protected static ?string $title = 'Adjuntos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('Tipo Documento')
                    ->options([
                        'acta'      => 'Acta',
                        'anexo'     => 'Anexo',
                        'documento' => 'Documento',
                    ])
                    ->afterStateUpdated(function ($state, $set) {
                        $this->typeValue = $state; // Guardar el valor en una propiedad temporal
                    })
                    ->required(),
                Forms\Components\FileUpload::make('storage_path')
                    ->label('Archivo')
                    ->columnSpan('full') 
                    ->disk('gcs')
                    ->directory('/unisalud/waitlist/attached') // Directorio donde se guardarán los archivos
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(function ($state) {
                        return ucfirst($state); // Formatear el estado para que se muestre con la primera letra en mayúscula
                    }),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre'),
                // Columna para ver o descargar el archivo
                Tables\Columns\IconColumn::make('storage_path')
                    ->label('Ver Archivo')
                    ->url(fn ($record) => Storage::disk('gcs')->url($record->storage_path)) // URL del archivo
                    ->openUrlInNewTab() // Abre la URL en una nueva pestaña
                    ->icon('heroicon-o-document-text'), // Ícono que representa un archivo
                    ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nuevo Adjunto')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Crear Nuevo Adjunto'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('show_link')
                    ->label('Mostrar Link')
                    ->icon('heroicon-o-link')
                    ->modalHeading('Enlace del archivo')
                    ->modalSubheading('Selecciona y copia el enlace a continuación.')
                    ->modalButton('Cerrar')
                    ->form(function ($record) {
                        $url = Storage::disk('gcs')->url($record->storage_path);

                        return [
                            Forms\Components\TextInput::make('fileUrl')
                                ->label('Enlace público')
                                ->default($url) // Establece el valor del enlace
                                ->readonly()
                                ->columnSpan('full')
                                ->extraAttributes(['onclick' => 'this.select()']),
                        ];
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
