@extends('web.layouts.app')

@section('titulo', 'Contacto')

@section('contenido')
    <section class="ftco-section contact-section">
        <div class="container mt-5">
            <div class="row block-9">
                <div class="col-md-4 contact-info ftco-animate">
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <h2 class="h4">Información de Contacto</h2>
                        </div>
                        <div class="col-md-12 mb-3">
                            <p><span>Teléfono:</span> <a href="tel:{{ $institucion?->telefono }}">(506)
                                    {{ $institucion?->telefono }}</a></p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <p><span>Email:</span> <a
                                    href="mailto:{{ $institucion?->correo }}">{{ $institucion?->correo }}</a></p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <p><span>Sitio Web:</span> <a href="{{ route('inicio') }}">NuestroSitio.com</a></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-1"></div>
                <div class="col-md-6 ftco-animate">
                    <form action="{{ route('contacto.enviar') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                        id="nombre" name="nombre" value="{{ old('nombre') }}"
                                        placeholder="Nombre Completo" required>
                                    @error('nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="email" class="form-control @error('correo') is-invalid @enderror"
                                        id="correo" name="correo" value="{{ old('correo') }}"
                                        placeholder="correo@ejemplo.com" required>
                                    @error('correo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control @error('asunto') is-invalid @enderror" id="asunto"
                                name="asunto" value="{{ old('asunto') }}" placeholder="Asunto del mensaje" required>
                            @error('asunto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="mensaje" class="form-label">Mensaje</label>
                            <textarea class="form-control @error('mensaje') is-invalid @enderror" id="mensaje" name="mensaje" rows="5"
                                placeholder="Escriba su mensaje aquí..." required>{{ old('mensaje') }}</textarea>
                            @error('mensaje')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary py-3 px-5">Enviar mensaje</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
