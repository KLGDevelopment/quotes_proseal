<div class="container py-3">
    <h3 class="mb-3">{{ $this->title }}</h3>

    @if($showForm)
        <form wire:submit.prevent="save" class="mb-4">
            @foreach ($fields as $key => $value)
                <div class="mb-2">
                    <label class="form-label text-capitalize">{{ str_replace('_', ' ', $key) }}</label>
                    <input wire:model.defer="fields.{{ $key }}" type="text" class="form-control">
                    @error("fields.$key") <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            @endforeach

            <div class="mt-2">
                <button type="submit" class="btn btn-primary">
                    {{ $isEdit ? 'Actualizar' : 'Crear' }}
                </button>
                <button type="button" wire:click="resetForm" class="btn btn-secondary">Cancelar</button>
            </div>
        </form>
    @endif

    @if(!$showForm)
        <div class="card">
        <div class="card-header">
            
            <button type="button" wire:click="create" class="btn btn-sm btn-primary me-2">Agregar</button>

            <button type="button" wire:click="syncFromOdoo" wire:loading.attr="disabled" wire:target="syncFromOdoo" class="btn btn-sm btn-success">
                <span wire:loading.remove wire:target="syncFromOdoo">
                    <i class="fas fa-sync-alt"></i> Actualizar desde Odoo
                </span>
                <span wire:loading wire:target="syncFromOdoo">
                    <i class="fas fa-spinner fa-spin"></i> Sincronizando...
                </span>
            </button>


            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="text" wire:model.defer="search"  class="form-control float-right" placeholder="Buscar">
                    
                    <div class="input-group-append">
                        <button type="button" class="btn btn-secondary me-2" wire:click="clearSearch">
                            <i class="fas fa-eraser"></i>
                        </button>
                        <button type="submit" class="btn btn-default" wire:click="$refresh">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
 
            </div>
        </div>

            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-bordered table-sm table-striped">
                    <thead class="thead-dark">
                        <tr>
                            @foreach ($this->fields() as $field)
                                <th wire:click="sortBy('{{ $field['name'] }}')" style="cursor: pointer;">
                                    {{ $field['label'] }}
                                    {!! $sortField === $field['name'] ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                                </th>
                            @endforeach
                            <th style="width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                @foreach ($this->fields() as $field)
                                    <td>{{ $this->transformValue($field['name'], $item->{$field['name']}) }}</td>


                                @endforeach
                                <td>
                                    <button wire:click="edit({{ $item->id }})" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button type="button" onclick="confirmDelete({{ $item->id }})" class="btn btn-sm btn-danger">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="100%" class="text-center">No hay registros</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                {{ $items->links() }}
            </div>
        </div>
    @endif
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(id) {
        Swal.fire({
            title: '¿Está seguro de eliminar el registro?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.Livewire.dispatch('deleteItem', { id: id });
            }
        });
    }
</script>
@endpush
