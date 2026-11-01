@extends('admin.layouts.app')

@section('titulo', 'Editar Noticia')
@section('encabezado', 'Editar Noticia')

@section('contenido')
    @include('admin.noticias.form')
@endsection
