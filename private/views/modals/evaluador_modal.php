<!-- ==================== MODAL EVALUADOR (ADMIN/DT) ==================== -->
<div x-show="evalModal" x-transition class="fixed inset-0 z-[60] bg-black/95 backdrop-blur-md overflow-y-auto" style="display: none;">
<div class="min-h-full flex items-start justify-center p-3 sm:p-6">
<div class="glass-box p-5 sm:p-8 rounded-3xl w-full max-w-5xl border border-nico-green/50 relative shadow-2xl space-y-5">
<button @click="evalModal = null" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg z-10">✕</button>
<div class="text-center">
<span class="text-xs font-black text-nico-green uppercase tracking-widest">Hoja de Evaluación del Jugador</span>
<h3 class="text-2xl font-black text-white uppercase" x-text="evalModal ? evalModal.name : ''"></h3>
<span class="text-[11px] text-gray-400" x-text="evalModal ? 'Categoría ' + evalModal.catKey.toUpperCase() + ' • #' + evalModal.dorsal : ''"></span>
</div>
<div class="flex items-center justify-center gap-3 bg-black/60 p-3 rounded-2xl border border-white/10 sticky top-0 z-10 backdrop-blur-xl">
<span class="text-[10px] text-gray-400 font-bold uppercase">Calificación en vivo:</span>
<span class="text-2xl font-black" :class="evalPromedio >= 3.5 ? 'text-nico-green' : (evalPromedio >= 2.5 ? 'text-amber-400' : 'text-nico-red')" x-text="evalPromedio ? evalPromedio + ' / 5' : '—'"></span>
</div>
<div class="grid md:grid-cols-2 gap-4">
<template x-for="(grupo, gKey) in gruposEval" :key="'ed-' + gKey">
<div>
<h4 class="text-xs font-black uppercase mb-2 flex items-center gap-2" :class="grupo[3]"><i class="fas" :class="grupo[2]"></i> <span x-text="grupo[1]"></span></h4>
<div class="space-y-1.5">
<template x-for="(item, i) in evalItems[grupo[0]]" :key="'ed-' + gKey + i">
<div class="flex items-center justify-between bg-black/40 border border-white/5 rounded-lg px-3 py-2">
<span class="text-[11px] text-gray-300" x-text="item"></span>
<div class="flex gap-1">
<template x-for="n in 5" :key="'ed-' + gKey + i + '-' + n">
<button @click="evalDatos[grupo[0]][i] = n" :class="evalDatos[grupo[0]][i] === n ? 'bg-nico-orange text-white shadow-[0_0_8px_rgba(255,85,0,0.6)]' : 'bg-black/60 text-gray-500 hover:text-white'" class="w-7 h-7 rounded text-[10px] font-black transition-all" x-text="n"></button>
</template>
</div>
</div>
</template>
</div>
</div>
</template>
</div>
<button @click="guardarEvaluacion()" class="w-full bg-nico-orange hover:bg-nico-orangeDark text-white font-black py-3 rounded-xl text-xs uppercase"><i class="fas fa-floppy-disk"></i> Guardar Evaluación</button>
</div>
</div>
</div>