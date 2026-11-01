/*! validaciones.js · Validaciones reutilizables para todo el proyecto (sitio y admin)
 *
 *  Sin dependencias (no necesita jQuery). Sirve para cualquier formulario:
 *  se declara en el HTML qué reglas lleva cada campo y esta librería se encarga del resto.
 *
 *  IMPORTANTE: esto mejora la experiencia (avisa antes de enviar), pero NO es seguridad.
 *  El servidor (Laravel) siempre vuelve a validar. Las dos capas deben pedir lo mismo.
 *
 *  USO RÁPIDO
 *    <form data-validar-form novalidate>
 *      <input name="mesa" type="text" inputmode="numeric"
 *             data-validar="requerido|entero|min:1|max:999" data-solo-digitos>
 *    </form>
 *
 *  REGLAS (se combinan con |)
 *    requerido          no puede ir vacío (en casillas: marcada; en radios: alguna elegida)
 *    entero             número entero (acepta signo; combínalo con min:1 para exigir positivos)
 *    decimal            número con decimales (punto o coma)
 *    min:N   max:N      valor mínimo / máximo
 *    minlen:N maxlen:N  cantidad de caracteres
 *    telefono           números, espacios, + - y paréntesis (7 a 25 caracteres, igual que el servidor)
 *    correo             correo electrónico
 *    sinhtml            no permite < ni >
 *    igual:#otroCampo   debe coincidir con otro campo (confirmar contraseña)
 *    patron             usa la expresión regular del atributo data-patron="^[A-Z]+$"
 *
 *  ATRIBUTOS OPCIONALES DEL CAMPO
 *    data-solo-digitos      el campo solo deja escribir dígitos (no entra el "-" ni letras)
 *    data-msg="..."         mensaje propio para cualquier error de ese campo
 *    data-msg-min="..."     mensaje propio solo para esa regla (data-msg-<regla>)
 *
 *  OTROS
 *    <div data-validar-resumen></div> dentro del formulario muestra "Revisa N campos."
 *    Los campos deshabilitados (disabled) o con el atributo hidden NO se validan.
 *
 *  JAVASCRIPT
 *    Validar.validarValor('-5', 'entero|min:1')   -> { ok:false, regla:'min', mensaje:'...' }
 *    Validar.validarCampo(input)  /  Validar.validarFormulario(form)
 *    Validar.agregarRegla('miRegla', function (valor, arg, ctx) { return cond ? null : 'Mensaje'; })
 */
(function (raiz, fabrica) {
  var V = fabrica();
  if (typeof module === 'object' && module.exports) {
    module.exports = V;                       // para pruebas con Node
  } else {
    raiz.Validar = V;
    if (typeof document !== 'undefined') {
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { V.iniciar(); });
      } else {
        V.iniciar();
      }
    }
  }
})(typeof self !== 'undefined' ? self : this, function () {
  'use strict';

  /* ======================= Reglas (funciones puras) =======================
     Cada regla recibe (valor, argumento, contexto) y devuelve:
       null           -> el valor es válido
       'texto'        -> el mensaje de error
     Todas menos "requerido" ignoran los valores vacíos: un campo opcional
     solo se valida si la persona escribió algo. */

  var reglas = {};

  function vacio(v) { return String(v).trim() === ''; }

  function aNumero(v) {
    return Number(String(v).trim().replace(',', '.'));
  }

  function agregarRegla(nombre, fn) {
    reglas[String(nombre).toLowerCase()] = fn;
  }

  agregarRegla('requerido', function (v) {
    return vacio(v) ? 'Este campo es obligatorio.' : null;
  });

  agregarRegla('entero', function (v) {
    if (vacio(v)) { return null; }
    return /^[-+]?\d+$/.test(String(v).trim()) ? null : 'Escribe un número entero, sin decimales ni letras.';
  });

  agregarRegla('decimal', function (v) {
    if (vacio(v)) { return null; }
    return /^[-+]?\d+([.,]\d+)?$/.test(String(v).trim()) ? null : 'Escribe un número válido.';
  });

  agregarRegla('min', function (v, arg) {
    if (vacio(v)) { return null; }
    var n = aNumero(v);
    if (isNaN(n)) { return 'Escribe un número válido.'; }
    return n >= Number(arg) ? null : 'El valor mínimo es ' + arg + '.';
  });

  agregarRegla('max', function (v, arg) {
    if (vacio(v)) { return null; }
    var n = aNumero(v);
    if (isNaN(n)) { return 'Escribe un número válido.'; }
    return n <= Number(arg) ? null : 'El valor máximo es ' + arg + '.';
  });

  agregarRegla('minlen', function (v, arg) {
    if (vacio(v)) { return null; }
    return String(v).trim().length >= Number(arg) ? null : 'Escribe al menos ' + arg + ' caracteres.';
  });

  agregarRegla('maxlen', function (v, arg) {
    if (vacio(v)) { return null; }
    return String(v).trim().length <= Number(arg) ? null : 'Máximo ' + arg + ' caracteres.';
  });

  agregarRegla('telefono', function (v) {
    if (vacio(v)) { return null; }
    return /^[0-9+\-\s()]{7,25}$/.test(String(v).trim())
      ? null
      : 'Solo números, espacios, +, - y paréntesis (de 7 a 25 caracteres).';
  });

  agregarRegla('correo', function (v) {
    if (vacio(v)) { return null; }
    return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(String(v).trim()) ? null : 'Escribe un correo válido.';
  });

  agregarRegla('sinhtml', function (v) {
    return /[<>]/.test(String(v)) ? 'No se permiten los caracteres < y >.' : null;
  });

  agregarRegla('igual', function (v, arg, ctx) {
    if (vacio(v)) { return null; }
    var otro = (ctx && ctx.campo && typeof document !== 'undefined') ? document.querySelector(arg) : null;
    if (!otro) { return null; }
    return String(v) === String(otro.value) ? null : 'Los valores no coinciden.';
  });

  agregarRegla('patron', function (v, arg, ctx) {
    if (vacio(v)) { return null; }
    var texto = (ctx && ctx.campo && ctx.campo.getAttribute('data-patron')) || arg;
    if (!texto) { return null; }
    var re;
    try { re = new RegExp(texto); } catch (e) { return null; }   // patrón mal escrito: no bloquea
    return re.test(String(v).trim()) ? null : 'El formato no es válido.';
  });

  /* ======================= Núcleo ======================= */

  function analizar(texto) {
    return String(texto || '')
      .split('|')
      .map(function (p) { return p.trim(); })
      .filter(Boolean)
      .map(function (p) {
        var i = p.indexOf(':');
        return i === -1
          ? { nombre: p.toLowerCase(), arg: undefined }
          : { nombre: p.slice(0, i).toLowerCase(), arg: p.slice(i + 1) };
      });
  }

  /** Valida un valor contra una lista de reglas. Se detiene en el primer error. */
  function validarValor(valor, texto, ctx) {
    var v = valor == null ? '' : String(valor);
    var lista = analizar(texto);
    for (var i = 0; i < lista.length; i++) {
      var fn = reglas[lista[i].nombre];
      if (!fn) {
        if (typeof console !== 'undefined') { console.warn('Validar: la regla "' + lista[i].nombre + '" no existe.'); }
        continue;
      }
      var msg = fn(v, lista[i].arg, ctx || {});
      if (msg) { return { ok: false, regla: lista[i].nombre, mensaje: msg }; }
    }
    return { ok: true, regla: null, mensaje: '' };
  }

  /* ======================= Parte del navegador ======================= */

  function valorDe(campo) {
    if (campo.type === 'checkbox') { return campo.checked ? 'on' : ''; }
    if (campo.type === 'radio') {
      var raiz = campo.form || document;
      var marcado = raiz.querySelector('input[type="radio"][name="' + campo.name + '"]:checked');
      return marcado ? marcado.value : '';
    }
    return campo.value;
  }

  function seValida(campo) {
    if (!campo || campo.disabled) { return false; }
    if (campo.type === 'hidden') { return false; }
    if (campo.closest && campo.closest('[hidden]')) { return false; }
    return true;
  }

  var contador = 0;

  function idError(campo) {
    if (!campo.id) { contador += 1; campo.id = 'validar-campo-' + contador; }
    return campo.id + '-error';
  }

  function lugarDelError(campo) {
    // En casillas y radios el mensaje va después de su grupo, si existe
    return (campo.closest && campo.closest('[data-validar-grupo]')) || campo;
  }

  function mostrarError(campo, mensaje) {
    var id = idError(campo);
    var aviso = document.getElementById(id);
    if (!aviso) {
      aviso = document.createElement('div');
      aviso.id = id;
      aviso.className = 'validar-error';
      aviso.setAttribute('role', 'alert');
      var ref = lugarDelError(campo);
      ref.parentNode.insertBefore(aviso, ref.nextSibling);
    }
    aviso.textContent = mensaje;

    campo.classList.add('is-invalid', 'validar-invalido');
    campo.setAttribute('aria-invalid', 'true');
    var describe = (campo.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);
    if (describe.indexOf(id) === -1) { describe.push(id); }
    campo.setAttribute('aria-describedby', describe.join(' '));
  }

  function limpiarError(campo) {
    var id = campo.id ? campo.id + '-error' : null;
    var aviso = id ? document.getElementById(id) : null;
    if (aviso) { aviso.parentNode.removeChild(aviso); }

    campo.classList.remove('is-invalid', 'validar-invalido');
    campo.removeAttribute('aria-invalid');
    if (id) {
      var describe = (campo.getAttribute('aria-describedby') || '').split(/\s+/).filter(function (x) { return x && x !== id; });
      if (describe.length) { campo.setAttribute('aria-describedby', describe.join(' ')); }
      else { campo.removeAttribute('aria-describedby'); }
    }
  }

  function mensajePropio(campo, regla, porDefecto) {
    return campo.getAttribute('data-msg-' + regla) || campo.getAttribute('data-msg') || porDefecto;
  }

  /** Valida un campo y muestra u oculta su mensaje. Devuelve true si es válido. */
  function validarCampo(campo) {
    if (!seValida(campo)) { limpiarError(campo); return true; }
    var texto = campo.getAttribute('data-validar');
    if (!texto) { return true; }

    var r = validarValor(valorDe(campo), texto, { campo: campo });
    if (r.ok) { limpiarError(campo); return true; }

    mostrarError(campo, mensajePropio(campo, r.regla, r.mensaje));
    return false;
  }

  /** Valida todos los campos de un formulario. Enfoca el primero inválido. */
  function validarFormulario(form) {
    var campos = form.querySelectorAll('[data-validar]');
    var malos = [];
    var vistosRadio = {};

    for (var i = 0; i < campos.length; i++) {
      var c = campos[i];
      if (c.type === 'radio') {                       // un grupo de radios se valida una sola vez
        if (vistosRadio[c.name]) { continue; }
        vistosRadio[c.name] = true;
      }
      if (!validarCampo(c)) { malos.push(c); }
    }

    var resumen = form.querySelector('[data-validar-resumen]');
    if (resumen) {
      resumen.textContent = malos.length
        ? 'Revisa ' + malos.length + (malos.length === 1 ? ' campo.' : ' campos.')
        : '';
    }

    if (malos.length) { malos[0].focus(); return false; }
    return true;
  }

  function limpiarFormulario(form) {
    var campos = form.querySelectorAll('[data-validar]');
    for (var i = 0; i < campos.length; i++) { limpiarError(campos[i]); }
    var resumen = form.querySelector('[data-validar-resumen]');
    if (resumen) { resumen.textContent = ''; }
  }

  var iniciado = false;

  /** Se llama sola al cargar. Usa delegación de eventos: también funciona con campos agregados después. */
  function iniciar() {
    if (iniciado || typeof document === 'undefined') { return; }
    iniciado = true;

    // Solo dígitos: filtra mientras se escribe o se pega (no permite "-", "e", "." ni letras)
    document.addEventListener('input', function (e) {
      var c = e.target;
      if (!c || !c.matches || !c.matches('[data-solo-digitos]')) { return; }
      var limpio = c.value.replace(/\D+/g, '');
      if (limpio !== c.value) { c.value = limpio; }
    });

    // Al salir del campo se valida; después, mientras escribe, se corrige en vivo
    document.addEventListener('focusout', function (e) {
      var c = e.target;
      if (c && c.matches && c.matches('[data-validar]')) {
        c.setAttribute('data-validar-tocado', '1');
        validarCampo(c);
      }
    });

    document.addEventListener('input', function (e) {
      var c = e.target;
      if (c && c.matches && c.matches('[data-validar]') && c.getAttribute('data-validar-tocado')) {
        validarCampo(c);
      }
    });

    document.addEventListener('change', function (e) {
      var c = e.target;
      if (c && c.matches && c.matches('[data-validar]')) {
        c.setAttribute('data-validar-tocado', '1');
        validarCampo(c);
      }
    });

    // Al enviar: si algo está mal, no se envía
    document.addEventListener('submit', function (e) {
      var f = e.target;
      if (f && f.matches && f.matches('[data-validar-form]') && !validarFormulario(f)) {
        e.preventDefault();
        e.stopImmediatePropagation();
      }
    }, true);

    document.addEventListener('reset', function (e) {
      var f = e.target;
      if (f && f.matches && f.matches('[data-validar-form]')) { limpiarFormulario(f); }
    });
  }

  return {
    reglas: reglas,
    agregarRegla: agregarRegla,
    validarValor: validarValor,
    validarCampo: validarCampo,
    validarFormulario: validarFormulario,
    limpiar: limpiarFormulario,
    iniciar: iniciar
  };
});
