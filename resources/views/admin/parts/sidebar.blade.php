<aside id="sidebar" class="offcanvas-md offcanvas-start text-white flex-shrink-0" tabindex="-1"
    style="width: 240px;">
    <div class="offcanvas-header">
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
            data-bs-target="#sidebar"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column p-0">
        <div class="px-3 py-4 border-bottom border-secondary">
            <span class="fs-5 fw-semibold">Panel administrativo</span>
        </div>

        <ul class="nav nav-pills flex-column p-2 gap-1">
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('admin.slides.*') ? 'active' : '' }}"
                    href="{{ route('admin.slides.index') }}">
                    <i class="bi bi-images me-2"></i>Slider
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('admin.noticias.*') ? 'active' : '' }}"
                    href="{{ route('admin.noticias.index') }}">
                    <i class="bi bi-newspaper me-2"></i>Noticias
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('admin.fotos.*') ? 'active' : '' }}"
                    href="{{ route('admin.galeria.index') }}">
                    <i class="bi bi-camera me-2"></i>Galería
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('admin.productos.*') ? 'active' : '' }}"
                    href="{{ route('admin.productos.index') }}">
                    <i class="bi bi-basket me-2"></i>Productos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('admin.categorias.*') ? 'active' : '' }}"
                    href="{{ route('admin.categorias.index') }}">
                    <i class="bi bi-tags me-2"></i>Categorías
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('admin.facturas.*') ? 'active' : '' }}"
                    href="{{ route('admin.facturas.index') }}">
                    <i class="bi bi-receipt me-2"></i>Facturas
                </a>
            </li>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('admin.compras.*') ? 'active' : '' }}"
                    href="{{ route('admin.compras.index') }}">
                    <i class="bi bi-bag-plus me-2"></i>Compras
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('admin.proveedores.*') ? 'active' : '' }}"
                    href="{{ route('admin.proveedores.index') }}">
                    <i class="bi bi-truck me-2"></i>Proveedores
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('admin.movimientos.*') ? 'active' : '' }}"
                    href="{{ route('admin.movimientos.index') }}">
                    <i class="bi bi-boxes me-2"></i>Movimientos de Stock
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('admin.contacto.*') ? 'active' : '' }}"
                    href="{{ route('admin.contacto.edit') }}">
                    <i class="bi bi-telephone me-2"></i>Contacto
                </a>
            </li>
        </ul>
    </div>
</aside>
