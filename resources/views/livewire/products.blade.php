<!-- resources/views/livewire/products.blade.php -->
<div class="container mt-4">
    <h2>Gestión de Productos</h2>

    <form wire:submit.prevent="save" class="mb-4">
        <div>
            <label>Código:</label>
            <input wire:model.defer="code" type="text" class="form-control">
            @error('code') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Nombre:</label>
            <input wire:model.defer="name" type="text" class="form-control">
            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mt-2">
            <button class="btn btn-primary">{{ $isEdit ? 'Actualizar' : 'Crear' }}</button>
            <button type="button" wire:click="resetForm" class="btn btn-secondary">Cancelar</button>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->code }}</td>
                    <td>{{ $product->name }}</td>
                    <td>
                        <button wire:click="edit({{ $product->id }})" class="btn btn-sm btn-warning">Editar</button>
                        <button type="button" onclick="confirmDelete({{ $product->id }})" class="btn btn-sm btn-danger">Eliminar</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">Sin productos</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Está seguro de eliminar el registro?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.emit('deleteProduct', id);
                }
            });
        }
    </script>
@endpush
