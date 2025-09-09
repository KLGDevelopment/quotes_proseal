<div class="container py-3">
    <h3>Cotización #{{ $this->quote->id }}</h3>

    <div class="row">
        <div class="col-sm-6">
            <ul class="list-group mb-4">
                <li class="list-group-item"><strong>Cliente:</strong> {{ $this->quote->customer->vat }} - {{ $this->quote->customer->name }}</li>
                <li class="list-group-item"><strong>Sucursal:</strong> {{ $this->quote->branchOffice->name }}</li>
                <li class="list-group-item"><strong>División:</strong> {{ $this->quote->division->name }}</li>
                <li class="list-group-item"><strong>Estado:</strong> {{ $this->quote->getData('status') }}</li>
            </ul>
        </div>
        <div class="col-sm-6">
            <ul class="list-group mb-4">
                <li class="list-group-item"><strong>Detalle:</strong> {{ $this->quoteDetail->item }}</li>
                <li class="list-group-item"><strong>Valor Venta:</strong> $ {{ number_format($this->quoteDetail->getData('sale_value'), 0, ',', '.') }}</li>
            </ul>      
        </div>
    </div>
    
    

    @include('livewire.generic-crud')
</div>
