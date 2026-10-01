<!-- ==================== MODAL HOJA DE EVALUACIÓN (SOLO LECTURA) ==================== -->
<div x-show="evalVerModal !== null" x-transition class="fixed inset-0 z-[70] bg-black/95 backdrop-blur-md overflow-y-auto" style="display: none;">
<div class="min-h-full flex items-start justify-center p-3 sm:p-6">
<div class="glass-box p-5 sm:p-8 rounded-3xl w-full max-w-3xl border border-amber-400/50 relative shadow-2xl space-y-5 my-8">
<button @click="evalVerModal = null" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg z-10">✕</button>
<div class="text-center">
<span class="text-xs font-black text-amber-400 uppercase tracking-widest"><i class="fas fa-file-lines"></i> Hoja de Evaluación Oficial</span>
<h3 class="text-2xl font-black text-white uppercase" x-text="evalVerModal ? evalVerModal.name : ''"></h3>
<span class="text-[11px] text-gray-400" x-text="evalVerModal ? 'Categoría ' + evalVerModal.catKey.toUpperCase() + ' • #' + evalVerModal.dorsal : ''"></span>
</div>
<template x-if="evalVerModal && !evalCompleta(evalVerModal)">
<div class="text-center text-gray-500 italic py-10">
<i class="fas fa-hourglass-half text-3xl mb-3 block text-gray-600"></i>
Aún no hay evaluaciones registradas para este jugador.<br>El cuerpo técnico cargará su hoja de evaluación próximamente.
</div>
</template>
<template x-if="evalVerModal && evalCompleta(evalVerModal)">
<div class="space-y-5">
<div class="flex items-center justify-center gap-3 bg-black/60 p-3 rounded-2xl border border-white/10">
<span class="text-[10px] text-gray-400 font-bold uppercase">Calificación General:</span>
<span class="text-2xl font-black" :class="notaJugador(evalVerModal) >= 3.5 ? 'text-nico-green' : (notaJugador(evalVerModal) >= 2.5 ? 'text-amber-400' : 'text-nico-red')" x-text="notaJugador(evalVerModal) ? notaJugador(evalVerModal).toFixed(1) + ' / 5' : '—'"></span>
</div>
<template x-for="(grupo, gKey) in gruposEval" :key="'ver-' + gKey">
<div class="bg-black/40 border border-white/5 rounded-2xl p-4 space-y-2">
<h4 class="text-xs font-black uppercase flex items-center gap-2" :class="grupo[3]">
<i class="fas" :class="grupo[2]"></i> <span x-text="grupo[1]"></span>
<span class="ml-auto text-white" x-text="(evalResumen(evalVerModal) ? evalResumen(evalVerModal)[grupo[0]] : '—') + ' / 5'"></span>
</h4>
<div class="grid sm:grid-cols-2 gap-1.5">
<template x-for="(item, i) in evalItems[grupo[0]]" :key="'ver-' + gKey + '-' + i">
<div class="flex items-center justify-between bg-black/50 border border-white/5 rounded-lg px-3 py-1.5">
<span class="text-[11px] text-gray-300" x-text="item"></span>
<span class="text-xs font-black"
:class="(evalCompleta(evalVerModal)[grupo[0]][i] || 0) >= 4 ? 'text-nico-green' : ((evalCompleta(evalVerModal)[grupo[0]][i] || 0) >= 3 ? 'text-amber-400' : ((evalCompleta(evalVerModal)[grupo[0]][i] || 0) >= 1 ? 'text-nico-red' : 'text-gray-600'))"
x-text="(evalCompleta(evalVerModal)[grupo[0]][i] || 0) || '—'"></span>
</div>
</template>
</div>
</div>
</template>
<p class="text-[10px] text-gray-500 text-center italic">Evaluación registrada por el cuerpo técnico de la Academia Nico Sport.</p>
</div>
</template>
<button @click="evalVerModal = null" class="w-full bg-white text-black font-black py-3 rounded-xl text-xs uppercase">Cerrar Hoja de Evaluación</button>
</div>
</div>
</div>