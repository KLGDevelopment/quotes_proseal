<div class="container-fluid pt-3 px-2">
    <h3 class="mb-3">{{ $this->title }}</h3>

    @if($showForm)
        <form wire:submit.prevent="save" class="mb-4">
            @foreach ($this->fields() as $field)
                @continue(isset($field['show_in_form']) && $field['show_in_form'] === false || isset($field['show_in_create']) && $field['show_in_create'] === false)
                <div class="mb-2">
                    <label class="form-label text-capitalize">
                        {{ str_replace('_', ' ', $field['label']) }}
                    </label>

                    @if(isset($field['ajax']) && $field['ajax'])
                        {{-- Campo AJAX Select2 --}}
                        <div wire:ignore x-data="{ value: @entangle('fields.' . $field['name']) }"
                            x-init="
                                let select = $el.querySelector('select');

                                $(select).select2({
                                    width: '100%',
                                    ajax: {
                                        url: '{{ $field['ajax_url'] ?? '/api/products' }}',
                                        dataType: 'json',
                                        delay: 250,
                                        data: function (params) {
                                            return { q: params.term };
                                        },
                                        processResults: function (data) {
                                            return { results: data };
                                        },
                                        cache: true
                                    }
                                });

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
                                    @if(!empty($fields[$field['name']]) && !empty($field['model']))
                                        @php
                                            $relatedModelClass = $field['model'];
                                            $selectedModel = $relatedModelClass::find($fields[$field['name']]);

                                            $label = $fields[$field['name']];
                                            if ($selectedModel) {
                                                if (!empty($field['display_format'])) {
                                                    // Ejemplo: "{code} - {name}"
                                                    $label = str_replace(
                                                        ['{code}', '{name}'],
                                                        [$selectedModel->code ?? '', $selectedModel->name ?? ''],
                                                        $field['display_format']
                                                    );
                                                } else {
                                                    $column = $field['display_column'] ?? 'name';
                                                    $label = $selectedModel->$column ?? $selectedModel->id;
                                                }
                                            }
                                        @endphp

                                        @if($selectedModel)
                                            <option value="{{ $selectedModel->id }}" selected>{{ $label }}</option>
                                        @endif
                                    @endif


                            </select>
                        </div>
                    @elseif(isset($field['options']) && is_array($field['options']))
                        {{-- Campo select estático --}}
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
                                    <option value="{{ $optionValue }}" @selected(($fields[$field['name']] ?? null) == $optionValue)>
                                        {{ $optionLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input wire:model.defer="fields.{{ $field['name'] }}" type="text" class="form-control" @if($field['read_only'] ?? false) readonly @endif>
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
                
                @if (isset($extraButtons) && is_array($extraButtons))

                    @foreach ($extraButtons as $button)
                        @if (isset($button['link']))
                            <a href="{{ $button['link'] }}" @if($button['target_blank']) target="_blank" @endif class="btn btn-sm btn-{{ $button['color'] ?? 'secondary' }} me-2">
                                @if(isset($button['icon']))
                                    <i class="{{ $button['icon'] }}"></i>
                                @endif
                                 {{ $button['text'] ?? 'Acción' }}
                            </a>
                        @endif
                        @if (isset($button['route']))
                        <button type="button" class="btn btn-sm btn-{{ $button['color'] ?? 'secondary' }} me-2"
                                @if(isset($button['route'])) onclick="window.location.href='{{ $button['route'] }}'" @endif>
                            @if(isset($button['icon']))
                                <i class="{{ $button['icon'] }}"></i>
                            @endif
                            {{ $button['text'] ?? 'Acción' }}
                        </button>
                        @endif
                    @endforeach
                @endif
                @if (isset($backLink))
                <button type="button" class="btn btn-sm btn-secondary me-2" onclick="window.location.href='{{ $this->backLink }}'">Volver</button>
                @endif

                @if($showSyncButton)
                    <button type="button" wire:click="syncFromOdoo" wire:loading.attr="disabled" wire:target="syncFromOdoo" class="btn btn-sm btn-success">
                        <span wire:loading.remove wire:target="syncFromOdoo">
                            <i class="fas fa-sync-alt"></i> Actualizar desde Odoo
                        </span>
                        <span wire:loading wire:target="syncFromOdoo">
                            <i class="fas fa-spinner fa-spin"></i> Sincronizando...
                        </span>
                    </button>
                @endif

                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" wire:model.defer="search" class="form-control float-right" placeholder="Buscar">
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
                <table class="table table-hover table-bordered table-sm table-striped" style="font-size: 11px">
                    <thead class="thead-dark">
                        <tr>
                            @foreach ($this->fields() as $field)
                                @if($this->isSortable() && $field['name'] === $this->getSortableField())
                                    <th colspan="1"
                                        @if($field['sortable'] ?? false)
                                            wire:click="sortBy('{{ $field['name'] }}')"
                                            style="cursor: pointer;"
                                        @endif
                                    >
                                        {{ $field['label'] }}
                                        {!! $sortField === $field['name'] ? ($sortDirection === 'asc' ? ' ↑' : ' ↓') : '' !!}
                                    </th>
                                @else
                                    <th
                                        @if($field['sortable'] ?? false)
                                            wire:click="sortBy('{{ $field['name'] }}')"
                                            style="cursor: pointer;"
                                        @endif
                                    >
                                        {{ $field['label'] }}
                                        {!! $sortField === $field['name'] ? ($sortDirection === 'asc' ? ' ↑' : ' ↓') : '' !!}
                                    </th>
                                @endif
                            @endforeach
                            <th style="width: 140px;">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse ($items as $index => $item)
                        <tr>
                            @foreach ($this->fields() as $field)
                                @php
                                    $rawValue = $item[$field['name']] ?? '';
                                    $value = method_exists($this, 'transformValue')
                                        ? $this->transformValue($field['name'], $rawValue)
                                        : $rawValue;

                                    if (($field['format'] ?? '') === 'currency') {
                                        $value = '$ ' . number_format((float) $rawValue, 0, ',', '.');
                                    }
                                    if (($field['format'] ?? '') === 'percentage') {
                                        $value = number_format((float) $rawValue, 2, ',', '.') . ' %';
                                    }

                                    $isSortable = $this->isSortable() && $field['name'] === $this->getSortableField();
                                    $isFirst = ($index === 0);
                                    $isLast = ($index === $items->count() - 1);

                                    if (isset($field['if_less_than'])){
                                        $compareField = $field['if_less_than'][0];
                                        $compareValue = $item[$compareField] ?? null;

                                        if ($compareValue > $rawValue) {
                                            $cssClass = $field['if_less_than'][1] ?? 'bg-danger';

                                            $field['class'] = $cssClass;
                                        }

                                       
                                    }
                                @endphp

                                @if($isSortable)
                                    {{-- Celda 1: Flechas horizontalmente centradas --}}
                                    @if (null)
                                    <td class="text-center align-middle p-0" style="width: 50px;">
                                        <div class="d-flex justify-content-center align-items-center gap-1">
                                            @unless($isFirst)
                                                <button wire:click="moveUp({{ $item->id }})" class="btn btn-link btn-sm p-0" title="Subir">
                                                    <i class="fas fa-arrow-up fa-sm text-primary"></i>
                                                </button>
                                            @endunless
                                            @unless($isLast)
                                                <button wire:click="moveDown({{ $item->id }})" class="btn btn-link btn-sm p-0" title="Bajar">
                                                    <i class="fas fa-arrow-down fa-sm text-primary"></i>
                                                </button>
                                            @endunless
                                        </div>
                                    </td>
                                    @endif
                                    {{-- Celda 2: Valor centrado --}}
                                    <td class="text-center align-middle">
                                        <strong>{{ $value }}</strong>
                                    </td>
                                @else
                                    <td class="{{ $field['class'] ?? '' }} align-middle">
                                        {{ $value }}
                                    </td>
                                @endif
                            @endforeach

                            {{-- Botones de acción --}}
                            <td class="text-center pl-0 pr-0">
                                @if (isset($masterLink))
                                @php
                                    $route = str_replace("%%rowId%%", $item->id, $masterLink);
                                @endphp
                                <a href="{{ $route }}" class="btn btn-sm btn-info">
                                    <i class="fa fa-list-ol"></i>
                                </a>
                                @endif
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
