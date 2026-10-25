{{-- resources/views/admin/pedidos/index.blade.php
     Requiere: $ver, $pedidos, $totalAbiertos, $totalHoy, $config --}}
@extends('admin.layouts.app')

@section('titulo', 'Pedidos')
@section('encabezado', 'Pedidos')

@section('boton')
    <a href="{{ route('admin.pedidos.edit') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-gear"></i> Ajustes
    </a>
@endsection

@section('contenido')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if (! $config->disponible())
        <div class="alert alert-warning">
            Los pedidos desde el sitio están desactivados. Puedes activarlos en
            <a href="{{ route('admin.pedidos.edit') }}">Ajustes de pedidos</a>.
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link {{ $ver === 'abiertos' ? 'active' : '' }}"
                    href="{{ route('admin.pedidos.index', ['ver' => 'abiertos']) }}">
                    Abiertos <span class="badge text-bg-light ms-1">{{ $totalAbiertos }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $ver === 'hoy' ? 'active' : '' }}"
                    href="{{ route('admin.pedidos.index', ['ver' => 'hoy']) }}">
                    Todos los de hoy <span class="badge text-bg-light ms-1">{{ $totalHoy }}</span>
                </a>
            </li>
        </ul>

        {{-- Los navegadores exigen que el permiso se pida con un clic --}}
        <button type="button" id="btnAvisos" class="btn btn-outline-secondary btn-sm d-none">
            <i class="bi bi-bell"></i> Activar avisos de pedidos nuevos
        </button>
    </div>

    {{-- pedidos-admin.js reemplaza el contenido de este bloque cuando hay cambios --}}
    <div class="row g-3" id="listaPedidos"
        data-url="{{ route('admin.pedidos.lista') }}" data-ver="{{ $ver }}">
        @include('admin.pedidos._lista')
    </div>
@endsection
