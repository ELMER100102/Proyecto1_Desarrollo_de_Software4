{{-- resources/views/admin/pedidos/ajustes.blade.php  ·  Requiere: $config --}}
@extends('admin.layouts.app')

@section('titulo', 'Ajustes de pedidos')
@section('encabezado', 'Ajustes de pedidos')

@section('contenido')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-4">
            <form action="{{ route('admin.pedidos.edit.update') }}" method="POST">
                @csrf
                @method('PUT')

                <p class="text-muted">
                    Define cómo pueden pedir tus clientes desde el sitio. Los cambios se ven de inmediato.
                </p>

                @error('acepta_local')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                @php
                    $opciones = [
                        'pedidos_activos'  => ['Recibir pedidos desde el sitio', 'Interruptor general. Apagado, el sitio no muestra el carrito.'],
                        'acepta_local'     => ['Comer aquí', 'El cliente pide y come en el local. Se identifica con su turno (o mesa).'],
                        'acepta_llevar'    => ['Para llevar', 'Recoge el pedido en el local. Se le pide un teléfono.'],
                        'acepta_domicilio' => ['A domicilio', 'Pide dirección y teléfono. Actívalo si el negocio hace entregas.'],
                        'usa_mesas'        => ['Pedir número de mesa', 'Solo en "Comer aquí". Útil en restaurantes con mesas numeradas.'],
                    ];
                @endphp

                @foreach ($opciones as $campo => [$titulo, $ayuda])
                    <div class="d-flex align-items-start justify-content-between py-3 border-bottom">
                        <div class="pe-3">
                            <label for="{{ $campo }}" class="fw-semibold mb-0">{{ $titulo }}</label>
                            <div class="form-text">{{ $ayuda }}</div>
                        </div>
                        <div class="form-check form-switch m-0">
                            <input type="hidden" name="{{ $campo }}" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="{{ $campo }}"
                                name="{{ $campo }}" value="1" @checked(old($campo, $config->$campo))>
                        </div>
                    </div>
                @endforeach

                <div class="mt-4">
                    <label for="mensaje_final" class="form-label">Mensaje al cliente (opcional)</label>
                    <input type="text" id="mensaje_final" name="mensaje_final" maxlength="255"
                        class="form-control @error('mensaje_final') is-invalid @enderror"
                        value="{{ old('mensaje_final', $config->mensaje_final) }}"
                        placeholder="Ej.: Pasa a caja cuando te llamemos por tu turno.">
                    @error('mensaje_final')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-dark">Guardar ajustes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
