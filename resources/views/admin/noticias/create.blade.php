@extends('admin.layouts.app')

@section('titulo', 'Nueva Noticia')
@section('encabezado', 'Nueva Noticia')

@section('contenido')
    @include('admin.noticias.form')
@endsection
