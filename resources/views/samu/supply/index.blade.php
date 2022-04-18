<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th width="50">Editar</th>
            <th>Categoría</th>
            <th>Codigo</th>
            <th>Nombre</th>
            <th>Válido desde</th>
            <th>Válido hasta</th>
            <th>Valor</th>
            <th width="50"></th>
        </tr>
    </thead>
    <tbody>
        @foreach($supplies as $supply)
        <tr>
            <td>
                <button type="button" class="btn btn-sm btn-primary" 
                    wire:click="edit({{$supply}})">
                    <i class="fas fa-edit"></i>
                </button>
            </td>
            <td>{{ $supply->category->name }}</td>
            <td>{{ $supply->code }}</td>
            <td>{{ $supply->name }}</td>
            <td>{{ $supply->valid_from->format('Y-m-d') }}</td>
            <td>{{ optional($supply->valid_to)->format('Y-m-d') }}</td>
            <td>{{ $supply->value }}</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" 
                    wire:click="delete({{$supply}})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>