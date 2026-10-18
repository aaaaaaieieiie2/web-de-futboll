/* ============================================================
app.js  LÓGICA PRINCIPAL (Alpine.js)
 MySQL + 5 FIXES:
1) Registro unificado padre + jugador (crear o elegir)
2) Fix caché zombi (valida sesión al cargar)
3) Padre va directo a Categorias con su hijo
4) Modal evaluación responsive total
5) Recordatorio pago solo rojo si vencido
============================================================ */
document.addEventListener('alpine:init', () => {

  const CORE_KEYS = ['sub6', 'sub12', 'sub16', 'submayor'];

  const catTorneo = () => ({
    faseActual: 'ninguna',
    tabla: [
      { nombre: 'Nico Sport ANS', pj: 0, pg: 0, pe: 0, pp: 0, gf: 0, gc: 0, pts: 0 },
      { nombre: 'Próximo', pj: 0, pg: 0, pe: 0, pp: 0, gf: 0, gc: 0, pts: 0 },
      { nombre: 'Próximo', pj: 0, pg: 0, pe: 0, pp: 0, gf: 0, gc: 0, pts: 0 },
      { nombre: 'Próximo', pj: 0, pg: 0, pe: 0, pp: 0, gf: 0, gc: 0, pts: 0 }
    ],
    ultimoResultado: { golesNico: 0, golesRival: 0, rival: 'Próximo', goleadores: 'Próximo', sede: 'Próximo' },
    proximoPartido: { fecha: 'Próximo', condicion: 'Próximo', rival: 'Próximo', sede: 'Próximo' }
  });

  const UI = {
    sub6:     { text: 'text-nico-orange', badgeBorder: 'border-nico-orange/30', boxBorder: 'border-nico-orange/40' },
    sub12:    { text: 'text-nico-green',  badgeBorder: 'border-nico-green/30',  boxBorder: 'border-nico-green/40' },
    sub16:    { text: 'text-amber-400',   badgeBorder: 'border-amber-400/30',   boxBorder: 'border-amber-400/40' },
    submayor: { text: 'text-nico-blue',   badgeBorder: 'border-nico-blue/30',   boxBorder: 'border-nico-blue/40' }
  };

  const PALETA_NUEVOS = [
    { text: 'text-purple-400', badgeBorder: 'border-purple-400/30', boxBorder: 'border-purple-400/40' },
    { text: 'text-pink-400',   badgeBorder: 'border-pink-400/30',   boxBorder: 'border-pink-400/40' },
    { text: 'text-cyan-400',   badgeBorder: 'border-cyan-400/30',   boxBorder: 'border-cyan-400/40' },
    { text: 'text-lime-400',   badgeBorder: 'border-lime-400/30',   boxBorder: 'border-lime-400/40' },
    { text: 'text-rose-400',   badgeBorder: 'border-rose-400/30',   boxBorder: 'border-rose-400/40' }
  ];

  window.obtenerCategoriaParaEdad = function(edad) {
    const ed = parseInt(edad, 10);
    if (isNaN(ed) || ed <= 0) return 'sub6';
    const cats = (window.DB && typeof window.DB.obtenerCategorias === 'function' ? window.DB.obtenerCategorias() : []);
    const numCats = [];
    cats.forEach(c => {
      const m = (c.key || '').match(/^sub(\d+)$/i);
      if (m) numCats.push({ key: c.key, maxEdad: parseInt(m[1], 10) });
    });
    if (numCats.length > 0) {
      numCats.sort((a, b) => a.maxEdad - b.maxEdad);
      for (const nc of numCats) {
        if (ed <= nc.maxEdad) return nc.key;
      }
      return 'submayor';
    }
    if (ed <= 6) return 'sub6';
    if (ed <= 12) return 'sub12';
    if (ed <= 16) return 'sub16';
    return 'submayor';
  };

  Alpine.data('nicoApp', () => ({

          /* ========== MEDIA HELPER ========== */
      getMediaUrl(source, fallbackPath = '') {
          if (!source) return fallbackPath;
          const s = source.toString().trim();
          if (s.startsWith('http://') || s.startsWith('https://') || s.startsWith('data:')) return s;
          if (s.startsWith('img/') || s.startsWith('uploads/')) return s;
          // Legacy support
          if (s.endsWith('.png') || s.endsWith('.jpg') || s.endsWith('.jpeg')) return fallbackPath + s;
          return fallbackPath + s; // Return as is, UI might add .png
      },
      editarImagenUrl(obj, prop, titulo = 'Editar URL de Imagen/Video') {
          if (!this.esAdmin) return;
          const url = prompt(`Ingrese la nueva URL HTTP/HTTPS para ${titulo} (O deje en blanco para usar la local):`, obj[prop] || '');
          if (url !== null) {
              obj[prop] = url.trim();
              return true;
          }
          return false;
      },
      obtenerTextoMedia(clave, defaultUrl) {
          const t = window.DB.obtenerTextos()[clave];
          return this.getMediaUrl(t || defaultUrl);
      },
      renderMediaGlobal(clave, defaultUrl, cssClass, reactTrigger = 0) {
          const url = this.obtenerTextoMedia(clave, defaultUrl);
          const canEdit = this.esAdmin && this.modoEdicion;
          const pointer = canEdit ? 'cursor-pointer ring-2 ring-dashed ring-nico-orange hover:ring-solid transition-all' : '';
          const fullClass = `${cssClass} ${pointer}`;
          const isVideo = url.match(/\.(mp4|webm|ogg)$/i);
          const isYoutube = url.includes('youtube.com') || url.includes('youtu.be');
          const dataAttrs = `data-ns-media="${clave}" data-ns-default="${defaultUrl}"`;

          if (isVideo) {
              if (canEdit) {
                  return `<div class="relative group/media inline-block w-full h-full cursor-pointer" ${dataAttrs}>
                      <video src="${url}" controls class="${fullClass}" ${dataAttrs}></video>
                      <button type="button" class="absolute top-2 right-2 bg-black/90 hover:bg-nico-orange text-white text-[11px] font-black px-3 py-1.5 rounded-xl z-30 shadow-2xl flex items-center gap-1.5 border border-white/20 transition-all cursor-pointer" ${dataAttrs}>
                          <i class="fas fa-edit text-nico-orange hover:text-white"></i> <span>Cambiar Video</span>
                      </button>
                  </div>`;
              }
              return `<video src="${url}" controls class="${fullClass}"></video>`;
          } else if (isYoutube) {
              let embedUrl = url.replace('watch?v=', 'embed/').replace('youtu.be/', 'youtube.com/embed/');
              const overlayBtn = canEdit ? `<button type="button" class="cursor-pointer absolute top-2 right-2 bg-black/90 text-white flex items-center gap-1.5 px-3 py-1.5 rounded-xl z-30 hover:bg-nico-orange border border-white/20 text-[11px] font-black shadow-2xl transition-all" ${dataAttrs} title="Editar Video URL"><i class="fas fa-edit text-nico-orange"></i> <span>Cambiar Video</span></button>` : '';
              return `<div class="relative ${fullClass}" style="padding-bottom: 56.25%; height: 0;"><iframe src="${embedUrl}" class="absolute top-0 left-0 w-full h-full rounded-lg" frameborder="0" allowfullscreen></iframe>${overlayBtn}</div>`;
          } else {
              if (canEdit) {
                  return `<div class="relative group/media inline-block w-full h-full cursor-pointer" ${dataAttrs}>
                      <img src="${url}" class="${fullClass}" alt="Media" ${dataAttrs}>
                      <button type="button" class="absolute top-2 right-2 bg-black/90 hover:bg-nico-orange text-white text-[11px] font-black px-3 py-1.5 rounded-xl z-30 shadow-2xl flex items-center gap-1.5 border border-white/20 transition-all cursor-pointer" ${dataAttrs}>
                          <i class="fas fa-camera text-nico-orange hover:text-white"></i> <span>Cambiar Imagen</span>
                      </button>
                  </div>`;
              }
              return `<img src="${url}" class="${fullClass}" alt="Media">`;
          }
      },
      guardarTextoGlobal(clave, val) {
        if (!this.esAdmin) return;
        if (window.DB && typeof window.DB.guardarTexto === 'function') {
          window.DB.guardarTexto(clave, val);
        }
        if (window.DB && typeof window.DB.obtenerTextos === 'function') {
          const t = window.DB.obtenerTextos();
          if (t) t[clave] = val;
        }
      },
      editarTextoMediaGlobal(clave, defaultUrl) { this.editarTextoMedia(clave, defaultUrl); },
      editarTextoMedia(clave, defaultUrl) {
      if (!this.esAdmin) return;
      const current = window.DB.obtenerTextos()[clave] || defaultUrl;
      this.openMediaEditModal('Editar URL', current, 'image', (u) => {
        if (u !== null && u.trim() !== '') {
            this.guardarTextoGlobal(clave, u.trim());
            this.adminPadresVersion++; this.comentariosVersion++;
        }
      });
    },

    /* ========== 01) ESTADO GENERAL Y MODALES ========== */
    loading: true, progress: 0, reloadSpamMsg: '', dbCargado: false,
    mainTab: 'historia', catTab: 'sub6', vitrinaCatTab: 'todos',
    loginModal: false, imageModal: null, staffModal: null,
    staffFormModal: false,
    staffForm: {
      id: 0,
      nombre: '',
      cargo: '',
      licencia: '',
      exp: '',
      especialidad: '',
      bio: '',
      fotoIcon: 'fa-user-tie',
      color: 'from-nico-orange to-amber-600'
    },
    videoModal: false, playerModal: null, selectedTrophy: null,
    evalVerModal: null,
    plantillasExtra: {},
    ipsBloqueadasWaf: [],
    ipManualForm: { ip: '', motivo: 'Bloqueo manual por el Administrador', horas: 24 },
    adminCredsForm: {
      usuarioNuevo: '',
      passActual: '',
      passNuevo: '',
      passConfirm: '',
      verPass: false,
      guardando: false,
      msg: '',
      msgTipo: ''
    },

    /* ========== 02) SESIÓN Y ROLES ========== */
    currentUser: null, loginUsuario: '', loginPassword: '', loginHpTrap: '', loginError: '', esBloqueado: false,
    evalModal: null, evaluacionesVersion: 0,
    evalDatos: { tecnicos: [0,0,0,0,0,0,0,0,0], tacticos: [0,0,0,0,0,0], fisicos: [0,0,0,0,0], actitudinales: [0,0,0,0,0,0] },
    evalItems: {
      tecnicos: ['Control de balón', 'Pase corto', 'Pase largo', 'Conducción', 'Regate (1 vs 1)', 'Recepción y primer toque', 'Remate a portería', 'Cabeceo', 'Definición'],
      tacticos: ['Posicionamiento', 'Marcación', 'Desmarque', 'Toma de decisiones', 'Visión de juego', 'Trabajo en equipo'],
      fisicos: ['Velocidad', 'Resistencia', 'Coordinación', 'Equilibrio', 'Agilidad'],
      actitudinales: ['Disciplina', 'Puntualidad', 'Esfuerzo', 'Respeto', 'Compromiso', 'Liderazgo']
    },
    get gruposEval() {
      return [
        ['tecnicos', 'Aspectos Técnicos', 'fa-futbol', 'text-nico-orange'],
        ['tacticos', 'Aspectos Tácticos', 'fa-chess', 'text-nico-green'],
        ['fisicos', 'Aspectos Físicos', 'fa-bolt', 'text-nico-blue'],
        ['actitudinales', 'Aspectos Actitudinales', 'fa-heart', 'text-amber-400']
      ];
    },
    get esAdmin() { return !!this.currentUser && this.currentUser.rol === 'admin'; },
    get esPadre() { return !!this.currentUser && this.currentUser.rol === 'padre'; },
    get esEntrenador() { return !!this.currentUser && this.currentUser.rol === 'entrenador'; },
    get padreIdActual() { return this.currentUser ? (this.currentUser.padreId || this.currentUser.id) : null; },
    get esSuperAdmin() {
      if (!this.esAdmin) return false;
      const u = this.currentUser;
      return !!u && (Number(u.es_top) === 1 || Number(u.id) === 1 || (u.usuario || '').toLowerCase() === 'admin');
    },
    tienePermiso(modulo) {
      if (!this.esAdmin) return false;
      if (this.esSuperAdmin) return true;
      const perms = this.currentUser ? (this.currentUser.permisos || []) : [];
      if (!Array.isArray(perms)) return false;
      if (perms.includes('*')) return true;
      return perms.includes(modulo);
    },

    // Modales desacoplados de Familias
    modalTelefonoPadre: false,
    padreTelefonoForm: { id: null, nombre: '', usuario: '', telefono: '' },
    msgTelefonoPadre: '',

    modalPasswordPadre: false,
    padrePasswordForm: { id: null, nombre: '', usuario: '', password: '', mostrar: false },
    msgPasswordPadre: '',

    // Modal de Edición de Edad del Jugador (Categorías)
    modalEditarEdad: false,
    jugadorEdadForm: { id: null, name: '', edad: 10, dorsal: 0, catKey: '', catDestino: '' },
    msgEdadJugador: '',

    // Modal de Edición de Entrenador (Multi-Sub)
    modalEditarEntrenador: false,
    entrenadorEditando: { id: null, nombre: '', usuario: '', telefono: '', catKey: '', categorias: [], nuevaPassword: '', mostrarPass: false },
    msgEditarEntrenador: '',

    // Modal de Políticas RBAC
    modalPoliticasAdmin: false,
    adminEditandoPoliticas: { id: null, nombre: '', usuario: '', permisos: [] },
    msgPoliticasAdmin: '',

    puedeEditarCategoria(catKey) {
      if (this.esAdmin) return true;
      if (this.esEntrenador && this.currentUser) {
        if (this.currentUser.catKey === catKey) return true;
        const cats = Array.isArray(this.currentUser.categorias) ? this.currentUser.categorias : [];
        if (cats.includes(catKey)) return true;
      }
      return false;
    },

    navTabs: [
      { id:'historia',    icon:'fa-shield-halved',  label:'Escudo & Historia',  short:'Historia',   active:'text-nico-orange', role:'all'   },
      { id:'categorias',  icon:'fa-users',          label:'Categorías',         short:'Categorías', active:'text-nico-green',  role:'all'   },
      { id:'staff',       icon:'fa-user-ninja',     label:'Cuerpo Técnico',     short:'Staff',      active:'text-nico-blue',   role:'all'   },
      { id:'torneo',      icon:'fa-list-ol',        label:'Torneo & Tabla',     short:'Torneo',     active:'text-amber-400',   role:'all'   },
      { id:'vitrina',     icon:'fa-trophy',         label:'Vitrina Campeonato', short:'Vitrina',    active:'text-amber-400',   role:'all'   },
      { id:'asistencia',  icon:'fa-clipboard-list', label:'Asistencia',         short:'Asistencia', active:'text-nico-green',  role:'coach' },
      { id:'mensajes',    icon:'fa-inbox',          label:'Mensajes',           short:'Mensajes',   active:'text-nico-orange', role:'admin' },
      { id:'pagosClub',   icon:'fa-money-bill-wave',label:'Pagos Club',         short:'Pagos',      active:'text-nico-green',  role:'admin' },
      { id:'entrenadores',icon:'fa-user-tie',       label:'Entrenadores',       short:'DTs',        active:'text-nico-blue',   role:'admin' },
      { id:'buzon',      icon:'fa-envelope',        label:'Buzón',            short:'Buzón',      active:'text-nico-orange', role:'padre' },
      { id:'portal',      icon:'fa-wallet',         label:'Mi Cuenta',          short:'Mi Cuenta',  active:'text-nico-green',  role:'padre' },
      { id:'admin',       icon:'fa-user-gear',      label:'Panel Admin',        short:'Admin',      active:'text-nico-orange', role:'admin' }
    ],

    scrollNavLeft() {
      const el = document.getElementById('mobNavScroll');
      if (el) el.scrollBy({ left: -220, behavior: 'smooth' });
    },
    scrollNavRight() {
      const el = document.getElementById('mobNavScroll');
      if (el) el.scrollBy({ left: 220, behavior: 'smooth' });
    },

    tabVisible(t) {
      if (!t || !t.role) return true;
      switch (t.role) {
        case 'all': return true;
        case 'admin':
          if (!this.esAdmin) return false;
          if (this.esSuperAdmin) return true;
          if (t.id === 'mensajes') return this.tienePermiso('mensajes');
          if (t.id === 'pagosClub') return this.tienePermiso('pagos');
          if (t.id === 'entrenadores') return this.tienePermiso('entrenadores');
          if (t.id === 'admin') return this.tienePermiso('admin') || this.tienePermiso('padres') || this.tienePermiso('categorias');
          return true;
        case 'padre': return this.esPadre;
        case 'coach': return this.esAdmin || this.esEntrenador;
        case 'entrenador': return this.esEntrenador;
        default: return false;
      }
    },

        /* ========== 03) MODO EDICIÓN (LAPICITO) ========== */
    modoEdicion: false, editMediaModal: { open: false, title: '', url: '', type: 'image', callback: null },
    habilitarEdicionEnDOM() {
      if (!this.esAdmin || !this.modoEdicion) return;
      document.querySelectorAll('[data-edit]').forEach(el => {
        el.setAttribute('contenteditable', 'true');
        el.style.outline = '2px dashed #FF5500';
        el.style.outlineOffset = '2px';
        el.style.cursor = 'text';
        // En cada tecla escrita, actualizar el caché en memoria para que jamás se pierda
        el.oninput = () => {
          const clave = el.getAttribute('data-edit');
          if (clave && window.DB && typeof window.DB.obtenerTextos === 'function') {
            const txt = window.DB.obtenerTextos();
            if (txt) txt[clave] = el.innerHTML;
          }
        };
        el.onblur = () => {
          const clave = el.getAttribute('data-edit');
          if (clave && window.DB && typeof window.DB.obtenerTextos === 'function') {
            const txt = window.DB.obtenerTextos();
            if (txt) txt[clave] = el.innerHTML;
          }
        };
      });
    },
    deshabilitarEdicionEnDOM() {
      document.querySelectorAll('[data-edit]').forEach(el => {
        el.removeAttribute('contenteditable');
        el.style.outline = '';
        el.style.outlineOffset = '';
        el.style.cursor = '';
        el.oninput = null;
        el.onblur = null;
      });
    },
    async toggleModoEdicion() {
      if (!this.esAdmin) return;
      if (this.modoEdicion) {
        // Al desactivar el lapicito, guardar todos los cambios pendientes en base de datos
        await this.guardarCambiosCMS(true);
        this.modoEdicion = false;
        this.deshabilitarEdicionEnDOM();
        this.adminPadresVersion++;
      } else {
        this.modoEdicion = true;
        this.adminPadresVersion++;
        this.$nextTick(() => {
          this.habilitarEdicionEnDOM();
        });
      }
    },
    async guardarCambiosCMS(mostrarAviso = true) {
      if (!this.esAdmin) return;
      const lote = {};
      let count = 0;
      document.querySelectorAll('[data-edit]').forEach(el => {
        const clave = el.getAttribute('data-edit');
        if (clave) {
          let val = (el.innerHTML || '')
            .replace(/<span[^>]*>/gi, '')
            .replace(/<\/span>/gi, '')
            .replace(/style="[^"]*"/gi, '')
            .replace(/&amp;amp;/gi, '&amp;')
            .replace(/âœ…/g, '✅')
            .trim();
          lote[clave] = val;
          if (window.DB && typeof window.DB.obtenerTextos === 'function') {
            const txt = window.DB.obtenerTextos();
            if (txt) txt[clave] = val;
          }
          count++;
        }
      });
      let exito = true;
      if (count > 0 && typeof window.DB.guardarTextosLote === 'function') {
        const res = await window.DB.guardarTextosLote(lote);
        if (res && res.error) exito = false;
      }
      if (this.categoriasTorneo) {
        this.categoriasTorneo.forEach(c => {
          if (lote[c.key + '-desc'] !== undefined) c.desc = lote[c.key + '-desc'];
          if (lote[c.key + '-badge'] !== undefined) c.badge = lote[c.key + '-badge'];
          if (lote[c.key + '-torneos'] !== undefined) c.torneos = lote[c.key + '-torneos'];
          if (lote[c.key + '-horario'] !== undefined) c.horario = lote[c.key + '-horario'];
          if (lote[c.key + '-cancha'] !== undefined) c.cancha = lote[c.key + '-cancha'];
          if (lote[c.key + '-count'] !== undefined) c.count = lote[c.key + '-count'];
          if (lote[c.key + '-entrenador'] !== undefined) c.entrenador = lote[c.key + '-entrenador'];
        });
        window.DB.guardarTorneo(this.categoriasTorneo);
      }
      if (this.staffList) {
        window.DB.guardarStaff(this.staffList);
      }
      if (mostrarAviso) {
        if (exito) {
          this.mostrarToastCms('¡Éxito! Textos y cambios guardados en base de datos (' + count + ' elementos)');
        } else {
          this.mostrarToastCms('Error al guardar en el servidor. Verifica tu conexión.');
        }
      }
    },
    exportarDatos(formato) {
      if (!this.esAdmin) return;
      if (window.DB && typeof window.DB.exportarDatos === 'function') {
        window.DB.exportarDatos(formato);
      }
    },
    mostrarToastCms(mensaje) {
      let toast = document.getElementById('cmsToast');
      if (!toast) {
        toast = document.createElement('div');
        toast.id = 'cmsToast';
        toast.style.position = 'fixed';
        toast.style.bottom = '24px';
        toast.style.right = '24px';
        toast.style.zIndex = '9999';
        toast.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
        toast.style.color = '#000';
        toast.style.fontWeight = '900';
        toast.style.fontSize = '13px';
        toast.style.padding = '12px 20px';
        toast.style.borderRadius = '14px';
        toast.style.boxShadow = '0 10px 30px rgba(0,0,0,0.5), 0 0 20px rgba(16,185,129,0.5)';
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';
        toast.style.gap = '10px';
        toast.style.transition = 'all 0.3s cubic-bezier(0.16, 1, 0.3, 1)';
        toast.style.transform = 'translateY(100px)';
        toast.style.opacity = '0';
        document.body.appendChild(toast);
      }
      toast.innerHTML = '<i class="fas fa-circle-check text-base"></i> ' + this.limpiarHtmlSeguro(mensaje);
      requestAnimationFrame(() => {
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';
      });
      clearTimeout(this._toastTimer);
      this._toastTimer = setTimeout(() => {
        toast.style.transform = 'translateY(100px)';
        toast.style.opacity = '0';
      }, 3500);
    },
    limpiarHtmlSeguro(html) {
      if (typeof html !== 'string') return '';
      if (!/<[a-z][\s\S]*>/i.test(html)) return html;
      let limpio = html.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
                       .replace(/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi, '')
                       .replace(/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/gi, '')
                       .replace(/<embed\b[^<]*(?:(?!<\/embed>)<[^<]*)*<\/embed>/gi, '');
      limpio = limpio.replace(/\son\w+\s*=\s*(['"]).*?\1/gi, '')
                     .replace(/\son\w+\s*=\s*[^\s>]+/gi, '')
                     .replace(/javascript\s*:/gi, '');
      return limpio;
    },
    aplicarTextos() {
      if (this.modoEdicion) return; // NUNCA sobreescribir el DOM si el administrador está editando
      const t = window.DB.obtenerTextos();
      if (!t || typeof t !== 'object') return;
      document.querySelectorAll('[data-edit]').forEach(el => {
        const k = el.getAttribute('data-edit');
        if (k && t[k] !== undefined && t[k] !== null && t[k] !== '') {
          el.innerHTML = this.limpiarHtmlSeguro(t[k]);
        }
      });
    },

    /* ========== 04) DESHACER / REHACER ========== */
    historial: [], pilaRehacer: [],
    registrarAccion(a) { this.historial.push(a); this.pilaRehacer = []; },
    buscarJugador(catKey, id) { return this.playersDe(catKey).find(x => x.id === id); },
    aplicarStat(catKey, playerId, campo, delta) {
      const s = this.getStats({ catKey, id: playerId });
      const nuevo = Math.max(0, (s[campo] || 0) + delta);
      window.DB.actualizarStats(catKey, playerId, {
        goles: campo === 'goles' ? nuevo : s.goles,
        asistencias: campo === 'asistencias' ? nuevo : s.asistencias,
        mvp: campo === 'mvp' ? nuevo : s.mvp
      });
      this.evaluacionesVersion++; this.asistenciaVersion++;
    },
    deshacer() {
      const a = this.historial.pop();
      if (!a) { alert('Nada que deshacer'); return; }
      if (a.tipo === 'stat') this.aplicarStat(a.catKey, a.playerId, a.campo, -a.delta);
      if (a.tipo === 'rename') { const p = this.buscarJugador(a.catKey, a.playerId); if (p) { p.name = a.antes; this.persistPlantilla(a.catKey); } }
      if (a.tipo === 'dorsal') { const p = this.buscarJugador(a.catKey, a.playerId); if (p) { p.dorsal = a.antes; this.persistPlantilla(a.catKey); } }
      if (a.tipo === 'add') { const arr = this.playersDe(a.catKey); const i = arr.findIndex(x => x.id === a.playerId); if (i >= 0) arr.splice(i, 1); this.persistPlantilla(a.catKey); }
      if (a.tipo === 'del') { const arr = this.playersDe(a.catKey); arr.splice(a.index, 0, a.player); this.persistPlantilla(a.catKey); }
      this.pilaRehacer.push(a);
    },
    rehacer() {
      const a = this.pilaRehacer.pop();
      if (!a) { alert('Nada que rehacer'); return; }
      if (a.tipo === 'stat') this.aplicarStat(a.catKey, a.playerId, a.campo, a.delta);
      if (a.tipo === 'rename') { const p = this.buscarJugador(a.catKey, a.playerId); if (p) { p.name = a.despues; this.persistPlantilla(a.catKey); } }
      if (a.tipo === 'dorsal') { const p = this.buscarJugador(a.catKey, a.playerId); if (p) { p.dorsal = a.despues; this.persistPlantilla(a.catKey); } }
      if (a.tipo === 'add') { this.playersDe(a.catKey).push(a.player); this.persistPlantilla(a.catKey); }
      if (a.tipo === 'del') { const arr = this.playersDe(a.catKey); const i = arr.findIndex(x => x.id === a.player.id); if (i >= 0) arr.splice(i, 1); this.persistPlantilla(a.catKey); }
      this.historial.push(a);
    },

    /* ========== 05) STATS Y BRILLO SS ========== */
    getPlayerScore(player) { const s = this.getStats(player); return (s.goles * 3) + (s.asistencias * 2) + (s.mvp * 5); },
    getStats(player) {
      void this.evaluacionesVersion; void this.asistenciaVersion;
      const d = window.DB.obtenerStats(player.catKey, player.id);
      return { goles: d ? d.goles : player.goles, asistencias: d ? d.asistencias : player.asistencias, mvp: d ? d.mvp : player.mvp };
    },
    nivelJugador(player) {
      const n = this.notaJugador(player);
      if (n !== null) return n >= 4.5 ? 5 : n >= 3.5 ? 4 : n >= 2.5 ? 3 : n >= 1.5 ? 2 : 1;
      const s = this.getPlayerScore(player);
      return s >= 30 ? 5 : s >= 20 ? 4 : s >= 10 ? 3 : s >= 5 ? 2 : 1;
    },
    gradientesNivel: {
      5: 'from-cyan-400 via-blue-500 to-indigo-600 shadow-[0_0_20px_rgba(6,182,212,0.6)]',
      4: 'from-yellow-300 via-amber-500 to-amber-700 shadow-[0_0_15px_rgba(245,158,11,0.5)]',
      3: 'from-gray-200 via-slate-400 to-gray-600 shadow-[0_0_10px_rgba(203,213,225,0.3)]',
      2: 'from-amber-700 via-amber-800 to-stone-900',
      1: 'from-amber-700 via-amber-800 to-stone-900'
    },
    getSSClass(p) { return 'ss-glow-' + this.nivelJugador(p); },
    getSSBadgeClass(p) { return 'ss-badge-' + this.nivelJugador(p); },
    getPlayerGradient(p) { return this.gradientesNivel[this.nivelJugador(p)]; },

    /* ========== 06) CRUD JUGADORES +  PAPELERA ========== */
    dorsalLibre(catKey) {
      const usados = this.playersDe(catKey).map(p => p.dorsal);
      let n = 1; while (usados.includes(n)) n++;
      return n;
    },
    crearOReciclarJugador(catKey, nombre, dorsal, pos, edad = 0) {
      const reciclado = window.DB.obtenerJugadorReciclado(catKey, dorsal);
      if (reciclado) {
        reciclado.name = (nombre && nombre.trim()) || reciclado.name;
        reciclado.edad = parseInt(edad) || reciclado.edad || 0;
        if (pos) reciclado.pos = pos;
        reciclado.catKey = catKey;
        window.DB.quitarJugadorReciclado(catKey, dorsal);
        return reciclado;
      }
      const nuevoId = (window.DB && typeof window.DB.siguienteId === 'function')
        ? window.DB.siguienteId()
        : (Date.now() + Math.floor(Math.random() * 90000 + 10000));
      return { id: nuevoId, catKey, name: nombre.trim(), edad: parseInt(edad) || 0, dorsal, pos, goles: 0, asistencias: 0, mvp: 0 };
    },
    renombrarJugador(player) {
      if (!this.esAdmin) return;
      const v = prompt('Nuevo nombre del jugador:', player.name);
      if (v && v.trim() && v.trim() !== player.name) {
        const antes = player.name; player.name = v.trim();
        this.persistPlantilla(player.catKey);
        this.registrarAccion({ tipo: 'rename', catKey: player.catKey, playerId: player.id, antes, despues: player.name });
      }
    },
    async editarDorsal(player) {
      if (!player || !this.puedeEditarCategoria(player.catKey)) return;
      const actual = player.dorsal > 0 ? player.dorsal : '';
      const v = prompt('Asignar o cambiar dorsal (#) para ' + player.name + ' (Escribe 0 para dejar Sin Número):', actual);
      if (v === null) return;
      const nuevo = parseInt(v, 10);
      if (isNaN(nuevo) || nuevo < 0 || nuevo > 99) {
        alert(' Ingresa un número de dorsal válido entre 0 y 99.');
        return;
      }
      const arr = this.playersDe(player.catKey);
      if (nuevo > 0 && arr.some(p => p.id !== player.id && p.dorsal === nuevo)) {
        alert(' El dorsal #' + nuevo + ' ya lo usa otro jugador en esta categoría.');
        return;
      }
      const res = await window.DB.guardarDorsalJugador(player.catKey, player.id, nuevo);
      if (res && res.ok === false && res.error) {
        alert(' ' + res.error);
        return;
      }
      const antes = player.dorsal;
      player.dorsal = nuevo;
      arr.sort((a, b) => (a.dorsal === 0 ? 999 : a.dorsal) - (b.dorsal === 0 ? 999 : b.dorsal));
      this.registrarAccion({ tipo: 'dorsal', catKey: player.catKey, playerId: player.id, antes, despues: nuevo });
    },
    abrirModalEdad(player) {
      if (!player) return;
      if (!this.puedeEditarCategoria(player.catKey)) {
        alert('⚠️ No tienes permisos para modificar este jugador.');
        return;
      }
      const ed = parseInt(player.edad, 10) || 10;
      this.jugadorEdadForm = {
        id: player.id,
        name: player.name,
        dorsal: player.dorsal || 0,
        pos: player.pos || 'MED',
        edad: ed,
        catKey: player.catKey,
        catDestino: this.obtenerCategoriaParaEdad(ed)
      };
      this.msgEdadJugador = '';
      this.modalEditarEdad = true;
    },
    async guardarEdadJugador() {
      if (!this.jugadorEdadForm.id) return;
      this.msgEdadJugador = '';
      const nuevaEdad = parseInt(this.jugadorEdadForm.edad, 10);
      if (isNaN(nuevaEdad) || nuevaEdad < 3 || nuevaEdad > 50) {
        this.msgEdadJugador = '⚠️ Ingresa una edad válida entre 3 y 50 años.';
        return;
      }
      const catActual = this.jugadorEdadForm.catKey;
      const catDestino = this.obtenerCategoriaParaEdad(nuevaEdad);

      const res = await window.DB.actualizarJugador({
        id: this.jugadorEdadForm.id,
        name: this.jugadorEdadForm.name,
        edad: nuevaEdad,
        dorsal: this.jugadorEdadForm.dorsal || 0,
        pos: this.jugadorEdadForm.pos || 'MED',
        catOrigen: catActual,
        catDestino: catDestino
      });

      if (res && res.ok) {
        this.msgEdadJugador = '✓ Edad actualizada a ' + nuevaEdad + ' años' + (catActual !== catDestino ? ' y transferido a ' + catDestino.toUpperCase() : '') + '.';
        if (this.playerModal && this.playerModal.id === this.jugadorEdadForm.id) {
          this.playerModal.edad = nuevaEdad;
          this.playerModal.catKey = catDestino;
        }
        if (catActual !== catDestino) {
          this.catTab = catDestino;
        }
        if (window.DB.sincronizar) await window.DB.sincronizar(this);
        this.adminPadresVersion++;
        setTimeout(() => { this.modalEditarEdad = false; }, 1200);
      } else {
        this.msgEdadJugador = '❌ ' + ((res && res.error) || 'Error al actualizar edad.');
      }
    },
    async editarEdadJugadorDirecto(player) {
      this.abrirModalEdad(player);
    },
    eliminarJugador(player) {
      if (!this.esAdmin) return;
      if (!confirm('¿Eliminar a ' + player.name + ' de la plantilla?')) return;
      window.DB.guardarJugadorReciclado(player);
      const arr = this.playersDe(player.catKey);
      const index = arr.indexOf(player);
      if (index >= 0) arr.splice(index, 1);
      this.persistPlantilla(player.catKey);
      this.registrarAccion({ tipo: 'del', catKey: player.catKey, playerId: player.id, player, index });
    },
    persistPlantilla(catKey) {
      const arr = this.playersDe(catKey);
      arr.sort((a, b) => a.dorsal - b.dorsal);
      window.DB.guardarPlantilla(catKey, arr);
    },
    cambiarStat(player, campo, delta) {
      if (!this.puedeEditarCategoria(player.catKey)) return;
      this.aplicarStat(player.catKey, player.id, campo, delta);
      this.registrarAccion({ tipo: 'stat', catKey: player.catKey, playerId: player.id, campo, delta });
    },

    /* ========== 07) CRUD TORNEO & TABLA ========== */
    editarNum(obj, campo) {
      if (!this.modoEdicion || !this.esAdmin) return;
      const v = prompt('Nuevo valor (' + campo.toUpperCase() + '):', obj[campo]);
      if (v !== null) { obj[campo] = parseInt(v) || 0; this.persistTorneo(); }
    },
    editarTextoObj(obj, campo, label) {
      if (!this.modoEdicion || !this.esAdmin) return;
      const v = prompt(label, obj[campo]);
      if (v !== null) { obj[campo] = v; this.persistTorneo(); }
    },
    editarNombreEquipo(team) { this.editarTextoObj(team, 'nombre', 'Nombre del equipo:'); },
    agregarEquipo(cat) { if (!this.esAdmin) return; cat.tabla.push({ nombre: 'Nuevo Equipo', pj: 0, pg: 0, pe: 0, pp: 0, gf: 0, gc: 0, pts: 0 }); this.persistTorneo(); },
    eliminarEquipo(cat, team) {
      if (!this.esAdmin) return;
      if (!confirm('¿Eliminar el equipo ' + team.nombre + '?')) return;
      cat.tabla = cat.tabla.filter(t => t !== team); this.persistTorneo();
    },
    persistTorneo() { window.DB.guardarTorneo(this.categoriasTorneo); },

    /* ========== 08) CRUD CUERPO TÉCNICO ========== */
    staffList: [],
    editarStaff(s) {
      if (!this.esAdmin) return;
      this.staffForm = {
        id: s.id,
        nombre: s.nombre || '',
        cargo: s.cargo || '',
        licencia: s.licencia || '',
        exp: s.exp || '',
        especialidad: s.especialidad || '',
        bio: s.bio || '',
        fotoIcon: s.fotoIcon || 'fa-user-tie',
        color: s.color || 'from-nico-orange to-amber-600'
      };
      this.staffFormModal = true;
    },
    eliminarStaff(s) {
      if (!this.esAdmin) return;
      if (!confirm('¿Eliminar a ' + s.nombre + ' del cuerpo técnico?')) return;
      this.staffList = this.staffList.filter(x => x.id !== s.id);
      this.persistStaff();
    },
    agregarStaff() {
      if (!this.esAdmin) return;
      this.staffForm = {
        id: 0,
        nombre: '',
        cargo: 'Entrenador',
        licencia: 'Licencia FEPAFUT',
        exp: 'Experiencia formativa',
        especialidad: 'Formación de talentos',
        bio: 'Integrante del cuerpo técnico de la Academia Nico Sport.',
        fotoIcon: 'fa-user-tie',
        color: 'from-nico-orange to-amber-600'
      };
      this.staffFormModal = true;
    },
    guardarStaffForm() {
      if (!this.esAdmin) return;
      const nom = (this.staffForm.nombre || '').trim();
      const car = (this.staffForm.cargo || '').trim();
      if (!nom) {
        alert('Por favor ingrese el nombre del integrante.');
        return;
      }
      if (!car) {
        alert('Por favor ingrese el cargo.');
        return;
      }
      if (this.staffForm.id) {
        const target = this.staffList.find(x => x.id === this.staffForm.id);
        if (target) {
          Object.assign(target, {
            nombre: nom,
            cargo: car,
            licencia: (this.staffForm.licencia || '').trim(),
            exp: (this.staffForm.exp || '').trim(),
            especialidad: (this.staffForm.especialidad || '').trim(),
            bio: (this.staffForm.bio || '').trim(),
            fotoIcon: this.staffForm.fotoIcon || 'fa-user-tie',
            color: this.staffForm.color || 'from-nico-orange to-amber-600'
          });
        }
      } else {
        const nuevoId = Date.now() % 1000000;
        this.staffList.push({
          id: nuevoId,
          nombre: nom,
          cargo: car,
          licencia: (this.staffForm.licencia || '').trim() || 'Licencia en trámite',
          exp: (this.staffForm.exp || '').trim() || 'Experiencia formativa',
          especialidad: (this.staffForm.especialidad || '').trim() || 'Fútbol formativo',
          bio: (this.staffForm.bio || '').trim() || 'Integrante del cuerpo técnico de la Academia Nico Sport.',
          fotoIcon: this.staffForm.fotoIcon || 'fa-user-tie',
          color: this.staffForm.color || 'from-nico-orange to-amber-600'
        });
      }
      this.persistStaff();
      this.staffFormModal = false;
    },
    persistStaff() { window.DB.guardarStaff(this.staffList); },

    /* ========== 09) VITRINA DE TROFEOS ========== */
        trofeosBase: [
      { id: 'trophy-sub12', nombre: 'Próximamente Sub-12', catKey: 'sub12', categoria: 'Categoría Sub 12', 'anio': '...', icono: 'fa-trophy', color: 'text-amber-400', descripcion: 'Próximamente en desarrollo...', evaluacion: { rendimientoGlobal: 0, posesion: '0%', efectividadPases: '0%', golesAFavor: 0, golesEnContra: 0, disciplina: 'En Proceso', resumenTactico: 'Próximamente...' } },
      { id: 'trophy-sub16', nombre: 'Próximamente Sub-16', catKey: 'sub16', categoria: 'Categoría Sub 16', 'anio': '...', icono: 'fa-medal', color: 'text-amber-400', descripcion: 'Próximamente en desarrollo...', evaluacion: { rendimientoGlobal: 0, posesion: '0%', efectividadPases: '0%', golesAFavor: 0, golesEnContra: 0, disciplina: 'En Proceso', resumenTactico: 'Próximamente...' } },
      { id: 'trophy-sub6', nombre: 'Próximamente Sub-6', catKey: 'sub6', categoria: 'Categoría Sub 6', 'anio': '...', icono: 'fa-award', color: 'text-amber-400', descripcion: 'Próximamente en desarrollo...', evaluacion: { rendimientoGlobal: 0, posesion: '0%', efectividadPases: '0%', golesAFavor: 0, golesEnContra: 0, disciplina: 'En Proceso', resumenTactico: 'Próximamente...' } },
      { id: 'trophy-submayor', nombre: 'Próximamente Sub-Mayor', catKey: 'submayor', categoria: 'Sub-Mayor', 'anio': '...', icono: 'fa-trophy', color: 'text-nico-blue', descripcion: 'Próximamente en desarrollo...', evaluacion: { rendimientoGlobal: 0, posesion: '0%', efectividadPases: '0%', golesAFavor: 0, golesEnContra: 0, disciplina: 'En Proceso', resumenTactico: 'Próximamente...' } }
    ],
    trofeosVersion: 0, mostrarFormTrofeo: false, trofeoEditId: null,
    nuevoTrofeo: { nombre: '', catKey: 'sub6', 'anio': '2026', descripcion: '', rendimientoGlobal: 100, posesion: '', efectividadPases: '', golesAFavor: 0, golesEnContra: 0, disciplina: '', resumenTactico: '' },
    get trophiesList() {
        if (!window._trophiesListCache || window._trophiesVersionCache !== this.trofeosVersion) {
            let ocultos = [];
            let edits = {};
            let custom = [];
            
            if (window.DB) {
                ocultos = window.DB.obtenerTrofeosOcultos && window.DB.obtenerTrofeosOcultos() || [];
                edits = window.DB.obtenerTrofeosEditados && window.DB.obtenerTrofeosEditados() || {};
                custom = window.DB.obtenerTrofeosCustom && window.DB.obtenerTrofeosCustom() || [];
            }
            
            if (!Array.isArray(ocultos)) ocultos = [];
            if (!Array.isArray(custom)) custom = [];
            if (typeof edits !== 'object' || edits === null) edits = {};
            
            const baseSafe = Array.isArray(this.trofeosBase) ? this.trofeosBase : [];
            
            const base = baseSafe
                .filter(t => !ocultos.includes(t.id))
                .map(t => edits[t.id] ? Object.assign({}, t, edits[t.id]) : t);
                
            window._trophiesListCache = base.concat(custom.map(t => edits[t.id] ? Object.assign({}, t, edits[t.id]) : t));
            window._trophiesVersionCache = this.trofeosVersion;
        }
        return window._trophiesListCache || [];
      },
    calcularRendimientoGlobal() {
        if (!this.nuevoTrofeo) return;
        let p = String(this.nuevoTrofeo.posesion).replace(/[^0-9]/g, '');
        let e = String(this.nuevoTrofeo.efectividadPases).replace(/[^0-9]/g, '');
        
        let pVal = parseInt(p) || 0;
        let eVal = parseInt(e) || 0;
        if (pVal > 0 && eVal > 0) {
            this.nuevoTrofeo.rendimientoGlobal = Math.round((pVal + eVal) / 2);
        } else if (pVal > 0) {
            this.nuevoTrofeo.rendimientoGlobal = pVal;
        } else if (eVal > 0) {
            this.nuevoTrofeo.rendimientoGlobal = eVal;
        }
      },
      abrirFormTrofeo() {
      if (!this.mostrarFormTrofeo) {
        this.trofeoEditId = null;
        this.nuevoTrofeo = { nombre: '', catKey: 'sub6', 'anio': '2026', descripcion: '', rendimientoGlobal: 100, posesion: '', efectividadPases: '', golesAFavor: 0, golesEnContra: 0, disciplina: '', resumenTactico: '' };
      }
      this.mostrarFormTrofeo = !this.mostrarFormTrofeo;
    },
    editarTrofeo(t) {
      this.trofeoEditId = t.id;
      this.nuevoTrofeo = {
        nombre: t.nombre, catKey: t.catKey, 'anio': t.anio, descripcion: t.descripcion,
        rendimientoGlobal: t.evaluacion.rendimientoGlobal, posesion: t.evaluacion.posesion,
        efectividadPases: t.evaluacion.efectividadPases, golesAFavor: t.evaluacion.golesAFavor,
        golesEnContra: t.evaluacion.golesEnContra, disciplina: t.evaluacion.disciplina,
        resumenTactico: t.evaluacion.resumenTactico
      };
      this.mostrarFormTrofeo = true;
    },
    guardarTrofeoForm() {
      if (!this.esAdmin) return;
      const nombresCat = {}; this.categoriasTorneo.forEach(c => nombresCat[c.key] = c.label);
      const t = {
        id: this.trofeoEditId || ('custom-' + Date.now()),
        nombre: this.nuevoTrofeo.nombre.trim() || 'Campeonato Nico Sport',
        catKey: this.nuevoTrofeo.catKey,
        categoria: nombresCat[this.nuevoTrofeo.catKey] || 'Categoría',
        'anio': this.nuevoTrofeo.anio.trim() || '2026',
        icono: 'fa-trophy', color: 'text-amber-400',
        descripcion: this.nuevoTrofeo.descripcion.trim() || 'Campeonato obtenido por el equipo.',
        evaluacion: {
          rendimientoGlobal: parseInt(this.nuevoTrofeo.rendimientoGlobal) || 0,
          posesion: this.nuevoTrofeo.posesion || '-',
          efectividadPases: this.nuevoTrofeo.efectividadPases || '-',
          golesAFavor: parseInt(this.nuevoTrofeo.golesAFavor) || 0,
          golesEnContra: parseInt(this.nuevoTrofeo.golesEnContra) || 0,
          disciplina: this.nuevoTrofeo.disciplina || 'Excelente',
          resumenTactico: this.nuevoTrofeo.resumenTactico || 'Informe tcnico pendiente.'
        }
      };
      if (this.trofeoEditId) {
        if (String(this.trofeoEditId).indexOf('custom-') === 0) {
          window.DB.eliminarTrofeoCustom(this.trofeoEditId);
          window.DB.guardarTrofeoCustom(t);
        } else window.DB.guardarTrofeoEditado(t);
      } else window.DB.guardarTrofeoCustom(t);
      this.trofeosVersion++;
      this.mostrarFormTrofeo = false; this.trofeoEditId = null;
    },
    agregarTrofeo() { this.guardarTrofeoForm(); },
    eliminarTrofeo(id) {
      if (!this.esAdmin) return;
      if (!confirm('¿Eliminar este trofeo de la vitrina?')) return;
      if (String(id).indexOf('custom-') === 0) window.DB.eliminarTrofeoCustom(id);
      else window.DB.ocultarTrofeoBase(id);
      if (this.selectedTrophy && this.selectedTrophy.id === id) this.selectedTrophy = null;
      this.trofeosVersion++;
    },
    editarAnalisis(campo, label, esNumero) {
        if (!this.esAdmin || !this.modoEdicion || !this.selectedTrophy) return;
        const t = this.selectedTrophy;
        let v = prompt(label, t.evaluacion[campo]);
        if (v === null) return;
        
        let valToSave = esNumero ? (parseInt(v) || 0) : v;
        if (campo === 'posesion' || campo === 'efectividadPases') {
            let n = String(v).replace(/[^0-9]/g, '');
            if (n) valToSave = n + '%';
        }
        
        let nuevaEvaluacion = Object.assign({}, t.evaluacion, { [campo]: valToSave });
        
        if (campo === 'posesion' || campo === 'efectividadPases') {
            let pVal = parseInt(String(nuevaEvaluacion.posesion || '').replace(/[^0-9]/g, '')) || 0;
            let eVal = parseInt(String(nuevaEvaluacion.efectividadPases || '').replace(/[^0-9]/g, '')) || 0;
            if (pVal > 0 && eVal > 0) nuevaEvaluacion.rendimientoGlobal = Math.round((pVal + eVal) / 2);
            else if (pVal > 0) nuevaEvaluacion.rendimientoGlobal = pVal;
            else if (eVal > 0) nuevaEvaluacion.rendimientoGlobal = eVal;
        }
        
        const nuevo = Object.assign({}, t, { evaluacion: nuevaEvaluacion });
        window.DB.guardarTrofeoEditado(nuevo);
        this.trofeosVersion++;
        this.selectedTrophy = this.trophiesList.find(x => x.id === t.id) || nuevo;
      },

    /* ========== 10) EVALUACIÓN GLOBAL ========== */
    evalGlobalVersion: 0,
    evalGlobalBase: [
      { id: 1, catKey: 'sub6',     name: 'Categoría Sub 6 ',              desc: 'Eficiencia Ludotécnica', value: 96 },
      { id: 2, catKey: 'sub12',    name: 'Categoría Sub 12 ',     desc: 'Rendimiento de Torneo',  value: 94 },
      { id: 3, catKey: 'sub16',    name: 'Categoría Sub 16 ',  desc: 'Despliegue Táctico',     value: 89 },
      { id: 4, catKey: 'submayor', name: 'Sub Mayor ',           desc: 'Dinámica y Presión',     value: 91 }
    ],
    get evalCategories() {
      void this.evalGlobalVersion;
      void this.catAdminVersion;
      const guardado = window.DB.obtenerEvalGlobal() || [];
      const lista = [];
      this.categoriasTorneo.forEach((cat, idx) => {
        let item = guardado.find(e => e.catKey === cat.key) || null;
        if (!item && guardado[idx] && !guardado[idx].catKey) {
          item = Object.assign({ catKey: cat.key }, guardado[idx]);
        }
        if (!item) {
          const b = this.evalGlobalBase.find(e => e.catKey === cat.key);
          item = b ? Object.assign({}, b) : null;
        }
        if (!item) {
          item = { id: 1000 + idx, catKey: cat.key, name: 'Categoría ' + cat.label, desc: 'Rendimiento General', value: 50 };
        }
        lista.push(item);
      });
      return lista;
    },
    cambiarEvalGlobal(id, delta) {
      if (!this.esAdmin) return;
      const lista = JSON.parse(JSON.stringify(this.evalCategories));
      const c = lista.find(x => x.id === id); if (!c) return;
      c.value = Math.max(0, Math.min(100, c.value + delta));
      window.DB.guardarEvalGlobal(lista); this.evalGlobalVersion++;
    },
    getColorEval(val) { return 'hsl(' + Math.max(0, Math.min(120, val * 1.2)) + ', 85%, 50%)'; },

    /* ========== 11) CRUD DE CATEGORÍAS ========== */
    catAdminVersion: 0,
    catNueva: { label: '', descripcion: '' },
    categoriasTorneo: [],
    categoriasVitrinaTabs: [],
    playersSub6: [],
    playersSub12: [],
    playersSub16: [],
    playersSubMayor: [],
    get categoriasUI() {
      void this.catAdminVersion;
      let lista = this.categoriasTorneo;
      if (this.esEntrenador && !this.esAdmin) {
        lista = lista.filter(c => c.key === this.currentUser.catKey);
      }
      return lista.map((c, i) => {
        const col = UI[c.key] ? UI[c.key] : PALETA_NUEVOS[i % PALETA_NUEVOS.length];
        return Object.assign({ key: c.key }, col, {
          tab: c.label || 'Sin Categoría',
          titulo: c.titulo || ('CATEGORÍA ' + (c.label ? c.label.toUpperCase() : '')),
          badge: c.badge || c.descripcion || '',
          desc: c.desc || c.descripcion || '',
          torneos: c.torneos || '',
          horario: c.horario || '',
          cancha: c.cancha || '',
          count: c.count || '',
          entrenador: c.entrenador || '',
          foto: c.foto || ''
        });
      });
    },
    get categoriasVisibles() {
      if (this.esEntrenador && !this.esAdmin) {
        return this.categoriasUI.filter(c => c.key === this.currentUser.catKey);
      }
      return this.categoriasUI;
    },
    get categoriasTorneoVisibles() {
      if (this.esEntrenador && !this.esAdmin) {
        return this.categoriasTorneo.filter(c => c.key === this.currentUser.catKey);
      }
      return this.categoriasTorneo;
    },
    get categoriasAsistenciaVisibles() { return this.categoriasTorneoVisibles; },
    get categoriasAdmin() {
      void this.catAdminVersion;
      return this.categoriasTorneo.map(c => ({
        key: c.key, label: c.label, descripcion: c.descripcion,
        core: CORE_KEYS.includes(c.key)
      }));
    },
    crearCategoria() {
      if (!this.esAdmin) return;
      const label = this.catNueva.label.trim();
      if (!label) { alert(' Escribe el nombre de la nueva categoría'); return; }
      let key = label.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/^sub\s*[-_]?\s*(\d+)$/i, 'sub$1').replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
      if (!key) key = 'sub_' + Date.now().toString(36);
      if (this.categoriasTorneo.some(c => c.key === key)) { alert('Ya existe una categoría con ese nombre'); return; }
      const nueva = Object.assign({ key, label, descripcion: this.catNueva.descripcion.trim() || '' }, catTorneo());
      this.categoriasTorneo.push(nueva);
      this.plantillasExtra[key] = [];
      this.persistTorneo();
      this._syncCategoriasDB();
      this.catTab = key;
      this.catNueva = { label: '', descripcion: '' };
      this.catAdminVersion++;
      this.evalGlobalVersion++;
      alert(' Categoría "' + label + '" creada con plantilla automática.');
    },
    
    openMediaEditModal(title, currentUrl, type, callback) {
      this.editMediaModal = { open: true, title, url: currentUrl || '', type, callback };
    },
    closeMediaEditModal() {
      this.editMediaModal.open = false;
    },
    saveMediaEditModal() {
      if (this.editMediaModal.callback) this.editMediaModal.callback(this.editMediaModal.url);
      this.closeMediaEditModal();
    },
    editarFotoCategoria(cat) {
      if (!this.esAdmin) return;
      this.openMediaEditModal('Foto Categoría', cat.foto, 'image', (u) => {
          if (u !== null && u.trim() !== '') {
              const t = this.categoriasTorneo.find(c => c.key === cat.key);
              if (t) { t.foto = u.trim(); }
              this.persistTorneo(); this._syncCategoriasDB(); this.catAdminVersion++;
          }
      });
    },
    editarFotoJugador(player) {
      if (!this.esAdmin) return;
      this.openMediaEditModal('Foto Jugador', player.foto, 'image', (u) => {
          if (u !== null && u.trim() !== '') {
              player.foto = u.trim();
              this.persistPlantilla(player.catKey);
              this.evaluacionesVersion++;
          }
      });
    },
    editarCategoria(cat) {
      if (!this.esAdmin) return;
      const label = prompt('Nombre de la categoría:', cat.label); if (label === null) return;
      const desc = prompt('Descripción:', cat.descripcion); if (desc === null) return;
      const t = this.categoriasTorneo.find(c => c.key === cat.key);
      if (t) { t.label = label.trim() || t.label; t.descripcion = desc; }
      this.persistTorneo(); this._syncCategoriasDB(); this.catAdminVersion++;
    },
    eliminarCategoria(cat) {
      if (!this.esAdmin) return;
      if (cat.core) { alert(' Las 4 categorías oficiales no se pueden eliminar.'); return; }
      if (!confirm('¿Eliminar la categoría "' + cat.label + '"?')) return;
      this.categoriasTorneo = this.categoriasTorneo.filter(c => c.key !== cat.key);
      delete this.plantillasExtra[cat.key];
      if (this.currentTorneoIdx >= this.categoriasTorneo.length) this.currentTorneoIdx = 0;
      if (this.catTab === cat.key) this.catTab = 'sub6';
      window.DB.eliminarCategoria(cat.key);
      this.persistTorneo(); this._syncCategoriasDB(); this.catAdminVersion++;
      this.evalGlobalVersion++;
    },
    subirCategoria(i) { this._moverCategoria(i, i - 1); },
    bajarCategoria(i) { this._moverCategoria(i, i + 1); },
    _moverCategoria(de, a) {
      if (a < 0 || a >= this.categoriasTorneo.length) return;
      const arr = this.categoriasTorneo;
      const tmp = arr[de]; arr[de] = arr[a]; arr[a] = tmp;
      this.persistTorneo(); this._syncCategoriasDB(); this.catAdminVersion++;
    },
    _syncCategoriasDB() {
      window.DB.guardarCategorias(this.categoriasTorneo.map(c => ({
        key: c.key, label: c.label, descripcion: c.descripcion, core: CORE_KEYS.includes(c.key)
      })));
    },
    aplicarCategoriasGuardadas(cats) {
      const mapa = {};
      this.categoriasTorneo.forEach(c => mapa[c.key] = c);
      const ordenada = cats.map(c => {
        const b = mapa[c.key]; if (!b) {
          const nueva = Object.assign({ key: c.key, label: c.label, descripcion: c.descripcion || '' }, catTorneo());
          return nueva;
        }
        b.label = c.label; if (c.descripcion) b.descripcion = c.descripcion;
        delete mapa[c.key]; return b;
      });
      Object.keys(mapa).forEach(k => ordenada.push(mapa[k]));
      this.categoriasTorneo = ordenada;
    },

    fasesTorneo: [
      { key: 'ninguna', label: 'Sin torneo', icon: 'fas fa-ban' },
      { key: 'octavos', label: 'Octavos', icon: 'fas fa-trophy' },
      { key: 'cuartos', label: 'Cuartos', icon: 'fas fa-medal' },
      { key: 'semis', label: 'Semis', icon: 'fas fa-flag-checkered' },
      { key: 'final', label: 'Final', icon: 'fas fa-crown' },
      { key: 'campeon', label: 'Campeón', icon: 'fas fa-award' }
    ],
    currentTorneoIdx: 0,
    nextTorneo() { this.currentTorneoIdx = (this.currentTorneoIdx + 1) % this.categoriasTorneoVisibles.length; this.particulasBtn(); },
    prevTorneo() { this.currentTorneoIdx = (this.currentTorneoIdx - 1 + this.categoriasTorneoVisibles.length) % this.categoriasTorneoVisibles.length; this.particulasBtn(); },
    particulasBtn() { if (window.triggerParticles && event && event.currentTarget) { const r = event.currentTarget.getBoundingClientRect(); window.triggerParticles(r.left + r.width / 2, r.top + r.height / 2); } },
    goToTorneo(idx) { this.currentTorneoIdx = idx; },
    setFaseTorneo(faseKey) {
      if (!this.esAdmin) return;
      this.categoriasTorneo[this.currentTorneoIdx].faseActual = faseKey;
      this.persistTorneo(); this.particulasBtn();
    },
    tablaOrdenada(cat) { return [...cat.tabla].sort((a, b) => (b.pts - a.pts) || ((b.gf - b.gc) - (a.gf - a.gc))); },
    bracketIdx(cat) { return ({ ninguna: -1, octavos: 0, cuartos: 1, semis: 2, final: 3, campeon: 4 })[cat.faseActual] ?? -1; },
    roundState(cat, i) { const idx = this.bracketIdx(cat); if (idx < 0) return 'next'; return idx > i ? 'done' : (idx === i ? 'current' : 'next'); },
    roundText(cat, i) { const s = this.roundState(cat, i); return s === 'done' ? '✅' : (s === 'current' ? 'NICO SPORT' : 'POR DEFINIR'); },
    roundClass(cat, i) {
      const s = this.roundState(cat, i);
      return s === 'done' ? 'border-nico-green/40 text-nico-green bg-nico-green/5' : (s === 'current' ? 'border-nico-orange bg-nico-orange/10 text-nico-orange shadow-[0_0_12px_rgba(255,85,0,0.4)]' : 'border-white/10 text-gray-600');
    },

    /* ========== 12) LOGIN / SESIÓN + EVALUACIÓN ========== */
    async intentarLogin() {
      this.loginError = 'Conectando...';
      this.esBloqueado = false;
      try { await window.DB.listo; } catch(e) {}
      const res = await window.DB.login(this.loginUsuario.trim(), this.loginPassword.trim(), this.loginHpTrap || '');
      if (res && res.success && res.usuario) {
        const u = res.usuario;
        let nextTab = 'historia';
        let nextCat = this.catTab || 'sub6';
        if (u.rol === 'admin') {
          nextTab = 'admin';
        } else if (u.rol === 'padre') {
          if (u.hijoCat && u.hijoId) {
            nextTab = 'categorias';
            nextCat = u.hijoCat;
          } else {
            nextTab = 'portal';
          }
        } else if (u.rol === 'entrenador') {
          nextTab = 'categorias';
          nextCat = u.catKey || 'sub6';
        }
        this.loginModal = false;
        this.loading = true;
        this.progress = 25;
        localStorage.removeItem('ns_currentUser');
        localStorage.setItem('ns_mainTab', nextTab);
        localStorage.setItem('ns_catTab', nextCat);
        window.location.reload();
      } else {
        if (res && res.bloqueado) {
          this.esBloqueado = true;
          this.loginError = res.error || 'Cuenta suspendida';
        } else {
          this.loginError = res ? (res.error || 'Credenciales inválidas') : 'Error de red';
        }
      }
    },
    async cerrarSesion() {
      if (this.modoEdicion) {
        this.modoEdicion = false;
        if (typeof this.deshabilitarEdicionEnDOM === 'function') {
          this.deshabilitarEdicionEnDOM();
        }
      }
      document.body.classList.remove('is-admin');
      this.currentUser = null;
      this.mainTab = 'historia';
      localStorage.removeItem('ns_currentUser');
      localStorage.removeItem('ns_mainTab');
      localStorage.removeItem('ns_catTab');
      try { sessionStorage.clear(); } catch(e) {}
      if (window.DB && typeof window.DB.logout === 'function') {
        try {
          await window.DB.logout();
        } catch(e) {}
      }
      window.location.replace(window.location.pathname);
    },
    obtenerUrlSitio() {
      if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        let p = window.location.pathname;
        if (p.match(/\/[^\/]+\.[^\/]+$/)) p = p.substring(0, p.lastIndexOf('/'));
        if (p.endsWith('/public')) p = p.slice(0, -7);
        if (!p.endsWith('/')) p += '/';
        return window.location.origin + p;
      }
      return 'https://www.academianicosport.com/';
    },
    obtenerEnlaceWhatsappInscripcion() {
      return 'https://wa.me/50763321779?text=' + encodeURIComponent('¡Hola Academia Nico Sport! ⚽👋 Deseo información para inscribir a mi hijo(a). ¿Cuáles son los requisitos, horarios y categorías disponibles?');
    },
    obtenerEnlaceInstagram() {
      return 'https://www.instagram.com/academia_nicosport?cplk=c3A2dTN1NXhxY2Q0';
    },
    abrirEvaluacion(player) {
      const previa = window.DB.obtenerEvaluacion(player.catKey, player.id);
      this.evalDatos = previa ? JSON.parse(JSON.stringify(previa))
        : { tecnicos: [0,0,0,0,0,0,0,0,0], tacticos: [0,0,0,0,0,0], fisicos: [0,0,0,0,0], actitudinales: [0,0,0,0,0,0] };
      this.playerModal = null; this.evalModal = player;
    },
    abrirEvalVer(player) {
      if (!player) return;
      this.evalVerModal = player;
    },
    evalCompleta(player) {
      void this.evaluacionesVersion;
      if (!player) return null;
      return window.DB.obtenerEvaluacion(player.catKey, player.id);
    },
    get evalPromedio() {
      const all = [...this.evalDatos.tecnicos, ...this.evalDatos.tacticos, ...this.evalDatos.fisicos, ...this.evalDatos.actitudinales]
        .map(v => parseFloat(v))
        .filter(n => !isNaN(n) && n > 0);
      if (!all.length) return 0;
      return Math.round((all.reduce((a, b) => a + b, 0) / all.length) * 10) / 10;
    },
    async guardarEvaluacion() {
      if (!this.evalModal) return;
      const ev = {
        catKey: this.evalModal.catKey,
        playerId: this.evalModal.id,
        tecnicos: (this.evalDatos.tecnicos || []).map(v => parseFloat(v) || 0),
        tacticos: (this.evalDatos.tacticos || []).map(v => parseFloat(v) || 0),
        fisicos: (this.evalDatos.fisicos || []).map(v => parseFloat(v) || 0),
        actitudinales: (this.evalDatos.actitudinales || []).map(v => parseFloat(v) || 0)
      };
      await window.DB.guardarEvaluacion(ev);
      this.evaluacionesVersion++;
      this.evalModal = null;
      const toast = document.createElement('div');
      toast.className = 'fixed top-4 right-4 bg-nico-green text-black font-black py-2.5 px-4 rounded-xl shadow-2xl z-50 border border-black/20';
      toast.innerText = '✓ Evaluación guardada correctamente (Escala 1 a 10)';
      document.body.appendChild(toast);
      setTimeout(() => toast.remove(), 2500);
    },
    notaJugador(player) {
      void this.evaluacionesVersion;
      const ev = window.DB.obtenerEvaluacion(player.catKey, player.id);
      if (!ev) return null;
      const all = [...ev.tecnicos, ...ev.tacticos, ...ev.fisicos, ...ev.actitudinales]
        .map(v => parseFloat(v))
        .filter(n => !isNaN(n) && n > 0);
      if (!all.length) return null;
      return Math.round((all.reduce((a, b) => a + b, 0) / all.length) * 10) / 10;
    },

    /* ========== 13) ASISTENCIA Y ENCUESTAS ========== */
    asistenciaCat: 'sub6',
    asistenciaFecha: new Date().toISOString().slice(0, 10),
    asistenciaVersion: 0, encuestaVersion: 0,
    encuesta: { aspectos: [0,0,0,0,0,0,0,0,0,0,0], gustaMas: '', mejorar: '', crecimiento: '', crecimientoWhy: '', recomienda: '', recomiendaWhy: '', observaciones: '' },
    encuestaAspectos: [
      'Calidad de los entrenamientos', 'Organización de la academia', 'Puntualidad de los entrenadores',
      'Trato hacia los jugadores', 'Comunicación con los padres', 'Desarrollo técnico de mi hijo(a)',
      'Desarrollo de valores (respeto, disciplina, compañerismo)', 'Ambiente de entrenamiento',
      'Seguridad durante las actividades', 'Relación costo-beneficio', 'Satisfacción general con la academia'
    ],
    playersDe(catKey) {
      const map = { sub6: this.playersSub6, sub12: this.playersSub12, sub16: this.playersSub16, submayor: this.playersSubMayor };
      if (map[catKey] !== undefined) return map[catKey];
      if (!this.plantillasExtra[catKey]) this.plantillasExtra[catKey] = [];
      return this.plantillasExtra[catKey];
    },
    _setPlantilla(catKey, lista) {
      const map = { sub6:'playersSub6', sub12:'playersSub12', sub16:'playersSub16', submayor:'playersSubMayor' };
      if (map[catKey]) this[map[catKey]] = lista;
      else this.plantillasExtra[catKey] = lista;
    },
    ciclarAsistencia(player) {
      if (!this.esAdmin && !this.puedeEditarCategoria(this.asistenciaCat)) return;
      const estado = this.estadoAsistencia(player);
      let nuevo;
      if (!estado) nuevo = 'presente';
      else if (estado === 'presente') nuevo = 'ausente';
      else if (estado === 'ausente') nuevo = 'justificado';
      else nuevo = null;
      if (nuevo === null) {
        window.DB.quitarAsistencia(this.asistenciaCat, this.asistenciaFecha, player.id);
      } else {
        window.DB.marcarAsistencia({ catKey: this.asistenciaCat, fecha: this.asistenciaFecha, playerId: player.id, estado: nuevo });
      }
      this.asistenciaVersion++;
    },
    estadoAsistencia(player) {
      void this.asistenciaVersion;
      const reg = window.DB.obtenerAsistencias(this.asistenciaCat, player.id).find(r => r.fecha === this.asistenciaFecha);
      return reg ? reg.estado : null;
    },
    conteoAsistenciaHoy() {
      void this.asistenciaVersion;
      const c = { presente: 0, ausente: 0, justificado: 0 };
      this.playersDe(this.asistenciaCat).forEach(p => { const e = this.estadoAsistencia(p); if (e && c[e] !== undefined) c[e]++; });
      return c;
    },
    resumenAsistencia(player) {
      void this.asistenciaVersion;
      const r = { presente: 0, ausente: 0, justificado: 0 };
      window.DB.obtenerAsistencias(player.catKey, player.id).forEach(x => { if (r[x.estado] !== undefined) r[x.estado]++; });
      return r;
    },
    evalResumen(player) {
      void this.evaluacionesVersion;
      const ev = window.DB.obtenerEvaluacion(player.catKey, player.id);
      if (!ev) return {};
      const prom = arr => { const f = (arr || []).map(v => parseFloat(v)).filter(n => !isNaN(n) && n > 0); if (!f.length) return 0; return Math.round((f.reduce((a, b) => a + b, 0) / f.length) * 10) / 10; };
      return { tecnicos: prom(ev.tecnicos), tacticos: prom(ev.tacticos), fisicos: prom(ev.fisicos), actitudinales: prom(ev.actitudinales), general: this.notaJugador(player) };
    },
    enviarEncuesta() {
      const enc = JSON.parse(JSON.stringify(this.encuesta));
      enc.padre = this.currentUser ? this.currentUser.nombre : 'Anónimo';
      window.DB.guardarEncuesta(enc);
      this.encuestaVersion++;
      this.encuesta = { aspectos: [0,0,0,0,0,0,0,0,0,0,0], gustaMas: '', mejorar: '', crecimiento: '', crecimientoWhy: '', recomienda: '', recomiendaWhy: '', observaciones: '' };
      alert('¡Gracias! Tu evaluación fue enviada ');
    },
    get encuestasPadres() { void this.encuestaVersion; return window.DB.obtenerEncuestas(); },
    get resultadosVozPadres() {
      void this.encuestaVersion;
      const encs = window.DB.obtenerEncuestas();
      if (!encs.length) return null;
      const moda = nums => { const f = {}; let best = 0, out = 0; nums.forEach(n => { f[n] = (f[n] || 0) + 1; if (f[n] > best) { best = f[n]; out = n; } }); return out; };
      return {
        total: encs.length,
        aspectos: this.encuestaAspectos.map((label, i) => ({ label, valor: moda(encs.map(e => e.aspectos[i]).filter(v => v > 0)) })),
        gustaMas: encs.map(e => e.gustaMas).filter(Boolean),
        mejorar: encs.map(e => e.mejorar).filter(Boolean),
        observaciones: encs.map(e => e.observaciones).filter(Boolean)
      };
    },

    /* ========== 14) PANEL ADMIN: REGISTRO UNIFICADO JUGADOR/PADRE ========== */
    subDestinoTransfer: '',
    hijoActivoIndex: 0,
    adminRegistro: {
      tipo: 'con_padre',
      hijos: [
        { nombre: '', edad: '', catKey: '', pos: 'MED' }
      ],
      jugadorNombre: '', jugadorEdad: '', catKey: '', jugadorDorsal: '', jugadorPos: 'MED',
      padreNombre: '', padreUsuario: '', padrePassword: '', telefonoPadre: ''
    },
    adminRegistroMsg: '', adminPadresVersion: 0, comentariosVersion: 0,
    padreEditando: null,
    modalEditarPadre: false,
    mostrarPassPadreEdit: false,
    editPadreMsg: '',
    nuevoAdmin: { nombre: '', usuario: '', password: '', usuarioManual: false },
    nuevoAdminMsg: '',
    mostrarPassNuevoAdmin: false,
    linkRegistroVersion: 0, copiadoLinkMsg: '', copiadoTokenMsg: '',
    get infoLinkRegistro() {
      void this.linkRegistroVersion;
      void this.adminPadresVersion;
      return (window.DB && window.DB.obtenerRegistroLinkTemp) ? window.DB.obtenerRegistroLinkTemp() : null;
    },
    get linkRegistroActivo() {
      const info = this.infoLinkRegistro;
      return !!(info && info.activo && info.token);
    },
    get linkRegistroExpiraStr() {
      const info = this.infoLinkRegistro;
      return (info && info.expiraStr) ? info.expiraStr : '';
    },
    get linkRegistroCodigoAcademia() {
      const info = this.infoLinkRegistro;
      return (info && info.codigoAcademia) ? info.codigoAcademia : 'NS-0000';
    },
    get urlLinkRegistroPadres() {
      let base = window.location.origin + window.location.pathname;
      base = base.replace(/\/index\.php$/i, '');
      if (!base.endsWith('/')) base += '/';
      return base + 'registro.php';
    },
    async generarLinkRegistroPadres(horas = 168) {
      if (!confirm('¿Deseas rotar y generar un nuevo código semanal de verificación ahora?')) return;
      const res = await window.DB.generarRegistroLinkTemp(168);
      if (res && res.ok) {
        this.linkRegistroVersion++;
        if (typeof this.mostrarToast === 'function') {
          this.mostrarToast('Código de verificación rotado con éxito.', 'exito');
        }
      } else if (res && res.error) {
        alert(res.error);
      }
    },
    async desactivarLinkRegistroPadres() {
      return this.generarLinkRegistroPadres(168);
    },
    copiarLinkRegistroPadres() {
      const url = this.urlLinkRegistroPadres;
      if (!url) return;
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url);
      }
      this.copiadoLinkMsg = '¡Copiado!';
      setTimeout(() => { this.copiadoLinkMsg = ''; }, 2500);
    },
    copiarCodigoAcademia() {
      const cod = this.linkRegistroCodigoAcademia;
      if (!cod) return;
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(cod);
      }
      this.copiadoTokenMsg = '¡Copiado!';
      setTimeout(() => { this.copiadoTokenMsg = ''; }, 2500);
    },

    async cambiarCredencialesAdmin() {
      if (!this.esAdmin || !this.currentUser) return;
      const f = this.adminCredsForm;
      f.msg = '';
      f.msgTipo = '';

      if (!f.passActual) {
        f.msg = 'Debes ingresar tu contraseña actual para autorizar los cambios.';
        f.msgTipo = 'error';
        return;
      }

      if (!f.usuarioNuevo && !f.passNuevo) {
        f.msg = 'Ingresa un nuevo nombre de usuario o una nueva contraseña.';
        f.msgTipo = 'error';
        return;
      }

      if (f.passNuevo) {
        if (f.passNuevo.length < 12) {
          f.msg = 'La nueva contraseña debe tener como mínimo 12 caracteres.';
          f.msgTipo = 'error';
          return;
        }
        if (!/[A-Za-z]/.test(f.passNuevo) || !/[0-9]/.test(f.passNuevo)) {
          f.msg = 'La nueva contraseña debe combinar letras y números.';
          f.msgTipo = 'error';
          return;
        }
        if (f.passNuevo !== f.passConfirm) {
          f.msg = 'La confirmación de la nueva contraseña no coincide.';
          f.msgTipo = 'error';
          return;
        }
      }

      f.guardando = true;
      try {
        const payload = {
          passActual: f.passActual,
          usuarioNuevo: (f.usuarioNuevo || '').trim(),
          passNuevo: f.passNuevo || '',
          passConfirm: f.passConfirm || ''
        };
        const res = await window.DB.post('usuarios/cambiar_mis_credenciales', payload);
        if (res && res.ok) {
          f.msg = res.mensaje || 'Credenciales actualizadas exitosamente.';
          f.msgTipo = 'exito';
          if (res.usuario && this.currentUser) {
            this.currentUser.usuario = res.usuario;
            this.currentUser.nombre = res.usuario;
          }
          f.passActual = '';
          f.passNuevo = '';
          f.passConfirm = '';
          if (typeof this.mostrarToast === 'function') {
            this.mostrarToast('Tus credenciales se han actualizado con éxito en la base de datos.', 'exito');
          }
        } else {
          f.msg = (res && res.error) ? res.error : 'No se pudo actualizar las credenciales.';
          f.msgTipo = 'error';
        }
      } catch (e) {
        f.msg = 'Error al comunicar con el servidor: ' + e.message;
        f.msgTipo = 'error';
      } finally {
        f.guardando = false;
      }
    },

    async cargarIpsBloqueadasWaf() {
      if (!this.esAdmin || !this.currentUser) return;
      try {
        if (window.DB && window.DB.listo) await window.DB.listo;
        const res = await window.DB.post('waf/listar', {});
        if (res && res.ok && Array.isArray(res.bloqueados)) {
          this.ipsBloqueadasWaf = res.bloqueados;
        }
      } catch (e) {}
    },
    async bloquearIpManual() {
      if (!this.esAdmin) return;
      const ip = (this.ipManualForm.ip || '').trim();
      if (!ip) {
        alert('Por favor introduce una dirección IP válida (ej: 198.51.100.12)');
        return;
      }
      try {
        const res = await window.DB.post('waf/bloquear', {
          ip: ip,
          motivo: (this.ipManualForm.motivo || '').trim() || 'Bloqueo manual por el Administrador',
          horas: parseInt(this.ipManualForm.horas) || 24
        });
        if (res && res.ok) {
          alert(res.mensaje || 'IP bloqueada con éxito.');
          this.ipManualForm.ip = '';
          this.ipsBloqueadasWaf = res.bloqueados || [];
        } else {
          alert('Error: ' + (res && res.error ? res.error : 'No se pudo bloquear la IP.'));
        }
      } catch (e) {
        alert('Error al bloquear la IP: ' + e.message);
      }
    },
    async desbloquearIpWaf(ip) {
      if (!this.esAdmin) return;
      if (!confirm('¿Desbloquear la dirección IP ' + ip + '?')) return;
      try {
        const res = await window.DB.post('waf/desbloquear', { ip: ip });
        if (res && res.ok) {
          this.ipsBloqueadasWaf = res.bloqueados || [];
          alert('La IP ' + ip + ' ha sido desbloqueada.');
        } else {
          alert('Error al desbloquear: ' + (res && res.error ? res.error : 'desconocido'));
        }
      } catch (e) {
        alert('Error: ' + e.message);
      }
    },
    async desbloquearTodasIpsWaf() {
      if (!this.esAdmin) return;
      if (!confirm('¿Estás seguro de que deseas desbloquear TODAS las direcciones IP bloqueadas?')) return;
      try {
        const res = await window.DB.post('waf/desbloquear', { ip: 'TODAS' });
        if (res && res.ok) {
          this.ipsBloqueadasWaf = [];
          alert('Todas las direcciones IP han sido desbloqueadas con éxito.');
        }
      } catch (e) {
        alert('Error: ' + e.message);
      }
    },

    filtroPadresCat: '', busquedaPadresAdmin: '',
    get listaPadres() { void this.adminPadresVersion; return window.DB.obtenerPadres(); },
    get listaAdmins() {
      void this.adminPadresVersion;
      try {
        return (window.DB && typeof window.DB.obtenerAdministradores === 'function') ? (window.DB.obtenerAdministradores() || []) : [];
      } catch (e) {
        return [];
      }
    },
    obtenerTotalAdmins() {
      try {
        return (this.listaAdmins || []).length;
      } catch (e) {
        return 0;
      }
    },
    obtenerCategoriaParaEdad(edad) {
      const ed = parseInt(edad, 10);
      if (isNaN(ed) || ed <= 0) return 'sub6';
      const cats = (Array.isArray(this.categoriasTorneo) && this.categoriasTorneo.length)
        ? this.categoriasTorneo
        : (window.DB && typeof window.DB.obtenerCategorias === 'function' ? window.DB.obtenerCategorias() : []);
      const numCats = [];
      cats.forEach(c => {
        const m = (c.key || '').match(/^sub(\d+)$/i);
        if (m) numCats.push({ key: c.key, maxEdad: parseInt(m[1], 10) });
      });
      if (numCats.length > 0) {
        numCats.sort((a, b) => a.maxEdad - b.maxEdad);
        for (const nc of numCats) {
          if (ed <= nc.maxEdad) return nc.key;
        }
        return 'submayor';
      }
      if (ed <= 6) return 'sub6';
      if (ed <= 12) return 'sub12';
      if (ed <= 16) return 'sub16';
      return 'submayor';
    },
    modalLey81: false,
    paginaPadres: 1, porPaginaPadres: 20,
      get listaPadresFiltradosTotal() {
        void this.adminPadresVersion;
        let lista = window.DB.obtenerPadres().slice().sort((a,b) => (a.nombre||'').localeCompare(b.nombre||''));
        if (this.filtroPadresCat) { lista = lista.filter(p => p.hijoCat === this.filtroPadresCat);  }
        if (this.busquedaPadresAdmin) {
          const q = this.busquedaPadresAdmin.toLowerCase();
          lista = lista.filter(p => (p.nombre||'').toLowerCase().includes(q) || (p.usuario||'').toLowerCase().includes(q));
          
        }
        return lista;
      },
      get totalPaginasPadres() {
        return Math.ceil(this.listaPadresFiltradosTotal.length / this.porPaginaPadres) || 1;
      },
      get listaPadresFiltrados() {
        const inicio = (this.paginaPadres - 1) * this.porPaginaPadres;
        return this.listaPadresFiltradosTotal.slice(inicio, inicio + this.porPaginaPadres);
      },
    
    autoAsignarSubAdmin() {
      const edad = parseInt(this.adminRegistro.jugadorEdad, 10);
      if (isNaN(edad) || edad < 3 || edad > 50) return;
      const claves = this.categoriasTorneo.map(c => c.key);
      const numericas = [];
      claves.forEach(k => {
        const m = k.match(/^sub_?(\d+)$/i);
        if (m) numericas.push({ lim: parseInt(m[1], 10), key: k });
      });
      numericas.sort((a, b) => a.lim - b.lim);
      let elegida = '';
      for (let i = 0; i < numericas.length; i++) {
        if (edad <= numericas[i].lim) { elegida = numericas[i].key; break; }
      }
      if (!elegida) elegida = claves.includes('submayor') ? 'submayor' : (claves[claves.length - 1] || 'sub6');
      this.adminRegistro.catKey = elegida;
    },
    agregarHijoAdmin() {
      if (!Array.isArray(this.adminRegistro.hijos)) {
        this.adminRegistro.hijos = [];
      }
      this.adminRegistro.hijos.push({ nombre: '', edad: '', catKey: '', pos: 'MED' });
    },
    quitarHijoAdmin(idx) {
      if (!Array.isArray(this.adminRegistro.hijos) || this.adminRegistro.hijos.length <= 1) return;
      this.adminRegistro.hijos.splice(idx, 1);
    },
    autoAsignarSubHijoAdmin(hijo) {
      const edad = parseInt(hijo.edad, 10);
      if (isNaN(edad) || edad < 3 || edad > 50) {
        hijo.catKey = '';
        return;
      }
      const claves = this.categoriasTorneo.map(c => c.key);
      const numericas = [];
      claves.forEach(k => {
        const m = k.match(/^sub_?(\d+)$/i);
        if (m) numericas.push({ lim: parseInt(m[1], 10), key: k });
      });
      numericas.sort((a, b) => a.lim - b.lim);
      let elegida = null;
      for (let i = 0; i < numericas.length; i++) {
        if (edad <= numericas[i].lim) { elegida = numericas[i].key; break; }
      }
      if (!elegida) elegida = claves.includes('submayor') ? 'submayor' : (claves[claves.length - 1] || 'sub6');
      hijo.catKey = elegida;
    },
    get dorsalLibreParaAdmin() {
      if (!this.adminRegistro.catKey) return '';
      return this.dorsalLibre(this.adminRegistro.catKey);
    },

    verPassManual: false,
    async registrarUnificado() {
      const r = this.adminRegistro;

      if (r.tipo === 'con_padre') {
        const hijosLista = (Array.isArray(r.hijos) && r.hijos.length > 0)
          ? r.hijos
          : [{ nombre: r.jugadorNombre, edad: r.jugadorEdad, catKey: r.catKey, pos: r.jugadorPos || 'MED' }];

        for (let i = 0; i < hijosLista.length; i++) {
          const h = hijosLista[i];
          const num = i + 1;
          const nom = (h.nombre || '').trim();
          const ed = parseInt(h.edad, 10);
          if (!nom) {
            this.adminRegistroMsg = `⚠️ Ingresa el nombre del hijo #${num}.`;
            setTimeout(() => this.adminRegistroMsg = '', 3500);
            return;
          }
          if (isNaN(ed) || ed < 3 || ed > 50) {
            this.adminRegistroMsg = `⚠️ Ingresa una edad válida (3 a 50 años) para el hijo #${num}.`;
            setTimeout(() => this.adminRegistroMsg = '', 3500);
            return;
          }
          this.autoAsignarSubHijoAdmin(h);
          if (!h.catKey) {
            this.adminRegistroMsg = `⚠️ No se pudo asignar categoría al hijo #${num}.`;
            setTimeout(() => this.adminRegistroMsg = '', 3500);
            return;
          }
        }

        const nombrePadre = (r.padreNombre || '').trim();
        const passPadre = (r.padrePassword || '').trim();
        const telPadre = (r.telefonoPadre || '').trim();
        if (!nombrePadre || !passPadre || !telPadre) {
          this.adminRegistroMsg = '⚠️ Completa el nombre del padre/madre, teléfono y contraseña.';
          setTimeout(() => this.adminRegistroMsg = '', 3500);
          return;
        }
        if (passPadre.length < 12 || !(/[a-zA-Z]/.test(passPadre) && /[0-9]/.test(passPadre))) {
          this.adminRegistroMsg = '⚠️ La contraseña debe tener mínimo 12 caracteres combinando letras y números.';
          setTimeout(() => this.adminRegistroMsg = '', 4000);
          return;
        }

        const hijosCreados = [];
        for (const h of hijosLista) {
          const arr = this.playersDe(h.catKey);
          const edadNum = parseInt(h.edad, 10) || 0;
          const player = this.crearOReciclarJugador(h.catKey, h.nombre.trim(), 0, h.pos || 'MED', edadNum);
          arr.push(player);
          this.persistPlantilla(h.catKey);
          this.registrarAccion({ tipo: 'add', catKey: h.catKey, playerId: player.id, player });
          hijosCreados.push({
            id: player.id,
            nombre: player.name,
            edad: edadNum,
            pos: player.pos,
            dorsal: 0,
            catKey: h.catKey,
            sub: h.catKey
          });
        }

        const res = window.DB.registrarPadre(
          nombrePadre,
          nombrePadre,
          passPadre,
          hijosCreados[0].catKey,
          hijosCreados[0].id,
          telPadre,
          hijosCreados[0].edad,
          hijosCreados[0].nombre,
          hijosCreados[0].pos,
          hijosCreados
        );

        if (!res || !res.ok) {
          this.adminRegistroMsg = '❌ Error al crear cuenta familiar: ' + (res ? res.msg : 'Usuario duplicado');
          return;
        }

        this.adminPadresVersion++;
        this.comentariosVersion++;
        this.adminRegistroMsg = '✓ Familia y ' + hijosCreados.length + ' hijo(s) inscritos exitosamente.';
        setTimeout(() => this.adminRegistroMsg = '', 4000);

        this.adminRegistro = {
          tipo: 'con_padre',
          hijos: [{ nombre: '', edad: '', catKey: '', pos: 'MED' }],
          jugadorNombre: '', jugadorEdad: '', catKey: '', jugadorDorsal: '', jugadorPos: 'MED',
          padreNombre: '', padreUsuario: '', padrePassword: '', telefonoPadre: ''
        };
      } else if (r.tipo === 'admin') {
        // Registro de Personal Administrativo (Máx. 3)
        if (this.obtenerTotalAdmins() >= 3) {
          this.adminRegistroMsg = '⚠️ Se ha alcanzado el límite máximo de 3 administradores.';
          setTimeout(() => this.adminRegistroMsg = '', 4000);
          return;
        }
        const nomAdmin = (r.padreNombre || '').trim();
        const telAdmin = (r.telefonoPadre || '').trim();
        const passAdmin = (r.padrePassword || '').trim();
        if (!nomAdmin || !telAdmin || !passAdmin) {
          this.adminRegistroMsg = '⚠️ Completa el nombre completo, teléfono y contraseña del nuevo administrador.';
          setTimeout(() => this.adminRegistroMsg = '', 3500);
          return;
        }
        if (passAdmin.length < 12 || !(/[a-zA-Z]/.test(passAdmin) && /[0-9]/.test(passAdmin))) {
          this.adminRegistroMsg = '⚠️ La contraseña debe tener mínimo 12 caracteres combinando letras y números.';
          setTimeout(() => this.adminRegistroMsg = '', 4000);
          return;
        }
        const usuarioAuto = nomAdmin.toLowerCase()
          .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
          .replace(/[^a-z0-9]/g, '.')
          .replace(/\.+/g, '.')
          .replace(/^\.|\.$/g, '');

        const res = await window.DB.registrarAdministrador({
          nombre: nomAdmin,
          usuario: usuarioAuto,
          password: passAdmin,
          telefono: telAdmin
        });

        if (res && res.ok) {
          this.adminRegistroMsg = `✓ Administrador ${nomAdmin} registrado exitosamente (@${usuarioAuto}).`;
          this.adminPadresVersion++;
          if (window.DB.sincronizar) await window.DB.sincronizar(this);
          setTimeout(() => this.adminRegistroMsg = '', 4000);
          this.adminRegistro = {
            tipo: 'con_padre',
            hijos: [{ nombre: '', edad: '', catKey: '', pos: 'MED' }],
            jugadorNombre: '', jugadorEdad: '', catKey: '', jugadorDorsal: '', jugadorPos: 'MED',
            padreNombre: '', padreUsuario: '', padrePassword: '', telefonoPadre: ''
          };
        } else {
          this.adminRegistroMsg = '❌ Error al registrar administrador: ' + ((res && res.error) || 'No se pudo crear la cuenta.');
          setTimeout(() => this.adminRegistroMsg = '', 5000);
        }
      } else {
        // Adulto sin tutor
        this.autoAsignarSubAdmin();
        const edadNum = parseInt(r.jugadorEdad, 10) || 0;
        if (!r.jugadorNombre.trim() || edadNum < 3 || edadNum > 50 || !r.catKey) {
          this.adminRegistroMsg = '⚠️ Ingresa el nombre y una edad válida (3 a 50 años) para asignar su categoría.';
          setTimeout(() => this.adminRegistroMsg = '', 3500);
          return;
        }
        const passAdulto = (r.padrePassword || '').trim();
        const telAdulto = (r.telefonoPadre || '').trim();
        if (!passAdulto || !telAdulto) {
          this.adminRegistroMsg = '⚠️ Completa el teléfono y la contraseña del jugador.';
          setTimeout(() => this.adminRegistroMsg = '', 3500);
          return;
        }
        if (passAdulto.length < 12 || !(/[a-zA-Z]/.test(passAdulto) && /[0-9]/.test(passAdulto))) {
          this.adminRegistroMsg = '⚠️ La contraseña debe tener mínimo 12 caracteres combinando letras y números.';
          setTimeout(() => this.adminRegistroMsg = '', 4000);
          return;
        }

        const arr = this.playersDe(r.catKey);
        const player = this.crearOReciclarJugador(r.catKey, r.jugadorNombre.trim(), 0, r.jugadorPos, edadNum);
        arr.push(player);
        this.persistPlantilla(r.catKey);
        this.registrarAccion({ tipo: 'add', catKey: r.catKey, playerId: player.id, player });

        const res = window.DB.registrarPadre(
          r.jugadorNombre.trim(),
          r.jugadorNombre.trim(),
          passAdulto,
          r.catKey,
          player.id,
          telAdulto,
          edadNum,
          player.name,
          player.pos
        );

        if (!res || !res.ok) {
          this.adminRegistroMsg = '❌ Error al crear usuario del jugador: ' + (res ? res.msg : 'Usuario duplicado');
          arr.pop(); this.persistPlantilla(r.catKey);
          return;
        }

        this.adminPadresVersion++;
        this.comentariosVersion++;
        this.adminRegistroMsg = '✓ ' + player.name + ' inscrito exitosamente en ' + r.catKey.toUpperCase() + '.';
        setTimeout(() => this.adminRegistroMsg = '', 4000);

        this.adminRegistro = {
          tipo: 'con_padre',
          hijos: [{ nombre: '', edad: '', catKey: '', pos: 'MED' }],
          jugadorNombre: '', jugadorEdad: '', catKey: '', jugadorDorsal: '', jugadorPos: 'MED',
          padreNombre: '', padreUsuario: '', padrePassword: '', telefonoPadre: ''
        };
      }
    },

    async abrirAgregarHijoModal(padre) {
      if (!padre || !padre.id) return;
      const nombre = prompt(`Añadir otro hijo a la familia de "${padre.nombre}":\n\nIngresa el Nombre Completo del hijo:`);
      if (!nombre || !nombre.trim()) return;
      const edadStr = prompt(`Ingresa la Edad en años de "${nombre.trim()}":`);
      const edad = parseInt(edadStr, 10);
      if (isNaN(edad) || edad < 3 || edad > 50) {
        alert('Edad inválida. Debe ser entre 3 y 50 años.');
        return;
      }
      const pos = prompt(`Posición preferida de "${nombre.trim()}":\n(DEL = Delantero, MED = Mediocampo, DEF = Defensa, POR = Portero)`, 'MED') || 'MED';
      
      const res = await window.DB.agregarHijoAPadre(padre.id, {
        nombre: nombre.trim(),
        edad: edad,
        pos: pos.toUpperCase()
      });

      if (res && res.ok) {
        if (res.hijo && res.hijo.catKey) {
          const arr = this.playersDe(res.hijo.catKey);
          if (!arr.find(p => p.id === res.hijo.id)) {
            arr.push({
              id: res.hijo.id,
              name: res.hijo.nombre,
              catKey: res.hijo.catKey,
              edad: res.hijo.edad,
              dorsal: res.hijo.dorsal || 0,
              pos: res.hijo.pos || 'MED',
              foto: 'img/escudo.png'
            });
          }
        }
        alert(`✓ ${nombre.trim()} agregado con éxito a la familia de ${padre.nombre} en ${res.hijo.catLabel || res.hijo.catKey}.`);
        if (window.DB && window.DB.sincronizar) {
          await window.DB.sincronizar(this);
        }
        this.adminPadresVersion++;
      } else {
        alert('Error: ' + ((res && res.error) || 'No se pudo agregar el hijo.'));
      }
    },

    async iniciarCambioSub(player, catOrigen) {
      const orig = catOrigen || player.catKey || this.categoriaActual;
      const opciones = this.categoriasTorneo.filter(c => c.key !== orig);
      const texto = opciones.map((c, i) => `${i + 1}. ${c.label} (${c.key})`).join('\n');
      const sel = prompt(`Transferir a ${player.name} de Categoría (Sub):\n\nEscribe el número de la categoría destino:\n\n${texto}`);
      if (!sel) return;
      let destino = null;
      const num = parseInt(sel.trim(), 10);
      if (!isNaN(num) && opciones[num - 1]) {
        destino = opciones[num - 1].key;
      } else {
        const q = sel.trim().toLowerCase();
        const cFound = opciones.find(c => c.key.toLowerCase() === q || c.label.toLowerCase().includes(q));
        if (cFound) destino = cFound.key;
      }
      if (!destino) {
        alert('Categoría no válida seleccionada.');
        return;
      }
      await this.confirmarCambioSub(player, destino, orig);
    },

    async confirmarCambioSub(player, catDestino, catOrigen = null) {
      const orig = catOrigen || player.catKey || this.categoriaActual;
      if (!orig || !catDestino || orig === catDestino) return;
      const cDestObj = this.categoriasTorneo.find(c => c.key === catDestino);
      const labelDest = cDestObj ? cDestObj.label : catDestino.toUpperCase();
      const cOrigObj = this.categoriasTorneo.find(c => c.key === orig);
      const labelOrig = cOrigObj ? cOrigObj.label : orig.toUpperCase();

      if (!confirm(`¿Confirmas transferir a ${player.name} de ${labelOrig} a ${labelDest}?`)) return;

      const res = await window.DB.cambiarCategoriaJugador(orig, catDestino, player.id);
      if (res && res.ok) {
        alert(`✓ ${player.name} fue transferido con éxito a ${res.labelDestino || labelDest}.\n\nDorsal asignado: ${res.dorsal > 0 ? ('#' + res.dorsal) : '#S/N (Disponible para asignar)'}`);
        const arrOrig = this.playersDe(orig);
        const idx = arrOrig.findIndex(p => p.id === player.id);
        if (idx >= 0) arrOrig.splice(idx, 1);
        this.persistPlantilla(orig);

        const arrDest = this.playersDe(catDestino);
        player.catKey = catDestino;
        player.dorsal = res.dorsal !== undefined ? res.dorsal : player.dorsal;
        arrDest.push(player);
        this.persistPlantilla(catDestino);

        if (this.playerModal && this.playerModal.id === player.id) {
          this.playerModal = null;
        }
        this.subDestinoTransfer = '';
        this.adminPadresVersion++;
        setTimeout(() => { if (window.DB && window.DB.sincronizar) window.DB.sincronizar(this); }, 500);
      } else {
        alert('Error al transferir: ' + ((res && res.error) || 'No se pudo completar la transferencia.'));
      }
    },

    async eliminarPadre(id) {
      if (!confirm('¿Dar de baja a esta familia (Ley 81)? Se eliminarán definitivamente los datos del padre/madre y los datos del jugador de la base de datos.')) return;
      const padre = this.listaPadres.find(p => p.id === id);
      if (padre && padre.hijoCat && padre.hijoId) {
        const arr = this.playersDe(padre.hijoCat);
        const idx = arr.findIndex(pl => pl.id === padre.hijoId);
        if (idx >= 0) arr.splice(idx, 1);
      }
      window.DB.eliminarPadre(id);
      this.adminPadresVersion++; this.comentariosVersion++;
      setTimeout(() => { if (window.DB && window.DB.sincronizar) window.DB.sincronizar(this); }, 400);
    },

    /* ========== GESTIÓN DE EQUIPO ADMINISTRATIVO (MÁXIMO 3) ========== */
    async registrarAdmin() {
      if (!this.esSuperAdmin) {
        alert('Solo el Administrador Principal puede registrar otros administradores.');
        return;
      }
      this.nuevoAdminMsg = '';
      const nom = (this.nuevoAdmin.nombre || '').trim();
      const usu = (this.nuevoAdmin.usuario || '').trim().toLowerCase();
      const pass = (this.nuevoAdmin.password || '').trim();

      if (!nom) {
        this.nuevoAdminMsg = 'El nombre completo es obligatorio.';
        return;
      }
      if (!usu) {
        this.nuevoAdminMsg = 'El usuario de acceso es obligatorio.';
        return;
      }
      if (pass.length < 12) {
        this.nuevoAdminMsg = 'La contraseña debe tener al menos 12 caracteres.';
        return;
      }
      if (this.listaAdmins.length >= 3) {
        this.nuevoAdminMsg = 'Límite alcanzado: máximo 3 administradores.';
        return;
      }

      const res = await window.DB.registrarAdministrador({
        nombre: nom,
        usuario: usu,
        password: pass
      });

      if (res && res.ok) {
        this.nuevoAdmin = { nombre: '', usuario: '', password: '', usuarioManual: false };
        this.nuevoAdminMsg = '✓ Administrador registrado con éxito.';
        this.adminPadresVersion++;
        setTimeout(() => { if (window.DB && window.DB.sincronizar) window.DB.sincronizar(this); }, 400);
        setTimeout(() => { this.nuevoAdminMsg = ''; }, 3500);
      } else {
        this.nuevoAdminMsg = (res && res.error) || 'Error al crear el administrador.';
      }
    },

    async eliminarAdmin(id, usuario) {
      if (!this.esSuperAdmin) {
        alert('Solo el Administrador Principal puede eliminar cuentas de administrador.');
        return;
      }
      if (this.listaAdmins.length <= 1) {
        alert('No puedes eliminar al único administrador del sistema.');
        return;
      }
      if (this.currentUser && (this.currentUser.id == id || this.currentUser.usuario == usuario)) {
        alert('No puedes eliminar tu propia cuenta activa de administrador.');
        return;
      }
      if (!confirm(`¿Estás seguro de que deseas eliminar el acceso administrativo a @${usuario}? Esta acción no se puede deshacer.`)) {
        return;
      }

      const res = await window.DB.eliminarAdministrador(id);
      if (res && res.ok) {
        this.adminPadresVersion++;
        setTimeout(() => { if (window.DB && window.DB.sincronizar) window.DB.sincronizar(this); }, 400);
        alert(`Administrador @${usuario} eliminado exitosamente.`);
      } else {
        alert('Error al eliminar administrador: ' + ((res && res.error) || 'No se pudo eliminar.'));
      }
    },

    async designarComoSuperAdmin(adm) {
      if (!this.esSuperAdmin) {
        alert('Solo el Super Administrador puede designar a otros usuarios como Super Admin.');
        return;
      }
      if (!confirm(`¿Estás seguro de ascender a "${adm.nombre}" (@${adm.usuario}) a Super Administrador? Tendrá acceso total a todas las políticas y configuraciones del club.`)) {
        return;
      }
      const res = await window.DB.designarSuperAdmin(adm.id);
      if (res && res.ok) {
        adm.es_top = 1;
        adm.permisos = ['*'];
        this.adminPadresVersion++;
        alert(`✓ "${adm.nombre}" ahora es Super Administrador del club.`);
      } else {
        alert('Error: ' + ((res && res.error) || 'No se pudo designar como Super Admin.'));
      }
    },

    /* ========== POLÍTICAS DE ACCESO ADMINISTRATIVO (RBAC) ========== */
    abrirModalPoliticas(adm) {
      if (!this.esSuperAdmin) {
        alert('Solo el Administrador Principal puede configurar políticas.');
        return;
      }
      let perms = [];
      if (Array.isArray(adm.permisos)) {
        perms = [...adm.permisos];
      } else if (typeof adm.permisos === 'string') {
        try { perms = JSON.parse(adm.permisos); } catch(e) { perms = []; }
      }
      this.adminEditandoPoliticas = {
        id: adm.id,
        nombre: adm.nombre,
        usuario: adm.usuario,
        permisos: perms
      };
      this.msgPoliticasAdmin = '';
      this.modalPoliticasAdmin = true;
    },
    async guardarPoliticasAdmin() {
      if (!this.adminEditandoPoliticas || !this.adminEditandoPoliticas.id) return;
      this.msgPoliticasAdmin = '';
      const res = await window.DB.actualizarPoliticasAdmin(
        this.adminEditandoPoliticas.id,
        this.adminEditandoPoliticas.permisos || []
      );
      if (res && res.ok) {
        this.msgPoliticasAdmin = '✓ Políticas guardadas con éxito.';
        this.adminPadresVersion++;
        setTimeout(() => { if (window.DB && window.DB.sincronizar) window.DB.sincronizar(this); }, 300);
        setTimeout(() => { this.modalPoliticasAdmin = false; }, 1200);
      } else {
        this.msgPoliticasAdmin = (res && res.error) || 'Error al guardar políticas.';
      }
    },

    /* ========== ACCIONES DESACOPLADAS PARA FAMILIAS (TELÉFONO Y CONTRASEÑA) ========== */
    abrirModalTelefonoPadre(padre) {
      if (!padre) return;
      this.padreTelefonoForm = {
        id: padre.id,
        nombre: padre.nombre || '',
        usuario: padre.usuario || '',
        telefono: padre.telefono || ''
      };
      this.msgTelefonoPadre = '';
      this.modalTelefonoPadre = true;
    },
    async guardarTelefonoPadre() {
      if (!this.padreTelefonoForm.id) return;
      this.msgTelefonoPadre = '';
      const tel = (this.padreTelefonoForm.telefono || '').trim();
      const res = await window.DB.actualizarTelefonoPadre(this.padreTelefonoForm.id, tel);
      if (res && res.ok) {
        this.msgTelefonoPadre = '✓ Teléfono actualizado correctamente.';
        this.adminPadresVersion++;
        setTimeout(() => { if (window.DB && window.DB.sincronizar) window.DB.sincronizar(this); }, 300);
        setTimeout(() => { this.modalTelefonoPadre = false; }, 1200);
      } else {
        this.msgTelefonoPadre = (res && res.error) || 'Error al actualizar teléfono.';
      }
    },

    abrirModalPasswordPadre(padre) {
      if (!padre) return;
      this.padrePasswordForm = {
        id: padre.id,
        nombre: padre.nombre || '',
        usuario: padre.usuario || '',
        password: '',
        mostrar: false
      };
      this.msgPasswordPadre = '';
      this.modalPasswordPadre = true;
    },
    async guardarPasswordPadre() {
      if (!this.padrePasswordForm.id) return;
      this.msgPasswordPadre = '';
      const pass = (this.padrePasswordForm.password || '').trim();
      if (!pass || pass.length < 12 || !(/[a-zA-Z]/.test(pass) && /[0-9]/.test(pass))) {
        this.msgPasswordPadre = 'La contraseña debe tener al menos 12 caracteres combinando letras y números.';
        return;
      }
      const res = await window.DB.restablecerPasswordPadre(this.padrePasswordForm.id, pass);
      if (res && res.ok) {
        this.msgPasswordPadre = '✓ Contraseña restablecida con éxito.';
        this.adminPadresVersion++;
        setTimeout(() => { this.modalPasswordPadre = false; }, 1200);
      } else {
        this.msgPasswordPadre = (res && res.error) || 'Error al restablecer contraseña.';
      }
    },

    /* ========== EDICIÓN COMPLETA DE FAMILIAS (TELÉFONO, EDADES, CATEGORÍA, CONTRASEÑA) ========== */
    abrirModalEditarPadre(padre) {
      if (!padre) return;
      let hijos = [];
      if (Array.isArray(padre.hijos) && padre.hijos.length > 0) {
        hijos = padre.hijos.map(h => ({
          id: h.id || 0,
          nombre: h.nombre || '',
          edad: parseInt(h.edad, 10) || 0,
          catKey: h.catKey || padre.hijoCat || 'sub6',
          sub: h.sub || h.catLabel || '',
          dorsal: h.dorsal || 0,
          pos: h.pos || 'MED'
        }));
      } else if (padre.hijoId) {
        hijos = [{
          id: padre.hijoId,
          nombre: padre.hijoNombre || '',
          edad: parseInt(padre.hijoEdad, 10) || 0,
          catKey: padre.hijoCat || 'sub6',
          sub: padre.sub || '',
          dorsal: padre.hijoDorsal || 0,
          pos: padre.hijoPos || 'MED'
        }];
      }

      this.padreEditando = {
        id: padre.id,
        nombre: padre.nombre || '',
        usuario: padre.usuario || '',
        telefono: padre.telefono || '',
        nuevaPassword: '',
        hijos: hijos,
        hijoCat: padre.hijoCat || 'sub6',
        metodoPago: padre.metodoPago || 'linea',
        fechaVencimiento: padre.fechaVencimiento || null
      };
      this.mostrarPassPadreEdit = false;
      this.editPadreMsg = '';
      this.modalEditarPadre = true;
    },

    cerrarModalEditarPadre() {
      this.modalEditarPadre = false;
      this.padreEditando = null;
      this.editPadreMsg = '';
      this.mostrarPassPadreEdit = false;
    },

    recalcularCatHijo(hijo) {
      if (!hijo) return;
      const edad = parseInt(hijo.edad, 10);
      if (isNaN(edad) || edad < 3 || edad > 50) return;
      const claves = this.categoriasTorneo.map(c => c.key);
      const numericas = [];
      claves.forEach(k => {
        const m = k.match(/^sub_?(\d+)$/i);
        if (m) numericas.push({ lim: parseInt(m[1], 10), key: k });
      });
      numericas.sort((a, b) => a.lim - b.lim);
      let elegida = null;
      for (let i = 0; i < numericas.length; i++) {
        if (edad <= numericas[i].lim) { elegida = numericas[i].key; break; }
      }
      if (!elegida) elegida = claves.includes('submayor') ? 'submayor' : (claves[claves.length - 1] || 'sub6');
      hijo.catKey = elegida;
    },

    async guardarEdicionPadre() {
      if (!this.padreEditando) return;
      this.editPadreMsg = '';

      const p = this.padreEditando;
      if (p.nuevaPassword && p.nuevaPassword.trim() !== '') {
        if (p.nuevaPassword.trim().length < 12) {
          this.editPadreMsg = 'La nueva contraseña debe tener al menos 12 caracteres.';
          return;
        }
      }

      for (let i = 0; i < (p.hijos || []).length; i++) {
        const h = p.hijos[i];
        if (!h.nombre || !h.nombre.trim()) {
          this.editPadreMsg = `El nombre del hijo #${i + 1} no puede estar vacío.`;
          return;
        }
        const edad = parseInt(h.edad, 10);
        if (isNaN(edad) || edad < 3 || edad > 50) {
          this.editPadreMsg = `La edad del hijo #${i + 1} debe estar entre 3 y 50 años.`;
          return;
        }
        if (!h.catKey) {
          this.recalcularCatHijo(h);
        }
      }

      const payload = {
        id: p.id,
        rol: 'padre',
        nombre: p.nombre,
        usuario: p.usuario,
        telefono: (p.telefono || '').trim(),
        password: (p.nuevaPassword || '').trim(),
        hijoCat: (p.hijos && p.hijos[0] && p.hijos[0].catKey) ? p.hijos[0].catKey : p.hijoCat,
        hijos: p.hijos,
        metodoPago: p.metodoPago,
        fechaVencimiento: p.fechaVencimiento
      };

      const res = await window.DB.actualizarPadreCompleto(payload);
      if (res && res.ok) {
        this.editPadreMsg = '✓ Familia actualizada con éxito.';
        this.adminPadresVersion++;
        setTimeout(() => { if (window.DB && window.DB.sincronizar) window.DB.sincronizar(this); }, 400);
        setTimeout(() => {
          this.cerrarModalEditarPadre();
        }, 1200);
      } else {
        this.editPadreMsg = (res && res.error) || 'Error al guardar los cambios de la familia.';
      }
    },

    /* ========== 15) TEMPORIZADOR MENSUAL (DÍA 30 DE CADA MES) ========== */
    calcularFechaDia30(avanzarMes = false) {
      const hoy = new Date();
      let y = hoy.getFullYear();
      let m = hoy.getMonth() + 1;
      if (avanzarMes || hoy.getDate() > 30) {
        m++;
        if (m > 12) { m = 1; y++; }
      }
      const maxDia = new Date(y, m, 0).getDate();
      const dia = Math.min(30, maxDia);
      return y + '-' + String(m).padStart(2, '0') + '-' + String(dia).padStart(2, '0');
    },
    proximaQuincena() {
      const hoy = new Date();
      let y = hoy.getFullYear();
      let m = hoy.getMonth();
      if (hoy.getDate() > 30) {
        m++;
        if (m > 11) { m = 0; y++; }
      }
      const maxDia = new Date(y, m + 1, 0).getDate();
      const proxima = new Date(y, m, Math.min(30, maxDia));
      return proxima.toLocaleDateString('es-PA', { day: '2-digit', month: 'short', year: 'numeric' });
    },
    diasParaQuincena() {
      const hoy = new Date();
      hoy.setHours(0,0,0,0);
      let y = hoy.getFullYear();
      let m = hoy.getMonth();
      if (hoy.getDate() > 30) {
        m++;
        if (m > 11) { m = 0; y++; }
      }
      const maxDia = new Date(y, m + 1, 0).getDate();
      const proxima = new Date(y, m, Math.min(30, maxDia));
      const diff = Math.ceil((proxima - hoy) / (1000 * 60 * 60 * 24));
      return diff < 0 ? 0 : diff;
    },

    /* ========== 16) PAGOS DEL CLUB ========== */
    filtroPagoCat: '', busquedaPagoPadre: '', busquedaPagoPadreEf: '', busquedaPagoPadreLi: '', modalPagoActivo: false, pagoActual: { id: null, concepto: 'mensualidad', monto: 15.00, fecha: new Date().toISOString().slice(0,10), referencia: '' },
    pagoManualTipo: 'efectivo', // 'efectivo' o 'linea'
    pagoManual: { padreId: '', concepto: 'mensualidad', monto: 15, fecha: new Date().toISOString().slice(0,10), referencia: '', metodo: 'efectivo' },
    
    get padresParaCobroEfectivo() {
      try {
        let lista = this.listaPadres.filter(p => p.metodoPago === 'efectivo');
        if (this.filtroPagoCat) lista = lista.filter(p => p.hijoCat === this.filtroPagoCat || p.catKey === this.filtroPagoCat);
        if (this.busquedaPagoPadre) {
          const busq = this.busquedaPagoPadre.toLowerCase();
          lista = lista.filter(p => (p.nombre || '').toLowerCase().includes(busq));
        }
        return lista;
      } catch (e) { return []; }
    },
    get padresParaCobroLinea() {
      try {
        let lista = this.listaPadres.filter(p => p.metodoPago === 'linea');
        if (this.filtroPagoCat) lista = lista.filter(p => p.hijoCat === this.filtroPagoCat || p.catKey === this.filtroPagoCat);
        if (this.busquedaPagoPadre) {
          const busq = this.busquedaPagoPadre.toLowerCase();
          lista = lista.filter(p => (p.nombre || '').toLowerCase().includes(busq));
        }
        return lista;
      } catch (e) { return []; }
    },
    
    filtroEstadoCuenta: '', // ''=todos, 'vencido', 'aldia', 'pendiente'
    filtroMetodoCuenta: '', // ''=todos, 'efectivo', 'linea'
    
    get estadoDeCuentas() {
      void this.adminPadresVersion; void this.comentariosVersion;
      try {
        let lista = this.listaPadres.map(p => {
           let estado = 'pendiente';
           const hoy = new Date().toISOString().slice(0,10);
           if (p.fechaVencimiento) {
              if (p.fechaVencimiento < hoy) estado = 'vencido';
              else estado = 'aldia';
           }
           if (window.DB.obtenerComprobantes().some(c => c.padreId == p.id && (c.estado === 'en_revision' || c.estado === 'pendiente'))) {
               estado = 'amarillo';
           }
           return { ...p, estadoPago: estado };
        });
        
        lista.sort((a,b) => {
           const orden = { 'amarillo': 0, 'vencido': 1, 'pendiente': 2, 'aldia': 3 };
           if (orden[a.estadoPago] !== orden[b.estadoPago]) return (orden[a.estadoPago] ?? 9) - (orden[b.estadoPago] ?? 9);
           return (a.nombre || '').localeCompare(b.nombre || '');
        });
        
        if (this.filtroEstadoCuenta) lista = lista.filter(p => p.estadoPago === this.filtroEstadoCuenta);
        if (this.filtroMetodoCuenta) lista = lista.filter(p => p.metodoPago === this.filtroMetodoCuenta);
        if (this.filtroPagoCat) lista = lista.filter(p => p.hijoCat === this.filtroPagoCat || p.catKey === this.filtroPagoCat);
        if (this.busquedaPagoPadre) {
          const busq = this.busquedaPagoPadre.toLowerCase();
          lista = lista.filter(p => (p.nombre || '').toLowerCase().includes(busq));
        }
        return lista;
      } catch(e) { return []; }
    },

    cambiarMetodoPago(padreId, nuevoMetodo) {
      if (!confirm(`¿Cambiar método de pago a ${nuevoMetodo === "efectivo" ? "Efectivo" : "En Línea"}?`)) return;
      const padre = this.listaPadres.find(p => p.id == padreId);
      if (!padre) return;
      padre.metodoPago = nuevoMetodo;
      this.adminPadresVersion++; this.comentariosVersion++; 
      window.DB.post("usuarios/actualizarPago", {
          id: padre.id,
          telefono: padre.telefono,
          metodoPago: nuevoMetodo,
          fechaVencimiento: padre.fechaVencimiento
      });
    },

    registrarPagoManual() {
      if (!this.esAdmin) return;
      if (!this.pagoManual.padreId) { alert(' Selecciona un padre'); return; }
      if (!this.pagoManual.monto || this.pagoManual.monto <= 0) { alert(' Ingresa un monto válido'); return; }
      const padre = this.listaPadres.find(p => p.id == this.pagoManual.padreId);
      if (!padre) { alert(' Padre no encontrado'); return; }
      
      const conceptoLabel = this.pagoManual.concepto === 'inscripcion' ? 'Inscripción' : (this.pagoManual.concepto === 'mensualidad' ? 'Mensualidad' : 'Otro');
      const metodo = this.pagoManualTipo;
      
      // Guardar el historial de pago
      window.DB.guardarPagoClub({
        padreId: padre.id,
        padreNombre: padre.nombre,
        concepto: conceptoLabel,
        monto: parseFloat(this.pagoManual.monto),
        fecha: this.pagoManual.fecha,
        referencia: this.pagoManual.referencia,
        metodo: metodo,
        estado: 'validado'
      });

      // Actualizar fecha de vencimiento +15 días
      const nuevaFecha = this.calcularFechaDia30(true);
      
      padre.fechaVencimiento = nuevaFecha;
      padre.metodoPago = metodo;
      
      window.DB.actualizarPadre(padre.id, { 
        fechaVencimiento: nuevaFecha,
        metodoPago: metodo
      });

      this.adminPadresVersion++; this.comentariosVersion++;
      this.pagoManual = { padreId: '', concepto: 'mensualidad', monto: 15, fecha: new Date().toISOString().slice(0,10), referencia: '', metodo: 'efectivo' };
      alert('Pago registrado correctamente. Próximo vencimiento: ' + nuevaFecha);
    },
    
    
    
          filtroEfectivo: 'todos', // todos, aldia, pendientes, vencidos
      get padresParaCobroEfectivoFiltrados() {
          let lista = this.estadoDeCuentas.filter(p => p.metodoPago === 'efectivo');
          if (this.filtroEfectivo === 'aldia') lista = lista.filter(p => p.estadoPago === 'aldia');
          else if (this.filtroEfectivo === 'pendientes') lista = lista.filter(p => p.estadoPago === 'pendiente');
          else if (this.filtroEfectivo === 'vencidos') lista = lista.filter(p => p.estadoPago === 'vencido');
          else if (this.filtroEfectivo === 'solicitudes') lista = lista.filter(p => p.estadoPago === 'amarillo');
          
          if (this.busquedaPagoPadreEf) {
              const q = this.busquedaPagoPadreEf.toLowerCase();
              lista = lista.filter(p => (p.nombre||'').toLowerCase().includes(q));
          }
          return lista;
      },
            filtroLinea: 'todos', // todos, aldia, pendientes, vencidos
      get padresParaCobroLineaFiltrados() {
          let lista = this.estadoDeCuentas.filter(p => p.metodoPago === 'linea' || p.estadoPago === 'amarillo');
          if (this.filtroLinea === 'aldia') lista = lista.filter(p => p.estadoPago === 'aldia');
          else if (this.filtroLinea === 'pendientes') lista = lista.filter(p => p.estadoPago === 'pendiente');
          else if (this.filtroLinea === 'vencidos') lista = lista.filter(p => p.estadoPago === 'vencido');
          else if (this.filtroLinea === 'solicitudes') lista = lista.filter(p => p.estadoPago === 'amarillo');
          
          if (this.busquedaPagoPadreLi) {
              const q = this.busquedaPagoPadreLi.toLowerCase();
              lista = lista.filter(p => (p.nombre||'').toLowerCase().includes(q));
          }
          return lista;
      },
      
      comprobanteActivo: null, // Para el modal de imagen
            tieneComprobante(padreId) {
          return window.DB.obtenerComprobantes().some(c => c.padreId == padreId && (c.estado === 'en_revision' || c.estado === 'pendiente'));
      },
      verComprobante(padreId) {
          const comp = window.DB.obtenerComprobantes().find(c => c.padreId == padreId && (c.estado === 'en_revision' || c.estado === 'pendiente'));
          if (comp) {
              this.comprobanteActivo = comp;
          } else {
              alert('Este padre no tiene comprobantes pendientes de validación.');
          }
      },
      cerrarComprobante() {
          this.comprobanteActivo = null;
      },
                        cambiarMetodoPago(padreId, nuevoMetodo) {
          const padre = this.listaPadres.find(p => p.id == padreId);
          if (padre) {
              padre.metodo_pago = nuevoMetodo;
              if(window.DB.post) {
                  window.DB.post('usuarios/actualizarPago', { id: padre.id, telefono: padre.telefono, fechaVencimiento: padre.fechaVencimiento, metodoPago: nuevoMetodo });
              }
              this.adminPadresVersion++; this.comentariosVersion++;
          }
      },
                  toggleBloqueo(padreId) {
          const padre = this.listaPadres.find(p => p.id == padreId);
          if (padre) {
              padre.bloqueado = padre.bloqueado === 1 ? 0 : 1;
              if(window.DB.post) {
                  window.DB.post('usuarios/bloquear', { id: padre.id, bloqueado: padre.bloqueado });
              }
              this.adminPadresVersion++; this.comentariosVersion++;
          }
      },
      async deshacerPago(padreId) {
          if (!confirm('¿Estás seguro de deshacer este pago y marcar al usuario como PENDIENTE?')) return;
          const padre = this.listaPadres.find(p => p.id == padreId);
          if (padre) {
              const f = new Date();
              f.setDate(f.getDate() - 35);
              const fechaPasada = f.toISOString().slice(0, 10);
              padre.fechaVencimiento = fechaPasada;

              if (window.DB && typeof window.DB.deshacerPagoPadre === 'function') {
                  window.DB.deshacerPagoPadre(padre.id, fechaPasada);
              }

              if (window.DB && window.DB.post) {
                  await window.DB.post('usuarios/actualizarPago', {
                      id: padre.id,
                      telefono: padre.telefono,
                      fechaVencimiento: fechaPasada,
                      metodoPago: padre.metodoPago || padre.metodo_pago,
                      deshacer: true
                  });
              }
              this.adminPadresVersion++; this.comentariosVersion++;

              const toast = document.createElement('div');
              toast.className = 'fixed top-4 right-4 bg-amber-500 text-black font-black py-2.5 px-4 rounded-xl shadow-2xl z-50 border border-black/20';
              toast.innerText = '✓ Pago deshecho. Estado actualizado a Pendiente / Vencido';
              document.body.appendChild(toast);
              setTimeout(() => toast.remove(), 2500);
          }
      },
      aprobarPagoDirecto(padreId) {
          const comp = window.DB.obtenerComprobantes().find(c => c.padreId == padreId && (c.estado === 'en_revision' || c.estado === 'pendiente'));
          if (comp) {
              window.DB.validarComprobante(comp.id, true);
          }
          const padre = this.listaPadres.find(p => p.id == padreId);
          if (padre) {
              padre.fechaVencimiento = this.calcularFechaDia30(true);
              // Save to database directly
              if(window.DB.post) {
                  window.DB.post('usuarios/actualizarPago', { id: padre.id, fechaVencimiento: padre.fechaVencimiento, metodoPago: padre.metodo_pago });
              }
              this.adminPadresVersion++; this.comentariosVersion++;
              
              const toast = document.createElement('div');
              toast.className = 'fixed top-4 right-4 bg-nico-green text-white font-bold py-2 px-4 rounded-xl shadow-lg z-50 animate-bounce';
              toast.innerText = 'Pago Aprobado Exitosamente';
              document.body.appendChild(toast);
              setTimeout(() => toast.remove(), 2000);
          }
      },
      procesarComprobanteDirecto(padreId, aprobar) {
          const comp = window.DB.obtenerComprobantes().find(c => c.padreId == padreId && (c.estado === 'en_revision' || c.estado === 'pendiente'));
          if(!comp) return;
          if(!aprobar && !confirm('¿Estás seguro de rechazar este pago?')) return;
          
          window.DB.validarComprobante(comp.id, aprobar);
          
          if (aprobar) {
              const padre = this.listaPadres.find(p => p.id == padreId);
              if (padre) {
                  padre.fechaVencimiento = this.calcularFechaDia30(true);
                  if(window.DB.post) {
                      window.DB.post('usuarios/actualizarPago', { id: padre.id, telefono: padre.telefono, fechaVencimiento: padre.fechaVencimiento, metodoPago: padre.metodo_pago });
                  }
                  this.adminPadresVersion++; this.comentariosVersion++;
                  // Muestra una notificación rápida sin bloquear (toast)
                  const toast = document.createElement('div');
                  toast.className = 'fixed top-4 right-4 bg-nico-green text-white font-bold py-2 px-4 rounded-xl shadow-lg z-50 animate-bounce';
                  toast.innerText = 'Pago Aprobado (Al Día)';
                  document.body.appendChild(toast);
                  setTimeout(() => toast.remove(), 2000);
              }
          }
      },
      procesarComprobante(aprobar) {
          if(!this.comprobanteActivo) return;
          window.DB.validarComprobante(this.comprobanteActivo.id, aprobar);
          
          if (aprobar) {
              const padre = this.listaPadres.find(p => p.id == this.comprobanteActivo.padreId);
              if (padre) {
                  padre.fechaVencimiento = this.calcularFechaDia30(true);
                  if(window.DB.post) {
                      window.DB.post('usuarios/actualizarPago', { id: padre.id, telefono: padre.telefono, fechaVencimiento: padre.fechaVencimiento, metodoPago: padre.metodo_pago });
                  }
                  this.adminPadresVersion++; this.comentariosVersion++;
                  alert('Pago aprobado. El estado del padre ha cambiado a Pagado (Al día).');
              }
          } else {
              alert('Comprobante rechazado. El padre deberá subirlo de nuevo.');
          }
          this.cerrarComprobante();
      },
      get morososList() {
          return this.estadoDeCuentas.filter(p => p.estadoPago === 'vencido');
      },
      abrirModalPago(id) {
          this.pagoActual = { id: id, concepto: 'mensualidad', monto: 15.00, fecha: new Date().toISOString().slice(0,10), referencia: '' };
          this.modalPagoActivo = true;
      },
      cerrarModalPago() {
          this.modalPagoActivo = false;
      },
      async guardarPagoModal() {
          if(!this.pagoActual.id) return;
          const padre = this.listaPadres.find(p => p.id == this.pagoActual.id);
          if(!padre) return;
          const fechaVenc = this.calcularFechaDia30(true);
          try {
              const res = await window.DB.post('usuarios/actualizarPago', {
                  id: padre.id,
                  metodoPago: this.pagoActual.metodo || 'linea',
                  telefono: padre.telefono || '',
                  fechaVencimiento: fechaVenc
              });
              if (res && res.ok) {
                  padre.fechaVencimiento = res.fechaVencimiento || fechaVenc;
                  padre.metodoPago = this.pagoActual.metodo || 'linea';
                  if (window.DB && typeof window.DB.obtenerPadres === 'function') {
                      const pDb = window.DB.obtenerPadres().find(x => x.id == padre.id);
                      if (pDb) {
                          pDb.fechaVencimiento = padre.fechaVencimiento;
                          pDb.metodoPago = padre.metodoPago;
                      }
                  }
                  if (window.DB && typeof window.DB.guardarPagoClub === 'function') {
                      window.DB.guardarPagoClub({
                          padreId: padre.id,
                          padreNombre: padre.nombre,
                          monto: this.pagoActual.monto || 15.00,
                          metodo: this.pagoActual.metodo || 'linea',
                          fecha: new Date().toISOString().split('T')[0]
                      });
                  }
                  this.adminPadresVersion++; this.comentariosVersion++;
                  alert('Pago registrado correctamente. El estado pasará a VERDE.');
                  this.cerrarModalPago();
              } else {
                  alert('Error al registrar pago en base de datos: ' + ((res && res.error) || 'Error de conexión'));
              }
          } catch(e) {
              alert('Error de conexión al registrar pago.');
          }
      },
      async bloquearCuenta(id, estado = true) { 
        if(estado && !confirm('Bloquear la cuenta?')) return; 
        if(!estado && !confirm('Desbloquear cuenta?')) return; 
        try {
            const res = await window.DB.post('usuarios/bloquear', {id: id, estado: estado ? 1 : 0}); 
            if (res && res.ok) {
                const u = window.DB.obtenerPadres().find(x => x.id == id); 
                if (u) u.bloqueado = estado ? 1 : 0; 
                this.adminPadresVersion++; this.comentariosVersion++; 
            } else {
                alert('Error al actualizar en el servidor.');
            }
        } catch(e) {
            
        }
    },
      recordatorioMasivo() {
      const padresPendientes = [...this.padresParaCobroEfectivoFiltrados, ...this.padresParaCobroLineaFiltrados].filter(p => p.estadoPago !== 'aldia' && p.estadoPago !== 'amarillo');
      if (padresPendientes.length === 0) { alert('No hay padres con pagos pendientes o vencidos.'); return; }
      if (!confirm('Se prepararán mensajes para ' + padresPendientes.length + ' familias pendientes. ¿Deseas continuar?')) return;
      let i = 0;
      const next = () => {
        if (i >= padresPendientes.length) { alert('¿Todos los recordatorios fueron preparados!'); return; }
        const p = padresPendientes[i];
        this.recordatorioWhatsapp(p.id, 'ambos');
        i++;
        if (i < padresPendientes.length) { setTimeout(next, 500); }
      };
      next();
    },
    recordatorioWhatsapp(padreId, tipo) {
      const padre = this.listaPadres.find(p => p.id == padreId);
      if (!padre) return;
      const telf = padre.telefono || '';
      if (!telf) { alert('Este padre no tiene número de teléfono registrado.'); return; }
      const webUrl = this.obtenerUrlSitio();
      const yapeStr = this.numeroYape ? `\n📲 *Pagar por Yape al:* ${this.numeroYape}` : '';
      let msg = '';
      if (tipo === 'efectivo') {
        msg = `⚽ *ACADEMIA NICO SPORT*\n\nHola *${padre.nombre}*, le recordamos cordialmente que la mensualidad oficial de la academia se encuentra pendiente.\n\nPuede acercarse a las instalaciones para realizar su pago en efectivo o consultar su estado en nuestro sitio web:\n🌐 *Sitio Web:* ${webUrl}`;
      } else {
        msg = `⚽ *ACADEMIA NICO SPORT*\n\nHola *${padre.nombre}*, le recordamos cordialmente que la mensualidad oficial está pendiente o próxima a vencer.\n\nPuede realizar su pago fácilmente por Yape y adjuntar el comprobante en nuestro portal:\n${yapeStr}\n🌐 *Accede a tu cuenta aquí:* ${webUrl}`;
      }
      let cleanTelf = telf.replace(/\D/g, '');
      if (cleanTelf.length === 8) cleanTelf = '507' + cleanTelf;
      window.open(`https://wa.me/${cleanTelf}?text=${encodeURIComponent(msg)}`, '_blank');
    },
    recordatorioWhatsappViejo(padreId) {
      const padre = this.listaPadres.find(p => p.id == padreId);
      if (!padre) return;
      const telf = padre.telefono || '';
      if (!telf) { alert('Este padre no tiene número de teléfono registrado.'); return; }
      const webUrl = this.obtenerUrlSitio();
      const yapeStr = this.numeroYape ? `\n📲 *Pagar por Yape al:* ${this.numeroYape}` : '';
      const msg = `⚽ *ACADEMIA NICO SPORT*\n\nHola *${padre.nombre}*, le recordamos desde la Academia Nico Sport que su fecha de mensualidad ha vencido.\n\nPor favor regularice su pago vía Yape ingresando a nuestro portal:\n${yapeStr}\n🌐 *Portal Web:* ${webUrl}`;
      let cleanTelf = telf.replace(/\D/g, '');
      if (cleanTelf.length === 8) cleanTelf = '507' + cleanTelf;
      window.open(`https://wa.me/${cleanTelf}?text=${encodeURIComponent(msg)}`, '_blank');
    },
    cambiarMetodoPago(padreId, nuevoMetodo) {
      if (!confirm(`¿Cambiar método de pago a ${nuevoMetodo === "efectivo" ? "Efectivo" : "En Línea"}?`)) return;
      const padre = this.listaPadres.find(p => p.id == padreId);
      if (!padre) return;
      padre.metodoPago = nuevoMetodo;
      this.adminPadresVersion++; this.comentariosVersion++; 
      window.DB.post("usuarios/actualizarPago", {
          id: padre.id,
          telefono: padre.telefono,
          metodoPago: nuevoMetodo,
          fechaVencimiento: padre.fechaVencimiento
      });
    },
    comprobantesPendientes() {
      return window.DB.obtenerComprobantes().filter(c => (c.estado === 'en_revision' || c.estado === 'pendiente'));
    },
    validarComprobante(id, aprobar) {
      if (!this.esAdmin) return;
      const accion = aprobar ? 'APROBAR' : 'RECHAZAR';
      if (!confirm('' + accion + ' este comprobante?')) return;
      window.DB.validarComprobante(id, aprobar);
      alert(aprobar ? ' Comprobante aprobado y registrado' : ' Comprobante rechazado');
    },
    historialPagosClub() { void this.adminPadresVersion;
      return window.DB.obtenerPagosClub().slice().reverse();
    },

        /* ========== 18) GESTIÓN DE ENTRENADORES ========== */
    nuevoEntrenador: { nombre: '', usuario: '', password: '', catKey: '', categorias: [] },
    mostrarPassEntrenador: false,
    entrenadorMsg: '',
    entrenadoresVersion: 0,

    modalEditarNombreDT: false,
    dtNombreForm: { id: null, nombre: '', nombreActual: '' },
    msgNombreDT: '',

    modalEditarTelefonoDT: false,
    dtTelefonoForm: { id: null, nombre: '', telefono: '' },
    msgTelefonoDT: '',

    modalEditarCategoriasDT: false,
    dtCategoriasForm: { id: null, nombre: '', categorias: [] },
    msgCategoriasDT: '',

    modalEditarPasswordDT: false,
    dtPasswordForm: { id: null, nombre: '', password: '', mostrar: false },
    msgPasswordDT: '',
    get listaEntrenadores() {
      void this.entrenadoresVersion;
      return window.DB.obtenerEntrenadores();
    },
    sincronizarUsuarioEntrenador() {
      this.nuevoEntrenador.usuario = this.nuevoEntrenador.nombre;
    },
    async registrarEntrenador() {
      if (!this.esAdmin) return;
      const d = this.nuevoEntrenador;
      const nombre = (d.nombre || '').trim();
      const usuario = (d.usuario && d.usuario.trim()) || nombre;
      const password = (d.password || '').trim();
      let cats = Array.isArray(d.categorias) ? d.categorias : [];
      if (cats.length === 0 && d.catKey) {
        cats = [d.catKey];
      }
      if (!nombre || !password || cats.length === 0) {
        this.entrenadorMsg = '⚠️ Todos los campos son obligatorios (Nombre, Contraseña y al menos una Categoría).';
        return;
      }
      if (password.length < 12 || !(/[a-zA-Z]/.test(password) && /[0-9]/.test(password))) {
        this.entrenadorMsg = '⚠️ La contraseña debe tener mínimo 12 caracteres combinando letras y números.';
        return;
      }
      const mapLabels = {};
      (this.categoriasTorneo || []).forEach(c => { mapLabels[c.key] = c.label; });
      const labelStr = cats.map(k => mapLabels[k] || k.toUpperCase()).join(', ');

      const res = await window.DB.registrarEntrenador({
        nombre: nombre,
        usuario: usuario,
        password: password,
        catKey: cats[0],
        catLabel: labelStr,
        categorias: cats
      });
      this.entrenadorMsg = (res && res.ok) ? '✓ ' + (res.msg || 'Entrenador registrado con éxito') : '⚠️ ' + ((res && (res.error || res.msg)) || 'Error al registrar entrenador');
      if (res && res.ok) {
        this.nuevoEntrenador = { nombre: '', usuario: '', password: '', catKey: '', categorias: [] };
        this.entrenadoresVersion++;
        setTimeout(() => { if (window.DB && window.DB.sincronizar) window.DB.sincronizar(this); }, 300);
      }
    },

    abrirModalEditarEntrenador(dt) {
      if (!this.esAdmin) return;
      let cats = [];
      if (Array.isArray(dt.categorias) && dt.categorias.length > 0) {
        cats = [...dt.categorias];
      } else if (dt.catKey) {
        cats = [dt.catKey];
      }
      this.entrenadorEditando = {
        id: dt.id,
        nombre: dt.nombre || '',
        usuario: dt.usuario || dt.nombre || '',
        telefono: dt.telefono || '',
        catKey: dt.catKey || (cats[0] || 'sub6'),
        categorias: cats,
        nuevaPassword: '',
        mostrarPass: false
      };
      this.msgEditarEntrenador = '';
      this.modalEditarEntrenador = true;
    },

    async guardarEdicionEntrenador() {
      if (!this.entrenadorEditando || !this.entrenadorEditando.id) return;
      this.msgEditarEntrenador = '';
      const nom = (this.entrenadorEditando.nombre || '').trim();
      if (!nom) {
        this.msgEditarEntrenador = 'El nombre no puede estar vacío.';
        return;
      }
      let cats = Array.isArray(this.entrenadorEditando.categorias) ? this.entrenadorEditando.categorias : [];
      if (cats.length === 0 && this.entrenadorEditando.catKey) {
        cats.push(this.entrenadorEditando.catKey);
      }
      if (cats.length === 0) {
        this.msgEditarEntrenador = 'Debes asignar al menos una categoría al entrenador.';
        return;
      }
      const mapLabels = {};
      (this.categoriasTorneo || []).forEach(c => { mapLabels[c.key] = c.label; });
      const labelStr = cats.map(k => mapLabels[k] || k.toUpperCase()).join(', ');

      const payload = {
        nombre: nom,
        usuario: nom,
        telefono: (this.entrenadorEditando.telefono || '').trim(),
        catKey: cats[0],
        catLabel: labelStr,
        categorias: cats
      };

      const pass = (this.entrenadorEditando.nuevaPassword || '').trim();
      if (pass !== '') {
        if (pass.length < 12 || !(/[a-zA-Z]/.test(pass) && /[0-9]/.test(pass))) {
          this.msgEditarEntrenador = 'La contraseña debe tener al menos 12 caracteres combinando letras y números.';
          return;
        }
        payload.password = pass;
      }

      const res = await window.DB.editarEntrenador(this.entrenadorEditando.id, payload);
      if (res && res.ok) {
        this.msgEditarEntrenador = '✓ Entrenador actualizado con éxito.';
        this.entrenadoresVersion++;
        setTimeout(() => { if (window.DB && window.DB.sincronizar) window.DB.sincronizar(this); }, 300);
        setTimeout(() => { this.modalEditarEntrenador = false; }, 1200);
      } else {
        this.msgEditarEntrenador = (res && res.error) || 'Error al actualizar el entrenador.';
      }
    },

    abrirEditarNombreDT(dt) {
      if (!this.esAdmin) return;
      this.dtNombreForm = { id: dt.id, nombre: dt.nombre || '', nombreActual: dt.nombre || '' };
      this.msgNombreDT = '';
      this.modalEditarNombreDT = true;
    },
    async guardarNombreDT() {
      if (!this.dtNombreForm.id) return;
      const nom = (this.dtNombreForm.nombre || '').trim();
      if (!nom) { this.msgNombreDT = '❌ El nombre no puede estar vacío.'; return; }
      const res = await window.DB.actualizarNombreEntrenador(this.dtNombreForm.id, nom);
      if (res && res.ok) {
        this.msgNombreDT = '✓ Nombre actualizado con éxito.';
        this.entrenadoresVersion++;
        setTimeout(() => { this.modalEditarNombreDT = false; }, 800);
      } else {
        this.msgNombreDT = '❌ ' + ((res && res.error) || 'Error al actualizar nombre.');
      }
    },

    abrirEditarTelefonoDT(dt) {
      if (!this.esAdmin) return;
      this.dtTelefonoForm = { id: dt.id, nombre: dt.nombre || '', telefono: dt.telefono || '' };
      this.msgTelefonoDT = '';
      this.modalEditarTelefonoDT = true;
    },
    async guardarTelefonoDT() {
      if (!this.dtTelefonoForm.id) return;
      const tel = (this.dtTelefonoForm.telefono || '').trim();
      const res = await window.DB.actualizarTelefonoEntrenador(this.dtTelefonoForm.id, tel);
      if (res && res.ok) {
        this.msgTelefonoDT = '✓ Teléfono actualizado con éxito.';
        this.entrenadoresVersion++;
        setTimeout(() => { this.modalEditarTelefonoDT = false; }, 800);
      } else {
        this.msgTelefonoDT = '❌ ' + ((res && res.error) || 'Error al actualizar teléfono.');
      }
    },

    abrirEditarCategoriasDT(dt) {
      if (!this.esAdmin) return;
      let cats = [];
      if (Array.isArray(dt.categorias) && dt.categorias.length > 0) {
        cats = [...dt.categorias];
      } else if (dt.catKey) {
        cats = [dt.catKey];
      }
      this.dtCategoriasForm = { id: dt.id, nombre: dt.nombre || '', categorias: cats };
      this.msgCategoriasDT = '';
      this.modalEditarCategoriasDT = true;
    },
    async guardarCategoriasDT() {
      if (!this.dtCategoriasForm.id) return;
      const cats = Array.isArray(this.dtCategoriasForm.categorias) ? this.dtCategoriasForm.categorias : [];
      if (cats.length === 0) {
        this.msgCategoriasDT = '❌ Selecciona al menos una categoría.';
        return;
      }
      const res = await window.DB.actualizarCategoriasEntrenador(this.dtCategoriasForm.id, cats);
      if (res && res.ok) {
        this.msgCategoriasDT = '✓ Categorías actualizadas con éxito.';
        this.entrenadoresVersion++;
        setTimeout(() => { this.modalEditarCategoriasDT = false; }, 800);
      } else {
        this.msgCategoriasDT = '❌ ' + ((res && res.error) || 'Error al actualizar categorías.');
      }
    },

    abrirEditarPasswordDT(dt) {
      if (!this.esAdmin) return;
      this.dtPasswordForm = { id: dt.id, nombre: dt.nombre || '', password: '', mostrar: false };
      this.msgPasswordDT = '';
      this.modalEditarPasswordDT = true;
    },
    async guardarPasswordDT() {
      if (!this.dtPasswordForm.id) return;
      const pass = (this.dtPasswordForm.password || '').trim();
      if (pass.length < 12 || !(/[a-zA-Z]/.test(pass) && /[0-9]/.test(pass))) {
        this.msgPasswordDT = '❌ La contraseña debe tener al menos 12 caracteres combinando letras y números.';
        return;
      }
      const res = await window.DB.actualizarPasswordEntrenador(this.dtPasswordForm.id, pass);
      if (res && res.ok) {
        this.msgPasswordDT = '✓ Contraseña restablecida con éxito.';
        this.entrenadoresVersion++;
        setTimeout(() => { this.modalEditarPasswordDT = false; }, 800);
      } else {
        this.msgPasswordDT = '❌ ' + ((res && res.error) || 'Error al actualizar contraseña.');
      }
    },

    editarEntrenador(dt) {
      this.abrirModalEditarEntrenador(dt);
    },
    eliminarEntrenador(id) {
      if (!this.esAdmin) return;
      if (!confirm('¿Eliminar este entrenador?')) return;
      window.DB.eliminarEntrenador(id);
      this.entrenadoresVersion++;
      alert(' Entrenador eliminado');
    },

    /* ========== 19) PORTAL DE PADRES Y CONFIGURACIÓN YAPE ========== */
      tarifaOficialMensualidad: 15.00,
      tarifaOficialInscripcion: 15.00,
      numeroYape: '6000-0000',
      titularYape: 'Academia Nico Sport',
      detallesYape: '',
      yapeGuardadoMsg: '',
      copiadoYapeMsg: '',
      configuracionesCargadas: false,
      yapeEditando: false,

      copiarNumeroYape() {
        if (!this.numeroYape) return;
        navigator.clipboard.writeText(this.numeroYape).then(() => {
          this.copiadoYapeMsg = '¡Copiado!';
          setTimeout(() => { this.copiadoYapeMsg = ''; }, 2000);
        }).catch(() => {});
      },

      cargarConfiguraciones(forzar = false) {
        if (this.configuracionesCargadas && !forzar) return;
        const conf = (window.DB && typeof window.DB.obtenerConfiguraciones === 'function')
          ? window.DB.obtenerConfiguraciones()
          : {};
        if (conf['tarifa_mensualidad'] !== undefined) this.tarifaOficialMensualidad = parseFloat(conf['tarifa_mensualidad']) || 15.00;
        if (conf['tarifa_inscripcion'] !== undefined) this.tarifaOficialInscripcion = parseFloat(conf['tarifa_inscripcion']) || 15.00;
        
        if (!this.yapeEditando || forzar) {
          if (conf['numero_yape'] !== undefined && conf['numero_yape'] !== null) {
            this.numeroYape = conf['numero_yape'];
          } else if (conf['banco_yappy'] !== undefined && conf['banco_yappy'] !== null) {
            this.numeroYape = conf['banco_yappy'];
          } else if (!this.configuracionesCargadas) {
            this.numeroYape = '6000-0000';
          }
          if (conf['titular_yape'] !== undefined) this.titularYape = conf['titular_yape'];
          if (conf['detalles_yape'] !== undefined) this.detallesYape = conf['detalles_yape'];
        }
        if (Object.keys(conf).length > 0) {
          this.configuracionesCargadas = true;
        }
      },
      cargarTarifas() {
        this.cargarConfiguraciones(true);
      },
      async guardarTarifas() {
        this.yapeEditando = false;
        if (window.DB && typeof window.DB.guardarConfiguraciones === 'function') {
          await window.DB.guardarConfiguraciones({
            mensualidad: this.tarifaOficialMensualidad,
            inscripcion: this.tarifaOficialInscripcion,
            numero_yape: this.numeroYape,
            titular_yape: this.titularYape,
            detalles_yape: this.detallesYape
          });
        }
        this.configuracionesCargadas = true;
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-nico-green text-black font-black py-2.5 px-4 rounded-xl shadow-2xl z-50 border border-black/20';
        toast.innerText = 'Tarifas y Yape Guardados con Éxito';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
      },
      async guardarYape() {
        this.yapeEditando = false;
        const payload = {
          numero_yape: this.numeroYape || '',
          titular_yape: this.titularYape || '',
          detalles_yape: this.detallesYape || '',
          whatsapp_admin: this.numeroYape || ''
        };
        if (window.DB && typeof window.DB.guardarConfiguraciones === 'function') {
          await window.DB.guardarConfiguraciones(payload);
        }
        this.configuracionesCargadas = true;
        this.yapeGuardadoMsg = '¡Número de Yape actualizado con éxito!';
        setTimeout(() => { this.yapeGuardadoMsg = ''; }, 3500);
      },
      reportePago: { concepto: 'Mensualidad', monto: 15, fecha: new Date().toISOString().slice(0,10), referencia: '', observaciones: '', comprobanteNombre: '', comprobanteData: '' },
    async handleComprobantePadre(e) {
      const file = e.target.files[0];
      if (!file) return;
      this.reportePago.comprobanteNombre = 'Subiendo...';
      try {
        const fd = new FormData();
        fd.append('file', file);
        if (this.reportePago.comprobanteData && this.reportePago.comprobanteData.startsWith('uploads/comprobantes/')) {
          fd.append('old_path', this.reportePago.comprobanteData);
        }
        let res = null;
        if (window.DB && typeof window.DB.subirComprobante === 'function') {
          res = await window.DB.subirComprobante(fd);
        }
        if (res && (res.url || res.path)) {
          this.reportePago.comprobanteData = res.url || res.path;
          this.reportePago.comprobanteNombre = file.name;
          return;
        }
      } catch (err) {}
      const reader = new FileReader();
      reader.onload = (ev) => {
        const img = new Image();
        img.onload = () => {
          const maxW = 900;
          let w = img.width, h = img.height;
          if (w > maxW) { h = Math.round(h * (maxW / w)); w = maxW; }
          const canvas = document.createElement('canvas');
          canvas.width = w; canvas.height = h;
          canvas.getContext('2d').drawImage(img, 0, 0, w, h);
          this.reportePago.comprobanteData = canvas.toDataURL('image/jpeg', 0.72);
          this.reportePago.comprobanteNombre = file.name;
        };
        img.src = ev.target.result;
      };
      reader.readAsDataURL(file);
    },
          comprobantesDelPadre() { void this.adminPadresVersion;
          if (!this.currentUser || this.currentUser.rol !== 'padre') return [];
          return window.DB.obtenerComprobantes().filter(c => c.padreId == this.currentUser.id && (c.estado === 'en_revision' || c.estado === 'pendiente'));
      },
    get misHijos() {
      if (!this.currentUser) return [];
      if (Array.isArray(this.currentUser.hijos) && this.currentUser.hijos.length > 0) {
        return this.currentUser.hijos;
      }
      if (this.currentUser.hijoCat && this.currentUser.hijoId) {
        return [{
          id: this.currentUser.hijoId,
          nombre: this.currentUser.hijoNombre || 'Mi Hijo(a)',
          catKey: this.currentUser.hijoCat,
          catLabel: this.currentUser.sub || this.currentUser.hijoCat,
          sub: this.currentUser.sub || this.currentUser.hijoCat,
          dorsal: this.currentUser.hijoDorsal || 0,
          pos: this.currentUser.hijoPos || 'MED'
        }];
      }
      return [];
    },
    get hijoSeleccionado() {
      const lista = this.misHijos;
      if (lista.length === 0) return null;
      const idx = Math.min(Math.max(0, this.hijoActivoIndex || 0), lista.length - 1);
      return lista[idx] || null;
    },
    get hijoPlayer() {
      const hSel = this.hijoSeleccionado;
      if (!hSel) {
        if (!this.currentUser || !this.currentUser.hijoCat || !this.currentUser.hijoId) return null;
        return this.playersDe(this.currentUser.hijoCat).find(p => p.id === this.currentUser.hijoId) || null;
      }
      const cat = hSel.catKey || this.currentUser.hijoCat;
      const hid = hSel.id || this.currentUser.hijoId;
      if (!cat || !hid) return null;
      return this.playersDe(cat).find(p => p.id === hid) || {
        id: hid,
        name: hSel.nombre || 'Mi Hijo(a)',
        catKey: cat,
        dorsal: hSel.dorsal || 0,
        pos: hSel.pos || 'MED',
        foto: null
      };
    },
    get hijoCatLabel() {
      const hSel = this.hijoSeleccionado;
      const catKey = hSel ? (hSel.catKey || this.currentUser.hijoCat) : (this.currentUser ? this.currentUser.hijoCat : '');
      if (!catKey) return '';
      const c = this.categoriasTorneo.find(x => x.key === catKey);
      return c ? c.label : catKey.toUpperCase();
    },
    estadoCuentaPadre() { void this.adminPadresVersion;
      if (!this.currentUser || this.currentUser.rol !== 'padre') return 'Vigente';
      if (this.currentUser.fechaVencimiento) {
        const hoy = new Date(); hoy.setHours(0,0,0,0);
        const venc = new Date(this.currentUser.fechaVencimiento + 'T00:00:00');
        return venc >= hoy ? 'Vigente' : 'Vencido';
      }
      const pagos = window.DB.obtenerPagosPadre(this.padreIdActual);
      const validados = pagos.filter(p => p.estado === 'validado');
      if (validados.length === 0) return 'Vencido';
      const ultimo = validados.sort((a,b) => new Date(b.fecha) - new Date(a.fecha))[0];
      const fechaUlt = new Date(ultimo.fecha);
      const hoy = new Date();
      const diff = Math.floor((hoy - fechaUlt) / (1000 * 60 * 60 * 24));
      return diff <= 30 ? 'Vigente' : 'Vencido';
    },
      async enviarReportePago() {
        if (!this.currentUser || this.currentUser.rol !== 'padre') { alert(' Inicia sesión como padre'); return; }
        if (!this.reportePago.comprobanteNombre || this.reportePago.comprobanteNombre === 'Subiendo...') { alert(' Adjunta el comprobante'); return; }
        
        const fechaActual = new Date().toISOString().slice(0,10);
        const montoOficial = parseFloat(this.tarifaOficialMensualidad) || 15;
        const conceptoStr = 'Mensualidad';
        const refStr = (this.reportePago.referencia || '').trim() || ('REF-' + Date.now().toString().slice(-6));

        await Promise.all([
          window.DB.guardarPagoPadre(this.padreIdActual, {
            concepto: conceptoStr,
            monto: montoOficial,
            fecha: fechaActual,
            referencia: refStr,
            observaciones: this.reportePago.observaciones || '',
            comprobante: this.reportePago.comprobanteData,
            estado: 'en_revision'
          }),
          window.DB.guardarComprobante({
            padreId: this.padreIdActual,
            padreNombre: this.currentUser.nombre,
            concepto: conceptoStr,
            monto: montoOficial,
            fecha: fechaActual,
            referencia: refStr,
            observaciones: this.reportePago.observaciones || '',
            comprobante: this.reportePago.comprobanteData,
            estado: 'en_revision'
          })
        ]);

        await window.DB.sincronizar(this);
        this.adminPadresVersion++; this.comentariosVersion++;

        const msg = encodeURIComponent(
          ' *NICO SPORT - Reporte de Pago*\n' +
          ' Padre: ' + this.currentUser.nombre + '\n' +
          ' Concepto: ' + conceptoStr + '\n' +
          ' Monto: $' + montoOficial.toFixed(2) + '\n' +
          ' Fecha: ' + new Date().toLocaleDateString('es-PA') + '\n' +
          ' Ref: ' + refStr + '\n' +
          ' Comprobante enviado. Pendiente de validación.'
        );
        this.reportePago = { concepto: 'Mensualidad', monto: montoOficial, fecha: fechaActual, referencia: '', observaciones: '', comprobanteNombre: '', comprobanteData: '' };
        const webUrl = this.obtenerUrlSitio();
        const msgYape = encodeURIComponent(
          '⚽ *ACADEMIA NICO SPORT — Reporte de Pago*\n\n' +
          '👤 *Padre:* ' + this.currentUser.nombre + '\n' +
          '📋 *Concepto:* ' + conceptoStr + '\n' +
          '💵 *Monto:* $' + montoOficial.toFixed(2) + '\n' +
          '📅 *Fecha:* ' + new Date().toLocaleDateString('es-PA') + '\n' +
          '🔢 *Ref / N° Operación:* ' + refStr + '\n' +
          '📱 *Método:* Yape\n\n' +
          '📎 Comprobante adjuntado en la plataforma.\n' +
          '🌐 *Portal Web:* ' + webUrl
        );
        const adminPhone = (this.numeroYape ? this.numeroYape.replace(/\D/g, '') : '50760000000');
        const phoneFormatted = adminPhone.length === 8 ? ('507' + adminPhone) : adminPhone;
        window.open('https://wa.me/' + phoneFormatted + '?text=' + msgYape, '_blank');
        alert('✓ Reporte enviado y guardado en tu historial.');
      },
      misPagos() { void this.adminPadresVersion;
      if (!this.currentUser || this.currentUser.rol !== 'padre') return [];
      return window.DB.obtenerPagosPadre(this.padreIdActual).slice().reverse();
    },

    /* ========== 20) NAVEGACIÓN Y CARGA ========== */
    switchTab(tab, event) {
      this.mainTab = tab;
      if (tab === 'admin' && this.esAdmin && this.currentUser) {
        if (!this.adminCredsForm.usuarioNuevo) this.adminCredsForm.usuarioNuevo = this.currentUser.usuario || '';
      }
      if (event && window.triggerParticles && event.clientX !== undefined) window.triggerParticles(event.clientX, event.clientY);
      this.$nextTick(() => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        const nav = document.getElementById('mobNavScroll') || document.querySelector('#mobileNav .nav-scroll');
        const btn = document.querySelector('[data-mobtab="' + tab + '"]');
        if (nav && btn) {
          const targetLeft = Math.max(0, btn.offsetLeft - (nav.clientWidth / 2) + (btn.clientWidth / 2));
          nav.scrollTo({ left: targetLeft, behavior: 'smooth' });
        }
        if (!this.modoEdicion) {
          this.aplicarTextos();
        } else {
          this.habilitarEdicionEnDOM();
        }
      });
    },
    cargarPersistidos() {
      if (this.modoEdicion || this.staffEditModal) return;
      const t = window.DB.obtenerTorneo();
      if (t && t.length) {
        this.categoriasTorneo = t.map(c => Object.assign(catTorneo(), c));
      }
      const cats = window.DB.obtenerCategorias();
      if (cats && cats.length) this.aplicarCategoriasGuardadas(cats);
      this.categoriasVitrinaTabs = this.categoriasTorneo;
      const s = window.DB.obtenerStaff();
      if (s && s.length) this.staffList = s;
      this.categoriasTorneo.forEach(c => {
        const p = window.DB.obtenerPlantilla(c.key) || [];
        p.sort((a, b) => ((a.dorsal || 0) === 0 ? 9999 : a.dorsal) - ((b.dorsal || 0) === 0 ? 9999 : b.dorsal));
        this._setPlantilla(c.key, p);
      });
      // FIX REACTIVO: forzar que Alpine re-evalúe listaPadres y categorías
      // después de que la BD cargó asíncronamente
      this.adminPadresVersion++; this.comentariosVersion++;
      this.catAdminVersion++;
    },
                /* ========== 19) VOZ DEL PADRE (FEEDBACK) ========== */
      feedbackForm: { tipo: '', asunto: '', mensaje: '', es_anonimo: false },
      feedbackStatus: { show: false, msg: '', error: false },
      
      enviarFeedback() {
        this.feedbackStatus.show = false;
        
        if(!this.feedbackForm.tipo) { 
            this.mostrarFeedback('Selecciona el tipo de mensaje', true); 
            return; 
        }
        if(!this.feedbackForm.asunto.trim()) { 
            this.mostrarFeedback('Ingresa un asunto o título', true); 
            return; 
        }
        if(!this.feedbackForm.mensaje.trim()) { 
            this.mostrarFeedback('Escribe tu mensaje detallado', true); 
            return; 
        }
        
        const f = {
          id: Date.now(),
          padre_id: this.currentUser ? this.currentUser.id : 0,
          tipo: this.feedbackForm.tipo,
          asunto: this.feedbackForm.asunto.trim(),
          mensaje: this.feedbackForm.mensaje.trim(),
          es_anonimo: this.feedbackForm.es_anonimo ? 1 : 0,
          estado: 'nuevo',
          created_at: new Date().toISOString()
        };
        
        window.DB.guardarComentario(f); this.comentariosVersion++;
        
        this.feedbackForm = { tipo: '', asunto: '', mensaje: '', es_anonimo: false };
        this.mostrarFeedback('¡Mensaje enviado con éxito al club! Gracias por tus comentarios.', false);
      },
      mostrarFeedback(msg, error = false) {
          this.feedbackStatus = { show: true, msg, error };
          setTimeout(() => { this.feedbackStatus.show = false; }, 4000);
      },
      get comentariosPadres() { void this.comentariosVersion; return window.DB.obtenerComentarios(); },
      borrarComentario(id) {
          if(confirm('¿Seguro que deseas borrar este comentario?')) {
            window.DB.borrarComentario(id); this.comentariosVersion++;
          }
        },
        async marcarLeido(id) {
            await window.DB.post('comentarios/marcar_leido', {id: id});
            const c = window.DB.obtenerComentarios().find(x => x.id == id);
            if(c) { c.estado = 'leido'; this.comentariosVersion++; }
        },
        async marcarTodoLeido() {
            if(!confirm('¿Marcar todos los mensajes como leídos?')) return;
            await window.DB.post('comentarios/marcar_todo_leido', {});
            window.DB.obtenerComentarios().forEach(x => x.estado = 'leido');
            this.comentariosVersion++;
        },
      estadoComentarioColor(tipo) {
        if (tipo === 'sugerencia') return 'text-blue-400 border-blue-400/30';
        if (tipo === 'critica_constructiva') return 'text-amber-400 border-amber-400/30';
        if (tipo === 'inconformidad') return 'text-red-400 border-red-400/30';
        if (tipo === 'felicitacion') return 'text-green-400 border-green-400/30';
        return 'text-gray-400 border-gray-400/30';
      },
      
      iniciarCarga() {
        this.selectedTrophy = this.trophiesList[0];
        this.cargarPersistidos();
        this.aplicarTextos();
        const finishLoad = () => {
          this.progress = 100;
          setTimeout(() => {
            this.loading = false;
            this.reloadSpamMsg = '';
          }, 180);
        };
        if (this._cooldownDelay && this._cooldownDelay > 0) {
          setTimeout(finishLoad, this._cooldownDelay);
        } else {
          finishLoad();
        }
    },
        init() {
        this.modoEdicion = false;
        this.currentUser = null;
        try { localStorage.removeItem('ns_currentUser'); } catch(e) {}

        // Escuchador desacoplado para edición de medios sin exponer window.nicoAppAdmin
        window.addEventListener('ns-edit-media', (e) => {
          if (this.esAdmin && this.modoEdicion && e.detail) {
            this.editarTextoMediaGlobal(e.detail.clave, e.detail.defaultUrl);
          }
        });

        // Delegación global de clics para medios en modo CMS
        document.addEventListener('click', (e) => {
          const target = e.target.closest('[data-ns-media]');
          if (target && this.esAdmin && this.modoEdicion) {
            e.preventDefault();
            e.stopPropagation();
            const clave = target.getAttribute('data-ns-media');
            const defaultUrl = target.getAttribute('data-ns-default') || '';
            this.editarTextoMediaGlobal(clave, defaultUrl);
          }
        }, true);

        // Protección anti-spam de recargas rápidas (F5 / Ctrl+R repetidos)
        this._cooldownDelay = 0;
        try {
          const now = Date.now();
          let hist = JSON.parse(sessionStorage.getItem('ns_reload_hist') || '[]').filter(t => (now - t) < 8000);
          hist.push(now);
          sessionStorage.setItem('ns_reload_hist', JSON.stringify(hist));
          if (hist.length >= 4) {
            this.reloadSpamMsg = 'ESPERA UN MOMENTO ANTES DE RECARGAR...';
            this._cooldownDelay = 2200;
          }
        } catch(e) {}

        // Bloquear F5 / Ctrl+R mientras la pantalla de carga ya está activa para evitar sobrecarga
        window.addEventListener('keydown', (ev) => {
          if ((ev.key === 'F5' || ((ev.ctrlKey || ev.metaKey) && ev.key.toLowerCase() === 'r')) && this.loading) {
            ev.preventDefault();
          }
        });

        // Animación progresiva suave de la barra de carga (0% -> 90%)
        const progTimer = setInterval(() => {
          if (!this.loading) { clearInterval(progTimer); return; }
          if (this.progress < 85) this.progress += 15;
        }, 90);

        // Watchers for saving state dynamically
        this.$watch('mainTab', val => localStorage.setItem('ns_mainTab', val));
        this.$watch('catTab', val => localStorage.setItem('ns_catTab', val));

        const self = this;
        Promise.resolve(window.DB.listo).then(async function () {
          // 1. Verificación asíncrona de sesión con el backend (Fuente única de verdad: HttpOnly Session Cookie)
          try {
            const realUser = await window.DB.verificarSesion();
            if (realUser && realUser.rol) {
              self.currentUser = realUser;
              if (realUser.rol === 'admin') document.body.classList.add('is-admin');
              
              const savedTab = localStorage.getItem('ns_mainTab');
              const savedCat = localStorage.getItem('ns_catTab');
              
              if (savedTab) self.mainTab = savedTab;
              else {
                if (realUser.rol === 'admin') self.mainTab = 'admin';
                else if (realUser.rol === 'padre') self.mainTab = (realUser.hijoCat && realUser.hijoId) ? 'categorias' : 'portal';
                else if (realUser.rol === 'entrenador') self.mainTab = 'categorias';
              }
              if (savedCat) self.catTab = savedCat;
              else if (realUser.rol === 'padre' && realUser.hijoCat) self.catTab = realUser.hijoCat;
              else if (realUser.rol === 'entrenador' && realUser.catKey) self.catTab = realUser.catKey;
              if (realUser.rol === 'admin' && !self.adminCredsForm.usuarioNuevo) {
                self.adminCredsForm.usuarioNuevo = realUser.usuario || '';
              }
            } else {
              self.currentUser = null;
              document.body.classList.remove('is-admin');
              if (self.mainTab === 'admin' || self.mainTab === 'portal') {
                self.mainTab = 'historia';
              }
            }
          } catch (e) {
            self.currentUser = null;
            document.body.classList.remove('is-admin');
          }

          // 2. Hidratar estado de MySQL hacia la reactividad de Alpine
          self.dbCargado = true;
          self.trofeosVersion++;
          self.cargarConfiguraciones();
          self.cargarPersistidos();
          self.aplicarTextos();

          // 3. Finalizar la pantalla de carga de manera sincronizada y limpia
          self.iniciarCarga();

          // 4. Polleo silencioso cada 15 segundos SOLO para usuarios autenticados
          setInterval(() => {
            if (self.currentUser && window.DB && typeof window.DB.sincronizar === 'function') {
              window.DB.sincronizar(self);
            }
          }, 15000);
        });
    }
  }));
});
