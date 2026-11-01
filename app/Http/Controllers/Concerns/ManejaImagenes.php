<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Subida de imágenes al disco "public" (storage/app/public, visible en /storage).
 * Lo usan los CRUD que tienen una imagen: noticias, slides, galería, productos y contacto.
 */
trait ManejaImagenes
{
    /** Regla de validación de una imagen JPG/PNG de hasta 2 MB */
    protected function reglaImagen(bool $obligatoria = false): array
    {
        return [$obligatoria ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'];
    }

    protected function mensajesImagen(string $campo = 'imagen'): array
    {
        return [
            "{$campo}.required" => 'Debe seleccionar una imagen.',
            "{$campo}.image"    => 'El archivo debe ser una imagen.',
            "{$campo}.mimes"    => 'La imagen debe ser JPG o PNG.',
            "{$campo}.max"      => 'La imagen no puede pesar más de 2 MB.',
        ];
    }

    /**
     * Si llegó un archivo nuevo lo guarda en $carpeta y borra el anterior.
     * Devuelve la ruta que debe quedar en la base de datos (la anterior si no se subió nada).
     */
    protected function reemplazarImagen(Request $request, string $carpeta, ?string $anterior = null, string $campo = 'imagen'): ?string
    {
        if (! $request->hasFile($campo)) {
            return $anterior;
        }

        $nueva = $request->file($campo)->store($carpeta, 'public');
        $this->borrarImagen($anterior);

        return $nueva;
    }

    protected function borrarImagen(?string $ruta): void
    {
        if ($ruta) {
            Storage::disk('public')->delete($ruta);
        }
    }
}
