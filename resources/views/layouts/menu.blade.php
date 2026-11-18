<nav class="flex flex-col gap-2 pr-4">
    <a href="{{ route('productos.index') }}"
       class="rounded px-3 py-2 {{ request()->routeIs('productos.*') ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-200' }}">
        Productos
    </a>
    <a href="{{ route('proveedores.index') }}"
       class="rounded px-3 py-2 {{ request()->routeIs('proveedores.*') ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-200' }}">
        Proveedores
    </a>
</nav>