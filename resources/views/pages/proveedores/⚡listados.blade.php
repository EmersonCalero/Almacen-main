<?php

use Livewire\Component;

new class extends Component
{
};
?>

@extends('layouts.app')

@section('titulo', 'Registro de Proveedores')

@section('menu')
    @include('layouts.menu')
@endsection

@section('contenido')
    @livewire('proveedores-index')
@endsection

@section('formulario')
    @livewire('proveedores-form')
@endsection