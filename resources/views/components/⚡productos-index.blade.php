<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Almacen;

new class extends Component
{
    use WithPagination;

    // #[Url] guarda cada filtro en la dirección del navegador
    #[Url(as: 'q', except: '')]
    public $buscar = '';

    #[Url(as: 'categoria', except: '')]
    public $categoria_id = '';

    #[Url(as: 'almacen', except: '')]
    public $almacen_id = '';

    // Filtro propio: rango de stock (agotado, bajo, medio, alto)
    #[Url(as: 'stock', except: '')]
    public $rango_stock = '';

    public $paginar = true;

    // Al cambiar la búsqueda, un filtro o el modo, vuelve a la página 1
    public function updating($propiedad)
    {
        if (in_array($propiedad, ['buscar', 'categoria_id', 'almacen_id', 'rango_stock', 'paginar'])) {
            $this->resetPage();
        }
    }

    public function with(): array
    {
        // Rangos del filtro propio: clave => mínimo, máximo y texto del desplegable
        $rangos = [
            'agotado' => ['min' => 0,   'max' => 0,           'texto' => 'Agotado (0)'],
            'bajo'    => ['min' => 1,   'max' => 50,          'texto' => 'Bajo (1 a 50)'],
            'medio'   => ['min' => 51,  'max' => 100,         'texto' => 'Medio (51 a 100)'],
            'alto'    => ['min' => 101, 'max' => PHP_INT_MAX, 'texto' => 'Alto (más de 100)'],
        ];
        $rango = $rangos[$this->rango_stock] ?? null;

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

        // Filtro propio: rango de stock (en el almacén elegido, si hay uno)
        if ($rango) {
            $consulta->whereHas('cantidad', function ($q) use ($rango) {
                $q->whereBetween('stock', [$rango['min'], $rango['max']]);
                if ($this->almacen_id) {
                    $q->where('almacen_id', $this->almacen_id);
                }
            });
        }

        $consulta->orderBy('productos.nombre');

        // ===== MEDICIÓN =====
        DB::flushQueryLog();
        DB::enableQueryLog();
        $memoriaInicio = memory_get_usage();
        $inicio = microtime(true);

        $productos = $this->paginar
            ? $consulta->paginate(10)
            : $consulta->get();

        $medicion = [
            'modo'      => $this->paginar ? 'Paginado (10 por página)' : 'Sin paginar (todo)',
            'filas'     => $productos->count(),
            'tiempo'    => round((microtime(true) - $inicio) * 1000, 1),
            'consultas' => count(DB::getQueryLog()),
            'memoria'   => round((memory_get_usage() - $memoriaInicio) / 1024 / 1024, 2),
        ];
        DB::disableQueryLog();

        return [
            'productos'  => $productos,
            'medicion'   => $medicion,
            'rangos'     => $rangos,
            'rango'      => $rango,
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

        <select wire:model.live="rango_stock" class="border rounded px-3 py-2 bg-white">
            <option value="">Todo el stock</option>
            @foreach ($rangos as $clave => $r)
                <option value="{{ $clave }}">{{ $r['texto'] }}</option>
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
                    // Elige qué fila de stock mostrar según los filtros activos
                    $fila = $producto->cantidad
                        ->when($this->almacen_id, fn ($c) => $c->where('almacen_id', $this->almacen_id))
                        ->when($rango, fn ($c) => $c->whereBetween('stock', [$rango['min'], $rango['max']]))
                        ->first();

                    // Color según el rango del stock
                    $color = match (true) {
                        ! $fila             => '',
                        $fila->stock == 0   => 'text-red-600 font-bold',
                        $fila->stock <= 50  => 'text-orange-600 font-bold',
                        $fila->stock <= 100 => 'text-yellow-700',
                        default             => 'text-green-700',
                    };
                @endphp
                <tr wire:key="producto-{{ $producto->id }}" class="border-b">
                    <td class="p-2">{{ $producto->codigo }}</td>
                    <td class="p-2">{{ $producto->nombre }}</td>
                    <td class="p-2">{{ $producto->categoria?->nombre }}</td>
                    <td class="p-2 {{ $color }}">
                        {{ $fila?->stock }}
                        @if ($fila && $fila->stock == 0) (agotado) @endif
                    </td>
                    <td class="p-2">{{ $fila?->almacen?->nombre }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-2 text-center">Sin datos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($paginar)
        {{ $productos->links() }}
    @endif
</div>