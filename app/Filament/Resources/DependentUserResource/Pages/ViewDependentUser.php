<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

use App\Filament\Resources\DependentUserResource;
use App\Models\User;
use Carbon\Carbon;
use Doctrine\DBAL\Schema\View;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewDependentUser extends ViewRecord
{
    protected static string $resource = DependentUserResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('user.mobileContactPoint.organization.alias')
                    ->label('Establecimiento'),
                Infolists\Components\TextEntry::make('user.text')
                    ->label('Nombre Completo')
                    ->getStateUsing(function ($record) {
                        return ($record->user->text)??$record->user->given . ' ' . $record->user->fathers_family . ' ' . $record->user->mothers_family;
                    }),
                Infolists\Components\TextEntry::make('user.sex')
                    ->label('Sexo'),
                Infolists\Components\TextEntry::make('user.gender')
                    ->label('Genero'),
                Infolists\Components\TextEntry::make('user.birthday')
                    ->label('Fecha Nacimiento')
                    ->date('Y-m-d'),
                Infolists\Components\TextEntry::make('user.age')
                    ->label('Edad'),
                Infolists\Components\TextEntry::make('user.address.use')
                    ->label('Tipo Dirección'),
                Infolists\Components\TextEntry::make('user.address.text')
                    ->label('Calle'),
                Infolists\Components\TextEntry::make('user.address.line')
                    ->label('N°'),
                Infolists\Components\TextEntry::make('user.address.commune.name')
                    ->label('Comuna'),
                Infolists\Components\TextEntry::make('user.address.location.longitude')
                    ->label('Longitud'),
                Infolists\Components\TextEntry::make('user.address.location.latitude')
                    ->label('Latitud'),
                Infolists\Components\TextEntry::make('user.mobileContactPoint.value')
                    ->label('Telefono'),
                Infolists\Components\TextEntry::make('dependentCaregiver.relative')
                    ->label('Parentesco Cuidador'),
                Infolists\Components\TextEntry::make('dependentCaregiver.user.text')
                    ->label('Nombre Cuidador'),
                Infolists\Components\TextEntry::make('dependentCaregiver.user.age')
                    ->label('Edad  Cuidador'),
                Infolists\Components\TextEntry::make('dependentCaregiver.healthcare_type')
                    ->label('Prevision  Cuidador'),
                Infolists\Components\TextEntry::make('dependentCaregiver.empam')
                    ->label('Empam Cuidador'),
                Infolists\Components\TextEntry::make('dependentCaregiver.zarit')
                    ->label('Zarit Cuidador'),
                Infolists\Components\TextEntry::make('dependentCaregiver.immunizations')
                    ->label('Imunizacion Cuidador'),
                Infolists\Components\TextEntry::make('dependentCaregiver.elaborated_plan')
                    ->label('Plan Elaborado Cuidador'),
                Infolists\Components\TextEntry::make('dependentCaregiver.evaluated_plan')
                    ->label('Plan Evaluado Cuidador'),
                Infolists\Components\TextEntry::make('dependentCaregiver.trained')
                    ->label('Plan Evaluado Cuidador'),
                Infolists\Components\TextEntry::make('dependentCaregiver.stipend')
                    ->label('Plan Evaluado Cuidador'),
                Infolists\Components\TextEntry::make('diagnosis')
                    ->label('Diagnostico'),
                Infolists\Components\TextEntry::make('healthcare_type')
                    ->label('Prevision'),
                Infolists\Components\TextEntry::make('check_in_date')
                    ->label('Fecha de Ingreso')
                    ->date(),
                Infolists\Components\TextEntry::make('check_out_date')
                    ->label('Fecha de Egreso')
                    ->date(),
                Infolists\Components\TextEntry::make('integral_visits')
                    ->label('Vistas Integrales')
                    ->numeric(),
                Infolists\Components\TextEntry::make('last_integral_visit')
                    ->label('Última Visita Integral')
                    ->date(),
                Infolists\Components\TextEntry::make('treatment_visits')
                    ->label('Visitas de Tratamiento')
                    ->numeric(),
                Infolists\Components\TextEntry::make('last_treatment_visit')
                    ->label('Última Visita de Tratamiento')
                    ->date(),
                Infolists\Components\TextEntry::make('barthel')
                    ->label('Barthel'),
            ]);
    }

}
