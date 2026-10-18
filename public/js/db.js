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
    pagosPadre: [], pagosPlataforma: [], registroLinkTemp: null, configuraciones: {}
  };

  let _csrfToken = '';
  try {
    const metaCsrf = document.querySelector('meta[name="csrf-token"]');
    if (metaCsrf) _csrfToken = metaCsrf.getAttribute('content') || '';
  } catch(e) {}

  function post(op, payload) {
    const doFetch = () => {
      const url = (typeof API_URL !== 'undefined' ? API_URL : '../api/api.php?op=') + op;
      return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfToken },
        credentials: 'include',
        body: JSON.stringify(payload || {})
      }).then(r => r.json()).then(res => {
        return res;
      }).catch(() => {});
    };

    if (!_csrfToken && listo) {
      return Promise.resolve(listo).then(() => doFetch());
    }
    return doFetch();
  }

  let _idCounter = 1000;
  function siguienteId() {
    let maxId = 1000;
    try {
      if (cache.plantillas && typeof cache.plantillas === 'object') {
        Object.values(cache.plantillas).forEach(arr => {
          if (Array.isArray(arr)) {
            arr.forEach(p => {
              const idNum = Number(p.id);
              if (!isNaN(idNum) && idNum > maxId && idNum < 1000000000) {
                maxId = idNum;
              }
            });
          }
        });
      }
      if (cache.usuarios && Array.isArray(cache.usuarios)) {
        cache.usuarios.forEach(u => {
          if (Array.isArray(u.hijos)) {
            u.hijos.forEach(h => {
              const hId = Number(h.id);
              if (!isNaN(hId) && hId > maxId && hId < 1000000000) {
                maxId = hId;
              }
            });
          }
        });
      }
    } catch(e) {}
    _idCounter = Math.max(_idCounter, maxId) + 1;
    return _idCounter;
  }

  // Limpiar cualquier basura de localStorage que pueda interferir
  try {
    Object.keys(localStorage)
      .filter(k => k.startsWith('nico_'))
      .forEach(k => localStorage.removeItem(k));
  } catch(e) {}

  const listo = fetch(BOOTSTRAP_URL + '?t=' + Date.now())
    .then(r => r.json())
    .then(data => {
      if (data && data.error) { return; }
      if (data && data.csrf_token) _csrfToken = data.csrf_token;
      Object.keys(cache).forEach(k => { if (data[k] !== undefined) cache[k] = data[k]; });
      
    })
    .catch(() => {});

  function verificarSesion() {
    const url = BOOTSTRAP_URL.replace('bootstrap.php', 'me.php');
    return fetch(url + '?t=' + Date.now(), { credentials: 'include', cache: 'no-store' })
      .then(r => r.json())
      .then(data => { if (data && data.csrf_token) _csrfToken = data.csrf_token; return data && data.success ? data.usuario : null; })
      .catch(e => null);
  }

  function login(usuario, password, hp = '') {
    const url = BOOTSTRAP_URL.replace('bootstrap.php', 'login.php');
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfToken },
      body: JSON.stringify({ usuario, password, hp })
    }).then(r => r.json()).then(data => {
      if (data.success && data.usuario) {
        if (data.csrf_token) { cache.csrf_token = data.csrf_token; _csrfToken = data.csrf_token; }
        return data;
      }
      return data;
    }).catch(e => ({success: false, error: 'Error de red'}));
  }

  async function guardarEvaluacion(ev) {
    const t = [...cache.evaluaciones];
    const i = t.findIndex(x => x.catKey === ev.catKey && x.playerId === ev.playerId);
    ev.fecha = new Date().toLocaleDateString('es-PA');
    if (i >= 0) t[i] = ev; else t.push(ev);
    cache.evaluaciones = t;
    return await post('evaluaciones/guardar', ev);
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
    enc.fecha = new Date().toLocaleDateString('es-PA');
    cache.encuestas = [...cache.encuestas, enc];
    post('encuestas/guardar', enc);
  }

  function obtenerEncuestas() { return cache.encuestas; }

  function registrarPadre(nombre, usuario, password, hijoCat, hijoId, telefono = null, hijoEdad = 0, hijoNombre = '', hijoPos = 'MED', hijos = []) {
    const uClean = (usuario || nombre || '').trim();
    if (cache.usuarios.find(u => (u.usuario || '').toLowerCase() === uClean.toLowerCase())) return { ok: false, msg: 'Ya existe un padre con ese nombre/usuario' };
    const maxId = cache.usuarios.length > 0 ? Math.max(...cache.usuarios.map(u => u.id)) : 10;
    const nuevoId = maxId + 1;
    const h = new Date(); let y = h.getFullYear(), m = h.getMonth() + 1; if (h.getDate() >= 30) { m++; if (m > 12) { m = 1; y++; } } const maxD = new Date(y, m, 0).getDate(); const d30 = String(Math.min(30, maxD)).padStart(2, '0'); const venc30 = y + '-' + String(m).padStart(2, '0') + '-' + d30;
    const hijosLista = (Array.isArray(hijos) && hijos.length > 0) ? hijos : (hijoId ? [{ id: hijoId, nombre: hijoNombre, edad: parseInt(hijoEdad) || 0, catKey: hijoCat, sub: hijoCat, pos: hijoPos, dorsal: 0 }] : []);
    const nuevo = { id: nuevoId, usuario: uClean, password, rol: 'padre', nombre: nombre.trim(), hijoCat: hijoCat || null, hijoId: hijoId || null, hijoNombre: hijoNombre || '', hijoPos: hijoPos || 'MED', hijoEdad: parseInt(hijoEdad) || 0, hijos: hijosLista, metodoPago: 'linea', fechaVencimiento: venc30, telefono: telefono || null };
    cache.usuarios = [...cache.usuarios, nuevo];
    post('usuarios/guardar', nuevo);
    return { ok: true, id: nuevoId, msg: 'Cuenta familiar creada y vinculada' };
  }

  async function agregarHijoAPadre(padreId, hijoData) {
    const res = await post('padre/agregar_hijo', { padreId, hijo: hijoData });
    if (res && res.ok && res.hijo) {
      const u = cache.usuarios.find(x => x.id === padreId);
      if (u) {
        if (!Array.isArray(u.hijos)) u.hijos = [];
        u.hijos.push(res.hijo);
      }
    }
    return res;
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

  async function actualizarTelefonoPadre(id, telefono) {
    const res = await post('padres/actualizar_telefono', { id, telefono });
    if (res && res.ok) {
      const idx = cache.usuarios.findIndex(x => x.rol === 'padre' && x.id === id);
      if (idx >= 0) cache.usuarios[idx].telefono = telefono;
    }
    return res;
  }

  async function restablecerPasswordPadre(id, password) {
    return await post('padres/restablecer_password', { id, password });
  }

  async function actualizarPoliticasAdmin(id, permisos) {
    const res = await post('admin/actualizar_politicas', { id, permisos });
    if (res && res.ok) {
      const idx = cache.usuarios.findIndex(x => x.rol === 'admin' && x.id === id);
      if (idx >= 0) cache.usuarios[idx].permisos = permisos;
    }
    return res;
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
  function guardarTexto(clave, html) {
    if (!cache.textos) cache.textos = {};
    cache.textos[clave] = html;
    return post('textos/guardar', { clave, html });
  }
  async function guardarTextosLote(lote) {
    if (!cache.textos) cache.textos = {};
    Object.assign(cache.textos, lote);
    return await post('textos/guardar', { lote });
  }

  function normalizarPlantilla(catKey, lista) {
    if (!Array.isArray(lista)) return [];
    const vistos = new Map();
    lista.forEach(p => {
      if (!p) return;
      const clave = p.id ? ('id_' + p.id) : (p.name + '_' + (p.dorsal || 0));
      const prev = vistos.get(clave);
      if (!prev) { vistos.set(clave, p); return; }
      if ((p.foto && !prev.foto) || (p.dorsal && !prev.dorsal)) vistos.set(clave, p);
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

    function registrarEntrenador(data) {
    const uClean = (data.usuario || data.nombre || '').trim();
    if (cache.usuarios.find(u => (u.usuario || '').toLowerCase() === uClean.toLowerCase())) {
      return { ok: false, msg: 'Ya existe un entrenador con ese nombre/usuario' };
    }
    const maxId = cache.usuarios.length > 0 ? Math.max(...cache.usuarios.map(u => u.id)) : 10;
    const nuevoId = maxId + 1;
    const nuevo = { id: nuevoId, usuario: uClean, password: data.password, rol: 'entrenador', nombre: data.nombre.trim(), catKey: data.catKey || null, catLabel: data.catLabel || '' };
    cache.usuarios = [...cache.usuarios, nuevo];
    post('usuarios/guardar', nuevo);
    return { ok: true, msg: 'Entrenador registrado con éxito' };
  }
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



  function obtenerEntrenadores() { return cache.usuarios.filter(u => u.rol === 'entrenador'); }

  async function editarEntrenador(id, data) {
    data.id = id;
    data.rol = 'entrenador';
    const res = await post('entrenadores/editar', data);
    if (res && res.ok) {
      const t = [...cache.usuarios];
      const i = t.findIndex(x => x.rol === 'entrenador' && x.id === id);
      if (i >= 0) {
        t[i] = Object.assign({}, t[i], data);
        cache.usuarios = t;
      }
    }
    return res;
  }

  async function actualizarNombreEntrenador(id, nombre) {
    const res = await post('entrenadores/actualizar_nombre', { id, nombre });
    if (res && res.ok) {
      const idx = cache.usuarios.findIndex(x => x.rol === 'entrenador' && x.id === id);
      if (idx >= 0) {
        cache.usuarios[idx].nombre = nombre;
        cache.usuarios[idx].usuario = nombre;
      }
    }
    return res;
  }

  async function actualizarTelefonoEntrenador(id, telefono) {
    const res = await post('entrenadores/actualizar_telefono', { id, telefono });
    if (res && res.ok) {
      const idx = cache.usuarios.findIndex(x => x.rol === 'entrenador' && x.id === id);
      if (idx >= 0) cache.usuarios[idx].telefono = telefono;
    }
    return res;
  }

  async function actualizarCategoriasEntrenador(id, categorias) {
    const res = await post('entrenadores/actualizar_categorias', { id, categorias });
    if (res && res.ok) {
      const idx = cache.usuarios.findIndex(x => x.rol === 'entrenador' && x.id === id);
      if (idx >= 0) {
        cache.usuarios[idx].categorias = categorias;
        if (categorias.length > 0) cache.usuarios[idx].catKey = categorias[0];
      }
    }
    return res;
  }

  async function actualizarPasswordEntrenador(id, password) {
    return await post('entrenadores/actualizar_password', { id, password });
  }

  async function designarSuperAdmin(id) {
    const res = await post('usuarios/designar_superadmin', { id });
    if (res && res.ok) {
      const idx = cache.usuarios.findIndex(x => x.rol === 'admin' && x.id === id);
      if (idx >= 0) {
        cache.usuarios[idx].es_top = 1;
        cache.usuarios[idx].permisos = ['*'];
      }
    }
    return res;
  }

  function eliminarEntrenador(id) {
    cache.usuarios = cache.usuarios.filter(u => !(u.rol === 'entrenador' && u.id === id));
    post('usuarios/eliminar', { id, rol: 'entrenador' });
    return true;
  }

  function obtenerAdministradores() {
    if (!cache || !cache.usuarios || !Array.isArray(cache.usuarios)) return [];
    return cache.usuarios.filter(u => u && u.rol === 'admin');
  }

  async function registrarAdministrador(data) {
    if (!cache.usuarios || !Array.isArray(cache.usuarios)) cache.usuarios = [];
    const admins = cache.usuarios.filter(u => u && u.rol === 'admin');
    if (admins.length >= 3) {
      return { ok: false, error: 'Límite alcanzado: no es posible registrar más de 3 administradores.' };
    }
    const uClean = (data.usuario || data.nombre || '').trim();
    if (cache.usuarios.find(u => u && (u.usuario || '').toLowerCase() === uClean.toLowerCase())) {
      return { ok: false, error: 'Ya existe un usuario con ese nombre/usuario' };
    }
    const res = await post('usuarios/guardar', {
      nombre: (data.nombre || '').trim(),
      usuario: uClean,
      password: data.password,
      telefono: (data.telefono || '').trim(),
      rol: 'admin'
    });
    if (res && res.ok) {
      const maxId = cache.usuarios.length > 0 ? Math.max(...cache.usuarios.map(u => u.id || 0)) : 10;
      const nuevo = { id: res.id || (maxId + 1), usuario: uClean, rol: 'admin', nombre: (data.nombre || '').trim(), telefono: (data.telefono || '').trim(), catKey: 'todas', catLabel: 'Administración General' };
      cache.usuarios = [...cache.usuarios, nuevo];
    }
    return res;
  }

  async function eliminarAdministrador(id) {
    const res = await post('usuarios/eliminar', { id, rol: 'admin' });
    if (res && res.ok) {
      cache.usuarios = cache.usuarios.filter(u => !(u.rol === 'admin' && u.id === id));
    }
    return res;
  }

  async function actualizarPadreCompleto(data) {
    const res = await post('usuarios/guardar', data);
    if (res && res.ok) {
      const idx = cache.usuarios.findIndex(x => x.rol === 'padre' && x.id === data.id);
      if (idx >= 0) {
        cache.usuarios[idx] = Object.assign({}, cache.usuarios[idx], data);
      }
    }
    return res;
  }

  function deshacerPagoPadre(padreId, fechaPasada) {
    const uIdx = cache.usuarios.findIndex(x => x.rol === 'padre' && x.id == padreId);
    if (uIdx >= 0) {
      cache.usuarios[uIdx].fechaVencimiento = fechaPasada;
    }
    cache.comprobantes = cache.comprobantes.map(c => {
      if (c.padreId == padreId && c.estado === 'validado') {
        return { ...c, estado: 'rechazado' };
      }
      return c;
    });
    cache.pagosClub = cache.pagosClub.filter(p => p.padreId != padreId);
    cache.pagosPadre = cache.pagosPadre.map(p => {
      if (p.padreId == padreId && p.estado === 'validado') {
        return { ...p, estado: 'rechazado' };
      }
      return p;
    });
  }

  async function cambiarCategoriaJugador(catOrigen, catDestino, playerId) {
    const res = await post('jugadores/editar', {
      id: playerId,
      catOrigen: catOrigen,
      catDestino: catDestino
    });
    if (res && res.ok) {
      if (cache.plantillas && cache.plantillas[catOrigen]) {
        const pOrig = cache.plantillas[catOrigen];
        const idx = pOrig.findIndex(p => p.id === playerId);
        if (idx >= 0) {
          const pl = pOrig.splice(idx, 1)[0];
          pl.catKey = res.catDestino || catDestino;
          if (res.dorsal !== undefined) pl.dorsal = res.dorsal;
          if (!cache.plantillas[catDestino]) cache.plantillas[catDestino] = [];
          cache.plantillas[catDestino].push(pl);
        }
      }
    }
    return res;
  }

  async function actualizarJugador(datos) {
    const res = await post('jugadores/editar', datos);
    if (res && res.ok) {
      const catDest = res.catDestino || datos.catDestino || datos.catKey;
      const catOrig = datos.catOrigen || datos.catKey;
      if (catOrig && catDest && catOrig !== catDest && cache.plantillas && cache.plantillas[catOrig]) {
        const pOrig = cache.plantillas[catOrig];
        const idx = pOrig.findIndex(p => p.id === datos.id);
        if (idx >= 0) {
          const pl = Object.assign(pOrig.splice(idx, 1)[0], datos, { catKey: catDest, dorsal: res.dorsal });
          if (!cache.plantillas[catDest]) cache.plantillas[catDest] = [];
          cache.plantillas[catDest].push(pl);
        }
      } else if (catDest && cache.plantillas && cache.plantillas[catDest]) {
        const idx = cache.plantillas[catDest].findIndex(p => p.id === datos.id);
        if (idx >= 0) {
          cache.plantillas[catDest][idx] = Object.assign({}, cache.plantillas[catDest][idx], datos);
        }
      }
    }
    return res;
  }

  function guardarPagoClub(pago) {
    pago.id = pago.id || Date.now();
    cache.pagosClub = [...cache.pagosClub, pago];
    post('pagosClub/agregar', pago);
  }

  function obtenerPagosClub() { return cache.pagosClub; }

  function guardarComprobante(c) {
    c.id = c.id || (Date.now() + Math.floor(Math.random() * 1000)); c.estado = c.estado || 'en_revision';
    cache.comprobantes = [...cache.comprobantes, c];
    return post('comprobantes/agregar', c);
  }

  function obtenerComprobantes() { return cache.comprobantes; }

  function validarComprobante(id, aprobar) {
    const t = [...cache.comprobantes];
    const i = t.findIndex(c => String(c.id) === String(id));
    if (i < 0) return false;
    const validacionData = Object.assign({}, t[i], { estado: aprobar ? 'validado' : 'rechazado' });
    t[i] = validacionData;
    cache.comprobantes = t;
    if (aprobar) {
      const pago = { id: Date.now(), padreId: t[i].padreId, padreNombre: t[i].padreNombre, concepto: t[i].concepto, monto: t[i].monto, fecha: t[i].fecha, referencia: t[i].referencia || '', metodo: 'digital', estado: 'validado' };
      cache.pagosClub = [...cache.pagosClub, pago];
      post('comprobantes/validar', { id, aprobar: true, pago });
    } else {
      post('comprobantes/validar', { id, aprobar: false });
    }
    return true;
  }

  function guardarPagoPadre(padreId, pago) {
    pago.id = pago.id || (Date.now() + Math.floor(Math.random() * 1000)); pago.padreId = padreId; pago.estado = pago.estado || 'en_revision';
    cache.pagosPadre = [...cache.pagosPadre, pago];
    return post('pagosPadre/agregar', pago);
  }

  function obtenerPagosPadre(padreId) {
    return cache.pagosPadre.filter(p => String(p.padreId) === String(padreId));
  }

  function guardarPagoPlataforma(p) {
    p.id = Date.now(); p.estado = 'pendiente';
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
        if (data.csrf_token) _csrfToken = data.csrf_token;
        Object.keys(cache).forEach(k => { if (data[k] !== undefined) cache[k] = data[k]; });
        if (app) {
          app.evalGlobalVersion++; app.catAdminVersion++; app.evaluacionesVersion++;
          app.asistenciaVersion++; app.encuestaVersion++; app.adminPadresVersion++;
          app.comentariosVersion++; app.entrenadoresVersion++; app.trofeosVersion++;
          if (typeof app.cargarConfiguraciones === 'function') app.cargarConfiguraciones();
          if (typeof app.cargarPersistidos === 'function') app.cargarPersistidos();
        }
      }).catch(e => {});
  }

  function obtenerConfiguraciones() { return cache.configuraciones || {}; }
  async function guardarConfiguraciones(nuevas) {
    cache.configuraciones = Object.assign({}, cache.configuraciones || {}, nuevas);
    return await post('configuraciones/guardar', nuevas);
  }

  const UPLOAD_URL = BOOTSTRAP_URL.replace('bootstrap.php', 'upload_comprobante.php');
  function subirComprobante(formData) {
    return fetch(UPLOAD_URL, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': _csrfToken },
      credentials: 'include',
      body: formData
    }).then(r => r.json());
  }

  function exportarDatos(formato) {
    const f = (formato === 'sql') ? 'sql' : 'excel';
    const url = BOOTSTRAP_URL.replace('bootstrap.php', 'exportar_datos.php?formato=' + encodeURIComponent(f) + '&csrf=' + encodeURIComponent(_csrfToken));
    window.location.href = url;
  }

  function logout() {
    const url = BOOTSTRAP_URL.replace('bootstrap.php', 'logout.php');
    return fetch(url + '?t=' + Date.now(), {
      method: 'GET',
      credentials: 'include',
      cache: 'no-store'
    }).then(r => r.json()).catch(() => ({ success: true }));
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
    sincronizar, listo, login, logout, verificarSesion, subirComprobante, exportarDatos, BOOTSTRAP_URL, UPLOAD_URL, quitarAsistencia,
    guardarEvaluacion, obtenerEvaluacion,
    marcarAsistencia, obtenerAsistencias,
    guardarEncuesta, obtenerEncuestas,
    registrarPadre, obtenerPadres, eliminarPadre, actualizarPadre,
    actualizarTelefonoPadre, restablecerPasswordPadre, actualizarPoliticasAdmin,
    actualizarStats, obtenerStats,
    obtenerTrofeosCustom, guardarTrofeoCustom, eliminarTrofeoCustom,
    obtenerTrofeosOcultos, ocultarTrofeoBase,
    obtenerTrofeosEditados, guardarTrofeoEditado,
    obtenerEvalGlobal, guardarEvalGlobal,
    obtenerTextos, guardarTexto, guardarTextosLote,
    normalizarPlantilla, obtenerPlantilla, guardarPlantilla,
    obtenerStaff, guardarStaff,
    obtenerTorneo, guardarTorneo,
    obtenerCategorias, guardarCategorias, upsertCategoria, eliminarCategoria, reordenarCategorias,
    guardarJugadorReciclado, obtenerJugadorReciclado, quitarJugadorReciclado,
    registrarEntrenador, obtenerEntrenadores, editarEntrenador, eliminarEntrenador,
    actualizarNombreEntrenador, actualizarTelefonoEntrenador, actualizarCategoriasEntrenador, actualizarPasswordEntrenador,
    designarSuperAdmin,
    registrarAdministrador, obtenerAdministradores, eliminarAdministrador,
    actualizarPadreCompleto, deshacerPagoPadre,
    cambiarCategoriaJugador, actualizarJugador,
    guardarPagoClub, obtenerPagosClub,
    guardarComprobante, obtenerComprobantes, validarComprobante,
    guardarPagoPadre, obtenerPagosPadre,
    guardarPagoPlataforma, obtenerPagosPlataforma,
    obtenerConfiguraciones, guardarConfiguraciones,
    obtenerComentarios, guardarComentario, borrarComentario, cambiarEstadoComentario,
    obtenerRegistroLinkTemp: function() { return cache.registroLinkTemp || null; },
    generarRegistroLinkTemp: async function(horas) {
      const res = await post('registroLink/generar', { horas: horas || 168 });
      if (res && res.ok && res.linkInfo) cache.registroLinkTemp = res.linkInfo;
      return res;
    },
    desactivarRegistroLinkTemp: async function() {
      const res = await post('registroLink/desactivar', {});
      if (res && res.ok) cache.registroLinkTemp = res.linkInfo;
      return res;
    },
    guardarDorsalJugador: async function(catKey, playerId, dorsal) {
      return await post('jugador/dorsal', { catKey, playerId, dorsal });
    },
    siguienteId,
    agregarHijoAPadre,
    post
  };
})();

