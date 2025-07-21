<!-- resources/views/livewire/permissions.blade.php -->
<div class="container py-3">
    <h3>Gestión de Permisos</h3>
    <hr>

    @if($showForm)
    <form wire:submit.prevent="save" class="mb-4">
        <div>
            <label>Slug: (Debe ser único)</label>
            <input wire:model.defer="name" type="text" class="form-control">
            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Nombre:</label>
            <input wire:model.defer="displayName" type="text" class="form-control">
            @error('displayName') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Descripción:</label>
            <input wire:model.defer="description" type="text" class="form-control">
            @error('description') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mt-2">
            <button class="btn btn-primary">{{ $isEdit ? 'Actualizar' : 'Crear' }}</button>
            <button type="button" wire:click="resetForm" class="btn btn-secondary">Cancelar</button>
        </div>
    </form>
    @else

    @endif

    @if(!$showForm)
    <div class="card">
        <div class="card-header">
            <button type="button" wire:click="create" class="btn btn-sm btn-primary me-2">Agregar</button>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" wire:model.defer="search"  class="form-control float-right" placeholder="Buscar">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-secondary me-2" wire:click="clearSearch">
                            <i class="fas fa-eraser"></i>
                        </button>
                        <button type="submit" class="btn btn-default" wire:click="applySearch">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-bordered table-sm  table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Slug</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($permissions as $permission)
                    <tr>
                        <td>{{ $permission->id }}</td>
                        <td>{{ $permission->name }}</td>
                        <td>{{ $permission->display_name }}</td>
                        <td>{{ $permission->description }}</td>
                        <td style="text-align: right">
                            <button wire:click="edit({{ $permission->id }})" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></button>
                            <button type="button" onclick="confirmDelete({{ $permission->id }})" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center">Sin permissions</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- /.card-body -->
    </div>
    @endif
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
                window.Livewire.dispatch('deletePermission', { id: id });
            }
        });
    }
</script>
@endpush
