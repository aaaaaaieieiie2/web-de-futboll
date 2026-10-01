<div x-show="mainTab === 'admin' && esAdmin" x-data="{ adminSubTab: 'padres', adminCatSeleccionada: 'sub6' }">
  <!-- Navegacin del CMS -->
  <div class="flex gap-2 mb-4 overflow-x-auto pb-2 scroll-container-sm border-b border-white/10">
    <button @click="adminSubTab = 'padres'" :class="adminSubTab === 'padres' ? 'bg-nico-orange text-white font-bold shadow-lg' : 'bg-black text-gray-400 hover:text-white border border-white/10'" class="px-4 py-2 rounded-xl text-xs uppercase whitespace-nowrap transition-all"><i class="fas fa-users mr-1"></i> Familias & Categoras</button>
    <button @click="adminSubTab = 'plantillas'" :class="adminSubTab === 'plantillas' ? 'bg-nico-green text-black font-bold shadow-lg' : 'bg-black text-gray-400 hover:text-white border border-white/10'" class="px-4 py-2 rounded-xl text-xs uppercase whitespace-nowrap transition-all"><i class="fas fa-tshirt mr-1"></i> Jugadores</button>
    <button @click="adminSubTab = 'cuerpo'" :class="adminSubTab === 'cuerpo' ? 'bg-nico-blue text-white font-bold shadow-lg' : 'bg-black text-gray-400 hover:text-white border border-white/10'" class="px-4 py-2 rounded-xl text-xs uppercase whitespace-nowrap transition-all"><i class="fas fa-user-tie mr-1"></i> Cuerpo Tcnico</button>
    <button @click="adminSubTab = 'media'" :class="adminSubTab === 'media' ? 'bg-amber-400 text-black font-bold shadow-lg' : 'bg-black text-gray-400 hover:text-white border border-white/10'" class="px-4 py-2 rounded-xl text-xs uppercase whitespace-nowrap transition-all"><i class="fas fa-photo-video mr-1"></i> Galera / Enlaces</button>
  </div>

  <!-- Pestaa 1: Familias -->
  <div x-show="adminSubTab === 'padres'" x-transition.opacity>
<!-- ==================== SECCI�?N 12: PANEL ADMIN (NUEVA UNIFICACI�?N) ==================== -->
<div>
<div class="glass-box p-6 rounded-3xl border border-nico-orange/40 text-center space-y-2">
<span class="text-xs font-black text-white border border-white/20 bg-black px-4 py-1 rounded-full uppercase tracking-widest shadow-lg">Paúnel de Administración</span>
<h3 class="text-2xl font-black text-white uppercase">Gestión del Club</h3>
<p class="text-xs text-gray-400">Registro unificado 2 en 1 · edita estadísticas en plaúntillas con el �?�️ lapicito</p>
</div>
<div class="admin-section relative overflow-hidden bg-gradient-to-br from-black to-[#0a0a0a]">
<div class="absolute top-0 right-0 w-40 h-40 bg-nico-orange/5 blur-[60px] rounded-full pointer-events-none"></div>
<h3 class="flex items-center gap-2"><i class="fas fa-user-plus text-nico-orange"></i> Registro Unificado: Jugador y Familia</h3>
<p class="text-[11px] text-gray-400 mb-4">Añade al jugador y crea la cuenta del padre/madre en un solo paso para vinculación inmediata en la plataforma.</p>
<div class="flex items-center gap-6 mb-5 bg-black/40 p-3 rounded-xl border border-white/5 inline-flex">
<label class="text-xs text-white font-black uppercase tracking-wider cursor-pointer flex items-center gap-2"><input type="radio" value="con_padre" x-model="adminRegistro.tipo" class="accent-nico-orange"> Menor de Edad (Con Tutor)</label>
<label class="text-xs text-white font-black uppercase tracking-wider cursor-pointer flex items-center gap-2"><input type="radio" value="sin_padre" x-model="adminRegistro.tipo" class="accent-nico-orange"> Adulto (Sin Tutor)</label>
</div>
<div class="p-4 rounded-2xl border border-nico-orange/30 bg-nico-orange/5 space-y-3 mb-4 shadow-lg">
<p class="text-[10px] font-black text-nico-orange uppercase tracking-widest flex items-center gap-2"><i class="fas fa-running"></i> Datos del Jugador a Inscribir</p>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
<input type="text" class="admin-input !bg-black/60 focus:!border-nico-orange" placeholder="Nombre completo del jugador" x-model="adminRegistro.jugadorNombre">
<select class="admin-input !bg-black/60 focus:!border-nico-orange" x-model="adminRegistro.catKey">
<option value="">Seleccionar Categoría...</option>
<template x-for="cat in categoriasTorneo" :key="cat.key"><option :value="cat.key" x-text="cat.label"></option></template>
</select>
<input type="number" class="admin-input !bg-black/60 focus:!border-nico-orange" :placeholder="'Dorsal (Sugerido libre: #' + dorsalLibreParaAdmin + ')'" x-model="adminRegistro.jugadorDorsal">
<select class="admin-input !bg-black/60 focus:!border-nico-orange" x-model="adminRegistro.jugadorPos">
<option value="POR">Portero (POR)</option><option value="DEF">Defensa (DEF)</option><option value="MED" selected>Mediocampo (MED)</option><option value="DEL">Delaúntero (DEL)</option>
</select>
</div>
</div>
<div x-show="adminRegistro.tipo === 'con_padre'" x-transition class="p-4 rounded-2xl border border-nico-green/30 bg-nico-green/5 space-y-3 mb-4 shadow-lg">
<p class="text-[10px] font-black text-nico-green uppercase tracking-widest flex items-center gap-2"><i class="fas fa-user-shield"></i> Credenciales de la Familia (Para la App)</p>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
<input type="text" class="admin-input !bg-black/60 focus:!border-nico-green" placeholder="Nombre del Padre/Madre" x-model="adminRegistro.padreNombre">
<input type="text" class="admin-input !bg-black/60 focus:!border-nico-green" placeholder="Número de Teléfono" x-model="adminRegistro.telefonoPadre">
<input type="text" class="admin-input !bg-black/60 focus:!border-nico-green" placeholder="Usuario de acceso (ej: familia.perez)" x-model="adminRegistro.padreUsuario">
<form onsubmit="return false"><input type="password" class="admin-input !bg-black/60 focus:!border-nico-green" placeholder="Contraseña de acceso" x-model="adminRegistro.padrePassword"></form>
</div>
<p class="text-[9px] text-gray-500 italic">Al crear la cuenta, el padre verá inmediatamente el progreso de este jugador en su portal.</p>
</div>
<div class="flex items-center gap-4 flex-wrap pt-2 border-t border-white/10">
<button class="admin-btn bg-gradient-to-r from-nico-orange to-red-600 px-6 py-2.5 rounded-xl shadow-[0_0_15px_rgba(255,85,0,0.3)] hover:scale-[1.02] transition-transform" @click="registrarUnificado()"><i class="fas fa-check-circle mr-1"></i> Completar Registro</button>
<div class="text-xs font-bold px-3 py-1.5 rounded-lg border" :class="adminRegistroMsg.includes('�??') ? 'text-nico-green bg-nico-green/10 border-nico-green/30' : 'text-nico-red bg-nico-red/10 border-nico-red/30'" x-text="adminRegistroMsg" x-show="adminRegistroMsg" x-transition></div>
</div>
</div>
<div class="admin-section">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <h3><i class="fas fa-users"></i> Familias Registradas (<span x-text="listaPadresFiltrados.length"></span>)</h3>
    <select x-model="filtroPadresCat" class="admin-input !bg-black/60 focus:!border-nico-green w-full sm:w-auto text-xs py-1.5 h-auto">
        <option value="">Todas las Categorías</option>
        <template x-for="cat in categoriasTorneo" :key="cat.key">
            <option :value="cat.key" x-text="cat.label"></option>
        </template>
    </select>
</div>
<template x-if="listaPadresFiltrados.length === 0">
    <p class="text-xs text-gray-500 text-center py-6 italic border border-white/5 rounded-xl bg-black/30">No hay familias registradas aún en esta categoría.</p>
</template>
<div class="scroll-container-sm pr-2 overflow-y-auto" style="max-height: 400px;">
    <div class="grid sm:grid-cols-2 gap-3">
        <template x-for="(padre, idx) in listaPadresFiltrados" :key="padre.id">
            <div class="bg-black/40 border border-white/10 rounded-xl p-3 flex justify-between items-center hover:border-nico-green/30 transition-colors group">
                <div>
                    <div class="font-black text-white text-sm" x-text="(idx + 1) + '. ' + padre.nombre"></div>
                    <div class="text-[10px] text-gray-400 font-mono">User: <span class="text-nico-orange" x-text="padre.usuario"></span></div>
                    <div class="text-[10px] text-gray-400 font-mono" x-show="padre.telefono">Tel: <span class="text-nico-green" x-text="padre.telefono"></span></div>
                    <div class="text-[10px] font-bold mt-1" :class="padre.hijoId ? 'text-nico-green' : 'text-gray-500'">
                        <i class="fas fa-child-reaching mr-1"></i>
                        <span x-text="padre.hijoCat && padre.hijoId ? 'Hijo vinculado: ' + ((playersDe(padre.hijoCat).find(p => p.id === padre.hijoId) || {}).name || 'Desconocido') + ' (' + padre.hijoCat + ')' : 'Sin jugador vinculado'"></span>
                    </div>
                </div>
                <button class="text-red-500 hover:text-white text-xs w-8 h-8 rounded-lg bg-red-500/10 hover:bg-red-500 transition-all flex items-center justify-center opacity-50 group-hover:opacity-100 shrink-0" title="Eliminar familia" @click="eliminarPadre(padre.id)"><i class="fas fa-trash"></i></button>
            </div>
        </template>
    </div>
</div>
</div>
<div class="admin-section">
<h3><i class="fas fa-layer-group"></i> Gestión de Categorías</h3>
<p class="text-[11px] text-gray-400 mb-3">Crea, edita o elimina divisiones del club. (Las oficiales no se pueden borrar).</p>
<template x-for="(cat, i) in categoriasAdmin" :key="cat.key">
<div class="bg-black/40 border border-white/10 rounded-xl p-2.5 mb-2 flex justify-between items-center hover:border-nico-orange/30 transition-colors">
<div class="flex-1 mr-2 min-w-0">
<div class="font-black text-white text-xs truncate uppercase tracking-wide" x-text="cat.label"></div>
<div class="text-[10px] text-gray-500 truncate mt-0.5" x-text="cat.descripcion"></div>
</div>
<div class="flex items-center gap-1.5 shrink-0">
<button @click="subirCategoria(i)" :disabled="i === 0" class="btn-edit-mini disabled:opacity-20" title="Subir orden"><i class="fas fa-arrow-up"></i></button>
<button @click="bajarCategoria(i)" :disabled="i === categoriasAdmin.length - 1" class="btn-edit-mini disabled:opacity-20" title="Bajar orden"><i class="fas fa-arrow-down"></i></button>
<button @click="editarCategoria(cat)" class="btn-edit-mini bg-white/5" title="Editar detalles"><i class="fas fa-pen"></i></button>
<button x-show="!cat.core" @click="eliminarCategoria(cat)" class="btn-del-mini" title="Eliminar categoría"><i class="fas fa-trash"></i></button>
<span x-show="cat.core" class="text-[8px] text-amber-400 font-black px-1.5 py-0.5 bg-amber-400/10 rounded border border-amber-400/20" title="Categoría base del sistema">OFICIAL</span>
</div>
</div>
</template>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-4 p-3 border border-white/10 rounded-xl bg-black/20">
<input type="text" class="admin-input mb-0" placeholder="Nombre nueva categoría (ej: Sub-Femenina)" x-model="catNueva.label">
<input type="text" class="admin-input mb-0" placeholder="Descripción corta" x-model="catNueva.descripcion">
<button class="admin-btn sm:col-span-2 mt-1 bg-gradient-to-r from-nico-blue to-[#052a55]" @click="crearCategoria()"><i class="fas fa-plus"></i> Crear Nueva Categoría</button>
</div>
</div>

  </div> <!-- Fin Pestaa Familias -->

  <!-- Pestaa 2: Plantillas (Jugadores) -->
  <div x-show="adminSubTab === 'plantillas'" x-transition.opacity class="space-y-6">
    <div class="admin-section">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <h3><i class="fas fa-tshirt"></i> Gestin de Plantillas</h3>
        <select x-model="adminCatSeleccionada" class="admin-input !bg-black/60 focus:!border-nico-green w-full sm:w-auto text-xs py-1.5 h-auto mb-0">
            <template x-for="cat in categoriasTorneo" :key="'admin-p-'+cat.key">
                <option :value="cat.key" x-text="cat.label"></option>
            </template>
        </select>
      </div>
      
      <!-- Lista de Jugadores de la categora seleccionada -->
      <div class="space-y-2">
        <template x-for="player in playersDe(adminCatSeleccionada)" :key="player.id">
            <div class="bg-black/40 border border-white/10 rounded-xl p-3 flex flex-col md:flex-row justify-between items-start md:items-center gap-3 hover:border-nico-green/50 transition-colors">
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <img :src="getMediaUrl(player.foto, 'img/placeholders/player_silueta.png')" class="w-10 h-10 rounded-full object-cover border border-white/20">
                    <div class="flex-1">
                        <input type="text" x-model="player.name" class="bg-transparent text-sm font-black text-white w-full border-b border-white/10 focus:border-nico-green outline-none" @change="persistPlantilla(adminCatSeleccionada)">
                        <div class="flex items-center gap-2 mt-1 text-[10px]">
                            <span class="text-gray-400">Dorsal:</span> <input type="number" x-model.number="player.dorsal" class="bg-black/50 text-nico-orange w-10 px-1 border border-white/10 rounded" @change="persistPlantilla(adminCatSeleccionada)">
                            <span class="text-gray-400 ml-2">Pos:</span> 
                            <select x-model="player.pos" class="bg-black/50 text-white border border-white/10 rounded px-1" @change="persistPlantilla(adminCatSeleccionada)">
                                <option value="POR">POR</option><option value="DEF">DEF</option><option value="MED">MED</option><option value="DEL">DEL</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2 w-full md:w-auto justify-end">
                    <button @click="openMediaEditModal('Foto de ' + player.name, player.foto, 'image', (u) => { player.foto = u; persistPlantilla(adminCatSeleccionada); })" class="btn-edit-mini bg-white/10" title="Editar URL de Foto"><i class="fas fa-image"></i> URL</button>
                    <button @click="eliminarJugador(player)" class="btn-del-mini" title="Eliminar Jugador"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </template>
        <template x-if="playersDe(adminCatSeleccionada).length === 0">
            <p class="text-xs text-gray-500 text-center py-4 italic">No hay jugadores en esta categora.</p>
        </template>
      </div>
      <button class="mt-4 admin-btn !bg-nico-green !text-black" @click="adminRegistro.catKey = adminCatSeleccionada; adminSubTab = 'padres';"><i class="fas fa-plus"></i> Inscribir Nuevo Jugador</button>
    </div>
  </div>

  <!-- Pestaa 3: Cuerpo Tcnico -->
  <div x-show="adminSubTab === 'cuerpo'" x-transition.opacity class="space-y-6">
    <div class="admin-section">
      <div class="flex items-center justify-between mb-4">
        <h3><i class="fas fa-user-tie"></i> Miembros del Staff</h3>
        <button class="btn-edit-mini !bg-nico-blue !text-white px-3" @click="agregarStaff()">+ Agregar Miembro</button>
      </div>
      <div class="space-y-3">
        <template x-for="s in staffList" :key="s.id">
            <div class="bg-black/40 border border-white/10 rounded-xl p-3 space-y-2 hover:border-nico-blue/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img :src="getMediaUrl(s.fotoUrl, '')" x-show="s.fotoUrl" class="w-10 h-10 rounded-full object-cover">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center bg-white/5 border border-white/10" x-show="!s.fotoUrl">
                            <i class="fas" :class="s.fotoIcon || 'fa-user'"></i>
                        </div>
                        <div>
                            <input type="text" x-model="s.nombre" class="bg-transparent text-sm font-black text-white w-full border-b border-white/10 focus:border-nico-blue outline-none" @change="persistStaff()">
                            <input type="text" x-model="s.cargo" class="bg-transparent text-[10px] text-nico-blue font-bold w-full outline-none mt-1" @change="persistStaff()">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="openMediaEditModal('Foto de ' + s.nombre, s.fotoUrl, 'image', (u) => { s.fotoUrl = u; persistStaff(); })" class="btn-edit-mini bg-white/10" title="Editar Foto"><i class="fas fa-image"></i></button>
                        <button @click="eliminarStaff(s)" class="btn-del-mini"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-[10px]">
                    <div><span class="text-gray-500">Licencia:</span> <input type="text" x-model="s.licencia" class="bg-black/50 text-white px-1 border border-white/10 rounded w-full" @change="persistStaff()"></div>
                    <div><span class="text-gray-500">Exp:</span> <input type="text" x-model="s.exp" class="bg-black/50 text-white px-1 border border-white/10 rounded w-full" @change="persistStaff()"></div>
                </div>
            </div>
        </template>
      </div>
    </div>
  </div>

  <!-- Pestaa 4: Media -->
  <div x-show="adminSubTab === 'media'" x-transition.opacity class="space-y-6">
    <div class="admin-section">
      <h3><i class="fas fa-photo-video"></i> Enlaces y Medios Globales</h3>
      <p class="text-[11px] text-gray-400 mb-4">Ingresa las URL (HTTP/HTTPS) para reemplazar las imgenes y videos por defecto de la web.</p>
      
      <div class="space-y-3">
        <!-- Usamos un array esttico con las claves ms comunes para que Alpine las renderice, pero podemos hardcodearlas -->
        <div class="bg-black/40 border border-white/10 rounded-xl p-3 flex justify-between items-center hover:border-amber-400/50">
            <div>
                <div class="text-xs font-black text-white">Video Principal (Home)</div>
                <div class="text-[9px] text-gray-500">Video de fondo en el carrusel inicial (Recomendado: enlace YouTube o MP4)</div>
            </div>
            <button @click="editarTextoMediaGlobal('home_hero_video', 'img/hero_bg.mp4')" class="admin-btn !py-1.5 !px-3 !text-[10px]"><i class="fas fa-link"></i> Cambiar URL</button>
        </div>
        <div class="bg-black/40 border border-white/10 rounded-xl p-3 flex justify-between items-center hover:border-amber-400/50">
            <div>
                <div class="text-xs font-black text-white">Foto Historia (Escudo)</div>
                <div class="text-[9px] text-gray-500">Imagen principal de la seccin Escudo e Historia</div>
            </div>
            <button @click="editarTextoMediaGlobal('media_escudo_historia', 'img/escudo.png')" class="admin-btn !py-1.5 !px-3 !text-[10px]"><i class="fas fa-link"></i> Cambiar URL</button>
        </div>
        <div class="bg-black/40 border border-white/10 rounded-xl p-3 flex justify-between items-center hover:border-amber-400/50">
            <div>
                <div class="text-xs font-black text-white">Comunicado Oficial</div>
                <div class="text-[9px] text-gray-500">Imagen del formulario o anuncio general</div>
            </div>
            <button @click="editarTextoMediaGlobal('media_comunicado', 'img/formulario.jpeg')" class="admin-btn !py-1.5 !px-3 !text-[10px]"><i class="fas fa-link"></i> Cambiar URL</button>
        </div>
        <div class="bg-black/40 border border-white/10 rounded-xl p-3 flex justify-between items-center hover:border-amber-400/50">
            <div>
                <div class="text-xs font-black text-white">Imagen Visoras</div>
                <div class="text-[9px] text-gray-500">Foto promocional para captacin de talentos</div>
            </div>
            <button @click="editarTextoMediaGlobal('media_visoria', 'img/visoria.jpg')" class="admin-btn !py-1.5 !px-3 !text-[10px]"><i class="fas fa-link"></i> Cambiar URL</button>
        </div>
        <div class="bg-black/40 border border-white/10 rounded-xl p-3 flex justify-between items-center hover:border-amber-400/50">
            <div>
                <div class="text-xs font-black text-white">Resumen de Temporada</div>
                <div class="text-[9px] text-gray-500">Video resumen de actividades</div>
            </div>
            <button @click="editarTextoMediaGlobal('media_video_temporada', 'video/resumen-temporada.mp4')" class="admin-btn !py-1.5 !px-3 !text-[10px]"><i class="fas fa-link"></i> Cambiar URL</button>
        </div>
        <div class="bg-black/40 border border-white/10 rounded-xl p-3 flex justify-between items-center hover:border-amber-400/50">
            <div>
                <div class="text-xs font-black text-white">Imagen Uniforme</div>
                <div class="text-[9px] text-gray-500">Foto de la indumentaria oficial</div>
            </div>
            <button @click="editarTextoMediaGlobal('media_uniforme', 'img/uniforme.png')" class="admin-btn !py-1.5 !px-3 !text-[10px]"><i class="fas fa-link"></i> Cambiar URL</button>
        </div>
      </div>
    </div>
  </div>

</div>
</div>
