/* ============================================================
   db.js — CAPA DE DATOS 100% MySQL
   SIN localStorage. MySQL es la ÚNICA fuente de verdad.
   Escrituras van a api/api.php, lecturas desde el caché
   que se llena al arrancar desde api/bootstrap.php.
============================================================ */
window.DB = (function () {
  'use strict';
  const BOOTSTRAP_URL = (function() {
    let p = window.location.pathname;
    if (p.match(/\/[^\/]+\.[^\/]+$/)) p = p.substring(0, p.lastIndexOf('/'));
    if (p.endsWith('/')) p = p.slice(0, -1);
    if (p.endsWith('/public')) p = p.slice(0, -7);
    return window.location.origin + p + '/api/bootstrap.php';
  })();
  const API_URL = BOOTSTRAP_URL.replace('bootstrap.php', 'api.php?op=');

  const cache = {
    usuarios: [], evaluaciones: [], asistencias: [], encuestas: [], comentarios: [],
    playerStats: [], trofeosCustom: [], trofeosOcultos: [], trofeosEditados: {},
    evalGlobal: null, textos: {}, plantillas: {}, staff: null, torneo: null,
    categorias: null, jugadoresReciclados: [], pagosClub: [], comprobantes: [],
    pagosPadre: [], pagosPlataforma: [], configuraciones: {}
  };

  function csrfHeader() {
    // Prioridad: token recibido del servidor (login/me) > inyectado por el layout PHP
    return cache.csrf_token || window.CSRF_TOKEN || '';
  }

  function post(op, payload) {
    const url = (typeof API_URL !== 'undefined' ? API_URL : '../api/api.php?op=') + op;
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfHeader() },
      body: JSON.stringify(payload || {})
    }).then(r => r.json()).then(res => {
      if (res && res.error) console.error('[DB MySQL] ' + op + ': ' + res.error);
      return res;
    }).catch(e => console.error('[DB MySQL] fallo de red en ' + op, e));
  }

  // Limpiar cualquier basura de localStorage que pueda interferir
  try {
    Object.keys(localStorage)
      .filter(k => k.startsWith('nico_'))
      .forEach(k => localStorage.removeItem(k));
  } catch(e) {}

  console.log('Fetching:', BOOTSTRAP_URL); console.log('[DB MySQL] Fetching:', BOOTSTRAP_URL); const listo = fetch(BOOTSTRAP_URL + '?t=' + Date.now())
    .then(r => r.json())
    .then(data => {
      if (data && data.error) { console.error('[DB MySQL] ' + data.error); return; }
      Object.keys(cache).forEach(k => { if (data[k] !== undefined) cache[k] = data[k]; });
      console.log('[DB MySQL] Cargado. Usuarios:', cache.usuarios.length, '| Padres:', cache.usuarios.filter(u => u.rol === 'padre').length);
    })
    .catch(e => console.error('[DB MySQL] No se pudo cargar bootstrap.php', e, 'URL:', BOOTSTRAP_URL));

  function verificarSesion() {
    const url = BOOTSTRAP_URL.replace('bootstrap.php', 'me.php');
    return fetch(url).then(r => r.json()).then(data => {
      if (data.success) { if (data.csrf_token) cache.csrf_token = data.csrf_token; return data.usuario; }
      return null;
    }).catch(e => null);
  }

  function login(usuario, password) {
    const url = BOOTSTRAP_URL.replace('bootstrap.php', 'login.php');
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfHeader() },
      body: JSON.stringify({ usuario, password })
    }).then(r => r.json()).then(data => {
      if (data.success && data.usuario) {
        if (data.csrf_token) cache.csrf_token = data.csrf_token;
        return data.usuario;
      }
      return null;
    }).catch(e => null);
  }

  // Fecha local en formato ISO YYYY-MM-DD (único formato de fechas del sistema)
  function hoyISO() {
    const d = new Date();
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }

  function guardarEvaluacion(ev) {
    const t = [...cache.evaluaciones];
    const i = t.findIndex(x => x.catKey === ev.catKey && x.playerId === ev.playerId);
    ev.fecha = hoyISO();
    if (i >= 0) t[i] = ev; else t.push(ev);
    cache.evaluaciones = t;
    post('evaluaciones/guardar', ev);
  }

  function obtenerEvaluacion(catKey, playerId) {
    return cache.evaluaciones.find(x => x.catKey === catKey && x.playerId === playerId) || null;
  }

  function marcarAsistencia(reg) {
    const t = [...cache.asistencias];
    const i = t.findIndex(x => x.catKey === reg.catKey && x.fecha === reg.fecha && x.playerId === reg.playerId);
    if (i >= 0) t[i] = reg; else t.push(reg);
    cache.asistencias = t;
    post('asistencias/guardar', reg);
  }

  function quitarAsistencia(catKey, fecha, playerId) {
    cache.asistencias = cache.asistencias.filter(x => !(x.catKey === catKey && x.fecha === fecha && x.playerId === playerId));
    post('asistencias/quitar', { catKey, fecha, playerId });
  }

  function obtenerAsistencias(catKey, playerId) {
    return cache.asistencias.filter(x => x.catKey === catKey && x.playerId === playerId);
  }

  function guardarEncuesta(enc) {
    enc.fecha = hoyISO();
    cache.encuestas = [...cache.encuestas, enc];
    post('encuestas/guardar', enc);
  }

  function obtenerEncuestas() { return cache.encuestas; }

  async function registrarPadre(nombre, usuario, password, hijoCat, hijoId) {
    if (cache.usuarios.find(u => u.usuario === usuario)) return { ok: false, msg: 'El usuario ya existe' };
    const nuevo = { id: null, usuario, password, rol: 'padre', nombre, hijoCat: hijoCat || null, hijoId: hijoId || null, metodoPago: 'linea', fechaVencimiento: null, telefono: null };
    // El ID lo asigna MySQL (AUTO_INCREMENT); nunca se calcula en el cliente.
    const res = await post('usuarios/guardar', nuevo);
    if (!res || res.ok === false) return { ok: false, msg: (res && res.error) || 'No se pudo crear la cuenta' };
    // Recargar desde el servidor para tomar el ID real generado por MySQL
    try {
      const data = await fetch(BOOTSTRAP_URL + '?t=' + Date.now()).then(r => r.json());
      if (data && !data.error && Array.isArray(data.usuarios)) cache.usuarios = data.usuarios;
    } catch (e) { /* el caché se refrescará al recargar */ }
    return { ok: true, msg: 'Cuenta familiar creada y vinculada' };
  }

  // COMENTARIOS PADRES
  function obtenerComentarios() { return cache.comentarios || []; }
  async function guardarComentario(c) {
      if(!cache.comentarios) cache.comentarios = [];
      cache.comentarios.unshift(c);
      return await post('comentarios/guardar', c);
  }
  async function borrarComentario(id) {
      if(cache.comentarios) cache.comentarios = cache.comentarios.filter(x => x.id !== id);
      return await post('comentarios/borrar', { id });
  }
  async function cambiarEstadoComentario(id, est) {
      if(cache.comentarios) {
          const c = cache.comentarios.find(x => x.id === id);
          if (c) c.estado = est;
      }
      return await post('comentarios/estado', { id, estado: est });
  }

  function obtenerPadres() { return cache.usuarios.filter(u => u.rol === 'padre'); }

  function eliminarPadre(id) {
    cache.usuarios = cache.usuarios.filter(u => !(u.rol === 'padre' && u.id === id));
    post('usuarios/eliminar', { id, rol: 'padre' });
    return true;
  }

  function actualizarPadre(id, data) {
    const t = [...cache.usuarios];
    const i = t.findIndex(x => x.rol === 'padre' && x.id === id);
    if (i >= 0) { t[i] = Object.assign({}, t[i], data); cache.usuarios = t; post('usuarios/guardar', t[i]); }
    return true;
  }

  function actualizarStats(catKey, playerId, stats) {
    const t = [...cache.playerStats];
    const i = t.findIndex(x => x.catKey === catKey && x.playerId === playerId);
    const reg = { catKey, playerId, goles: stats.goles, asistencias: stats.asistencias, mvp: stats.mvp, updatedAt: new Date().toISOString() };
    if (i >= 0) t[i] = reg; else t.push(reg);
    cache.playerStats = t;
    post('stats/guardar', reg);
  }

  function obtenerStats(catKey, playerId) {
    return cache.playerStats.find(x => x.catKey === catKey && x.playerId === playerId) || null;
  }

  function obtenerTrofeosCustom() { return cache.trofeosCustom; }
  function guardarTrofeoCustom(t) { cache.trofeosCustom = [...cache.trofeosCustom, t]; post('trofeosCustom/guardar', t); }
  function eliminarTrofeoCustom(id) { cache.trofeosCustom = cache.trofeosCustom.filter(t => t.id !== id); post('trofeosCustom/eliminar', { id }); }
  function obtenerTrofeosOcultos() { return cache.trofeosOcultos; }
  function ocultarTrofeoBase(id) { if (!cache.trofeosOcultos.includes(id)) { cache.trofeosOcultos = [...cache.trofeosOcultos, id]; post('trofeosOcultos/agregar', { id }); } }
  function obtenerTrofeosEditados() { return cache.trofeosEditados || {}; }
  function guardarTrofeoEditado(t) { cache.trofeosEditados = Object.assign({}, cache.trofeosEditados, { [t.id]: t }); post('trofeosEditados/guardar', t); }
  function obtenerEvalGlobal() { return cache.evalGlobal; }
  function guardarEvalGlobal(lista) { cache.evalGlobal = lista; post('documentos/guardar', { key: 'eval_global', value: lista }); }
  function obtenerTextos() { return cache.textos || {}; }
  function guardarTexto(clave, html) { cache.textos[clave] = html; post('textos/guardar', { clave, html }); }

  // CONFIGURACIONES (tarifas oficiales, etc.) — fuente única: tabla MySQL `configuraciones`
  function obtenerConfiguraciones() { return cache.configuraciones || {}; }
  async function guardarConfiguracion(clave, valor) {
    const res = await post('configuraciones/guardarValor', { clave, valor });
    if (res && res.ok !== false) cache.configuraciones[clave] = String(valor);
    return res;
  }

  function normalizarPlantilla(catKey, lista) {
    if (!Array.isArray(lista)) return [];
    const vistos = new Map();
    lista.forEach(p => {
      const clave = String(p.dorsal);
      const prev = vistos.get(clave);
      if (!prev) { vistos.set(clave, p); return; }
      if ((p.foto && !prev.foto) || (p.id && !prev.id)) vistos.set(clave, p);
    });
    return Array.from(vistos.values()).map(p => ({ ...p, catKey: catKey, foto: p.foto || null }));
  }

  function obtenerPlantilla(catKey) {
    const lista = cache.plantillas[catKey] || null;
    return lista ? normalizarPlantilla(catKey, lista) : null;
  }

  function guardarPlantilla(catKey, lista) {
    cache.plantillas = Object.assign({}, cache.plantillas, { [catKey]: normalizarPlantilla(catKey, lista) });
    post('plantillas/guardar', { catKey, lista: cache.plantillas[catKey] });
  }

  function obtenerStaff() { return cache.staff; }
  function guardarStaff(lista) { cache.staff = lista; post('staff/guardar', { lista }); }
  function obtenerTorneo() { return cache.torneo; }
  function guardarTorneo(data) { cache.torneo = data; post('documentos/guardar', { key: 'torneo', value: data }); }
  function obtenerCategorias() { return cache.categorias; }
  function guardarCategorias(lista) { cache.categorias = lista; post('categorias/guardar', { lista }); }

  function upsertCategoria(cat) {
    const lista = [...(cache.categorias || [])];
    const i = lista.findIndex(c => c.key === cat.key || (cat.id && c.id === cat.id));
    if (i >= 0) lista[i] = Object.assign({}, lista[i], cat); else lista.push(cat);
    guardarCategorias(lista);
    return lista;
  }

  function eliminarCategoria(key) { guardarCategorias((cache.categorias || []).filter(c => c.key !== key)); return true; }

  function reordenarCategorias(ordenKeys) {
    const lista = cache.categorias || [];
    const ordenada = ordenKeys.map(k => lista.find(c => c.key === k)).filter(Boolean);
    lista.forEach(c => { if (!ordenada.some(o => o.key === c.key)) ordenada.push(c); });
    guardarCategorias(ordenada);
    return ordenada;
  }

  function guardarJugadorReciclado(player) {
    const copia = Object.assign({}, player, { guardadoEn: new Date().toISOString() });
    const l = [...cache.jugadoresReciclados];
    const i = l.findIndex(x => x.catKey === player.catKey && String(x.dorsal) === String(player.dorsal));
    if (i >= 0) l[i] = copia; else l.push(copia);
    cache.jugadoresReciclados = l;
    post('reciclados/guardar', copia);
  }

  function obtenerJugadorReciclado(catKey, dorsal) {
    return cache.jugadoresReciclados.find(x => x.catKey === catKey && String(x.dorsal) === String(dorsal)) || null;
  }

  function quitarJugadorReciclado(catKey, dorsal) {
    cache.jugadoresReciclados = cache.jugadoresReciclados.filter(x => !(x.catKey === catKey && String(x.dorsal) === String(dorsal)));
    post('reciclados/quitar', { catKey, dorsal });
  }

  async function registrarEntrenador(data) {
    if (cache.usuarios.find(u => u.usuario === data.usuario)) return { ok: false, msg: 'El usuario ya existe' };
    // El ID lo asigna MySQL (AUTO_INCREMENT); nunca se calcula en el cliente.
    const nuevo = { id: null, usuario: data.usuario, password: data.password, rol: 'entrenador', nombre: data.nombre, catKey: data.catKey || null, catLabel: data.catLabel || '' };
    const res = await post('usuarios/guardar', nuevo);
    if (!res || res.ok === false) return { ok: false, msg: (res && res.error) || 'No se pudo registrar al entrenador' };
    // Recargar desde el servidor para tomar el ID real generado por MySQL
    try {
      const fresh = await fetch(BOOTSTRAP_URL + '?t=' + Date.now()).then(r => r.json());
      if (fresh && !fresh.error && Array.isArray(fresh.usuarios)) cache.usuarios = fresh.usuarios;
    } catch (e) { /* el caché se refrescará al recargar */ }
    return { ok: true, msg: 'Entrenador registrado correctamente' };
  }

  function obtenerEntrenadores() { return cache.usuarios.filter(u => u.rol === 'entrenador'); }

  function editarEntrenador(id, data) {
    const t = [...cache.usuarios];
    const i = t.findIndex(x => x.rol === 'entrenador' && x.id === id);
    if (i >= 0) { t[i] = Object.assign({}, t[i], data); cache.usuarios = t; post('usuarios/guardar', t[i]); }
    return true;
  }

  function eliminarEntrenador(id) {
    cache.usuarios = cache.usuarios.filter(u => !(u.rol === 'entrenador' && u.id === id));
    post('usuarios/eliminar', { id, rol: 'entrenador' });
    return true;
  }

  function guardarPagoClub(pago) {
    // El ID lo genera el servidor si no viene de bootstrap
    cache.pagosClub = [...cache.pagosClub, pago];
    post('pagosClub/agregar', pago);
  }

  function obtenerPagosClub() { return cache.pagosClub; }

  function guardarComprobante(c) {
    c.id = null; c.estado = 'pendiente';
    cache.comprobantes = [...cache.comprobantes, c];
    post('comprobantes/agregar', c);
  }

  function obtenerComprobantes() { return cache.comprobantes; }

  function validarComprobante(id, aprobar) {
    const t = [...cache.comprobantes];
    const i = t.findIndex(c => c.id === id);
    if (i < 0) return false;
    const validacionData = Object.assign({}, t[i], { estado: aprobar ? 'validado' : 'rechazado' });
    t[i] = validacionData;
    cache.comprobantes = t;
    if (aprobar) {
      const pago = { id: null, padreId: t[i].padreId, padreNombre: t[i].padreNombre, concepto: t[i].concepto, monto: t[i].monto, fecha: t[i].fecha, referencia: t[i].referencia || '', metodo: 'digital', estado: 'validado' };
      cache.pagosClub = [...cache.pagosClub, pago];
      post('comprobantes/validar', { id, aprobar: true, pago });
    } else {
      post('comprobantes/validar', { id, aprobar: false });
    }
    return true;
  }

  function guardarPagoPadre(padreId, pago) {
    pago.id = null; pago.padreId = padreId; pago.estado = pago.estado || 'pendiente';
    cache.pagosPadre = [...cache.pagosPadre, pago];
    post('pagosPadre/agregar', pago);
  }

  function obtenerPagosPadre(padreId) {
    return cache.pagosPadre.filter(p => String(p.padreId) === String(padreId));
  }

  function guardarPagoPlataforma(p) {
    p.id = null; p.estado = 'pendiente';
    cache.pagosPlataforma = [...cache.pagosPlataforma, p];
    post('pagosPlataforma/agregar', p);
  }

  function obtenerPagosPlataforma() { return cache.pagosPlataforma; }

  // Forzar actualización silenciosa para invalidar caché
  function sincronizar(app) {
    return fetch(BOOTSTRAP_URL + '?t=' + Date.now())
      .then(r => r.json())
      .then(data => {
        if (!data || data.error) return;
        Object.keys(cache).forEach(k => { if (data[k] !== undefined) cache[k] = data[k]; });
        if (app) {
          app.evalGlobalVersion++; app.catAdminVersion++; app.evaluacionesVersion++;
          app.asistenciaVersion++; app.encuestaVersion++; app.adminPadresVersion++;
          app.comentariosVersion++; app.entrenadoresVersion++; app.trofeosVersion++;
        }
      }).catch(e => {});
  }

  // COMENTARIOS PADRES
  function obtenerComentarios() { return cache.comentarios || []; }
  async function guardarComentario(c) {
      if(!cache.comentarios) cache.comentarios = [];
      cache.comentarios.unshift(c);
      return await post('comentarios/guardar', c);
  }
  async function borrarComentario(id) {
      if(cache.comentarios) cache.comentarios = cache.comentarios.filter(x => x.id !== id);
      return await post('comentarios/borrar', { id });
  }
  async function cambiarEstadoComentario(id, est) {
      if(cache.comentarios) {
          const c = cache.comentarios.find(x => x.id === id);
          if (c) c.estado = est;
      }
      return await post('comentarios/estado', { id, estado: est });
  }

  return {
    sincronizar, listo, login, verificarSesion, quitarAsistencia,
    guardarEvaluacion, obtenerEvaluacion,
    marcarAsistencia, obtenerAsistencias,
    guardarEncuesta, obtenerEncuestas,
    registrarPadre, obtenerPadres, eliminarPadre, actualizarPadre,
    actualizarStats, obtenerStats,
    obtenerTrofeosCustom, guardarTrofeoCustom, eliminarTrofeoCustom,
    obtenerTrofeosOcultos, ocultarTrofeoBase,
    obtenerTrofeosEditados, guardarTrofeoEditado,
    obtenerEvalGlobal, guardarEvalGlobal,
    obtenerTextos, guardarTexto,
    obtenerConfiguraciones, guardarConfiguracion,
    normalizarPlantilla, obtenerPlantilla, guardarPlantilla,
    obtenerStaff, guardarStaff,
    obtenerTorneo, guardarTorneo,
    obtenerCategorias, guardarCategorias, upsertCategoria, eliminarCategoria, reordenarCategorias,
    guardarJugadorReciclado, obtenerJugadorReciclado, quitarJugadorReciclado,
    registrarEntrenador, obtenerEntrenadores, editarEntrenador, eliminarEntrenador,
    guardarPagoClub, obtenerPagosClub,
    guardarComprobante, obtenerComprobantes, validarComprobante,
    guardarPagoPadre, obtenerPagosPadre,
    guardarPagoPlataforma, obtenerPagosPlataforma,
    obtenerComentarios, guardarComentario, borrarComentario, cambiarEstadoComentario,
    post
  };
})();
