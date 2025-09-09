<div class="container py-3">
    <h3>Cotización #{{ $quote->id }}</h3>
    <ul class="list-group mb-4">
        <li class="list-group-item"><strong>Cliente:</strong> {{ $quote->customer->vat }} - {{ $quote->customer->name }}</li>
        <li class="list-group-item"><strong>Estado:</strong> {{ $quote->getData('status') }}</li>
    </ul>

    @if ($showForm)
        <div class="card mb-3">
            <div class="card-header">Formulario Producto</div>
            <div class="card-body">
                <div class="mb-3">
                    <label>Producto</label>
                    <select class="form-control" wire:model="productId">
                        <option value="">Seleccione</option>
                        @foreach (\App\Models\Product::all() as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Cantidad</label>
                    <input type="number" class="form-control" wire:model="quantity">
                </div>
                <div class="mb-3">
                    <label>Valor</label>
                    <input type="number" step="0.01" class="form-control" wire:model="amount">
                </div>
                <div class="mb-3">
                    <label>Margen de Utilidad (%)</label>
                    <input type="number" step="0.01" class="form-control" wire:model="profit_margin">
                </div>
                <button class="btn btn-primary" wire:click="save">Guardar</button>
                <button class="btn btn-secondary" wire:click="$set('showForm', false)">Cancelar</button>
            </div>
        </div>
    @else
        <button class="btn btn-success mb-3" wire:click="openForm">Agregar Producto</button>
        <button class="btn btn-secondary mb-3" onclick="window.location.href='{{ route ('quotes.details',$this->quote->id) }}'">Volver</button>


            <table class="table table-bordered table-striped">
                <thead class="table-light">
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Valor</th>
                    <th>Margen</th>
                    <th>Valor Venta</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cartItems as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->amount }}</td>
                        <td>{{ $item->profit_margin }}%</td>
                        <td>{{ $item->sale_value }}</td>
                        <td class="text-center pl-0 pr-0">
                            <button class="btn btn-sm btn-info" wire:click="edit({{ $item->id }})"><i class="fa fa-edit"></i></button>
                            <button type="button" onclick="confirmDelete({{ $item->id }})" class="btn btn-sm btn-danger">
                                <i class="fa fa-trash"></i>
                            </button>

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
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
                window.Livewire.dispatch('deleteConfirmed', { id: id });
            }
        });
    }
</script>
@endpush
