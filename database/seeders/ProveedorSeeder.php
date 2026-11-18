<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            ['nombre' => 'Ferretería Industrial Cusco SAC', 'ruc' => '20100000001', 'telefono' => '984000001', 'correo' => 'ventas@ferreindustrial.pe', 'direccion' => 'Av. La Cultura 100, Cusco'],
            ['nombre' => 'Distribuidora Andina EIRL', 'ruc' => '20100000002', 'telefono' => '984000002', 'correo' => 'contacto@andina.pe', 'direccion' => 'Av. Collasuyo 250, Cusco'],
            ['nombre' => 'Importaciones Inca SA', 'ruc' => '20100000003', 'telefono' => '984000003', 'correo' => 'info@importinca.pe', 'direccion' => 'Calle Mantas 45, Cusco'],
            ['nombre' => 'Selva Central Distribuciones SAC', 'ruc' => '20100000004', 'telefono' => '984000004', 'correo' => 'ventas@selvacentral.pe', 'direccion' => 'Av. Lima 400, Quillabamba'],
            ['nombre' => 'Materiales Santa Ana SRL', 'ruc' => '20100000005', 'telefono' => '984000005', 'correo' => 'compras@santaana.pe', 'direccion' => 'Jr. Grau 76, Quillabamba'],
            ['nombre' => 'Juan Pérez Quispe', 'ruc' => '10456789012', 'telefono' => '984000006', 'correo' => null, 'direccion' => 'Echarati'],
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::create($proveedor);
        }
    }
}