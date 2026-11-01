@extends('admin.layouts.app')

@section('titulo', 'Nuevo Slide')
@section('encabezado', 'Nuevo Slide')

@section('contenido')
    @include('admin.slides.form')
@endsection
