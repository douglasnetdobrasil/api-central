{{-- Arquivo: resources/views/pdv-caixa/index.blade.php --}}

{{-- Esta linha "puxa" o nosso arquivo de layout --}}
@extends('pdv-caixa.layouts.pdv-caixa-layout')

{{-- Todo o conteúdo dentro desta seção será colocado no @yield('content') do layout --}}
@section('content')
    @livewire('pdv-caixa')
@endsection