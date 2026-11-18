<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Recorta espacios igual que el formulario, antes de validar
    protected function prepareForValidation(): void
    {
        foreach (['ruc', 'telefono', 'correo'] as $campo) {
            if (is_string($this->input($campo))) {
                $this->merge([$campo => trim($this->input($campo))]);
            }
        }
    }

    // Las MISMAS reglas que el componente proveedores-form (aquí sin ignore: solo crea)
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'max:100'],
            'ruc' => ['required', 'digits:11', 'regex:/^(10|15|17|20)\d{9}$/', 'unique:proveedores,ruc'],
            'telefono' => ['nullable', 'digits:9'],
            'correo' => ['nullable', 'email', 'max:100'],
            'direccion' => ['nullable', 'max:150'],
        ];
    }

    // Los MISMOS mensajes que el componente
    public function messages(): array
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
}