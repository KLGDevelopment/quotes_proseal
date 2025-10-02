@props(['items' => [], 'align' => 'end', 'bg' => 'bg-transparent'])

<nav aria-label="breadcrumb" class="d-flex justify-content-{{ $align }}">
    <ol class="breadcrumb mb-0 d-inline-flex w-auto px-2 py-1 rounded {{ $bg }}">
        @foreach ($items as $item)
            @php $isActive = empty($item['url']); @endphp
            <li class="breadcrumb-item {{ $isActive ? 'active' : '' }}" @if($isActive) aria-current="page" @endif>
                @if(!$isActive)
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @else
                    {{ $item['label'] }}
                @endif
            </li>
        @endforeach
    </ol>
</nav>