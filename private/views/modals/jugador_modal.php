<!-- ==================== MODAL JUGADOR ==================== -->
<div x-show="playerModal !== null" x-transition class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4 overflow-y-auto" style="display: none;">
<div class="glass-box p-6 sm:p-8 rounded-3xl max-w-sm w-full border border-nico-orange/50 relative shadow-2xl space-y-6 text-center my-8" @click.outside="playerModal = null">
<button @click="playerModal = null" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg">✕</button>
<div class="relative w-28 h-28 mx-auto rounded-2xl bg-gradient-to-b from-amber-400 via-amber-600 to-amber-900 p-[2px] shadow-2xl">
<div class="bg-black w-full h-full rounded-[14px] flex items-center justify-center overflow-hidden relative">
<div class="absolute top-2 left-2 text-xs font-black text-white" x-text="'#' + (playerModal ? playerModal.dorsal : '')"></div>
<img onerror="this.onerror=null;this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iIzY2NiI+PHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00cy0xLjc5LTQtNC00LTQgMS43OS00IDQgMS43OSA0IDQgNHptMCAyYy0yLjY3IDAtOCAxLjM0LTggNHYyaDE2di0yYzAtMi42Ni01LjMzLTQtOC00eiIvPjwvc3ZnPg==';" :src="playerModal ? (playerModal.foto || 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iIzY2NiI+PHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00cy0xLjc5LTQtNC00LTQgMS43OS00IDQgMS43OSA0IDQgNHptMCAyYy0yLjY3IDAtOCAxLjM0LTggNHYyaDE2di0yYzAtMi42Ni01LjMzLTQtOC00eiIvPjwvc3ZnPg==' + playerModal.catKey + '/' + playerModal.dorsal + '.png') : ''" alt="Jugador" class="h-24 object-contain">
</div>
</div>
<div>
<span class="text-xs font-bold text-nico-orange uppercase tracking-widest block" x-text="playerModal ? playerModal.pos : ''"></span>
<h3 class="text-2xl font-black text-white uppercase mt-1" x-text="playerModal ? playerModal.name : ''"></h3>
<span class="text-[11px] text-gray-400">Academia Nico Sport • Plantilla Oficial</span>
</div>
<div class="grid grid-cols-3 gap-3 bg-black/80 p-4 rounded-2xl border border-white/10 text-center">
<div class="space-y-1"><span class="text-[10px] text-gray-400 uppercase font-bold block">Goles</span><span class="text-2xl font-black text-nico-green" x-text="playerModal ? getStats(playerModal).goles : 0"></span></div>
<div class="space-y-1 border-x border-white/10 px-2"><span class="text-[10px] text-gray-400 uppercase font-bold block">Asistencias</span><span class="text-2xl font-black text-nico-orange" x-text="playerModal ? getStats(playerModal).asistencias : 0"></span></div>
<div class="space-y-1"><span class="text-[10px] text-gray-400 uppercase font-bold block">MVP</span><span class="text-2xl font-black text-amber-400" x-text="playerModal ? getStats(playerModal).mvp : 0"></span></div>
</div>
<template x-if="playerModal && puedeEditarCategoria(playerModal.catKey)">
<div class="bg-black/60 p-3 rounded-2xl border border-nico-green/30 space-y-2">
<span class="text-[10px] font-black text-nico-green uppercase tracking-widest block text-center"><i class="fas fa-pen"></i> Editar Estadísticas</span>
<div class="flex items-center justify-center gap-2 flex-wrap">
<div class="stat-editor"><button @click="cambiarStat(playerModal,'goles',-1)">−</button><span>⚽<span x-text="getStats(playerModal).goles"></span></span><button @click="cambiarStat(playerModal,'goles',1)">+</button></div>
<div class="stat-editor"><button @click="cambiarStat(playerModal,'asistencias',-1)">−</button><span>🎯<span x-text="getStats(playerModal).asistencias"></span></span><button @click="cambiarStat(playerModal,'asistencias',1)">+</button></div>
<div class="stat-editor"><button @click="cambiarStat(playerModal,'mvp',-1)">−</button><span>🏅<span x-text="getStats(playerModal).mvp"></span></span><button @click="cambiarStat(playerModal,'mvp',1)">+</button></div>
</div>
</div>
</template>
<template x-if="playerModal && currentUser">
<div class="space-y-2">
<div x-show="evalResumen(playerModal)" class="bg-black/60 p-4 rounded-2xl border border-nico-green/30 space-y-2">
<h4 class="text-[10px] font-black text-nico-green uppercase tracking-widest text-center">Evaluación del Jugador</h4>
<div class="grid grid-cols-5 gap-1 text-center">
<div><span class="text-[8px] text-gray-500 block">TÉC</span><span class="text-sm font-black text-white" x-text="evalResumen(playerModal).tecnicos || '—'"></span></div>
<div><span class="text-[8px] text-gray-500 block">TÁC</span><span class="text-sm font-black text-white" x-text="evalResumen(playerModal).tacticos || '—'"></span></div>
<div><span class="text-[8px] text-gray-500 block">FÍS</span><span class="text-sm font-black text-white" x-text="evalResumen(playerModal).fisicos || '—'"></span></div>
<div><span class="text-[8px] text-gray-500 block">ACT</span><span class="text-sm font-black text-white" x-text="evalResumen(playerModal).actitudinales || '—'"></span></div>
<div><span class="text-[8px] text-gray-500 block">GENERAL</span><span class="text-sm font-black text-nico-green" x-text="evalResumen(playerModal).general || '—'"></span></div>
</div>
</div>
<div class="bg-black/60 p-4 rounded-2xl border border-white/10 space-y-2">
<h4 class="text-[10px] font-black text-amber-400 uppercase tracking-widest text-center">Asistencia</h4>
<div class="grid grid-cols-3 gap-1 text-center">
<div><span class="text-[8px] text-gray-500 block">PRESENTES</span><span class="text-sm font-black text-nico-green" x-text="resumenAsistencia(playerModal).presente"></span></div>
<div><span class="text-[8px] text-gray-500 block">AUSENTES</span><span class="text-sm font-black text-nico-red" x-text="resumenAsistencia(playerModal).ausente"></span></div>
<div><span class="text-[8px] text-gray-500 block">JUSTIFICADOS</span><span class="text-sm font-black text-amber-400" x-text="resumenAsistencia(playerModal).justificado"></span></div>
</div>
</div>
</div>
</template>
<button x-show="playerModal && currentUser" @click="abrirEvalVer(playerModal)" class="w-full bg-amber-400 hover:bg-amber-500 text-black font-black py-3 rounded-xl text-xs uppercase"><i class="fas fa-file-lines"></i> Ver Hoja de Evaluación</button>
<button x-show="esAdmin || (esEntrenador && playerModal && puedeEditarCategoria(playerModal.catKey))" @click="abrirEvaluacion(playerModal)" class="w-full bg-nico-green hover:bg-nico-greenDark text-white font-black py-3 rounded-xl text-xs uppercase"><i class="fas fa-clipboard-check"></i> Evaluar al Jugador</button>
<button @click="playerModal = null" class="w-full bg-nico-orange hover:bg-nico-orangeDark text-white font-black py-3 rounded-xl text-xs uppercase">Cerrar Ficha</button>
</div>
</div>