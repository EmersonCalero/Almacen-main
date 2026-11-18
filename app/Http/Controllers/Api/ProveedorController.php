<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProveedorRequest;
use App\Models\Proveedor;

class ProveedorController extends Controller
{
    // GET /api/proveedores
    public function index()
    {
        return Proveedor::orderBy('nombre')->paginate(10);
    }

    // POST /api/proveedores
    public function store(StoreProveedorRequest $request)
    {
        $proveedor = Proveedor::create($request->validated());

        return response()->json(['data' => $proveedor], 201);
    }

    // GET /api/proveedores/{id}
    public function show(Proveedor $proveedor)
    {
        return response()->json(['data' => $proveedor]);
    }
}