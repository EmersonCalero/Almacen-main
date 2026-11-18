<?php

use App\Models\Proveedor;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?Proveedor $proveedor = null;   // null = crear; con proveedor = editar
    public $nombre = '';
    public $ruc = '';
    public $telefono = '';
    public $correo = '';
    public $direccion = '';

    // Reglas
    protected function rules(): array
    {
        return [
            'nombre' => ['required', 'max:100'],
            'ruc' => ['required', 'digits:11', 'regex:/^(10|15|17|20)\d{9}$/',
                Rule::unique('proveedores', 'ruc')->ignore($this->proveedor?->id)],
            'telefono' => ['nullable', 'digits:9'],
            'correo' => ['nullable', 'email', 'max:100'],
            'direccion' => ['nullable', 'max:150'],
        ];
    }

    // Mensajes en español
    protected function messages(): array
    {
        return [
            'nombre.required' => 'El nombre o razón social es obligatorio.',
            'nombre.max' => 'El nombre admite como máximo 100 caracteres.',
            'ruc.required' => 'El RUC es obligatorio.',
            'ruc.digits' => 'El RUC debe tener exactamente 11 dígitos.',
            'ruc.regex' => 'El RUC debe empezar con 10, 15, 17 o 20.',
            'ruc.unique' => 'Ya existe un proveedor con ese RUC.',
            'telefono.digits' => 'El teléfono debe tener exactamente 9 dígitos.',
            'correo.email' => 'El correo no tiene un formato válido.',
            'correo.max' => 'El correo admite como máximo 100 caracteres.',
            'direccion.max' => 'La dirección admite como máximo 150 caracteres.',
        ];
    }

    // Valida en tiempo real y recorta espacios del RUC, teléfono y correo
    public function updated($propiedad)
    {
        if (in_array($propiedad, ['ruc', 'telefono', 'correo'])) {
            $this->$propiedad = trim((string) $this->$propiedad);
        }
        $this->validateOnly($propiedad);
    }

    // Guardar crea o actualiza
    public function guardar()
    {
        $datos = $this->validate();

        // Los campos opcionales vacíos se guardan como NULL
        foreach (['telefono', 'correo', 'direccion'] as $campo) {
            if ($datos[$campo] === '') {
                $datos[$campo] = null;
            }
        }

        if ($this->proveedor) {
            $this->proveedor->update($datos);
        } else {
            Proveedor::create($datos);
        }

        $this->cancelar();
        session()->flash('ok', 'Proveedor guardado correctamente.');
        $this->dispatch('proveedor-guardado');
    }

    public function cancelar()
    {
        $this->reset();
        $this->resetValidation();
    }

    // La tabla pide editar un proveedor
    #[On('editar-proveedor')]
    public function editar($id)
    {
        $this->proveedor = Proveedor::findOrFail($id);
        $this->fill([
            'nombre' => $this->proveedor->nombre,
            'ruc' => $this->proveedor->ruc,
            'telefono' => $this->proveedor->telefono ?? '',
            'correo' => $this->proveedor->correo ?? '',
            'direccion' => $this->proveedor->direccion ?? '',
        ]);
        $this->resetValidation();
    }
};
?>

<div class="bg-white rounded-xl shadow p-5 ml-4">
    @if (session('ok'))
        <p class="mb-3 rounded bg-green-100 px-3 py-2 text-green-700">{{ session('ok') }}</p>
    @endif

    <form wire:submit="guardar" class="space-y-4">
        <h2 class="text-lg font-bold">{{ $proveedor ? 'Editar proveedor' : 'Nuevo proveedor' }}</h2>

        {{-- Nombre --}}
        <div>
            <label for="nombre" class="block text-sm font-medium">Nombre o razón social</label>
            <input id="nombre" type="text" wire:model.blur="nombre"
                   class="w-full border rounded px-3 py-2">
            @error('nombre')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- RUC --}}
        <div>
            <label for="ruc" class="block text-sm font-medium">RUC</label>
            <input id="ruc" type="text" wire:model.blur="ruc"
                   x-on:blur="$el.value = $el.value.trim()"
                   class="w-full border rounded px-3 py-2">
            @error('ruc')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Teléfono --}}
        <div>
            <label for="telefono" class="block text-sm font-medium">Teléfono (opcional)</label>
            <input id="telefono" type="text" wire:model.blur="telefono"
                   x-on:blur="$el.value = $el.value.trim()"
                   class="w-full border rounded px-3 py-2">
            @error('telefono')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Correo --}}
        <div>
            <label for="correo" class="block text-sm font-medium">Correo (opcional)</label>
            <input id="correo" type="text" wire:model.blur="correo"
                   x-on:blur="$el.value = $el.value.trim()"
                   class="w-full border rounded px-3 py-2">
            @error('correo')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Dirección --}}
        <div>
            <label for="direccion" class="block text-sm font-medium">Dirección (opcional)</label>
            <textarea id="direccion" rows="2" wire:model.blur="direccion"
                      class="w-full border rounded px-3 py-2"></textarea>
            @error('direccion')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit" class="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">
                Guardar
            </button>
            @if ($proveedor)
                <button type="button" wire:click="cancelar"
                        class="rounded bg-gray-400 px-4 py-2 text-white hover:bg-gray-500">
                    Cancelar
                </button>
            @endif
        </div>
    </form>
</div>