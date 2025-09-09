<div class="container py-3">
    <h3>Cotización #{{ $quote->id }}</h3>

    <ul class="list-group mb-4">
        <li class="list-group-item"><strong>Cliente:</strong> {{ $quote->customer->vat }} - {{ $quote->customer->name }}</li>
        <li class="list-group-item"><strong>Sucursal:</strong> {{ $quote->branchOffice->name }}</li>
        <li class="list-group-item"><strong>División:</strong> {{ $quote->division->name }}</li>
        <li class="list-group-item"><strong>Valor Venta:</strong> $ {{ number_format($quote->neto, 0, ',', '.') }}</li>
        <li class="list-group-item"><strong>Estado:</strong> {{ $quote->getData('status') }}</li>
    </ul>

    @include('livewire.generic-crud')

    @include('components.sweetalert-confirm')

    <div class="btn-group ml-2" role="group">


        @if(auth()->user()->hasRole('approver-basic') && $quote->status === 0 && $quote->neto > 0)
            <button type="button" class="btn btn-sm btn-primary" onclick="sweetConfirm(event, () => { window.Livewire.dispatch('approveBasic', {id:{{ $quote->id }} } ) })">
                <i class="fas fa-check"></i> Aprobar (Básico)
            </button>
        @endif

        @if(auth()->user()->hasRole('approver-advanced') && $quote->status === 1)
            <button type="button" class="btn btn-sm btn-primary" onclick="sweetConfirm(event, () => { window.Livewire.dispatch('approveAdvanced', {id:{{ $quote->id }} } ) })">
                <i class="fas fa-check-double"></i> Aprobar (Avanzado)
            </button>
        @endif

        @if (auth()->user()->hasRole('approver-advanced') && $quote->status == 2)
            <button type="button" class="btn btn-sm btn-success" onclick="sweetConfirm(event, () => { window.Livewire.dispatch('sentToOdoo', {id:{{ $quote->id }} } ) })">
                <span>
                    <i class="fas fa-sync-alt"></i> Inyectar a Odoo
                </span>
            </button>
        @endif
    </div>
</div>

