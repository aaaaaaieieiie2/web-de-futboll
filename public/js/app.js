/* ============================================================
app.js  LÃ“GICA PRINCIPAL (Alpine.js)
 MySQL + 5 FIXES:
1) Registro unificado padre + jugador (crear o elegir)
2) Fix cachÃ© zombi (valida sesiÃ³n al cargar)
3) Padre va directo a CategorÃ­as con su hijo
4) Modal evaluaciÃ³n responsive total
5) Recordatorio pago solo rojo si vencido
============================================================ */
document.addEventListener('alpine:init', () => {

  const CORE_KEYS = ['sub6', 'sub12', 'sub16', 'submayor'];

  const catTorneo = () => ({
    faseActual: 'ninguna',
    tabla: [
      { nombre: 'Nico Sport ANS', pj: 0, pg: 0, pe: 0, pp: 0, gf: 0, gc: 0, pts: 0 },
      { nombre: 'PrÃ³ximo', pj: 0, pg: 0, pe: 0, pp: 0, gf: 0, gc: 0, pts: 0 },
      { nombre: 'PrÃ³ximo', pj: 0, pg: 0, pe: 0, pp: 0, gf: 0, gc: 0, pts: 0 },
      { nombre: 'PrÃ³ximo', pj: 0, pg: 0, pe: 0, pp: 0, gf: 0, gc: 0, pts: 0 }
    ],
    ultimoResultado: { golesNico: 0, golesRival: 0, rival: 'PrÃ³ximo', goleadores: 'PrÃ³ximo', sede: 'PrÃ³ximo' },
    proximoPartido: { fecha: 'PrÃ³ximo', condicion: 'PrÃ³ximo', rival: 'PrÃ³ximo', sede: 'PrÃ³ximo' }
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

  const P_SUB6 = [
    { id: 1, catKey: 'sub6', name: 'M. Aguirre', dorsal: 1, pos: 'POR', goles: 0, asistencias: 0, mvp: 2 },
    { id: 2, catKey: 'sub6', name: 'D. Castro', dorsal: 2, pos: 'DEF', goles: 1, asistencias: 3, mvp: 1 },
    { id: 3, catKey: 'sub6', name: 'S. PÃ©rez', dorsal: 3, pos: 'DEF', goles: 0, asistencias: 2, mvp: 0 },
    { id: 4, catKey: 'sub6', name: 'A. Ruiz', dorsal: 4, pos: 'MED', goles: 5, asistencias: 4, mvp: 3 },
    { id: 5, catKey: 'sub6', name: 'L. Torres', dorsal: 5, pos: 'DEL', goles: 8, asistencias: 2, mvp: 4 },
    { id: 6, catKey: 'sub6', name: 'J. GÃ³mez', dorsal: 6, pos: 'DEL', goles: 4, asistencias: 1, mvp: 1 },
    { id: 7, catKey: 'sub6', name: 'K. Morales', dorsal: 7, pos: 'MED', goles: 2, asistencias: 5, mvp: 1 },
    { id: 8, catKey: 'sub6', name: 'E. RÃ­os', dorsal: 8, pos: 'DEF', goles: 0, asistencias: 1, mvp: 0 },
    { id: 9, catKey: 'sub6', name: 'C. Vega', dorsal: 9, pos: 'DEL', goles: 6, asistencias: 2, mvp: 2 },
    { id: 10, catKey: 'sub6', name: 'B. Soto', dorsal: 10, pos: 'MED', goles: 3, asistencias: 6, mvp: 2 },
    { id: 11, catKey: 'sub6', name: 'R. Pinto', dorsal: 11, pos: 'POR', goles: 0, asistencias: 0, mvp: 1 },
    { id: 12, catKey: 'sub6', name: 'G. Navas', dorsal: 12, pos: 'DEF', goles: 1, asistencias: 0, mvp: 0 },
    { id: 13, catKey: 'sub6', name: 'H. Molina', dorsal: 13, pos: 'MED', goles: 2, asistencias: 2, mvp: 0 },
    { id: 14, catKey: 'sub6', name: 'J. CedeÃ±o', dorsal: 14, pos: 'DEL', goles: 3, asistencias: 1, mvp: 1 },
    { id: 15, catKey: 'sub6', name: 'F. BenÃ­tez', dorsal: 15, pos: 'DEF', goles: 0, asistencias: 0, mvp: 0 },
    { id: 16, catKey: 'sub6', name: 'T. Ortega', dorsal: 16, pos: 'MED', goles: 1, asistencias: 3, mvp: 0 },
    { id: 17, catKey: 'sub6', name: 'N. Duarte', dorsal: 17, pos: 'DEL', goles: 2, asistencias: 0, mvp: 0 },
    { id: 18, catKey: 'sub6', name: 'P. AcuÃ±a', dorsal: 18, pos: 'MED', goles: 1, asistencias: 2, mvp: 0 },
    { id: 19, catKey: 'sub6', name: 'I. Vargas', dorsal: 19, pos: 'DEF', goles: 0, asistencias: 1, mvp: 0 },
    { id: 20, catKey: 'sub6', name: 'O. Espino', dorsal: 20, pos: 'DEL', goles: 4, asistencias: 1, mvp: 1 }
  ];

  const P_SUB12 = [
    { id: 1, catKey: 'sub12', name: 'E. Morales', dorsal: 1, pos: 'POR', goles: 0, asistencias: 1, mvp: 3 },
    { id: 2, catKey: 'sub12', name: 'C. Herrera', dorsal: 2, pos: 'DEF', goles: 2, asistencias: 3, mvp: 2 },
    { id: 3, catKey: 'sub12', name: 'J. Rivera', dorsal: 3, pos: 'MED', goles: 6, asistencias: 8, mvp: 4 },
    { id: 4, catKey: 'sub12', name: 'M. Aguirre', dorsal: 4, pos: 'MED', goles: 12, asistencias: 7, mvp: 6 },
    { id: 5, catKey: 'sub12', name: 'A. DÃ­az', dorsal: 5, pos: 'DEL', goles: 15, asistencias: 4, mvp: 5 },
    { id: 6, catKey: 'sub12', name: 'F. Silva', dorsal: 6, pos: 'DEL', goles: 9, asistencias: 5, mvp: 2 },
    { id: 7, catKey: 'sub12', name: 'L. Campos', dorsal: 7, pos: 'DEF', goles: 1, asistencias: 2, mvp: 1 },
    { id: 8, catKey: 'sub12', name: 'S. Ramos', dorsal: 8, pos: 'MED', goles: 4, asistencias: 6, mvp: 2 },
    { id: 9, catKey: 'sub12', name: 'D. Paredes', dorsal: 9, pos: 'DEL', goles: 7, asistencias: 3, mvp: 2 },
    { id: 10, catKey: 'sub12', name: 'K. NuÃ±ez', dorsal: 10, pos: 'POR', goles: 0, asistencias: 0, mvp: 1 },
    { id: 11, catKey: 'sub12', name: 'R. Castillo', dorsal: 11, pos: 'DEF', goles: 0, asistencias: 4, mvp: 1 },
    { id: 12, catKey: 'sub12', name: 'G. Mendoza', dorsal: 12, pos: 'MED', goles: 3, asistencias: 4, mvp: 0 },
    { id: 13, catKey: 'sub12', name: 'H. Rojas', dorsal: 13, pos: 'DEL', goles: 5, asistencias: 2, mvp: 1 },
    { id: 14, catKey: 'sub12', name: 'B. Montero', dorsal: 14, pos: 'DEF', goles: 1, asistencias: 1, mvp: 0 },
    { id: 15, catKey: 'sub12', name: 'V. Quedo', dorsal: 15, pos: 'MED', goles: 2, asistencias: 3, mvp: 0 },
    { id: 16, catKey: 'sub12', name: 'J. LeÃ³n', dorsal: 16, pos: 'DEL', goles: 4, asistencias: 1, mvp: 1 },
    { id: 17, catKey: 'sub12', name: 'P. Sosa', dorsal: 17, pos: 'DEF', goles: 0, asistencias: 2, mvp: 0 },
    { id: 18, catKey: 'sub12', name: 'N. Garza', dorsal: 18, pos: 'MED', goles: 1, asistencias: 2, mvp: 0 },
    { id: 19, catKey: 'sub12', name: 'T. Ponce', dorsal: 19, pos: 'DEL', goles: 3, asistencias: 0, mvp: 0 },
    { id: 20, catKey: 'sub12', name: 'W. Cordero', dorsal: 20, pos: 'POR', goles: 0, asistencias: 0, mvp: 0 }
  ];

  const P_SUB16 = [
    { id: 1, catKey: 'sub16', name: 'G. Vargas', dorsal: 1, pos: 'POR', goles: 0, asistencias: 2, mvp: 4 },
    { id: 2, catKey: 'sub16', name: 'K. Ramos', dorsal: 2, pos: 'DEF', goles: 3, asistencias: 2, mvp: 3 },
    { id: 3, catKey: 'sub16', name: 'R. Delgado', dorsal: 3, pos: 'DEF', goles: 1, asistencias: 4, mvp: 2 },
    { id: 4, catKey: 'sub16', name: 'B. MartÃ­nez', dorsal: 4, pos: 'MED', goles: 8, asistencias: 10, mvp: 6 },
    { id: 5, catKey: 'sub16', name: 'H. Salazar', dorsal: 5, pos: 'DEL', goles: 16, asistencias: 5, mvp: 7 },
    { id: 6, catKey: 'sub16', name: 'N. Vega', dorsal: 6, pos: 'DEL', goles: 11, asistencias: 6, mvp: 4 },
    { id: 7, catKey: 'sub16', name: 'J. Alveo', dorsal: 7, pos: 'MED', goles: 5, asistencias: 8, mvp: 2 },
    { id: 8, catKey: 'sub16', name: 'S. BultrÃ³n', dorsal: 8, pos: 'DEF', goles: 2, asistencias: 1, mvp: 1 },
    { id: 9, catKey: 'sub16', name: 'L. Samaniego', dorsal: 9, pos: 'DEL', goles: 7, asistencias: 3, mvp: 2 },
    { id: 10, catKey: 'sub16', name: 'D. Quintero', dorsal: 10, pos: 'MED', goles: 4, asistencias: 6, mvp: 2 },
    { id: 11, catKey: 'sub16', name: 'M. AraÃºz', dorsal: 11, pos: 'POR', goles: 0, asistencias: 0, mvp: 0 },
    { id: 12, catKey: 'sub16', name: 'C. Espinoza', dorsal: 12, pos: 'DEF', goles: 1, asistencias: 2, mvp: 0 },
    { id: 13, catKey: 'sub16', name: 'A. Batista', dorsal: 13, pos: 'MED', goles: 3, asistencias: 3, mvp: 1 },
    { id: 14, catKey: 'sub16', name: 'E. Becker', dorsal: 14, pos: 'DEL', goles: 6, asistencias: 2, mvp: 2 },
    { id: 15, catKey: 'sub16', name: 'O. JimÃ©nez', dorsal: 15, pos: 'DEF', goles: 0, asistencias: 1, mvp: 0 },
    { id: 16, catKey: 'sub16', name: 'F. Wong', dorsal: 16, pos: 'MED', goles: 2, asistencias: 4, mvp: 0 },
    { id: 17, catKey: 'sub16', name: 'I. Pimentel', dorsal: 17, pos: 'DEL', goles: 4, asistencias: 1, mvp: 1 },
    { id: 18, catKey: 'sub16', name: 'Y. Mosquera', dorsal: 18, pos: 'DEF', goles: 1, asistencias: 0, mvp: 0 },
    { id: 19, catKey: 'sub16', name: 'X. ValdÃ©s', dorsal: 19, pos: 'MED', goles: 2, asistencias: 2, mvp: 0 },
    { id: 20, catKey: 'sub16', name: 'Z. Guerra', dorsal: 20, pos: 'DEL', goles: 5, asistencias: 2, mvp: 1 }
  ];

  const P_SUBMAYOR = [
    { id: 1, catKey: 'submayor', name: 'V. CÃ³rdoba', dorsal: 1, pos: 'POR', goles: 0, asistencias: 3, mvp: 5 },
    { id: 2, catKey: 'submayor', name: 'O. Paredes', dorsal: 2, pos: 'DEF', goles: 4, asistencias: 2, mvp: 3 },
    { id: 3, catKey: 'submayor', name: 'I. Navarro', dorsal: 3, pos: 'DEF', goles: 2, asistencias: 5, mvp: 3 },
    { id: 4, catKey: 'submayor', name: 'E. Castillo', dorsal: 4, pos: 'MED', goles: 10, asistencias: 12, mvp: 8 },
    { id: 5, catKey: 'submayor', name: 'T. Meza', dorsal: 5, pos: 'MED', goles: 7, asistencias: 9, mvp: 4 },
    { id: 6, catKey: 'submayor', name: 'P. Fuentes', dorsal: 6, pos: 'DEL', goles: 18, asistencias: 4, mvp: 9 },
    { id: 7, catKey: 'submayor', name: 'R. Jaramillo', dorsal: 7, pos: 'DEL', goles: 12, asistencias: 7, mvp: 5 },
    { id: 8, catKey: 'submayor', name: 'K. Grimaldo', dorsal: 8, pos: 'MED', goles: 5, asistencias: 8, mvp: 2 },
    { id: 9, catKey: 'submayor', name: 'L. Melgar', dorsal: 9, pos: 'DEF', goles: 1, asistencias: 3, mvp: 1 },
    { id: 10, catKey: 'submayor', name: 'J. Caballero', dorsal: 10, pos: 'POR', goles: 0, asistencias: 1, mvp: 1 },
    { id: 11, catKey: 'submayor', name: 'S. Bernal', dorsal: 11, pos: 'DEF', goles: 2, asistencias: 1, mvp: 0 },
    { id: 12, catKey: 'submayor', name: 'A. CÃ¡rdenas', dorsal: 12, pos: 'MED', goles: 4, asistencias: 5, mvp: 1 },
    { id: 13, catKey: 'submayor', name: 'D. Hurtado', dorsal: 13, pos: 'DEL', goles: 8, asistencias: 3, mvp: 3 },
    { id: 14, catKey: 'submayor', name: 'F. CedeÃ±o', dorsal: 14, pos: 'DEF', goles: 0, asistencias: 2, mvp: 0 },
    { id: 15, catKey: 'submayor', name: 'M. PatiÃ±o', dorsal: 15, pos: 'MED', goles: 3, asistencias: 4, mvp: 1 },
    { id: 16, catKey: 'submayor', name: 'G. SolÃ­s', dorsal: 16, pos: 'DEL', goles: 6, asistencias: 2, mvp: 2 },
    { id: 17, catKey: 'submayor', name: 'H. MacÃ­as', dorsal: 17, pos: 'DEF', goles: 1, asistencias: 0, mvp: 0 },
    { id: 18, catKey: 'submayor', name: 'N. Broce', dorsal: 18, pos: 'MED', goles: 2, asistencias: 3, mvp: 0 },
    { id: 19, catKey: 'submayor', name: 'B. UreÃ±a', dorsal: 19, pos: 'DEL', goles: 5, asistencias: 1, mvp: 1 },
    { id: 20, catKey: 'submayor', name: 'J. Vergara', dorsal: 20, pos: 'MED', goles: 3, asistencias: 4, mvp: 1 }
  ];

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
          const pointer = canEdit ? 'cursor-pointer border-2 border-dashed border-nico-orange hover:border-solid hover:bg-white/5 transition-all' : '';
          const fullClass = `${cssClass} ${pointer}`;
          const isVideo = url.match(/\.(mp4|webm|ogg)$/i);
          const isYoutube = url.includes('youtube.com') || url.includes('youtu.be');
          
          const clickAction = canEdit ? `onclick="if(window.nicoAppAdmin) window.nicoAppAdmin.editarTextoMediaGlobal('${clave}', '${defaultUrl}')"` : '';
          
          if (isVideo) {
              return `<video src="${url}" controls class="${fullClass}" ${clickAction}></video>`;
          } else if (isYoutube) {
              let embedUrl = url.replace('watch?v=', 'embed/').replace('youtu.be/', 'youtube.com/embed/');
              const overlayBtn = canEdit ? `<div class="cursor-pointer absolute top-0 left-0 w-8 h-8 bg-black/80 text-white flex items-center justify-center rounded-br-lg z-10 hover:bg-nico-orange" ${clickAction} title="Editar Video URL"><i class="fas fa-edit"></i></div>` : '';
              return `<div class="relative ${fullClass}" style="padding-bottom: 56.25%; height: 0;"><iframe src="${embedUrl}" class="absolute top-0 left-0 w-full h-full rounded-lg" frameborder="0" allowfullscreen></iframe>${overlayBtn}</div>`;
          } else {
              return `<img src="${url}" class="${fullClass}" ${clickAction} alt="Media">`;
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
    loading: true, progress: 0,
    mainTab: 'historia', catTab: 'sub6', vitrinaCatTab: 'todos',
    loginModal: false, imageModal: null, staffModal: null,
    videoModal: false, playerModal: null, selectedTrophy: null,
    evalVerModal: null,
    plantillasExtra: {},

    /* ========== 02) SESIÃ“N Y ROLES ========== */
    currentUser: null, loginUsuario: '', loginPassword: '', loginError: '',
    evalModal: null, evaluacionesVersion: 0,
    evalDatos: { tecnicos: [0,0,0,0,0,0,0,0,0], tacticos: [0,0,0,0,0,0], fisicos: [0,0,0,0,0], actitudinales: [0,0,0,0,0,0] },
    evalItems: {
      tecnicos: ['Control de balÃ³n', 'Pase corto', 'Pase largo', 'ConducciÃ³n', 'Regate (1 vs 1)', 'RecepciÃ³n y primer toque', 'Remate a porterÃ­a', 'Cabeceo', 'DefiniciÃ³n'],
      tacticos: ['Posicionamiento', 'MarcaciÃ³n', 'Desmarque', 'Toma de decisiones', 'VisiÃ³n de juego', 'Trabajo en equipo'],
      fisicos: ['Velocidad', 'Resistencia', 'CoordinaciÃ³n', 'Equilibrio', 'Agilidad'],
      actitudinales: ['Disciplina', 'Puntualidad', 'Esfuerzo', 'Respeto', 'Compromiso', 'Liderazgo']
    },
    get gruposEval() {
      return [
        ['tecnicos', 'Aspectos TÃ©cnicos', 'fa-futbol', 'text-nico-orange'],
        ['tacticos', 'Aspectos TÃ¡cticos', 'fa-chess', 'text-nico-green'],
        ['fisicos', 'Aspectos FÃ­sicos', 'fa-bolt', 'text-nico-blue'],
        ['actitudinales', 'Aspectos Actitudinales', 'fa-heart', 'text-amber-400']
      ];
    },
    get esAdmin() { return !!this.currentUser && this.currentUser.rol === 'admin'; },
    get esPadre() { return !!this.currentUser && this.currentUser.rol === 'padre'; },
    get esEntrenador() { return !!this.currentUser && this.currentUser.rol === 'entrenador'; },
    get padreIdActual() { return this.currentUser ? (this.currentUser.padreId || this.currentUser.id) : null; },

    puedeEditarCategoria(catKey) {
      if (this.esAdmin) return true;
      if (this.esEntrenador && this.currentUser.catKey === catKey) return true;
      return false;
    },

    tabVisible(t) {
      if (!t || !t.role) return true;
      switch (t.role) {
        case 'all': return true;
        case 'admin': return this.esAdmin;
        case 'padre': return this.esPadre;
        case 'coach': return this.esAdmin || this.esEntrenador;
        case 'entrenador': return this.esEntrenador;
        default: return false;
      }
    },

    /* ========== 03) MODO EDICIÃ“N (LAPICITO) ========== */
    modoEdicion: false, editMediaModal: { open: false, title: '', url: '', type: 'image', callback: null },
    toggleModoEdicion() {
      this.modoEdicion = !this.modoEdicion;
      this.$nextTick(() => {
        document.querySelectorAll('[data-edit]').forEach(el => {
          if (this.modoEdicion && this.esAdmin) {
            el.setAttribute('contenteditable', 'true');
            el.onblur = () => { window.DB.guardarTexto(el.getAttribute('data-edit'), el.innerHTML); };
          } else {
            el.removeAttribute('contenteditable');
            el.onblur = null;
          }
        });
      });
    },
    aplicarTextos() {
      const t = window.DB.obtenerTextos();
      document.querySelectorAll('[data-edit]').forEach(el => {
        const k = el.getAttribute('data-edit');
        if (t[k] !== undefined) el.innerHTML = t[k];
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
    crearOReciclarJugador(catKey, nombre, dorsal, pos) {
      const reciclado = window.DB.obtenerJugadorReciclado(catKey, dorsal);
      if (reciclado) {
        reciclado.name = (nombre && nombre.trim()) || reciclado.name;
        if (pos) reciclado.pos = pos;
        reciclado.catKey = catKey;
        window.DB.quitarJugadorReciclado(catKey, dorsal);
        return reciclado;
      }
      return { id: Date.now() % 1000000, catKey, name: nombre.trim(), dorsal, pos, goles: 0, asistencias: 0, mvp: 0 };
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
    editarDorsal(player) {
      if (!this.esAdmin) return;
      const v = prompt('Nuevo nÃºmero (dorsal) para ' + player.name + ':', player.dorsal);
      if (v === null) return;
      const nuevo = parseInt(v); if (!nuevo || nuevo < 1) return;
      const arr = this.playersDe(player.catKey);
      if (arr.some(p => p.id !== player.id && p.dorsal === nuevo)) { alert(' El dorsal #' + nuevo + ' ya lo usa otro jugador'); return; }
      const antes = player.dorsal; player.dorsal = nuevo;
      this.persistPlantilla(player.catKey);
      this.registrarAccion({ tipo: 'dorsal', catKey: player.catKey, playerId: player.id, antes, despues: nuevo });
    },
    eliminarJugador(player) {
      if (!this.esAdmin) return;
      if (!confirm('Â¿Eliminar a ' + player.name + ' de la plantilla?')) return;
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
      if (!confirm('Â¿Eliminar el equipo ' + team.nombre + '?')) return;
      cat.tabla = cat.tabla.filter(t => t !== team); this.persistTorneo();
    },
    persistTorneo() { window.DB.guardarTorneo(this.categoriasTorneo); },

    /* ========== 08) CRUD CUERPO TÃ‰CNICO ========== */
    staffList: [
      { id: 1, nombre: 'Profe. Niko', cargo: 'Director TÃ©cnico Principal & Fundador', licencia: 'Licencia A FEPAFUT', exp: '18 AÃ±os de Experiencia', especialidad: 'Financiera', bio: 'Fundador de la Academia Nico Sport en 2008. Ha formado a mÃ¡s de 500 jÃ³venes deportistas y obtenido mÃºltiples tÃ­tulos en torneos locales.', fotoIcon: 'fa-user-tie', color: 'from-nico-orange via-red-600 to-amber-600' },
      { id: 2, nombre: 'Prof. Luis MuÃ±oz', cargo: 'Preparador FÃ­sico & psicolÃ³gico', licencia: 'Grado en Ciencias del Deporte', exp: '10 AÃ±os de Experiencia', especialidad: 'Resistencia AerÃ³bica, Potencia y PrevenciÃ³n de Lesiones', bio: 'Especialista en acondicionamiento fÃ­sico adaptado a fÃºtbol base y juvenil.', fotoIcon: 'fa-stopwatch-20', color: 'from-nico-green to-emerald-700' },
      { id: 3, nombre: 'Abdiel Aguirre', cargo: 'Entrenador de TÃ¡ctico', licencia: 'PrÃ³ximamente...', exp: '9 AÃ±os de Experiencia en el deporte', especialidad: 'Reflejos, Juego AÃ©reo y Salida con los Pies', bio: 'Jugador amateur enfocado en perfeccionar la tÃ©cnica de juego, posicionamiento en la cancha y liderazgo.', fotoIcon: 'fa-hands-glove', color: 'from-nico-blue to-cyan-700' },
      { id: 4, nombre: 'Fernado', cargo: 'PsicÃ³loga Deportiva & FormaciÃ³n Integral', licencia: 'PrÃ³ximamente...', exp: '5 AÃ±os de Experiencia', especialidad: 'GestiÃ³n de la FrustraciÃ³n, Trabajo en Equipo y ConcentraciÃ³n', bio: 'Encargada del desarrollo mental y emocional de los alumnos.', fotoIcon: 'fa-brain', color: 'from-purple-600 to-indigo-800' }
    ],
    editarStaff(s) {
      if (!this.esAdmin) return;
      const n = prompt('Nombre:', s.nombre); if (n === null) return;
      const c = prompt('Cargo:', s.cargo); if (c === null) return;
      const l = prompt('Licencia:', s.licencia); if (l === null) return;
      const e = prompt('Experiencia:', s.exp); if (e === null) return;
      const es = prompt('Especialidad:', s.especialidad); if (es === null) return;
      const b = prompt('BiografÃ­a:', s.bio); if (b === null) return;
      Object.assign(s, { nombre: n, cargo: c, licencia: l, exp: e, especialidad: es, bio: b });
      this.persistStaff();
    },
    eliminarStaff(s) {
      if (!this.esAdmin) return;
      if (!confirm('Â¿Eliminar a ' + s.nombre + ' del cuerpo tcnico?')) return;
      this.staffList = this.staffList.filter(x => x.id !== s.id); this.persistStaff();
    },
    agregarStaff() {
      if (!this.esAdmin) return;
      const n = prompt('Nombre del nuevo integrante:'); if (!n) return;
      this.staffList.push({ id: Date.now() % 1000000, nombre: n, cargo: 'Entrenador', licencia: 'PrÃ³ximamente...', exp: 'Por definir', especialidad: 'Por definir', bio: 'Integrante del cuerpo tcnico de la Academia Nico Sport.', fotoIcon: 'fa-user', color: 'from-nico-orange to-amber-600' });
      this.persistStaff();
    },
    persistStaff() { window.DB.guardarStaff(this.staffList); },

    /* ========== 09) VITRINA DE TROFEOS ========== */
    trofeosBase: [
      { id: 'trophy-sub12', nombre: 'PrÃ³ximamente Sub-12', catKey: 'sub12', categoria: 'CategorÃ­a Sub-12', 'anio': '...', icono: 'fa-trophy', color: 'text-amber-400', descripcion: 'PrÃ³ximamente en desarrollo...', evaluacion: { rendimientoGlobal: 0, posesion: '0%', efectividadPases: '0%', golesAFavor: 0, golesEnContra: 0, disciplina: 'En Proceso', resumenTactico: 'PrÃ³ximamente...' } },
      { id: 'trophy-sub16', nombre: 'PrÃ³ximamente Sub-16', catKey: 'sub16', categoria: 'CategorÃ­a Sub-16', 'anio': '...', icono: 'fa-medal', color: 'text-amber-400', descripcion: 'PrÃ³ximamente en desarrollo...', evaluacion: { rendimientoGlobal: 0, posesion: '0%', efectividadPases: '0%', golesAFavor: 0, golesEnContra: 0, disciplina: 'En Proceso', resumenTactico: 'PrÃ³ximamente...' } },
      { id: 'trophy-sub6', nombre: 'PrÃ³ximamente Sub-6', catKey: 'sub6', categoria: 'CategorÃ­a Sub-6', 'anio': '...', icono: 'fa-award', color: 'text-amber-400', descripcion: 'PrÃ³ximamente en desarrollo...', evaluacion: { rendimientoGlobal: 0, posesion: '0%', efectividadPases: '0%', golesAFavor: 0, golesEnContra: 0, disciplina: 'En Proceso', resumenTactico: 'PrÃ³ximamente...' } },
      { id: 'trophy-submayor', nombre: 'PrÃ³ximamente Sub-Mayor', catKey: 'submayor', categoria: 'Sub-Mayor', 'anio': '...', icono: 'fa-trophy', color: 'text-nico-blue', descripcion: 'PrÃ³ximamente en desarrollo...', evaluacion: { rendimientoGlobal: 0, posesion: '0%', efectividadPases: '0%', golesAFavor: 0, golesEnContra: 0, disciplina: 'En Proceso', resumenTactico: 'PrÃ³ximamente...' } }
    ],
    trofeosVersion: 0, mostrarFormTrofeo: false, trofeoEditId: null,
    nuevoTrofeo: { nombre: '', catKey: 'sub6', 'anio': '2026', descripcion: '', rendimientoGlobal: 100, posesion: '', efectividadPases: '', golesAFavor: 0, golesEnContra: 0, disciplina: '', resumenTactico: '' },
    get trophiesList() {
      void this.trofeosVersion;
      const ocultos = window.DB.obtenerTrofeosOcultos();
      const edits = window.DB.obtenerTrofeosEditados();
      const custom = window.DB.obtenerTrofeosCustom();
      const base = this.trofeosBase.filter(t => !ocultos.includes(t.id)).map(t => edits[t.id] ? Object.assign({}, t, edits[t.id]) : t);
      return base.concat(custom.map(t => edits[t.id] ? Object.assign({}, t, edits[t.id]) : t));
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
        categoria: nombresCat[this.nuevoTrofeo.catKey] || 'CategorÃ­a',
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
      if (!confirm('Â¿Eliminar este trofeo de la vitrina?')) return;
      if (String(id).indexOf('custom-') === 0) window.DB.eliminarTrofeoCustom(id);
      else window.DB.ocultarTrofeoBase(id);
      if (this.selectedTrophy && this.selectedTrophy.id === id) this.selectedTrophy = null;
      this.trofeosVersion++;
    },
    editarAnalisis(campo, label, esNumero) {
      if (!this.esAdmin || !this.modoEdicion || !this.selectedTrophy) return;
      const t = this.selectedTrophy;
      const v = prompt(label, t.evaluacion[campo]);
      if (v === null) return;
      const nuevo = Object.assign({}, t, { evaluacion: Object.assign({}, t.evaluacion, { [campo]: esNumero ? (parseInt(v) || 0) : v }) });
      window.DB.guardarTrofeoEditado(nuevo);
      this.trofeosVersion++;
      this.selectedTrophy = this.trophiesList.find(x => x.id === t.id) || nuevo;
    },

    /* ========== 10) EVALUACIÃ“N GLOBAL ========== */
    evalGlobalVersion: 0,
    evalGlobalBase: [
      { id: 1, catKey: 'sub6',     name: 'CategorÃ­a Sub-6 (Semillero)',              desc: 'Eficiencia LudotÃ©cnica', value: 96 },
      { id: 2, catKey: 'sub12',    name: 'CategorÃ­a Sub-12 (Desarrollo 11vs11)',     desc: 'Rendimiento de Torneo',  value: 94 },
      { id: 3, catKey: 'sub16',    name: 'CategorÃ­a Sub-16 (CompeticiÃ³n Regional)',  desc: 'Despliegue TÃ¡ctico',     value: 89 },
      { id: 4, catKey: 'submayor', name: 'Sub-Mayor & Futsal Tecnificado',           desc: 'DinÃ¡mica y PresiÃ³n',     value: 91 }
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
          item = { id: 1000 + idx, catKey: cat.key, name: 'CategorÃ­a ' + cat.label, desc: 'Rendimiento General', value: 50 };
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

    /* ========== 11) CRUD DE CATEGORÃAS ========== */
    catAdminVersion: 0,
    catNueva: { label: '', descripcion: '' },
    categoriasTorneo: [
      Object.assign({ key: 'sub6',     label: 'Sub-6',      descripcion: 'IniciaciÃ³n (4 a 6 aÃ±os)  LudotÃ©cnica' }, catTorneo()),
      Object.assign({ key: 'sub12',    label: 'Sub-12',     descripcion: 'Desarrollo (10 a 12 aÃ±os)  Formativa' }, catTorneo()),
      Object.assign({ key: 'sub16',    label: 'Sub-16',     descripcion: 'CompeticiÃ³n (14 a 16 aÃ±os)  Alto rendimiento' }, catTorneo()),
      Object.assign({ key: 'submayor', label: 'Sub-Mayor',  descripcion: 'Futsal & TecnificaciÃ³n  EspecializaciÃ³n' }, catTorneo())
    ],
    playersSub6: JSON.parse(JSON.stringify(P_SUB6)),
    playersSub12: JSON.parse(JSON.stringify(P_SUB12)),
    playersSub16: JSON.parse(JSON.stringify(P_SUB16)),
    playersSubMayor: JSON.parse(JSON.stringify(P_SUBMAYOR)),
    get categoriasUI() {
      void this.catAdminVersion;
      const oficiales = {
        sub6:     { tab: 'CategorÃ­a Sub-6', titulo: 'CATEGORÃA SUB-6', badge: 'IniciaciÃ³n (4 a 6 AÃ±os)', desc: 'CoordinaciÃ³n motriz primaria, psicomotricidad bÃ¡sica, lateralidad y primera adaptaciÃ³n divertida al balÃ³n mediante dinÃ¡micas ludotÃ©cnicas.', torneos: 'Proximante....', horario: 'sÃ¡bado (10:00 AM - 12:00 PM)', cancha: 'El pueblito - Santa Librada', count: '20 Jugadores Inscritos', entrenador: 'Prof. Luis jesus', foto: 'img/sub6.jpg' },
        sub12:    { tab: 'CategorÃ­a Sub-12', titulo: 'CATEGORÃA SUB-12', badge: 'Desarrollo (10 a 12 AÃ±os)', desc: 'FundamentaciÃ³n tÃ©cnica avanzada: pase orientado, conducciÃ³n en velocidad, perfilamiento tÃ¡ctico y partidos estructurados en formato 11vs11.', torneos: 'Pretemporada...', horario: 'sÃ¡bado (10:00 AM - 12:00 PM)', cancha: 'El pueblito - Santa Librada', count: '20 Jugadores Inscritos', entrenador: 'Prof. Abdiel Aguirre', foto: 'img/sub12.jpg' },
        sub16:    { tab: 'CategorÃ­a Sub-16', titulo: 'CATEGORÃA SUB-16', badge: 'CompeticiÃ³n (14 a 16 AÃ±os)', desc: 'Alto rendimiento fÃ­sico, tÃ¡ctica colectiva, sistemas de juego (4-3-3 / 4-2-3-1), resistencia aerÃ³bica y preparaciÃ³n para visorÃ­as universitarias o profesionales.', torneos: 'Pretemporada....', horario: 'sÃ¡bado (10:00 AM - 12:00 PM)', cancha: 'El pueblito - Santa Librada', count: '20 Jugadores Inscritos', entrenador: 'Prof. Fernando', foto: 'img/sub16.jpg' },
        submayor: { tab: 'Sub-Mayor / Futsal', titulo: 'SUB-MAYOR & TECNIFICACIÃ“N FUTSAL', badge: 'Alto Rendimiento & Futsal', desc: 'EspecializaciÃ³n para categorÃ­as mayores, acondicionamiento de alta exigencia, tÃ©cnica de piso, toma de decisiones veloz en espacios reducidos y salidas a presiÃ³n.', torneos: '.......', horario: '......', cancha: '.......', count: '20 Jugadores Seleccionados', entrenador: 'Dra. Elena Ramos', foto: 'img/mayor.jpg' }
      };
      let lista = this.categoriasTorneo;
      if (this.esEntrenador && !this.esAdmin) {
        lista = lista.filter(c => c.key === this.currentUser.catKey);
      }
      return lista.map((c, i) => {
        const base = oficiales[c.key];
        const col = base ? UI[c.key] : PALETA_NUEVOS[i % PALETA_NUEVOS.length];
        if (base) return Object.assign({ key: c.key }, col, base);
        return Object.assign({ key: c.key }, col, {
          tab: c.label,
          titulo: 'CATEGORÃA ' + c.label.toUpperCase(),
          badge: c.descripcion || 'CategorÃ­a Personalizada',
          desc: c.descripcion || 'CategorÃ­a personalizada de la Academia Nico Sport. Edita esta descripciÃ³n con el modo ediciÃ³n .',
          torneos: 'Por definir', horario: 'Por definir', cancha: 'Por definir',
          count: (this.playersDe(c.key).length || 0) + ' Jugadores Inscritos',
          entrenador: 'Por asignar',
          foto: 'img/escudo.png'
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
      if (!label) { alert(' Escribe el nombre de la nueva categorÃ­a'); return; }
      const key = 'cat' + Date.now().toString(36);
      const nueva = Object.assign({ key, label, descripcion: this.catNueva.descripcion.trim() || '' }, catTorneo());
      this.categoriasTorneo.push(nueva);
      this.plantillasExtra[key] = [];
      this.persistTorneo();
      this._syncCategoriasDB();
      this.catTab = key;
      this.catNueva = { label: '', descripcion: '' };
      this.catAdminVersion++;
      this.evalGlobalVersion++;
      alert(' CategorÃ­a "' + label + '" creada con plantilla automÃ¡tica.');
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
      this.openMediaEditModal('Foto Categora', cat.foto, 'image', (u) => {
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
      const label = prompt('Nombre de la categorÃ­a:', cat.label); if (label === null) return;
      const desc = prompt('DescripciÃ³n:', cat.descripcion); if (desc === null) return;
      const t = this.categoriasTorneo.find(c => c.key === cat.key);
      if (t) { t.label = label.trim() || t.label; t.descripcion = desc; }
      this.persistTorneo(); this._syncCategoriasDB(); this.catAdminVersion++;
    },
    eliminarCategoria(cat) {
      if (!this.esAdmin) return;
      if (cat.core) { alert(' Las 4 categorÃ­as oficiales no se pueden eliminar.'); return; }
      if (!confirm('Â¿Eliminar la categorÃ­a "' + cat.label + '"?')) return;
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
      { key: 'campeon', label: 'CampeÃ³n', icon: 'fas fa-award' }
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

    /* ========== 12) LOGIN / SESIÃ“N + EVALUACIÃ“N ========== */
    async intentarLogin() {
      this.loginError = 'Conectando...';
      try { await window.DB.listo; } catch(e) {}
      const u = await window.DB.login(this.loginUsuario.trim(), this.loginPassword.trim());
      if (u) {
        this.currentUser = u; localStorage.setItem('ns_currentUser', JSON.stringify(u)); this.loginModal = false; this.loginError = '';
        this.loginUsuario = ''; this.loginPassword = '';
        if (u.rol === 'admin') {
          this.mainTab = 'admin';
        } else if (u.rol === 'padre') {
          /* FIX 3: Padre va a CategorÃ­as si tiene hijo vinculado */
          if (u.hijoCat && u.hijoId) {
            this.mainTab = 'categorias';
            this.catTab = u.hijoCat;
          } else {
            this.mainTab = 'portal';
          }
        } else if (u.rol === 'entrenador') {
          this.mainTab = 'categorias';
          this.catTab = u.catKey || 'sub6';
        } else {
          this.mainTab = 'historia';
        }
        localStorage.setItem('ns_mainTab', this.mainTab); localStorage.setItem('ns_catTab', this.catTab); window.location.reload();
      } else this.loginError = 'Usuario o contraseÃ±a incorrectos';
    },
    cerrarSesion() {
        fetch('../api/logout.php').then(() => window.location.reload());
        this.currentUser = null; localStorage.removeItem('ns_currentUser'); localStorage.removeItem('ns_mainTab'); localStorage.removeItem('ns_catTab'); this.mainTab = 'historia';
      if (this.modoEdicion) this.toggleModoEdicion();
      document.body.classList.remove('is-admin');
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
      const all = [...this.evalDatos.tecnicos, ...this.evalDatos.tacticos, ...this.evalDatos.fisicos, ...this.evalDatos.actitudinales].filter(n => n > 0);
      if (!all.length) return 0;
      return Math.round((all.reduce((a, b) => a + b, 0) / all.length) * 10) / 10;
    },
    guardarEvaluacion() {},
    notaJugador(player) {
      void this.evaluacionesVersion;
      const ev = window.DB.obtenerEvaluacion(player.catKey, player.id);
      if (!ev) return null;
      const all = [...ev.tecnicos, ...ev.tacticos, ...ev.fisicos, ...ev.actitudinales].filter(n => n > 0);
      if (!all.length) return null;
      return all.reduce((a, b) => a + b, 0) / all.length;
    },

    /* ========== 13) ASISTENCIA Y ENCUESTAS ========== */
    asistenciaCat: 'sub6',
    asistenciaFecha: new Date().toISOString().slice(0, 10),
    asistenciaVersion: 0, encuestaVersion: 0,
    encuesta: { aspectos: [0,0,0,0,0,0,0,0,0,0,0], gustaMas: '', mejorar: '', crecimiento: '', crecimientoWhy: '', recomienda: '', recomiendaWhy: '', observaciones: '' },
    encuestaAspectos: [
      'Calidad de los entrenamientos', 'OrganizaciÃ³n de la academia', 'Puntualidad de los entrenadores',
      'Trato hacia los jugadores', 'ComunicaciÃ³n con los padres', 'Desarrollo tcnico de mi hijo(a)',
      'Desarrollo de valores (respeto, disciplina, compaÃ±erismo)', 'Ambiente de entrenamiento',
      'Seguridad durante las actividades', 'RelaciÃ³n costo-beneficio', 'SatisfacciÃ³n general con la academia'
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
      const prom = arr => { const f = arr.filter(n => n > 0); if (!f.length) return 0; return Math.round((f.reduce((a, b) => a + b, 0) / f.length) * 10) / 10; };
      return { tecnicos: prom(ev.tecnicos), tacticos: prom(ev.tacticos), fisicos: prom(ev.fisicos), actitudinales: prom(ev.actitudinales), general: this.notaJugador(player) };
    },
    enviarEncuesta() {
      const enc = JSON.parse(JSON.stringify(this.encuesta));
      enc.padre = this.currentUser ? this.currentUser.nombre : 'AnÃ³nimo';
      window.DB.guardarEncuesta(enc);
      this.encuestaVersion++;
      this.encuesta = { aspectos: [0,0,0,0,0,0,0,0,0,0,0], gustaMas: '', mejorar: '', crecimiento: '', crecimientoWhy: '', recomienda: '', recomiendaWhy: '', observaciones: '' };
      alert('Â¡Gracias! Tu evaluaciÃ³n fue enviada ');
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
    adminRegistro: {
      tipo: 'con_padre',
      jugadorNombre: '', catKey: '', jugadorDorsal: '', jugadorPos: 'MED',
      padreNombre: '', padreUsuario: '', padrePassword: '', telefonoPadre: ''
    },
    adminRegistroMsg: '', adminPadresVersion: 0, comentariosVersion: 0,
    filtroPadresCat: '', busquedaPadresAdmin: '',
    get listaPadres() { void this.adminPadresVersion; return window.DB.obtenerPadres(); },
    get listaPadresFiltrados() {
      void this.adminPadresVersion;
      let lista = window.DB.obtenerPadres().slice().sort((a,b) => (a.nombre||'').localeCompare(b.nombre||''));
      if (this.filtroPadresCat) lista = lista.filter(p => p.hijoCat === this.filtroPadresCat);
      if (this.busquedaPadresAdmin) {
        const q = this.busquedaPadresAdmin.toLowerCase();
        lista = lista.filter(p => (p.nombre||'').toLowerCase().includes(q) || (p.usuario||'').toLowerCase().includes(q));
      }
      return lista;
    },
    
    get dorsalLibreParaAdmin() {
      if (!this.adminRegistro.catKey) return '';
      return this.dorsalLibre(this.adminRegistro.catKey);
    },

    registrarUnificado() {
      const r = this.adminRegistro;
      
      // 1. Validar Jugador
      if (!r.jugadorNombre.trim() || !r.catKey) {
        this.adminRegistroMsg = ' Nombre del jugador y categorÃ­a son obligatorios';
        setTimeout(() => this.adminRegistroMsg = '', 3000);
        return;
      }
      
      // 2. Validar Padre (Si aplica)
      if (r.tipo === 'con_padre') {
        if (!r.padreNombre.trim() || !r.padreUsuario.trim() || !r.padrePassword.trim() || !r.telefonoPadre.trim()) {
          this.adminRegistroMsg = ' Completa todos los datos de la cuenta familiar';
          setTimeout(() => this.adminRegistroMsg = '', 3000);
          return;
        }
      }

      // 3. Crear Jugador Directamente en la Plantilla
      const arr = this.playersDe(r.catKey);
      const libre = this.dorsalLibre(r.catKey);
      let dorsal = parseInt(r.jugadorDorsal) || libre;
      
      // Si el dorsal sugerido estÃ¡ ocupado, usar el libre automÃ¡ticamente.
      if (arr.some(pl => pl.dorsal === dorsal)) dorsal = libre;
      
      const player = this.crearOReciclarJugador(r.catKey, r.jugadorNombre, dorsal, r.jugadorPos);
      arr.push(player);
      this.persistPlantilla(r.catKey);
      this.registrarAccion({ tipo: 'add', catKey: r.catKey, playerId: player.id, player });

      // 4. Crear Padre y Vincularlo Inmediatamente
      if (r.tipo === 'con_padre') {
        const res = window.DB.registrarPadre(r.padreNombre.trim(), r.padreUsuario.trim(), r.padrePassword, r.catKey, player.id, r.telefonoPadre.trim());
        if (!res.ok) {
          this.adminRegistroMsg = ' Error al crear cuenta familiar: ' + res.msg;
          arr.pop(); this.persistPlantilla(r.catKey);
          return;
        }
        this.adminPadresVersion++; this.comentariosVersion++;
      }

      // 5. Exito y reset
      this.adminRegistroMsg = ' ' + player.name + ' registrado exitosamente.';
      setTimeout(() => this.adminRegistroMsg = '', 4000);
      
      this.adminRegistro = {
        tipo: 'con_padre',
        jugadorNombre: '', catKey: '', jugadorDorsal: '', jugadorPos: 'MED',
        padreNombre: '', padreUsuario: '', padrePassword: '', telefonoPadre: ''
      };
    },

    eliminarPadre(id) {
      if (!confirm('Â¿Eliminar este perfil familiar? El jugador seguirÃ¡ existiendo en la plantilla.')) return;
      window.DB.eliminarPadre(id);
      this.adminPadresVersion++; this.comentariosVersion++;
    },

    /* ========== 15) TEMPORIZADOR QUINCENAL ========== */
    proximaQuincena() {
      const hoy = new Date();
      const dia = hoy.getDate();
      let proxima;
      if (dia <= 15) {
        proxima = new Date(hoy.getFullYear(), hoy.getMonth(), 15);
      } else {
        const ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).getDate();
        proxima = new Date(hoy.getFullYear(), hoy.getMonth(), ultimoDia);
      }
      if (proxima < hoy) {
        if (dia <= 15) proxima = new Date(hoy.getFullYear(), hoy.getMonth(), 15);
        else proxima = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 15);
      }
      return proxima.toLocaleDateString('es-PA', { day: '2-digit', month: 'short', year: 'numeric' });
    },
    diasParaQuincena() {
      const hoy = new Date();
      hoy.setHours(0,0,0,0);
      const dia = hoy.getDate();
      let proxima;
      if (dia <= 15) {
        proxima = new Date(hoy.getFullYear(), hoy.getMonth(), 15);
      } else {
        const ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).getDate();
        proxima = new Date(hoy.getFullYear(), hoy.getMonth(), ultimoDia);
      }
      if (proxima < hoy) proxima = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 15);
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
           const orden = { 'vencido': 1, 'pendiente': 2, 'aldia': 3 };
           if (orden[a.estadoPago] !== orden[b.estadoPago]) return orden[a.estadoPago] - orden[b.estadoPago];
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
      if (!confirm(`Â¿Cambiar mÃ©todo de pago a ${nuevoMetodo === "efectivo" ? "Efectivo" : "En LÃ­nea"}?`)) return;
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
      if (!this.pagoManual.monto || this.pagoManual.monto <= 0) { alert(' Ingresa un monto vÃ¡lido'); return; }
      const padre = this.listaPadres.find(p => p.id == this.pagoManual.padreId);
      if (!padre) { alert(' Padre no encontrado'); return; }
      
      const conceptoLabel = this.pagoManual.concepto === 'inscripcion' ? 'InscripciÃ³n' : (this.pagoManual.concepto === 'mensualidad' ? 'Mensualidad' : 'Otro');
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

      // Actualizar fecha de vencimiento +15 dÃ­as
      const d = new Date(this.pagoManual.fecha);
      d.setDate(d.getDate() + 15);
      const nuevaFecha = d.toISOString().slice(0,10);
      
      padre.fechaVencimiento = nuevaFecha;
      padre.metodoPago = metodo;
      
      window.DB.actualizarPadre(padre.id, { 
        fechaVencimiento: nuevaFecha,
        metodoPago: metodo
      });

      this.adminPadresVersion++; this.comentariosVersion++;
      this.pagoManual = { padreId: '', concepto: 'mensualidad', monto: 15, fecha: new Date().toISOString().slice(0,10), referencia: '', metodo: 'efectivo' };
      alert('Pago registrado correctamente. PrÃ³ximo vencimiento: ' + nuevaFecha);
    },
    
    bloquearCuentaPadre(padreId) {
      if (!confirm('Â¿Bloquear el acceso de esta cuenta a la plataforma? (El niÃ±o solo tendrÃ¡ 1 dÃ­a de entreno)')) return;
      window.DB.actualizarPadre(padreId, { password: 'BLOQUEADO_' + Date.now() });
      this.adminPadresVersion++; this.comentariosVersion++;
      alert('Cuenta bloqueada exitosamente.');
    },
    
    desbloquearCuentaPadre(padreId) {
      if (!confirm('Â¿Desbloquear esta cuenta? Se restaurar? su contraseÃ±a a su nÃºmero de usuario (DNI).')) return;
      const padre = this.listaPadres.find(p => p.id == padreId);
      if (padre) {
        window.DB.actualizarPadre(padreId, { password: padre.usuario });
        this.adminPadresVersion++; this.comentariosVersion++;
        alert('Cuenta desbloqueada. Su nueva contraseÃ±a es su nÃºmero de usuario.');
      }
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
          let lista = this.estadoDeCuentas.filter(p => p.metodoPago === 'linea');
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
              alert('Este padre no tiene comprobantes pendientes de validaciÃ³n.');
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
      deshacerPago(padreId) {
          if (!confirm('Â¿EstÃ¡s seguro de deshacer este pago y marcar al usuario como PENDIENTE?')) return;
          const padre = this.listaPadres.find(p => p.id == padreId);
          if (padre) {
              const f = new Date();
              f.setDate(f.getDate() - 15);
              padre.fechaVencimiento = f.toISOString().slice(0,10);
              if(window.DB.post) {
                  window.DB.post('usuarios/actualizarPago', { id: padre.id, telefono: padre.telefono, fechaVencimiento: padre.fechaVencimiento, metodoPago: padre.metodo_pago });
              }
              this.adminPadresVersion++; this.comentariosVersion++;
          }
      },
      aprobarPagoDirecto(padreId) {
          const comp = window.DB.obtenerComprobantes().find(c => c.padreId == padreId && (c.estado === 'en_revision' || c.estado === 'pendiente'));
          if (comp) {
              window.DB.validarComprobante(comp.id, true);
          }
          const padre = this.listaPadres.find(p => p.id == padreId);
          if (padre) {
              const f = new Date();
              f.setDate(f.getDate() + 15);
              padre.fechaVencimiento = f.toISOString().slice(0,10);
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
          if(!aprobar && !confirm('Â¿EstÃ¡s seguro de rechazar este pago?')) return;
          
          window.DB.validarComprobante(comp.id, aprobar);
          
          if (aprobar) {
              const padre = this.listaPadres.find(p => p.id == padreId);
              if (padre) {
                  const f = new Date();
                  f.setDate(f.getDate() + 15);
                  padre.fechaVencimiento = f.toISOString().slice(0,10);
                  if(window.DB.post) {
                      window.DB.post('usuarios/actualizarPago', { id: padre.id, telefono: padre.telefono, fechaVencimiento: padre.fechaVencimiento, metodoPago: padre.metodo_pago });
                  }
                  this.adminPadresVersion++; this.comentariosVersion++;
                  // Muestra una notificaciÃ³n rÃ¡pida sin bloquear (toast)
                  const toast = document.createElement('div');
                  toast.className = 'fixed top-4 right-4 bg-nico-green text-white font-bold py-2 px-4 rounded-xl shadow-lg z-50 animate-bounce';
                  toast.innerText = 'Pago Aprobado (Al DÃ­a)';
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
                  const f = new Date();
                  f.setDate(f.getDate() + 15);
                  padre.fechaVencimiento = f.toISOString().slice(0,10);
                  if(window.DB.post) {
                      window.DB.post('usuarios/actualizarPago', { id: padre.id, telefono: padre.telefono, fechaVencimiento: padre.fechaVencimiento, metodoPago: padre.metodo_pago });
                  }
                  this.adminPadresVersion++; this.comentariosVersion++;
                  alert('Pago aprobado. El estado del padre ha cambiado a Pagado (Al dÃ­a).');
              }
          } else {
              alert('Comprobante rechazado. El padre deberÃ¡ subirlo de nuevo.');
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
      guardarPagoModal() {
          if(!this.pagoActual.id) return;
          const padre = this.listaPadres.find(p => p.id == this.pagoActual.id);
          if(!padre) return;
          const f = new Date(this.pagoActual.fecha);
          f.setDate(f.getDate() + 15);
          padre.fechaVencimiento = f.toISOString().slice(0,10);
          this.adminPadresVersion++; this.comentariosVersion++;
          alert('Pago registrado correctamente. El estado pasarÃ¡ a VERDE.');
          this.cerrarModalPago();
      },
      bloquearCuenta(id) {
          if(!confirm('Â¿Bloquear la cuenta de esta familia? No podrÃ¡n acceder a la plataforma.')) return;
          alert('Cuenta bloqueada exitosamente.');
      },
      recordatorioMasivo() {
      const padresPendientes = [...this.padresParaCobroEfectivoFiltrados, ...this.padresParaCobroLineaFiltrados].filter(p => p.estadoPago !== 'aldia' && p.estadoPago !== 'amarillo');
      if (padresPendientes.length === 0) { alert('No hay padres con pagos pendientes o vencidos.'); return; }
      if (!confirm('Se prepararÃ¡n mensajes para ' + padresPendientes.length + ' familias pendientes. Â¿Deseas continuar?')) return;
      let i = 0;
      const next = () => {
        if (i >= padresPendientes.length) { alert('Â¿Todos los recordatorios fueron preparados!'); return; }
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
          if (!telf) { alert('Este padre no tiene nÃºmero de telÃ©fono registrado.'); return; }
          let msg = '';
          if (tipo === 'efectivo') {
              msg = `Hola ${padre.nombre}, le recordamos desde la Academia Nico Sport que su pago estÃ¡ pendiente. Por favor acÃ©rquese a nuestras instalaciones para realizar su pago en efectivo.`;
          } else {
              msg = `Hola ${padre.nombre}, le recordamos desde la Academia Nico Sport que su ciclo de pago estÃ¡ por vencer. Por favor realice su pago en lÃ­nea.`;
          }
          window.open(`https://wa.me/${telf}?text=${encodeURIComponent(msg)}`, '_blank');
      },
      recordatorioWhatsappViejo(padreId) {
      const padre = this.listaPadres.find(p => p.id == padreId);
      if (!padre) return;
      const telf = padre.telefono || '';
      if (!telf) { alert('Este padre no tiene nÃºmero de telÃ©fono registrado.'); return; }
      const msg = `Hola ${padre.nombre}, le recordamos desde la Academia Nico Sport que su fecha de pago ha vencido. Por favor, regularice su situaciÃ³n.`;
      window.open(`https://wa.me/${telf}?text=${encodeURIComponent(msg)}`, '_blank');
    },
    cambiarMetodoPago(padreId, nuevoMetodo) {
      if (!confirm(`Â¿Cambiar mÃ©todo de pago a ${nuevoMetodo === "efectivo" ? "Efectivo" : "En LÃ­nea"}?`)) return;
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
    historialPagosClub() {
      return window.DB.obtenerPagosClub().slice().reverse();
    },

    /* ========== 18) GESTIÃ“N DE ENTRENADORES ========== */
    nuevoEntrenador: { nombre: '', usuario: '', password: '', catKey: '' },
    entrenadorMsg: '',
    entrenadoresVersion: 0,
    get listaEntrenadores() {
      void this.entrenadoresVersion;
      return window.DB.obtenerEntrenadores();
    },
    registrarEntrenador() {
      if (!this.esAdmin) return;
      const d = this.nuevoEntrenador;
      if (!d.nombre.trim() || !d.usuario.trim() || !d.password.trim() || !d.catKey) {
        this.entrenadorMsg = ' Todos los campos son obligatorios';
        return;
      }
      const cat = this.categoriasTorneo.find(c => c.key === d.catKey);
      const res = window.DB.registrarEntrenador({
        nombre: d.nombre.trim(),
        usuario: d.usuario.trim(),
        password: d.password,
        catKey: d.catKey,
        catLabel: cat ? cat.label : d.catKey
      });
      this.entrenadorMsg = res.ok ? ' ' + res.msg : ' ' + res.msg;
      if (res.ok) {
        this.nuevoEntrenador = { nombre: '', usuario: '', password: '', catKey: '' };
        this.entrenadoresVersion++;
      }
    },
    editarEntrenador(dt) {
      if (!this.esAdmin) return;
      const nombre = prompt('Nombre:', dt.nombre); if (nombre === null) return;
      const usuario = prompt('Usuario:', dt.usuario); if (usuario === null) return;
      const password = prompt('ContraseÃ±a (dejar igual para no cambiar):', '');
      const cat = this.categoriasTorneo.find(c => c.key === dt.catKey);
      const data = { nombre, usuario, catLabel: cat ? cat.label : dt.catKey };
      if (password && password.trim()) data.password = password.trim();
      window.DB.editarEntrenador(dt.id, data);
      this.entrenadoresVersion++;
      alert(' Entrenador actualizado');
    },
    eliminarEntrenador(id) {
      if (!this.esAdmin) return;
      if (!confirm('Â¿Eliminar este entrenador?')) return;
      window.DB.eliminarEntrenador(id);
      this.entrenadoresVersion++;
      alert(' Entrenador eliminado');
    },

    /* ========== 19) PORTAL DE PADRES ========== */
          tarifaOficialMensualidad: 15.00,
      tarifaOficialInscripcion: 15.00,
      cargarTarifas() {
          const conf = window.DB.obtenerConfiguraciones ? window.DB.obtenerConfiguraciones() : {};
          if(conf['tarifa_mensualidad']) this.tarifaOficialMensualidad = parseFloat(conf['tarifa_mensualidad']);
          if(conf['tarifa_inscripcion']) this.tarifaOficialInscripcion = parseFloat(conf['tarifa_inscripcion']);
      },
      guardarTarifas() {
          window.DB.post('configuraciones/guardar', {
              mensualidad: this.tarifaOficialMensualidad,
              inscripcion: this.tarifaOficialInscripcion
          });
          const toast = document.createElement('div');
          toast.className = 'fixed top-4 right-4 bg-nico-green text-white font-bold py-2 px-4 rounded-xl shadow-lg z-50 animate-bounce';
          toast.innerText = 'Tarifas Guardadas (Refresca para sincronizar global)';
          document.body.appendChild(toast);
          setTimeout(() => toast.remove(), 3000);
      },
      reportePago: { concepto: 'Mensualidad', monto: 15, fecha: new Date().toISOString().slice(0,10), referencia: '', observaciones: '', comprobanteNombre: '', comprobanteData: '' },
    handleComprobantePadre(e) {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (ev) => {
        this.reportePago.comprobanteNombre = file.name;
        this.reportePago.comprobanteData = ev.target.result;
      };
      reader.readAsDataURL(file);
    },
          comprobantesDelPadre() {
          if (!this.currentUser || this.currentUser.rol !== 'padre') return [];
          return window.DB.obtenerComprobantes().filter(c => c.padreId == this.currentUser.id && (c.estado === 'en_revision' || c.estado === 'pendiente'));
      },
      get hijoPlayer() {
      if (!this.currentUser || !this.currentUser.hijoCat || !this.currentUser.hijoId) return null;
      return this.playersDe(this.currentUser.hijoCat).find(p => p.id === this.currentUser.hijoId) || null;
    },
    get hijoCatLabel() {
      if (!this.currentUser || !this.currentUser.hijoCat) return '';
      const c = this.categoriasTorneo.find(x => x.key === this.currentUser.hijoCat);
      return c ? c.label : this.currentUser.hijoCat;
    },
    estadoCuentaPadre() {
      if (!this.currentUser || this.currentUser.rol !== 'padre') return 'Vigente';
      const pagos = window.DB.obtenerPagosPadre(this.padreIdActual);
      const validados = pagos.filter(p => p.estado === 'validado');
      if (validados.length === 0) return 'Vencido';
      const ultimo = validados.sort((a,b) => new Date(b.fecha) - new Date(a.fecha))[0];
      const fechaUlt = new Date(ultimo.fecha);
      const hoy = new Date();
      const diff = Math.floor((hoy - fechaUlt) / (1000 * 60 * 60 * 24));
      return diff <= 15 ? 'Vigente' : 'Vencido';
    },
          enviarReportePago() {
        if (!this.currentUser || this.currentUser.rol !== 'padre') { alert(' Inicia sesiÃ³n como padre'); return; }
        if (!this.reportePago.comprobanteNombre || this.reportePago.comprobanteNombre === 'Subiendo...') { alert(' Adjunta el comprobante'); return; }
        
        const fechaActual = new Date().toISOString().slice(0,10);
        const montoOficial = this.tarifaOficialMensualidad;
        const conceptoStr = 'Mensualidad';

        window.DB.guardarPagoPadre(this.padreIdActual, {
          concepto: conceptoStr,
          monto: montoOficial,
          fecha: fechaActual,
          referencia: this.reportePago.referencia,
          observaciones: this.reportePago.observaciones || '',
          comprobante: this.reportePago.comprobanteData
        });
        window.DB.guardarComprobante({
          padreId: this.padreIdActual,
          padreNombre: this.currentUser.nombre,
          concepto: conceptoStr,
          monto: montoOficial,
          fecha: fechaActual,
          referencia: this.reportePago.referencia,
          observaciones: this.reportePago.observaciones || '',
          comprobante: this.reportePago.comprobanteData
        });
        const msg = encodeURIComponent(
          ' *NICO SPORT - Reporte de Pago*\n' +
          ' Padre: ' + this.currentUser.nombre + '\n' +
          ' Concepto: ' + conceptoStr + '\n' +
          ' Monto: $' + montoOficial.toFixed(2) + '\n' +
          ' Fecha: ' + new Date().toLocaleDateString('es-PA') + '\n' +
          ' Ref: ' + (this.reportePago.referencia || '') + '\n' +
          ' Comprobante enviado. Pendiente de validaciÃ³n.'
        );
        window.open('https://wa.me/50760000000?text=' + msg, '_blank');
        alert(' Reporte enviado + NotificaciÃ³n por WhatsApp al administrador');
        this.reportePago = { concepto: 'Mensualidad', monto: 15, fecha: fechaActual, referencia: '', observaciones: '', comprobanteNombre: '', comprobanteData: '' };
        this.adminPadresVersion++; this.comentariosVersion++; // Forzar actualizaciÃ³n reactiva en Alpine
      },
      misPagos() {
      if (!this.currentUser || this.currentUser.rol !== 'padre') return [];
      return window.DB.obtenerPagosPadre(this.padreIdActual).slice().reverse();
    },

    /* ========== 20) NAVEGACIÃ“N Y CARGA ========== */
    switchTab(tab, event) {
      this.mainTab = tab;
      if (event && window.triggerParticles && event.clientX !== undefined) window.triggerParticles(event.clientX, event.clientY);
      this.$nextTick(() => {
        const target = document.getElementById('contenido-dinamico');
        if (target) {
          const offset = window.innerWidth < 640 ? 90 : 160;
          const y = target.getBoundingClientRect().top + window.scrollY - offset;
          window.scrollTo({ top: y, behavior: 'smooth' });
        }
        const nav = document.querySelector('#mobileNav .nav-scroll');
        const btn = document.querySelector('[data-mobtab="' + tab + '"]');
        if (nav && btn) {
          nav.scrollTo({ left: btn.offsetLeft - (nav.clientWidth / 2) + (btn.clientWidth / 2), behavior: 'smooth' });
        }
      });
    },
    cargarPersistidos() {
      const t = window.DB.obtenerTorneo();
      if (t && t.length) {
        this.categoriasTorneo = t.map(c => Object.assign(catTorneo(), c));
      }
      const cats = window.DB.obtenerCategorias();
      if (cats && cats.length) this.aplicarCategoriasGuardadas(cats);
      const s = window.DB.obtenerStaff();
      if (s && s.length) this.staffList = s;
      this.categoriasTorneo.forEach(c => {
        const p = window.DB.obtenerPlantilla(c.key);
        if (p && p.length) { p.sort((a, b) => a.dorsal - b.dorsal); this._setPlantilla(c.key, p); }
      });
      // FIX REACTIVO: forzar que Alpine re-evalÃºe listaPadres y categorÃ­as
      // despuÃ©s de que la BD cargÃ³ asÃ­ncronamente
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
            this.mostrarFeedback('Ingresa un asunto o tÃ­tulo', true); 
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
        this.mostrarFeedback('Â¡Mensaje enviado con Ã©xito al club! Gracias por tus comentarios.', false);
      },
      mostrarFeedback(msg, error = false) {
          this.feedbackStatus = { show: true, msg, error };
          setTimeout(() => { this.feedbackStatus.show = false; }, 4000);
      },
      get comentariosPadres() { void this.comentariosVersion; return window.DB.obtenerComentarios(); },
      borrarComentario(id) {
        if(confirm('Â¿Seguro que deseas borrar este comentario?')) {
            window.DB.borrarComentario(id); this.comentariosVersion++;
        }
      },
      estadoComentarioColor(tipo) {
        if (tipo === 'sugerencia') return 'text-blue-400 border-blue-400/30';
        if (tipo === 'critica_constructiva') return 'text-amber-400 border-amber-400/30';
        if (tipo === 'inconformidad') return 'text-red-400 border-red-400/30';
        if (tipo === 'felicitacion') return 'text-green-400 border-green-400/30';
        return 'text-gray-400 border-gray-400/30';
      },
      
      iniciarCarga() {
      let intervalo = setInterval(() => {
        this.progress += 2;
        if (this.progress >= 100) {
          this.progress = 100; clearInterval(intervalo);
          setTimeout(() => {
            this.loading = false;
            this.selectedTrophy = this.trophiesList[0];
            this.cargarPersistidos();
            this.aplicarTextos();
          }, 800);
        }
      }, 50);
    },
        init() {
        window.nicoAppAdmin = this;
        
        // --- AUTH PERSISTENCE (HYDRATION) ---
        const savedUser = localStorage.getItem('ns_currentUser');
        if (savedUser) {
            try {
                const u = JSON.parse(savedUser);
                this.currentUser = u;
                if(u.rol === 'admin') document.body.classList.add('is-admin');
                
                const savedTab = localStorage.getItem('ns_mainTab');
                const savedCat = localStorage.getItem('ns_catTab');
                
                if (savedTab) this.mainTab = savedTab;
                else {
                    if (u.rol === 'admin') this.mainTab = 'admin';
                    else if (u.rol === 'padre') this.mainTab = (u.hijoCat && u.hijoId) ? 'categorias' : 'portal';
                    else if (u.rol === 'entrenador') this.mainTab = 'categorias';
                }
                if (savedCat) this.catTab = savedCat;
                else if (u.rol === 'padre' && u.hijoCat) this.catTab = u.hijoCat;
                else if (u.rol === 'entrenador' && u.catKey) this.catTab = u.catKey;
            } catch(e) {}
        }

        // Watchers for saving state dynamically
        this.$watch('mainTab', val => localStorage.setItem('ns_mainTab', val));
        this.$watch('catTab', val => localStorage.setItem('ns_catTab', val));

        this.cargarTarifas();
        
        const self = this;
        Promise.resolve(window.DB.listo).then(async function () {
          // --- ASYNC AUTH VERIFICATION ---
          if (self.currentUser) {
              const realUser = await window.DB.verificarSesion();
              if (realUser) {
                  self.currentUser = realUser;
                  localStorage.setItem('ns_currentUser', JSON.stringify(realUser));
              } else {
                  self.cerrarSesion();
              }
          }
          self.iniciarCarga();

          // --- AUTO SYNC EN TIEMPO REAL (Polleo silencioso) ---
          setInterval(() => {
            if (window.DB && typeof window.DB.sincronizar === 'function') {
                window.DB.sincronizar(self);
            }
          }, 15000);

        });
    }
  }));
});
