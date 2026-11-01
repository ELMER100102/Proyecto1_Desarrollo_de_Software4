/* =====================================================================
   panel-pedido.js · Botón del carrito y panel lateral del pedido (Mi pedido).
   Sin dependencias. Se carga con defer; no hace nada fuera de Mi pedido.

   - Datos de los productos: los data-* de las tarjetas .abrir-pedido de la página
     (sin peticiones nuevas al servidor)
   - Carrito: window.CarritoPedido (leer, agregar, fijar, quitar) con la forma de siempre
   - Se mantiene al día observando .contador-carrito, que pedido.js actualiza
     cada vez que el carrito cambia (desde aquí, la ventana, la tabla u otra pestaña)
   ===================================================================== */
(function () {
  'use strict';

  var MAX = 99;
  var raiz = document.documentElement;
  var reducido = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  // Precios como en el resto del sitio (Blade: number_format($n, 0, ',', '.')): ₡1.200
  var moneda = {
    format: function (n) { return '₡' + String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
  };

  function carrito() { return window.CarritoPedido ? window.CarritoPedido.leer() : {}; }
  function contar() { return window.CarritoPedido ? window.CarritoPedido.contar() : 0; }
  function refrescarResumen() {
    if (window.MenuCarta && window.MenuCarta.refrescarResumen) { window.MenuCarta.refrescarResumen(); }
  }

  /* ---------------- Botón del carrito: estado y "latido" ---------------- */

  var boton = document.getElementById('btnCarrito');
  var anterior = contar();

  function pintarBoton() {
    if (!boton) { return; }
    var n = contar();
    boton.classList.toggle('tiene-productos', n > 0);
    if (n > anterior && !reducido) {
      boton.classList.remove('late');
      void boton.offsetWidth;   // reinicia la animación
      boton.classList.add('late');
    }
    anterior = n;
  }

  var panel = document.getElementById('panelPedido');
  var listos = [];   // funciones que se llaman cuando cambia el carrito

  // pedido.js cambia el texto de TODOS los .contador-carrito al guardar el carrito
  if ('MutationObserver' in window) {
    var observador = new MutationObserver(function () { listos.forEach(function (f) { f(); }); });
    document.querySelectorAll('.contador-carrito').forEach(function (el) {
      observador.observe(el, { childList: true, characterData: true, subtree: true });
    });
  }
  listos.push(pintarBoton);
  pintarBoton();

  // Sin panel (o sin <dialog> nativo): el botón baja al resumen de la página
  if (!panel || typeof panel.showModal !== 'function') {
    if (boton) {
      boton.addEventListener('click', function () {
        var resumen = document.getElementById('tuPedido');
        if (resumen) { resumen.scrollIntoView({ behavior: reducido ? 'auto' : 'smooth' }); }
      });
    }
    return;
  }

  var lineas = panel.querySelector('.panel-pedido__lineas');
  var vacio = panel.querySelector('.panel-pedido__vacio');
  var algo = panel.querySelector('.panel-pedido__algo');
  var sugerencias = panel.querySelector('.panel-pedido__sugerencias');
  var pie = panel.querySelector('.panel-pedido__pie');
  var totalBoton = panel.querySelector('.panel-pedido__finalizar-total');
  var titulo = panel.querySelector('#panelPedidoTitulo');
  var origen = null;
  var cerrando = false;
  var ultimoTotal = null;

  var svgMas = '<svg class="icono-mas" viewBox="0 0 16 16" width="14" height="14" aria-hidden="true"><path d="M8 3v10M3 8h10" /></svg>';
  var svgMenos = '<svg viewBox="0 0 16 16" width="14" height="14" aria-hidden="true"><path d="M3 8h10" /></svg>';
  var svgCheck = '<svg class="icono-check" viewBox="0 0 16 16" width="14" height="14" aria-hidden="true"><path d="M2.5 8.5l3.5 3.5 7.5-8" /></svg>';

  /* ---------------- Datos de los productos (tarjetas de la página) ---------------- */

  function productos() {
    var mapa = {};
    document.querySelectorAll('.abrir-pedido').forEach(function (t) {
      var d = t.dataset;
      mapa[d.id] = {
        id: d.id,
        nombre: d.nombre,
        precio: parseFloat(d.precio) || 0,
        imagen: d.imagen || '',
        stock: d.stock === '' || d.stock === undefined ? null : parseInt(d.stock, 10),
        categoria: d.categoria
      };
    });
    return mapa;
  }
  function tope(p) { return Math.min(p.stock === null ? MAX : p.stock, MAX); }

  function el(tag, clase, texto) {
    var e = document.createElement(tag);
    if (clase) { e.className = clase; }
    if (texto !== undefined) { e.textContent = texto; }
    return e;
  }
  function miniatura(p, clase) {
    var caja = el('span', clase);
    if (p.imagen) {
      var img = el('img');
      img.src = p.imagen;
      img.alt = '';
      img.width = 64;
      img.height = 64;
      img.loading = 'lazy';
      img.decoding = 'async';
      caja.appendChild(img);
    } else {
      var ini = el('span', 'panel-inicial', (p.nombre || '?').charAt(0));
      ini.setAttribute('aria-hidden', 'true');
      caja.appendChild(ini);
    }
    return caja;
  }
  function botonPaso(html, etiqueta) {
    var b = el('button', 'modal-pedido__paso');   // mismo botón circular que la ventana
    b.type = 'button';
    b.innerHTML = html;
    b.setAttribute('aria-label', etiqueta);
    return b;
  }

  /* ---------------- Dibujar ---------------- */

  var sugerenciaPendiente = false;   // mientras se ve el check de una sugerencia, no se reemplazan

  function dibujar(conEntrada) {
    var mapa = productos();
    var c = carrito();
    var ids = Object.keys(c);
    var total = 0;

    lineas.textContent = '';

    ids.forEach(function (id, i) {
      var p = mapa[id];
      var li = el('li', 'panel-linea');
      li.dataset.id = id;
      li.style.setProperty('--i', conEntrada ? i : 0);
      if (!conEntrada) { li.style.animation = 'none'; }

      if (!p) {
        // Producto retirado o agotado: no está entre las tarjetas de la página
        li.classList.add('panel-linea--no-disponible');
        li.appendChild(miniatura({ nombre: '?', imagen: '' }, 'panel-linea__foto'));
        var texto = el('span', 'panel-linea__texto');
        texto.appendChild(el('span', 'panel-linea__nombre', 'Producto no disponible'));
        var quitar = el('button', 'panel-linea__quitar', 'Quitar');
        quitar.type = 'button';
        quitar.addEventListener('click', function () { cambiar(id, 0, li); });
        texto.appendChild(quitar);
        li.appendChild(texto);
        li.appendChild(el('span'));
        lineas.appendChild(li);
        return;
      }

      var n = c[id];
      total += p.precio * n;
      li.appendChild(miniatura(p, 'panel-linea__foto'));

      var info = el('span', 'panel-linea__texto');
      info.appendChild(el('span', 'panel-linea__nombre', p.nombre));
      info.appendChild(el('span', 'panel-linea__precio', moneda.format(p.precio * n)));

      // Nota por producto, con opción de agregarla o editarla
      if (notasDisponibles()) {
        var nota = window.CarritoPedido.nota(id);
        if (nota) { info.appendChild(el('span', 'panel-linea__nota', 'Nota: ' + nota)); }
        var editar = el('button', 'panel-linea__editar', nota ? 'Editar nota' : 'Agregar nota');
        editar.type = 'button';
        editar.setAttribute('aria-label', (nota ? 'Editar la nota de ' : 'Agregar una nota a ') + p.nombre);
        editar.addEventListener('click', function () { abrirEditor(li, id, p.nombre, editar); });
        info.appendChild(editar);
      }
      li.appendChild(info);

      var control = el('span', 'panel-linea__control');
      control.setAttribute('role', 'group');
      control.setAttribute('aria-label', 'Cantidad de ' + p.nombre);
      var menos = botonPaso(svgMenos, n === 1 ? 'Quitar ' + p.nombre + ' del pedido' : 'Quitar una unidad de ' + p.nombre);
      var numero = el('output', '', n);
      var mas = botonPaso(svgMas, 'Agregar una unidad de ' + p.nombre);
      mas.disabled = n >= tope(p);
      menos.addEventListener('click', function () { cambiar(id, n - 1, li); });
      mas.addEventListener('click', function () { if (n < tope(p)) { cambiar(id, n + 1); } });
      control.append(menos, numero, mas);
      li.appendChild(control);
      lineas.appendChild(li);
    });

    var hayAlgo = ids.length > 0;
    vacio.hidden = hayAlgo;
    pie.hidden = !hayAlgo;

    var texto = moneda.format(Math.round(total));
    panel.querySelectorAll('[data-total]').forEach(function (e) { e.textContent = texto; });
    var unidades = contar();
    panel.querySelectorAll('[data-resumen]').forEach(function (e) {
      e.textContent = unidades + (unidades === 1 ? ' producto' : ' productos') + ' · ' + texto + ' (IVA incluido)';
    });
    // Sin productos no hay nada que enviar: de vuelta al paso 1
    if (!hayAlgo && paso === 2) { irPaso(1, { sinAnimacion: true }); }
    if (ultimoTotal !== null && ultimoTotal !== texto && !reducido) {
      totalBoton.classList.remove('cambia');
      void totalBoton.offsetWidth;
      totalBoton.classList.add('cambia');
    }
    ultimoTotal = texto;

    if (!sugerenciaPendiente) { dibujarSugerencias(mapa, c); }
  }

  // Productos pedibles que NO están en el carrito; de otras categorías primero
  function elegirSugerencias(mapa, c) {
    var enCarrito = {};
    Object.keys(c).forEach(function (id) { if (mapa[id]) { enCarrito[mapa[id].categoria] = true; } });
    var libres = Object.keys(mapa).map(function (id) { return mapa[id]; })
      .filter(function (p) { return !c[p.id] && tope(p) > 0; });
    return libres.filter(function (p) { return !enCarrito[p.categoria]; })
      .concat(libres.filter(function (p) { return enCarrito[p.categoria]; }))
      .slice(0, 3);
  }

  function dibujarSugerencias(mapa, c) {
    var lista = elegirSugerencias(mapa, c);
    sugerencias.textContent = '';
    lista.forEach(function (p) {
      var li = el('li', 'panel-sugerencia');
      var foto = miniatura(p, 'panel-sugerencia__foto');
      var mas = botonPaso(svgMas + svgCheck, 'Agregar 1 ' + p.nombre);
      mas.addEventListener('click', function () {
        if (!window.CarritoPedido) { return; }
        sugerenciaPendiente = true;
        mas.classList.add('es-agregado');
        mas.disabled = true;
        window.CarritoPedido.agregar(p.id, 1);   // la línea nueva aparece enseguida
        refrescarResumen();
        // Se ve el check y luego entra otra sugerencia
        setTimeout(function () { sugerenciaPendiente = false; dibujar(false); }, reducido ? 0 : 1200);
      });
      foto.appendChild(mas);
      li.appendChild(foto);
      li.appendChild(el('span', 'panel-sugerencia__nombre', p.nombre));
      li.appendChild(el('span', 'panel-sugerencia__precio', moneda.format(p.precio)));
      sugerencias.appendChild(li);
    });
    algo.hidden = lista.length === 0;
  }

  /* ---------------- Nota por producto (editor en la línea) ---------------- */

  var editando = false;   // con el editor abierto no se redibuja (se perdería lo escrito)

  function notasDisponibles() {
    return window.CarritoPedido && typeof window.CarritoPedido.nota === 'function';
  }

  function abrirEditor(li, id, nombre, boton) {
    if (editando) { return; }
    editando = true;
    var idCampo = 'panelNota' + id;

    var caja = el('div', 'panel-linea__editor');
    var etiqueta = el('label', 'sr-only', 'Instrucciones especiales para ' + nombre);
    etiqueta.htmlFor = idCampo;
    var campo = el('textarea', 'modal-pedido__campo');   // mismo campo que la ventana de producto
    campo.id = idCampo;
    campo.rows = 2;
    campo.maxLength = 200;
    campo.setAttribute('data-validar', 'maxlen:200');
    campo.setAttribute('data-msg-maxlen', 'La nota de un producto puede tener hasta 200 caracteres.');
    campo.value = window.CarritoPedido.nota(id);
    var contador = el('span', 'modal-pedido__contador', campo.value.length + '/200');
    campo.addEventListener('input', function () { contador.textContent = campo.value.length + '/200'; });

    var acciones = el('div', 'panel-linea__acciones');
    var guardar = el('button', 'btn btn-sm', 'Guardar');
    guardar.type = 'button';
    var cancelar = el('button', 'btn btn-sm', 'Cancelar');
    cancelar.type = 'button';
    acciones.append(guardar, cancelar);
    caja.append(etiqueta, campo, contador, acciones);
    li.appendChild(caja);
    boton.hidden = true;
    campo.focus();

    function cerrarEditor(guardado) {
      editando = false;
      if (guardado) {
        window.CarritoPedido.fijarNota(id, campo.value);
        refrescarResumen();   // la tabla de abajo también muestra la nota
      }
      dibujar(false);
      var nuevo = lineas.querySelector('.panel-linea[data-id="' + id + '"] .panel-linea__editar');
      if (nuevo) { nuevo.focus(); }
    }
    guardar.addEventListener('click', function () { cerrarEditor(true); });
    cancelar.addEventListener('click', function () { cerrarEditor(false); });
    campo.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); cerrarEditor(false); }
    });
  }

  /* ---------------- Cambiar cantidades ---------------- */

  function aplicar(id, n) {
    window.CarritoPedido.fijar(id, n);   // n < 1 quita el producto
    refrescarResumen();
    dibujar(false);
  }

  function cambiar(id, n, li) {
    if (!window.CarritoPedido) { return; }
    if (n >= 1 || !li || reducido) { aplicar(id, n); return; }

    // Quitar la línea: colapso de altura y opacidad (.3s) y luego se aplica
    li.style.height = li.offsetHeight + 'px';
    li.classList.add('se-va');
    void li.offsetHeight;
    li.style.height = '0px';
    li.style.opacity = '0';
    li.style.paddingTop = '0';
    li.style.paddingBottom = '0';
    li.style.borderBottomColor = 'transparent';
    setTimeout(function () { aplicar(id, 0); }, 300);
  }

  // Cambios que vienen de afuera (ventana de producto, tabla de abajo, otra pestaña)
  listos.push(function () { if (panel.open && !editando) { dibujar(false); } });
  window.addEventListener('storage', function (e) {
    if ((e.key === 'carrito_pedido' || e.key === 'carrito_notas') && panel.open && !editando) { dibujar(false); }
  });

  /* ---------------- Abrir y cerrar ---------------- */

  function bloquearFondo(si) {
    if (si) {
      raiz.style.setProperty('--ancho-barra-scroll', (window.innerWidth - raiz.clientWidth) + 'px');
      raiz.classList.add('modal-abierto');
    } else {
      raiz.classList.remove('modal-abierto');
      raiz.style.removeProperty('--ancho-barra-scroll');
    }
  }

  // opciones.fondoInmediato: viene de la ventana de producto (el fondo ya estaba oscuro)
  // opciones.paso: 1 (por defecto) o 2
  function abrir(elementoOrigen, opciones) {
    if (panel.open) { return; }
    opciones = opciones || {};
    origen = elementoOrigen || document.activeElement;
    cerrando = false;
    editando = false;
    ultimoTotal = null;
    irPaso(opciones.paso === 2 ? 2 : 1, { sinAnimacion: true, sinFoco: true });
    dibujar(true);
    panel.classList.toggle('fondo-inmediato', !!opciones.fondoInmediato);
    bloquearFondo(true);
    panel.showModal();
    enfocarPaso();
    if (reducido) { panel.classList.add('es-abierto'); return; }
    requestAnimationFrame(function () {
      requestAnimationFrame(function () { panel.classList.add('es-abierto'); });
    });
  }

  function cerrar(enfocar, despues) {
    if (!panel.open || cerrando) { return; }
    cerrando = true;
    panel.classList.remove('es-abierto', 'fondo-inmediato');

    var fin = function () {
      if (!cerrando) { return; }
      panel.close();
      bloquearFondo(false);
      cerrando = false;
      if (enfocar !== false && origen && document.contains(origen)) { origen.focus(); }
      if (despues) { despues(); }
    };
    if (reducido) { fin(); return; }
    panel.addEventListener('transitionend', function alTerminar(e) {
      if (e.target !== panel || e.propertyName !== 'transform') { return; }
      panel.removeEventListener('transitionend', alTerminar);
      fin();
    });
    setTimeout(fin, 600);   // respaldo si no llega transitionend
  }

  panel.addEventListener('cancel', function (e) { e.preventDefault(); cerrar(); });
  panel.addEventListener('click', function (e) {
    if (e.target === panel) { cerrar(); return; }   // clic en el fondo
    if (e.target.closest('[data-cerrar]')) { cerrar(); }
  });

  function irA(id, enfocarId) {
    var destino = document.getElementById(id);
    if (!destino) { return; }
    destino.scrollIntoView({ behavior: reducido ? 'auto' : 'smooth', block: 'start' });
    var foco = enfocarId ? document.getElementById(enfocarId) : null;
    if (foco) { foco.focus({ preventScroll: true }); }
  }

  // CONTINUAR: paso 2 (sin JavaScript sería un enlace al formulario de la página)
  panel.querySelector('[data-continuar]').addEventListener('click', function (e) {
    e.preventDefault();
    irPaso(2);
  });
  panel.querySelector('[data-volver]').addEventListener('click', function () { irPaso(1); });
  // Vacío: volver a elegir productos
  panel.querySelector('[data-elegir]').addEventListener('click', function () {
    cerrar(false, function () { irA('carta'); });
  });

  /* =====================================================================
     PASOS: 1 Tu pedido · 2 Tus datos
     ===================================================================== */

  var paso = 1;
  var anuncio = panel.querySelector('#panelAnuncio');
  var NOMBRES_PASO = { 1: 'Paso 1 de 2: Tu pedido', 2: 'Paso 2 de 2: Tus datos' };

  function vistaPaso(n) { return panel.querySelector('.panel-pedido__paso[data-paso="' + n + '"]'); }

  function enfocarPaso() {
    if (paso === 2) {
      var invalido = form.querySelector('[aria-invalid="true"]');
      var primero = invalido || form.querySelector('input[name="tipo"]:checked, .campo__control:not([disabled])');
      if (primero) { primero.focus({ preventScroll: false }); return; }
    }
    titulo.focus({ preventScroll: true });
  }

  function irPaso(n, opciones) {
    opciones = opciones || {};
    if (n === 2 && Object.keys(carrito()).length === 0) { return; }
    if (n === paso && !opciones.sinAnimacion) { return; }

    var sale = vistaPaso(paso);
    var entra = vistaPaso(n);
    var haciaAdelante = n > paso;
    paso = n;

    panel.querySelectorAll('[data-indicador]').forEach(function (li) {
      if (+li.dataset.indicador === n) { li.setAttribute('aria-current', 'step'); } else { li.removeAttribute('aria-current'); }
    });
    panel.querySelectorAll('[data-pie]').forEach(function (p) { p.hidden = +p.dataset.pie !== n; });
    if (anuncio && !opciones.sinFoco) { anuncio.textContent = NOMBRES_PASO[n]; }

    var mostrar = function () {
      panel.querySelectorAll('.panel-pedido__paso').forEach(function (v) {
        v.hidden = v !== entra;
        v.classList.remove('sale-izq', 'sale-der', 'entra-izq', 'entra-der');
      });
      if (!opciones.sinAnimacion && !reducido) { entra.classList.add(haciaAdelante ? 'entra-der' : 'entra-izq'); }
      if (!opciones.sinFoco) { enfocarPaso(); }
    };

    // Deslizamiento horizontal con fundido cruzado (~.35s: sale .17s, entra .18s)
    if (opciones.sinAnimacion || reducido || sale === entra) { mostrar(); return; }
    sale.classList.add(haciaAdelante ? 'sale-izq' : 'sale-der');
    setTimeout(mostrar, 170);
  }

  /* =====================================================================
     FORMULARIO "TUS DATOS"
     El servidor (PedidoController@store) es la autoridad: aquí solo se ayuda.
     ===================================================================== */

  var form = panel.querySelector('#formPanel');
  var itemsOcultos = panel.querySelector('#pItems');
  var botonEnviar = panel.querySelector('[data-enviar]');
  var textoEnviar = panel.querySelector('[data-enviar-texto]');
  var recordar = panel.querySelector('#pRecordar');
  var CLAVE_DATOS = 'datos_cliente';
  var enviando = false;
  var TELEFONO = /^[0-9+\-\s()]{7,25}$/;   // mismo patrón que el servidor

  function tipoActual() {
    var r = form.querySelector('input[name="tipo"]:checked') || form.querySelector('input[type="hidden"][name="tipo"]');
    return r ? r.value : '';
  }

  // Campos que dependen del tipo: aparecen y desaparecen (altura y opacidad, .3s).
  // Los ocultos quedan disabled (no se envían) pero conservan lo escrito.
  function ajustarCampos(animar) {
    var tipo = tipoActual();
    form.querySelectorAll('[data-tipos]').forEach(function (grupo) {
      var ver = grupo.dataset.tipos.split(',').indexOf(tipo) !== -1;
      grupo.querySelectorAll('input, textarea').forEach(function (c) {
        c.disabled = !ver;
        if (!ver && window.Validar) { window.Validar.validarCampo(c); }   // deshabilitado: se quita su aviso
      });
      if (ver === !grupo.hidden) { return; }

      if (!animar || reducido) { grupo.hidden = !ver; return; }
      if (ver) {
        grupo.hidden = false;
        var alto = grupo.scrollHeight;
        grupo.style.height = '0px';
        grupo.style.opacity = '0';
        void grupo.offsetHeight;
        grupo.classList.add('se-mueve');
        grupo.style.height = alto + 'px';
        grupo.style.opacity = '1';
      } else {
        grupo.style.height = grupo.scrollHeight + 'px';
        void grupo.offsetHeight;
        grupo.classList.add('se-mueve');
        grupo.style.height = '0px';
        grupo.style.opacity = '0';
      }
      setTimeout(function () {
        grupo.classList.remove('se-mueve');
        grupo.style.height = '';
        grupo.style.opacity = '';
        grupo.hidden = !ver;
      }, 300);
    });
  }
  form.querySelectorAll('input[name="tipo"]').forEach(function (r) {
    r.addEventListener('change', function () {
      ajustarCampos(true);
      mostrarError('tipo', '');
      var resumen = form.querySelector('[data-validar-resumen]');
      if (resumen) { resumen.textContent = ''; }
    });
  });

  // Nota general del pedido: enlace plegable
  var desplegar = form.querySelector('.campo__desplegar');
  var cajaNota = form.querySelector('#pNotaCaja');
  desplegar.addEventListener('click', function () {
    var abrirla = desplegar.getAttribute('aria-expanded') !== 'true';
    desplegar.setAttribute('aria-expanded', abrirla ? 'true' : 'false');
    cajaNota.hidden = !abrirla;
    if (abrirla) { form.querySelector('#pNota').focus(); }
  });

  /* ---- Validación ligera (mensajes iguales a los del servidor) ----
     validaciones.js (data-validar) revisa antes y bloquea el envío; validar() queda
     como respaldo si esa librería no cargó. */

  var ERRORES = { tipo: 'pErrTipo', cliente: 'pErrCliente', telefono: 'pErrTelefono', mesa: 'pErrMesa', direccion: 'pErrDireccion' };

  function mostrarError(nombre, mensaje) {
    var caja = form.querySelector('#' + ERRORES[nombre]);
    var campo = nombre === 'tipo' ? null : form.querySelector('[name="' + nombre + '"]');
    if (caja) { caja.textContent = mensaje; caja.hidden = !mensaje; }
    if (campo) {
      if (mensaje) { campo.setAttribute('aria-invalid', 'true'); } else { campo.removeAttribute('aria-invalid'); }
    }
  }

  function valor(nombre) {
    var c = form.querySelector('[name="' + nombre + '"]');
    return c && !c.disabled ? c.value.trim() : null;   // null = el campo no aplica
  }

  function validar() {
    var errores = {};
    if (!tipoActual()) { errores.tipo = 'Elige cómo quieres recibir tu pedido.'; }
    if (!valor('cliente')) { errores.cliente = 'Escribe tu nombre.'; }
    var tel = valor('telefono');
    if (tel !== null) {
      if (!tel) { errores.telefono = 'Necesitamos un teléfono para avisarte cuando esté listo.'; }
      else if (!TELEFONO.test(tel)) { errores.telefono = 'El teléfono solo puede tener números, espacios, + y guiones (de 7 a 25 caracteres).'; }
    }
    if (valor('mesa') === '') { errores.mesa = 'Indica tu número de mesa.'; }
    if (valor('direccion') === '') { errores.direccion = 'Escribe la dirección de entrega.'; }

    Object.keys(ERRORES).forEach(function (n) { mostrarError(n, errores[n] || ''); });
    var primero = form.querySelector('[aria-invalid="true"]') || (errores.tipo ? form.querySelector('input[name="tipo"]') : null);
    if (primero) { primero.focus(); }
    return Object.keys(errores).length === 0;
  }

  // Al corregir un campo, su mensaje desaparece
  ['cliente', 'telefono', 'mesa', 'direccion'].forEach(function (n) {
    var c = form.querySelector('[name="' + n + '"]');
    if (c) { c.addEventListener('input', function () { if (c.getAttribute('aria-invalid')) { mostrarError(n, ''); } }); }
  });

  // Al salir de un campo validaciones.js lo revisa y muestra su propio aviso:
  // el anterior (del servidor o de validar()) se retira para no verlo duplicado
  form.addEventListener('focusout', function (e) {
    var n = e.target && e.target.name;
    if (window.Validar && n && n !== 'tipo' && ERRORES[n] && e.target.hasAttribute('data-validar')) { mostrarError(n, ''); }
  });

  /* ---- Recordar mis datos (solo nombre, teléfono y tipo; nunca la dirección) ---- */

  function leerDatos() {
    try { return JSON.parse(localStorage.getItem(CLAVE_DATOS) || 'null'); } catch (e) { return null; }
  }
  function guardarDatos() {
    try {
      if (recordar.checked) {
        localStorage.setItem(CLAVE_DATOS, JSON.stringify({
          cliente: (form.querySelector('[name="cliente"]').value || '').trim(),
          telefono: (form.querySelector('[name="telefono"]') ? form.querySelector('[name="telefono"]').value : '').trim(),
          tipo: tipoActual()
        }));
      } else {
        localStorage.removeItem(CLAVE_DATOS);
      }
    } catch (e) { /* sin almacenamiento: no se recuerda nada */ }
  }
  (function precargar() {
    var d = leerDatos();
    if (!d || typeof d !== 'object') { return; }
    recordar.checked = true;
    if (form.dataset.hayOld === '1') { return; }   // lo que devolvió el servidor manda
    var cliente = form.querySelector('[name="cliente"]');
    var telefono = form.querySelector('[name="telefono"]');
    if (cliente && !cliente.value && d.cliente) { cliente.value = String(d.cliente).slice(0, 80); }
    if (telefono && !telefono.value && d.telefono) { telefono.value = String(d.telefono).slice(0, 25); }
    var radio = d.tipo && form.querySelector('input[name="tipo"][value="' + String(d.tipo).replace(/[^a-z]/g, '') + '"]');
    if (radio) { radio.checked = true; }
  })();

  /* ---- Enviar ---- */

  form.addEventListener('submit', function (e) {
    if (enviando) { e.preventDefault(); return; }   // doble clic: un solo pedido

    refrescarResumen();   // pedido.js depura el carrito (productos retirados, stock)
    var c = carrito();
    var ids = Object.keys(c);
    if (!ids.length) { e.preventDefault(); irPaso(1); return; }
    if (!validar()) { e.preventDefault(); return; }

    // items[id]=cantidad y notas[id]=texto: el mismo formato que el formulario de "Mi pedido"
    var notas = notasDisponibles() ? window.CarritoPedido.notas() : {};
    itemsOcultos.textContent = '';
    ids.forEach(function (id) {
      var item = el('input');
      item.type = 'hidden';
      item.name = 'items[' + id + ']';
      item.value = c[id];
      itemsOcultos.appendChild(item);
      if (notas[id]) {
        var nota = el('input');
        nota.type = 'hidden';
        nota.name = 'notas[' + id + ']';
        nota.value = notas[id];
        itemsOcultos.appendChild(nota);
      }
    });

    guardarDatos();
    enviando = true;
    botonEnviar.disabled = true;
    textoEnviar.textContent = 'Enviando…';
  });

  // Botón "atrás" del navegador (bfcache): el botón vuelve a estar disponible
  window.addEventListener('pageshow', function () {
    enviando = false;
    botonEnviar.disabled = false;
    textoEnviar.textContent = 'Enviar pedido';
  });

  ajustarCampos(false);

  /* =====================================================================
     ARRANQUE
     ===================================================================== */

  // Con el panel listo, el formulario de la página queda oculto: es solo el respaldo sin JavaScript
  raiz.classList.add('pedido-con-panel');

  if (boton) {
    boton.addEventListener('click', function () { abrir(boton); });
  }

  // El servidor devolvió errores: se abre en el paso que corresponde
  if (panel.dataset.abrir === 'datos') {
    abrir(boton, { paso: 2 });
  } else if (panel.dataset.abrir === 'pedido') {
    abrir(boton, { paso: 1 });
  } else if (location.hash === '#tuPedido' || location.hash === '#tusDatos') {
    // Llegó desde "Ver pedido" del menú (o un enlace al formulario)
    abrir(boton, { paso: location.hash === '#tusDatos' ? 2 : 1 });
  }

  window.PanelPedido = { abrir: abrir, cerrar: cerrar, irPaso: irPaso };
})();
