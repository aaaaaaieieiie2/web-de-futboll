<!-- ==================== SECCIÓN 10: GESTIÓN DE ENTRENADORES ==================== -->
<div x-show="mainTab === 'entrenadores' && esAdmin"" class="space-y-6 anim-entry">
<div class="text-center space-y-2">
<span class="text-xs font-black text-white border border-white/20 bg-black px-4 py-1 rounded-full uppercase tracking-widest shadow-lg">Control de Acceso</span>
<h3 class="text-2xl font-black text-white uppercase">Gestión de Entrenadores</h3>
<p class="text-xs text-gray-400">Crea credenciales para DT con acceso restringido a su categoría asignada</p>
</div>
<div class="glass-box p-5 rounded-3xl border border-nico-blue/40 space-y-4">
<h4 class="text-xs font-black text-nico-blue uppercase flex items-center gap-2"><i class="fas fa-user-plus"></i> Registrar Nuevo Entrenador</h4>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
<input type="text" class="admin-input" placeholder="Nombre completo" x-model="nuevoEntrenador.nombre">
<input type="text" class="admin-input" placeholder="Usuario (ej: dt.sub6)" x-model="nuevoEntrenador.usuario">
<form onsubmit="return false" style="margin:0;padding:0;width:100%"><input type="password" autocomplete="new-password" class="admin-input" placeholder="Contraseña" x-model="nuevoEntrenador.password"></form>
<select class="admin-input" x-model="nuevoEntrenador.catKey">
<option value="">Categoría asignada...</option>
<template x-for="cat in categoriasTorneo" :key="cat.key"><option :value="cat.key" x-text="cat.label"></option></template>
</select>
</div>
<button @click="registrarEntrenador()" class="admin-btn" style="background: linear-gradient(135deg, #0B4F9C, #052a55); box-shadow: 0 0 15px rgba(11,79,156,0.4);"><i class="fas fa-user-tie"></i> Crear Entrenador</button>
<span class="text-xs font-bold ml-2" :class="entrenadorMsg.includes('?') ? 'text-nico-green' : 'text-nico-red'" x-text="entrenadorMsg" x-show="entrenadorMsg"></span>
</div>
<div class="glass-box p-5 rounded-3xl border border-white/10 space-y-4">
<h4 class="text-xs font-black text-white uppercase flex items-center gap-2"><i class="fas fa-users"></i> Entrenadores Registrados (<span x-text="listaEntrenadores.length"></span>)</h4>
<template x-if="listaEntrenadores.length === 0">
<p class="text-xs text-gray-500 text-center py-6">No hay entrenadores registrados</p>
</template>
<div class="scroll-container-sm rounded-2xl">
<template x-for="dt in listaEntrenadores" :key="dt.id">
<div class="bg-black/60 p-4 rounded-2xl border border-white/10 mb-2 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
<div class="flex items-center gap-3">
<div class="w-12 h-12 rounded-xl bg-nico-blue/20 border border-nico-blue/40 flex items-center justify-center">
<i class="fas fa-user-tie text-nico-blue text-xl"></i>
</div>
<div>
<p class="text-sm font-black text-white" x-text="dt.nombre"></p>
<p class="text-[10px] text-gray-400">Usuario: <span class="text-nico-blue font-mono" x-text="dt.usuario"></span></p>
<p class="text-[10px] text-nico-green font-bold">Categoría: <span x-text="dt.catLabel"></span></p>
</div>
</div>
<div class="flex gap-2">
<button @click="editarEntrenador(dt)" class="btn-edit-mini"><i class="fas fa-pen"></i></button>
<button @click="eliminarEntrenador(dt.id)" class="btn-del-mini"><i class="fas fa-trash"></i></button>
</div>
</div>
</template>
</div>
</div>
<div class="glass-box p-5 rounded-3xl border border-amber-400/30 space-y-3">
<h4 class="text-xs font-black text-amber-400 uppercase flex items-center gap-2"><i class="fas fa-shield-halved"></i> Permisos del Rol Entrenador</h4>
<div class="grid sm:grid-cols-2 gap-3 text-[11px]">
<div class="bg-nico-green/10 border border-nico-green/30 p-3 rounded-xl">
<p class="text-nico-green font-black uppercase mb-2"><i class="fas fa-check"></i> Acceso Permitido</p>
<ul class="text-gray-300 space-y-1">
<li>? Vitrina de Campeonatos</li>
<li>? Torneo & Tabla (solo su categoría)</li>
<li>? Cuerpo Técnico</li>
<li>? Categorías (solo la asignada)</li>
<li>? Escudo e Historia</li>
<li>? Asistencia (solo su categoría)</li>
<li>? Evaluación de jugadores (solo su categoría)</li>
</ul>
</div>
<div class="bg-nico-red/10 border border-nico-red/30 p-3 rounded-xl">
<p class="text-nico-red font-black uppercase mb-2"><i class="fas fa-ban"></i> Bloqueado</p>
<ul class="text-gray-300 space-y-1">
<li>? Panel Administrativo general</li>
<li>? Evaluación de Padres</li>
<li>? Mensajes de padres</li>
<li>? Asistencia Global</li>
<li>? Pagos del Club</li>
<li>? Pago de Plataforma</li>
<li>? Gestión de Entrenadores</li>
</ul>
</div>
</div>
</div>
</div>