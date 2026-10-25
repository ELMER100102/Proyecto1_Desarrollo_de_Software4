<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

/**
 * PWA del sitio público y del admin.
 * Nombre y logo salen de la tabla "contacto", así que si el administrador los cambia
 * la app instalada los toma sola (los íconos se regeneran cuando cambia el logo).
 */
class PwaController extends Controller
{
    // Paleta de la marca: rojo de acento y café
    private const ROJO  = '#8b2a22';
    private const CAFE  = '#4a2c1d';
    private const CREMA = '#fbf3ea';

    private const TAMANOS = [180, 192, 512];

    public function manifest(): Response
    {
        return $this->manifiesto(
            nombre: $this->nombreNegocio(),
            corto: $this->nombreCorto(),
            inicio: '/',
            alcance: '/',
            tema: self::ROJO,
            descripcion: 'Menú y pedidos de ' . $this->nombreNegocio(),
        );
    }

    public function manifestAdmin(): Response
    {
        return $this->manifiesto(
            nombre: 'Pedidos · ' . $this->nombreNegocio(),
            corto: 'Pedidos',
            inicio: route('admin.pedidos.index', [], false),
            alcance: '/admin/',
            tema: self::CAFE,
            descripcion: 'Panel de pedidos de ' . $this->nombreNegocio(),
            id: '/admin/',
        );
    }

    /** PNG cuadrado a partir del logo. ?m=1 deja margen extra para íconos "maskable" de Android. */
    public function icono(int $size)
    {
        abort_unless(in_array($size, self::TAMANOS, true), 404);

        $maskable = request()->boolean('m');
        $logo     = $this->rutaLogo();

        // El nombre incluye la fecha del logo: si lo cambian, se genera uno nuevo
        $carpeta = storage_path('app/pwa');
        File::ensureDirectoryExists($carpeta);
        $archivo = sprintf('%s/icono-%d-%d-%s.png', $carpeta, $size, $maskable ? 1 : 0, $logo ? filemtime($logo) : 'sin');

        if (! is_file($archivo)) {
            $png = $this->dibujar($size, $maskable, $logo);
            if ($png === null) {
                abort(404);
            }
            file_put_contents($archivo, $png);
        }

        return response()->file($archivo, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    // ------------------------------------------------------------------

    private function manifiesto(string $nombre, string $corto, string $inicio, string $alcance,
                                string $tema, string $descripcion, ?string $id = null): Response
    {
        $v = $this->version();

        $datos = [
            'id'               => $id ?? '/',
            'name'             => $nombre,
            'short_name'       => $corto,
            'description'      => $descripcion,
            'start_url'        => $inicio,
            'scope'            => $alcance,
            'display'          => 'standalone',
            'lang'             => 'es-CR',
            'background_color' => self::CREMA,
            'theme_color'      => $tema,
            'icons'            => [
                ['src' => route('pwa.icono', 192) . "?v=$v",                 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => route('pwa.icono', 512) . "?v=$v",                 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => route('pwa.icono', 192) . "?m=1&v=$v",             'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
                ['src' => route('pwa.icono', 512) . "?m=1&v=$v",             'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ];

        return response(json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 200, [
            'Content-Type'  => 'application/manifest+json; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function dibujar(int $size, bool $maskable, ?string $logo): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;   // PHP sin GD: el manifest sigue funcionando, solo faltan íconos
        }

        $lienzo = imagecreatetruecolor($size, $size);
        imagealphablending($lienzo, true);
        imagefill($lienzo, 0, 0, $this->color($lienzo, self::CREMA));

        if ($logo && ($origen = $this->cargar($logo))) {
            // Zona segura: el logo ocupa ~62 % en "maskable" (Android recorta los bordes) y ~80 % en el normal
            $caja = (int) round($size * ($maskable ? 0.62 : 0.8));
            $ow = imagesx($origen);
            $oh = imagesy($origen);
            $escala = min($caja / $ow, $caja / $oh);
            $w = max(1, (int) round($ow * $escala));
            $h = max(1, (int) round($oh * $escala));

            imagecopyresampled($lienzo, $origen, intdiv($size - $w, 2), intdiv($size - $h, 2), 0, 0, $w, $h, $ow, $oh);
        } else {
            // Sin logo: círculo rojo como marca provisional
            imagefilledellipse($lienzo, intdiv($size, 2), intdiv($size, 2), (int) ($size * 0.6), (int) ($size * 0.6),
                $this->color($lienzo, self::ROJO));
        }

        ob_start();
        imagepng($lienzo);

        return ob_get_clean();
    }

    private function cargar(string $ruta)
    {
        $datos = @file_get_contents($ruta);
        $img = $datos ? @imagecreatefromstring($datos) : false;

        return $img ?: null;
    }

    private function color($img, string $hex): int
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return imagecolorallocate($img, $r, $g, $b);
    }

    private function rutaLogo(): ?string
    {
        $logo = Contacto::first()?->logo;
        $ruta = $logo ? public_path('storage/' . $logo) : null;

        return ($ruta && is_file($ruta)) ? $ruta : null;
    }

    /** Cambia cuando cambia el logo, para que el navegador pida los íconos nuevos */
    private function version(): string
    {
        $r = $this->rutaLogo();

        return $r ? (string) filemtime($r) : '0';
    }

    private function nombreNegocio(): string
    {
        return Contacto::first()?->nombre_empresa ?: config('negocio.nombre', config('app.name'));
    }

    private function nombreCorto(): string
    {
        return mb_substr($this->nombreNegocio(), 0, 12);
    }
}
