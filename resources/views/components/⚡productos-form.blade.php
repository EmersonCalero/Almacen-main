<?php

use App\Models\Producto;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?Producto $producto = null;   // null = crear; con producto = editar
    public $codigo = '';
    public $nombre = '';
    public $descripcion = '';
    public $precio = '';

    // Paso 2: reglas
    protected function rules(): array
    {
        return [
            'codigo' => ['required', 'max:20', 'alpha_dash',
                Rule::unique('productos', 'codigo')->ignore($this->producto?->id)],
            'nombre' => ['required', 'max:100'],
            'descripcion' => ['required', 'max:500'],
            'precio' => ['required', 'numeric', 'gt:0', 'max:999999.99', 'decimal:0,2'],
        ];
    }

    // Paso 3: mensajes en español
    protected function messages(): array
    {
        return [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.max' => 'El código admite como máximo 20 caracteres.',
            'codigo.alpha_dash' => 'El código solo admite letras, números y guiones.',
            'codigo.unique' => 'Ya existe un producto con ese código.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre admite como máximo 100 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción admite como máximo 500 caracteres.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número.',
            'precio.gt' => 'El precio debe ser mayor que cero.',
            'precio.max' => 'El precio no puede superar 999999.99.',
            'precio.decimal' => 'El precio admite como máximo 2 decimales.',
        ];
    }

    // Paso 4 + caso borde: valida en tiempo real y recorta espacios del código
    public function updated($propiedad)
    {
        if ($propiedad === 'codigo') {
            $this->codigo = trim((string) $this->codigo);
        }
        $this->validateOnly($propiedad);
    }

    // Paso 6: guardar crea o actualiza
    public function guardar()
    {
        $datos = $this->validate();

        if ($this->producto) {
            $this->producto->update($datos);
        } else {
            Producto::create($datos);
        }

        $this->cancelar();
        session()->flash('ok', 'Producto guardado correctamente.');
        $this->dispatch('producto-guardado');
    }

    public function cancelar()
    {
        $this->reset();
        $this->resetValidation();
    }

    // Paso 7: la tabla pide editar un producto
    #[On('editar-producto')]
    public function editar($id)
    {
        $this->producto = Producto::findOrFail($id);
        $this->fill($this->producto->only('codigo', 'nombre', 'descripcion', 'precio'));
        $this->resetValidation();
    }
};
?>

<div class="bg-white rounded-xl shadow p-5 ml-4">
    @if (session('ok'))
        <p class="mb-3 rounded bg-green-100 px-3 py-2 text-green-700">{{ session('ok') }}</p>
    @endif

    <form wire:submit="guardar" class="space-y-4">
        <h2 class="text-lg font-bold">{{ $producto ? 'Editar producto' : 'Nuevo producto' }}</h2>

        {{-- Código --}}
        <div>
            <label for="codigo" class="block text-sm font-medium">Código</label>
            <input id="codigo" type="text" wire:model.blur="codigo"
                   class="w-full border rounded px-3 py-2">
            @error('codigo')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Nombre --}}
        <div>
            <label for="nombre" class="block text-sm font-medium">Nombre</label>
            <input id="nombre" type="text" wire:model.blur="nombre"
                   class="w-full border rounded px-3 py-2">
            @error('nombre')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Descripción --}}
        <div>
            <label for="descripcion" class="block text-sm font-medium">Descripción</label>
            <textarea id="descripcion" rows="3" wire:model.blur="descripcion"
                      class="w-full border rounded px-3 py-2"></textarea>
            @error('descripcion')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Precio --}}
        <div>
            <label for="precio" class="block text-sm font-medium">Precio</label>
            <input id="precio" type="text" wire:model.blur="precio"
                   class="w-full border rounded px-3 py-2">
            @error('precio')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit" class="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">
                Guardar
            </button>
            @if ($producto)
                <button type="button" wire:click="cancelar"
                        class="rounded bg-gray-400 px-4 py-2 text-white hover:bg-gray-500">
                    Cancelar
                </button>
            @endif
        </div>
    </form>
</div>