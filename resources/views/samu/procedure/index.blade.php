<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th>Editar</th>
            <th>Codigo</th>
            <th>Nombre</th>
            <th>Válido desde</th>
            <th>Válido hasta</th>
            <th>Valor</th>
            <th>Eliminar</th>
        </tr>
    </thead>
    <tbody>
        @foreach($procedures as $procedure)
        <tr>
            <td>
                <button type="button" class="btn btn-sm btn-primary" 
                    wire:click="edit({{$procedure}})"><i class="fas fa-edit"></i></button>
            </td>
            <td>{{ $procedure->code }}</td>
            <td>{{ $procedure->name }}</td>
            <td>{{ $procedure->valid_from->format('Y-m-d') }}</td>
            <td>{{ optional($procedure->valid_to)->format('Y-m-d') }}</td>
            <td>{{ $procedure->value }}</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" 
                    wire:click="delete({{$procedure}})"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>