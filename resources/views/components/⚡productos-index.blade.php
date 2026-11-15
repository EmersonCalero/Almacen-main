<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Almacen;

new class extends Component
{
    use WithPagination;

    public $buscar = '';
    public $categoria_id = '';
    public $almacen_id = '';

    // Al cambiar la búsqueda o un filtro, vuelve a la página 1
    public function updating($propiedad)
    {
        if (in_array($propiedad, ['buscar', 'categoria_id', 'almacen_id'])) {
            $this->resetPage();
        }
    }

    public function with(): array
    {
        $consulta = Producto::query()
            ->select('productos.*')
            ->with(['categoria', 'cantidad.almacen']);

        // Búsqueda por nombre o código
        if ($this->buscar) {
            $consulta->where(function ($q) {
                $q->where('productos.nombre', 'like', '%' . $this->buscar . '%')
                  ->orWhere('productos.codigo', 'like', '%' . $this->buscar . '%');
            });
        }

        // Filtro 1: categoría
        if ($this->categoria_id) {
            $consulta->where('productos.categoria_id', $this->categoria_id);
        }

        // Filtro 2: almacén
        if ($this->almacen_id) {
            $consulta->join('stock__almacen', 'productos.id', '=', 'stock__almacen.producto_id');
            $consulta->where('stock__almacen.almacen_id', $this->almacen_id);
        }

        return [
            'productos'  => $consulta->orderBy('productos.nombre')->paginate(10),
            'categorias' => Categoria::orderBy('nombre')->get(),
            'almacenes'  => Almacen::orderBy('nombre')->get(),
        ];
    }
};
?>

<div class="space-y-4">
    <div class="flex flex-wrap gap-3">
        <input type="text"
               wire:model.live.debounce.400ms="buscar"
               placeholder="Buscar por nombre o código"
               class="border rounded px-3 py-2 bg-white">

        <select wire:model.live="categoria_id" class="border rounded px-3 py-2 bg-white">
            <option value="">Todas las categorías</option>
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
            @endforeach
        </select>

        <select wire:model.live="almacen_id" class="border rounded px-3 py-2 bg-white">
            <option value="">Todos los almacenes</option>
            @foreach ($almacenes as $almacen)
                <option value="{{ $almacen->id }}">{{ $almacen->nombre }}</option>
            @endforeach
        </select>
    </div>

    <table class="w-full text-left bg-white rounded">
        <thead>
            <tr class="bg-gray-200">
                <th class="p-2">Código</th>
                <th class="p-2">Nombre</th>
                <th class="p-2">Categoría</th>
                <th class="p-2">Stock</th>
                <th class="p-2">Almacén</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($productos as $producto)
                @php
                    // Si hay almacén elegido, muestra el stock de ESE almacén; si no, el del primero
                    $fila = $this->almacen_id
                        ? $producto->cantidad->firstWhere('almacen_id', $this->almacen_id)
                        : $producto->cantidad->first();
                @endphp
                <tr wire:key="producto-{{ $producto->id }}" class="border-b">
                    <td class="p-2">{{ $producto->codigo }}</td>
                    <td class="p-2">{{ $producto->nombre }}</td>
                    <td class="p-2">{{ $producto->categoria?->nombre }}</td>
                    <td class="p-2">{{ $fila?->stock }}</td>
                    <td class="p-2">{{ $fila?->almacen?->nombre }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-2 text-center">Sin datos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $productos->links() }}
</div>