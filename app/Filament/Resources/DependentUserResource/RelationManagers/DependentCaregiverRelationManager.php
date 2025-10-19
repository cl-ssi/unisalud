<?php

namespace App\Filament\Resources\DependentUserResource\RelationManagers;

use App\Models\DependentCaregiver;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class DependentCaregiverRelationManager extends RelationManager
{
    protected static string $relationship = 'dependentCaregiver';

    protected static ?string $title = 'Información Cuidador';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('relative')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('relative')
            ->columns([
                Tables\Columns\TextColumn::make('relative')
                    ->label('Parentesco'),
                Tables\Columns\TextColumn::make('user.text')
                    ->wrap()
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('user.given')
                    ->hidden()
                    ->label('Nombre Cuidador'),
                Tables\Columns\TextColumn::make('user.fathers_family')
                    ->hidden()
                    ->label('Apellido Paterno Cuidador'),
                Tables\Columns\TextColumn::make('user.mothers_family')
                    ->hidden()
                    ->label('Apellido Materno Cuidador'),
                Tables\Columns\TextColumn::make('user.age')
                    ->label('Edad '),
                Tables\Columns\TextColumn::make('healthcare_type')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Prevision '),
                Tables\Columns\IconColumn::make('empam')
                    ->label('Empam')
                    ->hidden()
                    ->boolean(),
                Tables\Columns\IconColumn::make('zarit')
                    ->label('Zarit')
                    ->hidden()
                    ->boolean(),
                Tables\Columns\TextColumn::make('immunizations')
                    ->hidden()
                    ->label('Imunizaciones'),
                Tables\Columns\IconColumn::make('elaborated_plan')
                    ->hidden()
                    ->label('Plan Elaborado')
                    ->boolean(),
                Tables\Columns\IconColumn::make('evaluated_plan')
                    ->hidden()
                    ->label('Plan Evaluado')
                    ->boolean(),
                Tables\Columns\IconColumn::make('trained')
                    ->hidden()
                    ->label('Capacitacion')
                    ->boolean(),
                Tables\Columns\IconColumn::make('stipend')
                    ->hidden()
                    ->label('Estipéndio')
                    ->boolean(),
                Tables\Columns\TextColumn::make('controls')
                    ->label('Controles')
                    ->badge()
                    ->separator(',')
                    ->color(fn(string $state): string => match (true) {
                        Str::contains($state, DependentCaregiver::getLabel('empam'))            => 'fuchsia',
                        Str::contains($state, DependentCaregiver::getLabel('zarit'))            => 'amber',
                        Str::contains($state, DependentCaregiver::getLabel('elaborated_plan'))  => 'sky',
                        Str::contains($state, DependentCaregiver::getLabel('evaluated_plan'))   => 'violet',
                        Str::contains($state, DependentCaregiver::getLabel('trained'))          => 'lime',
                        Str::contains($state, DependentCaregiver::getLabel('stipend'))          => 'teal',
                        Str::contains($state, DependentCaregiver::getLabel('immunizations'))    => 'orange',
                        default                                                                 => 'primary',
                    })
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

    public function getContentTabLabel(): ?string
    {
        return 'Informacion Cuidador';
    }
}
