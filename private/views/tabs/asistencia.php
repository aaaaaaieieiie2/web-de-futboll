<!-- ==================== SECCIÓN 6: ASISTENCIA ==================== -->
<div x-show="mainTab === 'asistencia'" class="space-y-6 anim-entry">
<div class="text-center space-y-2">
<span class="text-xs font-black text-white border border-white/20 bg-black px-4 py-1 rounded-full uppercase tracking-widest shadow-lg">Lista de Asistencia</span>
<h3 class="text-2xl font-black text-white uppercase">Toca al jugador para marcar</h3>
<p class="text-[11px] text-gray-400">1 toque = ✔ Presente (verde) • 2 toques = ✖ Ausente (rojo) • 3 toques = ⭕ Justificado (ámbar) • 4 toques = se limpia. Se guarda solo.</p>
</div>
<div class="flex flex-wrap items-center justify-center gap-2">
<template x-for="cat in categoriasAsistenciaVisibles" :key="cat.key">
<button @click="asistenciaCat = cat.key" :class="asistenciaCat === cat.key ? 'bg-nico-green text-black font-extrabold scale-105' : 'bg-black text-gray-400 hover:text-white border border-white/10'" class="px-4 py-2 rounded-full text-xs font-black uppercase transition-all" x-text="cat.label"></button>
</template>
<input type="date" x-model="asistenciaFecha" class="bg-black border border-white/20 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-nico-green">
</div>
<div class="flex justify-center gap-3 text-center">
<div class="bg-nico-green/10 border border-nico-green/40 rounded-xl px-4 py-2"><span class="text-lg font-black text-nico-green block" x-text="conteoAsistenciaHoy().presente"></span><span class="text-[9px] text-gray-400 font-bold uppercase">Presentes</span></div>
<div class="bg-nico-red/10 border border-nico-red/40 rounded-xl px-4 py-2"><span class="text-lg font-black text-nico-red block" x-text="conteoAsistenciaHoy().ausente"></span><span class="text-[9px] text-gray-400 font-bold uppercase">Ausentes</span></div>
<div class="bg-amber-400/10 border border-amber-400/40 rounded-xl px-4 py-2"><span class="text-lg font-black text-amber-400 block" x-text="conteoAsistenciaHoy().justificado"></span><span class="text-[9px] text-gray-400 font-bold uppercase">Justificados</span></div>
</div>
<div class="glass-box p-5 rounded-3xl border border-white/10">
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
<template x-for="player in playersDe(asistenciaCat)" :key="player.id">
<div @click="ciclarAsistencia(player)" class="rounded-xl border-2 bg-[#0a0a0a] p-3 cursor-pointer transition-all duration-300 select-none"
:class="estadoAsistencia(player) === 'presente' ? 'border-nico-green shadow-[0_0_15px_rgba(16,185,129,0.45)]' : (estadoAsistencia(player) === 'ausente' ? 'border-nico-red shadow-[0_0_15px_rgba(220,38,38,0.45)]' : (estadoAsistencia(player) === 'justificado' ? 'border-amber-400 shadow-[0_0_15px_rgba(245,158,11,0.45)]' : 'border-white/10 hover:border-white/30'))">
<div class="flex items-center justify-between">
<span class="text-xs font-black text-white" x-text="'#' + player.dorsal"></span>
<span class="text-[8px] font-bold text-amber-400" x-text="player.pos"></span>
</div>
<p class="text-[11px] font-bold text-gray-200 truncate mt-1" x-text="player.name"></p>
<div class="mt-2 rounded-lg py-1 text-center text-[9px] font-black uppercase tracking-wider"
:class="estadoAsistencia(player) === 'presente' ? 'bg-nico-green text-black' : (estadoAsistencia(player) === 'ausente' ? 'bg-nico-red text-white' : (estadoAsistencia(player) === 'justificado' ? 'bg-amber-400 text-black' : 'bg-black/60 text-gray-500'))"
x-text="estadoAsistencia(player) === 'presente' ? '✔ PRESENTE' : (estadoAsistencia(player) === 'ausente' ? '✖ AUSENTE' : (estadoAsistencia(player) === 'justificado' ? '⭕ JUSTIFICADO' : 'TOCA PARA MARCAR'))"></div>
</div>
</template>
</div>
</div>
</div>