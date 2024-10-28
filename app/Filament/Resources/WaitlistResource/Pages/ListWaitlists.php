<?php

namespace App\Filament\Resources\WaitlistResource\Pages;

use App\Filament\Resources\WaitlistResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Waitlist;

class ListWaitlists extends ListRecords
{
    protected static string $resource = WaitlistResource::class;

    /* 
    
    SE COMENTA DEBIDO AL USO DE TABS.

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    */

    public function getTabs(): array
    {
        $tabs['Todas'] = Tab::make()
            ->modifyQueryUsing(callback: fn (Builder $query): Builder => 
                $query
            );

        if(auth()->user()->organizations->count() > 0){
            foreach(auth()->user()->organizations as $organization){
                $tabs[$organization->alias] = Tab::make()
                    ->modifyQueryUsing(callback: fn (Builder $query): Builder => 
                        $query->where(column: 'destiny_organization_id', operator: $organization->id)
                    );
            }
        }

        return $tabs;
    }

    public function exportReport()
    {
        $query = Waitlist::query();
        $filters = $this->getTable()->getFilters();

        if ($filters['status']->getState()['value'] != NULL) {
            $query->where('status', $filters['status']->getState()['value']);
        }

        $waitlistResults = $query->get();

        $waitlistUsers = [];

        $waitlistResume = [
            'users'                 => 0, // Inicializar el conteo total de usuarios únicos
            'pendientes'            => 0, // Inicializar el conteo de registros sin estado
            'contactados'           => 0, // Inicializar el conteo de registros con contacto "sí"
            'no contactados'        => 0, // Inicializar el conteo de registros con contacto "no"
            'rechazos egresados'    => 0 // Inicializar el conteo de registros con estado "egresado" y "rechazo"
        ];

        foreach ($waitlistResults as $waitlist) {
            if ($waitlist->user && !in_array($waitlist->user->id, $waitlistUsers)) {
                $waitlistUsers[] = $waitlist->user->id;
            }

            // Verificar si existen eventos con estado "EGRESADO" y al menos un "RECHAZO" en discharge
            $egresadoEvents = $waitlist->events()->where('status', 'egresado')->get();
            $hasRechazo = $egresadoEvents->contains('discharge', 'rechazo');

            if ($egresadoEvents->isNotEmpty() && $hasRechazo) {
                $waitlistResume['rechazos egresados']++;
            }

            // Verificar el último contacto del registro
            $lastContact = $waitlist->contacts()->latest('id')->first();
            if ($lastContact) {
                if ($lastContact->status === 'si') {
                    $waitlistResume['contactados']++;
                } elseif ($lastContact->status === 'no') {
                    $waitlistResume['no contactados']++;
                }
            }

            // Si el estado es nulo o vacío, contarlo como 'pendientes'
            if (empty($waitlist->status)) {
                $waitlistResume['pendientes']++;
            } else {
                // Inicializar el estado si no existe en el array de resumen
                if (!isset($waitlistResume[$waitlist->status])) {
                    $waitlistResume[$waitlist->status] = 0;
                }
                // Incrementar el contador del estado correspondiente
                $waitlistResume[$waitlist->status]++;
            }
        }

        $waitlistResume['users'] = count($waitlistUsers);

        // Generar el PDF utilizando la vista Blade
        $pdf = \PDF::loadView('exports.waitlist-report', [
            'waitlistResume' => $waitlistResume
        ]);

        // Devolver el PDF como descarga
        return response()->streamDownload(
            fn() => print($pdf->stream()),
            'waitlist-report.pdf'
        );
    }
}
