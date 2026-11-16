<?php

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Almacen;

new class extends Component
{
    use WithPagination;

    public $buscar = '';
    public $categoria_id = '';
    public $almacen_id = '';
    public $paginar = true;   // interruptor: true = paginado, false = sin paginar

    // Al cambiar la búsqueda, un filtro o el modo, vuelve a la página 1
    public function updating($propiedad)
    {
        if (in_array($propiedad, ['buscar', 'categoria_id', 'almacen_id', 'paginar'])) {
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

        $consulta->orderBy('productos.nombre');

        // ===== MEDICIÓN: empieza el cronómetro =====
        DB::flushQueryLog();
        DB::enableQueryLog();
        $memoriaInicio = memory_get_usage();
        $inicio = microtime(true);

        $productos = $this->paginar
            ? $consulta->paginate(10)   // solo 10
            : $consulta->get();         // todos

        $medicion = [
            'modo'      => $this->paginar ? 'Paginado (10 por página)' : 'Sin paginar (todo)',
            'filas'     => $productos->count(),
            'tiempo'    => round((microtime(true) - $inicio) * 1000, 1),
            'consultas' => count(DB::getQueryLog()),
            'memoria'   => round((memory_get_usage() - $memoriaInicio) / 1024 / 1024, 2),
        ];
        DB::disableQueryLog();
        // ===== fin de la medición =====

        return [
            'productos'  => $productos,
            'medicion'   => $medicion,
            'categorias' => Categoria::orderBy('nombre')->get(),
            'almacenes'  => Almacen::orderBy('nombre')->get(),
        ];
    }
};
?>

<div class="space-y-4">
    {{-- Filtros --}}
    <div class="flex flex-wrap gap-3 items-center">
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

        <label class="flex items-center gap-2 bg-white border rounded px-3 py-2">
            <input type="checkbox" wire:model.live="paginar">
            Paginar
        </label>
    </div>

    {{-- Cuadro de medición --}}
    <div class="bg-yellow-100 border border-yellow-400 rounded p-3 text-sm">
        <strong>Medición:</strong> {{ $medicion['modo'] }} ·
        Filas: <strong>{{ $medicion['filas'] }}</strong> ·
        Tiempo: <strong>{{ $medicion['tiempo'] }} ms</strong> ·
        Consultas SQL: <strong>{{ $medicion['consultas'] }}</strong> ·
        Memoria: <strong>{{ $medicion['memoria'] }} MB</strong>
    </div>

    {{-- Tabla --}}
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

    {{-- Botones 1 2 3 solo si está paginado --}}
    @if ($paginar)
        {{ $productos->links() }}
    @endif
</div>