<div>
    @switch($view)
        @case('index')
        <button class="btn btn-primary" wire:click="create">Crear</button>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Nombre</th>
                    <th>Válido desde</th>
                    <th>Válido hasta</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($procedures as $procedure)
                
                <tr>
                    <td>{{ $procedure->code }}</td>
                    <td>{{ $procedure->name }}</td>
                    <td>{{ $procedure->valid_from->format('Y-m-d') }}</td>
                    <td>{{ optional($procedure->valid_to)->format('Y-m-d') }}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary" 
                            wire:click="edit({{$procedure}})">Editar</button>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" 
                            wire:click="delete({{$procedure}})">Eliminar</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @break

        @case('create')
        @case('edit')

            @if($view == 'create')
                <h4>Crear Procedimiento</h4>
            @else
                <h4>Editar Procedimiento</h4>
            @endif
            
            <div class="form-row mb-3">
                <fieldset class="col-md-2">
                    <label for="for-cod">Código</label>
                    <input type="text" wire:model="code" class="form-control">
                </fieldset>

                <fieldset class="col-md-2">
                    <label for="for-name">Nombre</label>
                    <input type="text" wire:model="name" class="form-control">
                </fieldset>

                <fieldset class="col-md-2">
                    <label for="for-valid-from">Valido desde</label>
                    <input type="date" wire:model="valid_from" class="form-control">
                </fieldset>

                <fieldset class="col-md-2">
                    <label for="for-valid-to">Válido hasta</label>
                    <input type="text" wire:model="valid-to" class="form-control">
                </fieldset>
            </div>

            @if($view == 'create')
                <button type="button" class="btn btn-primary" wire:click="store">Crear</button>
            @else
                <button type="button" class="btn btn-primary" wire:click="update({{$procedure}})">Guardar</button>
            @endif
            <button type="button" class="btn btn-outline-secondary" wire:click="index">Cancelar</button>
        
        @break
    
    @endswitch
</div>
