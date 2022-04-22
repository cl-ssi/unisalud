<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th width="50">Editar</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Válido desde</th>
            <th>Válido hasta</th>
            <th>Estado</th>
            <th width="50"></th>
        </tr>
    </thead>
    <tbody>
        @foreach($types as $type)
        <tr class="{{ $type->status ? '':'table-secondary' }}">
            <td>
                <button type="button" class="btn btn-sm btn-primary" 
                    wire:click="edit({{$type}})">
                    <i class="fas fa-edit"></i>
                </button>
            </td>
            <td>{{ $type->name }}</td>
            <td>{{ $type->description }}</td>
            <td>{{ $type->valid_from }}</td>
            <td>{{ $type->valid_to }}</td>
            <td>{{ $type->status ? 'Activo':'Inactivo' }}</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" 
                    wire:click="delete({{$type}})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>