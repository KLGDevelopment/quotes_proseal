<div class="container py-3">
    <h3 class="mb-3">{{ $this->title }}</h3>

    @if($showForm)
        <form wire:submit.prevent="save" class="mb-4">
            @foreach ($this->fields() as $field)
                @continue(isset($field['show_in_form']) && $field['show_in_form'] === false)
                <div class="mb-2">
                    <label class="form-label text-capitalize">
                        {{ str_replace('_', ' ', $field['label']) }}
                    </label>

                    @if(isset($field['options']) && is_array($field['options']))
                        <div wire:ignore x-data="{ value: @entangle('fields.' . $field['name']) }"
                            x-init="
                                let select = $el.querySelector('select');
                                $(select).select2({ width: '100%' });

                                $(select).on('change', function () {
                                    value = this.value;
                                });

                                $watch('value', val => {
                                    $(select).val(val).trigger('change');
                                });
                            "
                        >
                            <select class="form-control w-100">
                                <option value="">Seleccione...</option>
                                @foreach ($field['options'] as $optionValue => $optionLabel)
                                    <option value="{{ $optionValue }}" @selected($fields[$field['name']] == $optionValue)>
                                        {{ $optionLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>



                    @else
                        <input wire:model.defer="fields.{{ $field['name'] }}" type="text" class="form-control">
                    @endif

                    @error("fields.".$field['name']) 
                        <span class="text-danger small">{{ $message }}</span> 
                    @enderror
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
                                <th
                                    @if($field['sortable'] ?? false)
                                        wire:click="sortBy('{{ $field['name'] }}')"
                                        style="cursor: pointer;"
                                    @endif
                                >
                                    {{ $field['label'] }}
                                    {!! $sortField === $field['name'] ? ($sortDirection === 'asc' ? ' ↑' : ' ↓') : '' !!}
                                </th>
                            @endforeach
                            <th style="width: 140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                @foreach ($this->fields() as $field)
                                    <td class="{{ $field['class'] ?? '' }}">
                                        @php
                                            $rawValue = $item[$field['name']] ?? '';
                                            $value = method_exists($this, 'transformValue')
                                                ? $this->transformValue($field['name'], $rawValue)
                                                : $rawValue;

                                            // Formato extra (como moneda)
                                            if (($field['format'] ?? '') === 'currency') {
                                                $value = '$ ' . number_format((float) $rawValue, 0, ',', '.');
                                            }
                                        @endphp

                                        {{ $value }}
                                    </td>



                                @endforeach
                                <td class="text-center">
                                    <a href="{{ route('quotes.details', ['parentId' => $item->id]) }}" class="btn btn-sm btn-info">
                                        <i class="fa fa-list-ol"></i>
                                    </a>
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
