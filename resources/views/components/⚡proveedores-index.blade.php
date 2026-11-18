<?php

use App\Models\Proveedor;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public $buscar = '';

    // Al buscar, vuelve a la página 1
    public function updatingBuscar()
    {
        $this->resetPage();
    }

    // Cuando el formulario guarda, la tabla se vuelve a dibujar
    #[On('proveedor-guardado')]
    public function refrescar()
    {
    }

    public function with(): array
    {
        $consulta = Proveedor::query();

        // Búsqueda por nombre o RUC
        if ($this->buscar) {
            $consulta->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->buscar . '%')
                  ->orWhere('ruc', 'like', '%' . $this->buscar . '%');
            });
        }

        return [
            'proveedores' => $consulta->orderBy('nombre')->paginate(10),
        ];
    }
};
?>

<div class="space-y-4">
    <input type="text"
           wire:model.live.debounce.400ms="buscar"
           placeholder="Buscar por nombre o RUC"
           class="border rounded px-3 py-2 bg-white w-full">

    <table class="w-full text-left bg-white rounded">
        <thead>
            <tr class="bg-gray-200">
                <th class="p-2">RUC</th>
                <th class="p-2">Nombre</th>
                <th class="p-2">Teléfono</th>
                <th class="p-2">Correo</th>
                <th class="p-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($proveedores as $proveedor)
                <tr wire:key="proveedor-{{ $proveedor->id }}" class="border-b">
                    <td class="p-2 whitespace-nowrap">{{ $proveedor->ruc }}</td>
                    <td class="p-2">{{ $proveedor->nombre }}</td>
                    <td class="p-2 whitespace-nowrap">{{ $proveedor->telefono }}</td>
                    <td class="p-2">{{ $proveedor->correo }}</td>
                    <td class="p-2">
                        <button type="button"
                                wire:click="$dispatch('editar-proveedor', { id: {{ $proveedor->id }} })"
                                class="rounded bg-blue-600 px-3 py-1 text-sm text-white hover:bg-blue-700">
                            Editar
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-2 text-center">Sin datos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $proveedores->links() }}
</div>