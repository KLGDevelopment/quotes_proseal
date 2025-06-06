{{-- resources/views/layouts/livewire-adminlte.blade.php --}}
@extends('adminlte::page')

@section('title', $title ?? 'Panel')

@section('content')
    {{ $slot }}
@endsection

@push('css')
    @livewireStyles
@endpush

@push('js')
    @livewireScripts
@endpush
