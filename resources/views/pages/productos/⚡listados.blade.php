<?php

use Livewire\Component;
use App\Models\Producto;
use Livewire\Attributes\Computed;


new class extends Component
{
};
?>

@extends('layouts.app')

@section('titulo', 'Registro de Productos')

@section('menu')
    @include('layouts.menu')
@endsection

@section('contenido')
    @livewire('productos-index')
@endsection

@section('formulario')
    @livewire('productos-form')
@endsection
