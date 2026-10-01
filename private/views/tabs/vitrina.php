<!-- ==================== SECCIÓN 5: VITRINA ==================== -->
<div x-show="mainTab === 'vitrina'" class="space-y-8 anim-entry">
<div class="glass-box p-6 sm:p-8 rounded-3xl border border-amber-400/40 space-y-6">
<div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/10 pb-4">
<div>
<span class="text-xs font-black text-amber-400 uppercase tracking-widest flex items-center gap-2">
<i class="fas fa-trophy"></i> Vitrina de Campeonatos por Categoría
</span>
<h3 class="text-2xl sm:text-3xl font-black text-white uppercase mt-1">Palmarés & Rendimiento en Competiciones</h3>
</div>
<div class="flex items-center gap-2">
<span class="bg-black text-amber-400 text-xs font-black px-4 py-1.5 rounded-full border border-amber-400/40 shadow-lg">4 Categorías Oficiales</span>
<button x-show="esAdmin" @click="abrirFormTrofeo()" class="w-9 h-9 rounded-full bg-nico-green text-black font-black text-xl leading-none shadow-[0_0_15px_rgba(16,185,129,0.5)] hover:scale-110 transition-all" title="Agregar trofeo">+</button>
</div>
</div>
<div x-show="esAdmin && mostrarFormTrofeo" x-transition class="bg-black/60 p-4 rounded-2xl border border-nico-green/40 space-y-3">
<h4 class="text-xs font-black text-nico-green uppercase tracking-widest flex items-center gap-2"><i class="fas fa-trophy"></i> <span x-text="trofeoEditId ? 'Editar Trofeo' : 'Agregar Trofeo a la Vitrina'"></span></h4>
<div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
<input type="text" x-model="nuevoTrofeo.nombre" placeholder="Nombre del campeonato" class="admin-input">
<select x-model="nuevoTrofeo.catKey" class="admin-input">
<template x-for="cat in categoriasTorneo" :key="cat.key"><option :value="cat.key" x-text="cat.label"></option></template>
</select>
<input type="text" x-model="nuevoTrofeo.anio" placeholder="Año (ej: 2026)" class="admin-input">
<input type="text" x-model="nuevoTrofeo.descripcion" placeholder="Descripción corta" class="admin-input">
</div>
<div class="grid grid-cols-2 sm:grid-cols-6 gap-2">
<input type="number" x-model="nuevoTrofeo.rendimientoGlobal" placeholder="Rendimiento %" class="admin-input">
<input type="text" x-model="nuevoTrofeo.posesion" placeholder="Posesión (ej: 65%)" class="admin-input">
<input type="text" x-model="nuevoTrofeo.efectividadPases" placeholder="Pases (ej: 78%)" class="admin-input">
<input type="number" x-model="nuevoTrofeo.golesAFavor" placeholder="Goles a favor" class="admin-input">
<input type="number" x-model="nuevoTrofeo.golesEnContra" placeholder="Goles en contra" class="admin-input">
<input type="text" x-model="nuevoTrofeo.disciplina" placeholder="Disciplina (ej: Excelente)" class="admin-input">
</div>
<textarea x-model="nuevoTrofeo.resumenTactico" rows="2" placeholder="Informe técnico de rendimiento en competición..." class="admin-input"></textarea>
<div class="flex items-center gap-2">
<button @click="guardarTrofeoForm()" class="admin-btn"><i class="fas fa-floppy-disk"></i> Guardar Trofeo</button>
<button @click="mostrarFormTrofeo = false; trofeoEditId = null" class="admin-btn admin-btn-secondary">Cancelar</button>
</div>
</div>
<div class="flex items-center gap-2 flex-wrap">
<button @click="vitrinaCatTab = 'todos'; selectedTrophy = trophiesList[0] || selectedTrophy; triggerParticles($event.clientX, $event.clientY)" :class="vitrinaCatTab === 'todos' ? 'bg-amber-400 text-black font-extrabold shadow-md' : 'bg-black text-gray-400 hover:text-white border border-white/10'" class="px-3.5 py-2 rounded-xl text-xs transition-all">Todos los Campeonatos</button>
<template x-for="cat in categoriasTorneo" :key="'vit-'+cat.key">
<button @click="vitrinaCatTab = cat.key; selectedTrophy = trophiesList.find(t => t.catKey === cat.key) || selectedTrophy; triggerParticles($event.clientX, $event.clientY)" :class="vitrinaCatTab === cat.key ? 'bg-amber-400 text-black font-extrabold shadow-md' : 'bg-black text-gray-400 hover:text-white border border-white/10'" class="px-3.5 py-2 rounded-xl text-xs transition-all" x-text="cat.label"></button>
</template>
</div>
<div class="scroll-container rounded-2xl">
<div class="grid sm:grid-cols-2 md:grid-cols-4 gap-4 p-2">
<template x-for="trophy in trophiesList" :key="trophy.id">
<div x-show="vitrinaCatTab === 'todos' || vitrinaCatTab === trophy.catKey" @click="selectedTrophy = trophy; triggerParticles($event.clientX, $event.clientY)"
:class="selectedTrophy && selectedTrophy.id === trophy.id ? 'border-amber-400 scale-[1.02] shadow-[0_0_20px_rgba(245,158,11,0.3)]' : 'border-amber-400/30 hover:border-amber-400'"
class="vitrina-card p-5 rounded-2xl space-y-3 cursor-pointer transition-all relative">
<template x-if="esAdmin">
<div class="absolute -top-2 -right-2 z-20 flex gap-1">
<button @click.stop="editarTrofeo(trophy)" class="btn-edit-mini" title="Editar trofeo"><i class="fas fa-pen"></i></button>
<button @click.stop="eliminarTrofeo(trophy.id)" class="btn-del-mini" title="Eliminar trofeo"><i class="fas fa-minus"></i></button>
</div>
</template>
<div class="flex items-center justify-between">
<i class="fas" :class="[trophy.icono, trophy.color, 'text-2xl']"></i>
<span class="text-[10px] font-mono font-bold text-amber-400 bg-black/80 px-2.5 py-1 rounded-full border border-amber-400/30" x-text="trophy.anio"></span>
</div>
<div>
<h4 class="font-black text-white text-sm uppercase" x-text="trophy.nombre"></h4>
<span class="text-[11px] font-bold text-nico-orange block mt-0.5" x-text="trophy.categoria"></span>
<p class="text-[11px] text-gray-400 mt-1" x-text="trophy.descripcion"></p>
</div>
<div class="pt-2 border-t border-white/10 flex justify-between items-center text-[10px] font-bold text-gray-300">
<span>Ver Rendimiento Táctico</span>
<i class="fas fa-chart-line text-amber-400"></i>
</div>
</div>
</template>
</div>
</div>
</div>
<div x-show="selectedTrophy !== null" class="glass-box p-6 sm:p-8 rounded-3xl border-t-4 border-t-amber-400 space-y-6">
<div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/10 pb-4">
<div>
<span class="text-xs font-black text-amber-400 uppercase tracking-widest flex items-center gap-2">
<i class="fas fa-chart-pie"></i> Análisis Metodológico de Competición
</span>
<h3 class="text-2xl font-black text-white uppercase mt-1" x-text="'Evaluación: ' + (selectedTrophy ? selectedTrophy.nombre : '')"></h3>
<span class="text-xs font-bold text-nico-orange uppercase tracking-wider" x-text="'Campeón: ' + (selectedTrophy ? selectedTrophy.categoria : '')"></span>
</div>
<div class="flex items-center gap-3 bg-black/80 px-4 py-2 rounded-2xl border border-amber-400/30">
<i class="fas fa-award text-2xl text-amber-400"></i>
<div>
<span class="text-[9px] text-gray-400 font-bold uppercase block">Rendimiento Global</span>
<span class="text-lg font-black text-amber-400" x-text="(selectedTrophy ? selectedTrophy.evaluacion.rendimientoGlobal : 0) + '%'"></span>
<button x-show="esAdmin && modoEdicion" @click="editarAnalisis('rendimientoGlobal','Nuevo Rendimiento Global (%):', true)" class="btn-edit-mini ml-1" title="Editar rendimiento global"><i class="fas fa-pen"></i></button>
</div>
</div>
</div>
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
<div class="glass-card p-4 rounded-2xl border border-white/5 space-y-1 text-center hover:border-nico-orange transition-colors">
<span class="text-[10px] text-gray-400 uppercase font-bold block">Posesión Promedio</span>
<span class="text-2xl font-black text-nico-orange" x-text="selectedTrophy ? selectedTrophy.evaluacion.posesion : ''"></span>
<button x-show="esAdmin && modoEdicion" @click="editarAnalisis('posesion','Nueva Posesión Promedio (ej: 65%):', false)" class="btn-edit-mini" title="Editar posesión"><i class="fas fa-pen"></i></button>
</div>
<div class="glass-card p-4 rounded-2xl border border-white/5 space-y-1 text-center hover:border-nico-green transition-colors">
<span class="text-[10px] text-gray-400 uppercase font-bold block">Efectividad de Pases</span>
<span class="text-2xl font-black text-nico-green" x-text="selectedTrophy ? selectedTrophy.evaluacion.efectividadPases : ''"></span>
<button x-show="esAdmin && modoEdicion" @click="editarAnalisis('efectividadPases','Nueva Efectividad de Pases (ej: 78%):', false)" class="btn-edit-mini" title="Editar efectividad de pases"><i class="fas fa-pen"></i></button>
</div>
<div class="glass-card p-4 rounded-2xl border border-white/5 space-y-1 text-center hover:border-white transition-colors">
<span class="text-[10px] text-gray-400 uppercase font-bold block">Goles A Favor / En Contra</span>
<span class="text-2xl font-black text-white" x-text="selectedTrophy ? selectedTrophy.evaluacion.golesAFavor + ' / ' + selectedTrophy.evaluacion.golesEnContra : ''"></span>
<div x-show="esAdmin && modoEdicion" class="flex justify-center gap-1 mt-1">
<button @click="editarAnalisis('golesAFavor','Goles a favor:', true)" class="btn-edit-mini" title="Editar goles a favor"><i class="fas fa-pen"></i></button>
<button @click="editarAnalisis('golesEnContra','Goles en contra:', true)" class="btn-edit-mini" title="Editar goles en contra"><i class="fas fa-pen"></i></button>
</div>
</div>
<div class="glass-card p-4 rounded-2xl border border-white/5 space-y-1 text-center hover:border-amber-400 transition-colors">
<span class="text-[10px] text-gray-400 uppercase font-bold block">Índice Disciplinario</span>
<span class="text-xs font-extrabold text-amber-400 uppercase block mt-2" x-text="selectedTrophy ? selectedTrophy.evaluacion.disciplina : ''"></span>
<button x-show="esAdmin && modoEdicion" @click="editarAnalisis('disciplina','Índice disciplinario (ej: Excelente, Regular, Malo):', false)" class="btn-edit-mini" title="Editar índice disciplinario"><i class="fas fa-pen"></i></button>
</div>
</div>
<div class="bg-black/60 p-5 rounded-2xl border border-white/10 space-y-3">
<h4 class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-2">
<i class="fas fa-clipboard-check text-nico-orange"></i> Informe Técnico de Rendimiento en Competición
<button x-show="esAdmin && modoEdicion" @click="editarAnalisis('resumenTactico','Editar informe técnico:', false)" class="btn-edit-mini ml-1" title="Editar informe técnico"><i class="fas fa-pen"></i></button>
</h4>
<p class="text-xs text-gray-300 leading-relaxed" x-text="selectedTrophy ? selectedTrophy.evaluacion.resumenTactico : ''"></p>
</div>
</div>
<div class="glass-box p-6 sm:p-8 rounded-3xl border border-white/10 space-y-6">
<div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/10 pb-4">
<div>
<span class="text-xs font-black text-nico-orange uppercase tracking-widest flex items-center gap-2">
<i class="fas fa-layer-group"></i> Simulación General de Competiciones
</span>
<h3 class="text-2xl font-black text-white uppercase mt-1">Evaluación Global del Equipo por Categoría</h3>
</div>
<span x-show="esAdmin" class="text-xs font-bold text-gray-400">Interactivo: Modifica el Rendimiento</span>
<span x-show="!esAdmin" class="text-xs font-bold text-gray-400">Rendimiento oficial · Actualizado por el cuerpo técnico</span>
</div>
<div class="space-y-6">
<template x-for="cat in evalCategories" :key="'eval-' + cat.catKey + '-' + cat.id">
<div class="space-y-2">
<div class="flex justify-between items-center text-xs font-black flex-wrap gap-2">
<span class="text-white" x-text="cat.name"></span>
<div class="flex items-center gap-4">
<span class="hidden sm:inline-block transition-colors duration-500" :style="`color: ${getColorEval(cat.value)}`" x-text="cat.desc"></span>
<template x-if="esAdmin">
<div class="flex items-center bg-black rounded-lg border border-white/20 overflow-hidden shadow-lg">
<button @click="cambiarEvalGlobal(cat.id, -5)" class="px-2.5 py-1.5 bg-white/5 hover:bg-red-500/20 text-gray-300 hover:text-red-400 transition-colors"><i class="fas fa-minus text-[10px]"></i></button>
<div class="px-2 w-14 text-center transition-colors duration-500 text-sm" :style="`color: ${getColorEval(cat.value)}`"><span x-text="cat.value + '%'"></span></div>
<button @click="cambiarEvalGlobal(cat.id, 5)" class="px-2.5 py-1.5 bg-white/5 hover:bg-green-500/20 text-gray-300 hover:text-green-400 transition-colors border-l border-white/20"><i class="fas fa-plus text-[10px]"></i></button>
</div>
</template>
<template x-if="!esAdmin">
<span class="text-sm font-black px-2" :style="`color: ${getColorEval(cat.value)}`" x-text="cat.value + '%'"></span>
</template>
</div>
</div>
<div class="w-full h-3 bg-black rounded-full overflow-hidden border border-white/10 p-0.5">
<div class="h-full rounded-full transition-all duration-500 ease-out" :style="`width: ${cat.value}%; background-color: ${getColorEval(cat.value)}; box-shadow: 0 0 12px ${getColorEval(cat.value)}`"></div>
</div>
</div>
</template>
</div>
</div>
</div>