@if ($paginator->hasPages())
    <nav>
        <div class="row align-items-center justify-content-between">
            <div class="col-12 col-md-4 text-left text-md-start mb-2 mb-md-0">
                <small class="text-muted">
                    Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados
                </small>
            </div>

            <div class="col-12 col-md-8">
                <ul class="pagination justify-content-center justify-content-md-end flex-wrap">
                    {{-- Botón anterior --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                    @else
                        <li class="page-item">
                            <button wire:click="previousPage('{{ $paginator->getPageName() }}')" class="page-link" aria-label="Anterior" rel="prev">&laquo;</button>
                        </li>
                    @endif

                    {{-- Números de página (solo en pantallas md o mayores) --}}
                    <span class="d-none d-md-flex">
                        @foreach ($elements as $element)
                            @if (is_string($element))
                                <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                            @endif

                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $paginator->currentPage())
                                        <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                                    @else
                                        <li class="page-item">
                                            <button wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" class="page-link">{{ $page }}</button>
                                        </li>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </span>

                    {{-- Botón siguiente --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <button wire:click="nextPage('{{ $paginator->getPageName() }}')" class="page-link" aria-label="Siguiente" rel="next">&raquo;</button>
                        </li>
                    @else
                        <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif
