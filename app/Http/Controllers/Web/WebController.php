<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\MensajeContactoMail;
use App\Models\Categoria;
use App\Models\Contacto;
use App\Models\Foto;
use App\Models\Noticia;
use App\Models\Slide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class WebController extends Controller
{
    public function inicio()
    {
        $slides = Slide::orderBy('id_slide')->get();
        $noticias = Noticia::latest('id_noticia')->take(3)->get();

        return view('web.inicio', compact('slides', 'noticias'));
    }

    public function noticias()
    {

        // $noticias = Noticia::all();
        // return view('web.noticias.index', compact('noticias'));
        $noticias = Noticia::orderByDesc('id_noticia')->paginate(6);
        return view('web.noticias.index', compact('noticias'));
    }

    public function noticia(Noticia $noticia)
    {

        return view('web.noticias.show', compact('noticia'));
    }

    public function galeria()
    {

        $fotos = Foto::all();

        return view('web.galeria', compact('fotos'));
    }

    public function contacto()
    {
        return view('web.contacto');
    }

    public function enviarContacto(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'correo' => 'required|email|max:100',
            'asunto' => 'required|string|max:150',
            'mensaje' => 'required|string|max:2000',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'Ingrese un correo válido.',
            'asunto.required' => 'El asunto es obligatorio.',
            'mensaje.required' => 'El mensaje es obligatorio.',
            'mensaje.max' => 'El mensaje no puede superar los 2000 caracteres.',
        ]);

        // es el correo guardado en el panel de Contacto.
        $destino = Contacto::first()?->correo ?? config('mail.from.address');

        Mail::to($destino)->send(new MensajeContactoMail($datos));

        return redirect()->route('contacto')
            ->with('success', 'Mensaje enviado correctamente. Gracias por escribirnos.');
    }

    public function menu()
    {
        $categorias = Categoria::with(['productos' => fn($q) => $q->where('visible', true)->orderBy('nombre')])
            ->orderBy('nombre')
            ->get()
            ->filter(fn($categoria) => $categoria->productos->isNotEmpty());

        $todos = $categorias->flatMap->productos->sortBy('nombre');

        return view('web.menu', compact('categorias', 'todos'));
    }
}
