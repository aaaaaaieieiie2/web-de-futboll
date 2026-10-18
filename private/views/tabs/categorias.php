<!-- ==================== SECCIÓN 2: CATEGORÍAS ==================== -->
<div x-show="mainTab === 'categorias'" class="space-y-6 anim-entry">
<div class="flex justify-center gap-2 flex-wrap">
<template x-for="cat in categoriasVisibles" :key="cat.key">
<button @click="catTab = cat.key; triggerParticles($event.clientX, $event.clientY)" :class="catTab === cat.key ? 'bg-white text-black font-extrabold shadow-[0_0_20px_rgba(255,255,255,0.4)] scale-105' : 'glass-box text-gray-400 hover:text-white'" class="px-4 py-2.5 rounded-xl text-xs transition-all border border-white/10 whitespace-nowrap" x-text="cat.tab"></button>
</template>
</div>
<div class="glass-box p-6 sm:p-8 rounded-3xl border border-white/10 relative overflow-hidden">
<template x-for="cat in categoriasVisibles" :key="'blk-'+cat.key">
<div x-show="catTab === cat.key" class="space-y-8 relative z-10" style="display:none;">
<div class="grid sm:grid-cols-12 gap-6 items-center">
<div class="sm:col-span-7 space-y-4">
<span class="text-xs font-bold bg-black px-3 py-1 rounded-full uppercase border" :class="[cat.text, cat.badgeBorder]" :data-edit="cat.key + '-badge'" x-html="cat.badge"></span>
<h4 class="text-2xl font-black text-white" x-text="cat.titulo"></h4>
<p class="text-xs sm:text-sm text-gray-400 leading-relaxed" :data-edit="cat.key + '-desc'" x-html="cat.desc"></p>
<div class="text-xs space-y-2 text-gray-400 bg-black/60 p-4 rounded-2xl border border-white/10">
<p><strong class="text-nico-orange">Torneos:</strong> <span :data-edit="cat.key + '-torneos'" x-html="cat.torneos"></span></p>
<p><strong class="text-nico-green">Horarios de Entreno:</strong> <span :data-edit="cat.key + '-horario'" x-html="cat.horario"></span></p>
<p><strong class="text-white">Cancha:</strong> <span :data-edit="cat.key + '-cancha'" x-html="cat.cancha"></span></p>
</div>
</div>
<div class="sm:col-span-5">
<div class="h-56 rounded-2xl border relative overflow-hidden shadow-2xl cursor-pointer group" :class="cat.boxBorder" @click="if(esAdmin && modoEdicion){ editarFotoCategoria(cat); } else { imageModal = 'Foto Oficial: ' + cat.tab; triggerParticles($event.clientX, $event.clientY); }">
<img :src="getMediaUrl(cat.foto, 'img/escudo.png')"  :class="(esAdmin && modoEdicion) ? 'cursor-pointer ring-2 ring-dashed ring-nico-orange hover:ring-solid' : ''" :alt="'Foto Oficial ' + cat.tab" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" @error.once="$event.target.src='img/escudo.png'">
<div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent flex flex-col justify-between p-4">
<div class="flex items-center gap-2">
<div class="w-8 h-8 rounded-full bg-black p-1 border border-white/40"><img src="img/escudo.png" alt="Escudo" class="w-full h-full object-contain"></div>
<span class="text-xs font-black text-white uppercase drop-shadow" x-text="'Nico Sport ' + cat.tab"></span>
</div>
<div>
<span class="text-xs font-black uppercase block drop-shadow" :class="cat.text" x-text="'Ver Foto Oficial ' + cat.tab"></span>
<span class="text-[10px] text-gray-300 font-bold" :data-edit="cat.key + '-count'" x-html="cat.count"></span>
</div>
</div>
</div>
</div>
</div>
<div class="pt-6 border-t border-white/10 space-y-4">
<div class="flex items-center justify-between flex-wrap gap-2">
<h5 class="text-sm font-black text-white uppercase tracking-wider flex items-center gap-2">
<i class="fas fa-id-card" :class="cat.text"></i> Plantilla Oficial <span x-text="cat.tab"></span> <span class="hidden sm:inline">(Toca para ver estadísticas)</span>
</h5>
<span class="text-[11px] font-bold text-gray-400">Entrenador: <strong class="text-white" :data-edit="cat.key + '-entrenador'" x-html="cat.entrenador"></strong></span>
</div>
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
<template x-for="player in playersDe(cat.key)" :key="player.id">
<div @click="if (!modoEdicion) { playerModal = player; triggerParticles($event.clientX, $event.clientY); }"
:class="`bg-gradient-to-b ${getPlayerGradient(player)} ${getSSClass(player)}`"
class="relative p-[2px] rounded-xl hover:scale-105 transition-all duration-300 cursor-pointer shadow-lg group">
<div class="bg-[#0a0a0a] rounded-[10px] p-2 flex flex-col items-center relative h-full overflow-hidden">
<?php if (!empty($esAdmin) || !empty($esEntrenador)): ?>
<div class="absolute top-2 left-2 flex flex-col items-center z-10"
     @click.stop="if (puedeEditarCategoria(cat.key)) { editarDorsal(player); } else { playerModal = player; }"
     :title="puedeEditarCategoria(cat.key) ? 'Toca aquí para asignar o cambiar el Dorsal (#)' : ''">
<span class="text-xs font-black leading-none px-1.5 py-0.5 rounded transition-colors"
      :class="puedeEditarCategoria(cat.key) ? (player.dorsal > 0 ? 'text-white bg-white/10 hover:bg-nico-orange hover:text-white' : 'text-amber-300 bg-amber-500/20 border border-amber-400/40 hover:bg-nico-orange hover:text-white') : (player.dorsal > 0 ? 'text-white' : 'text-gray-400')"
      x-text="player.dorsal > 0 ? ('#' + player.dorsal) : '#S/N'"></span>
<span class="text-[8px] font-bold text-amber-400 mt-0.5" x-text="player.pos"></span>
</div>
<?php else: ?>
<div class="absolute top-2 left-2 flex flex-col items-center z-10">
<span class="text-xs font-black leading-none px-1.5 py-0.5 rounded text-white"
      x-text="player.dorsal > 0 ? ('#' + player.dorsal) : '#S/N'"></span>
<span class="text-[8px] font-bold text-amber-400 mt-0.5" x-text="player.pos"></span>
</div>
<?php endif; ?>
<div class="absolute top-2 right-2 z-10"><img src="img/escudo.png" alt="Escudo Nico Sport" class="w-3.5 h-3.5 object-contain"></div>
<div class="w-full h-20 flex justify-center items-end mt-2 z-0">
<img :src="getMediaUrl(player.foto, 'img/escudo.png')" @click="if(esAdmin && modoEdicion){ editarFotoJugador(player); }" :class="(esAdmin && modoEdicion) ? 'cursor-pointer ring-2 ring-dashed ring-nico-orange hover:ring-solid' : ''" alt="Foto Jugador" class="h-20 object-contain group-hover:scale-110 transition-transform duration-300">
</div>
<div class="w-full text-center mt-2 border-t border-white/10 pt-1.5 bg-[#0a0a0a] z-10">
<span class="font-black text-[10px] text-white uppercase tracking-tighter block truncate" x-text="player.name"></span>
<div class="flex items-center justify-center gap-1 mt-1 flex-wrap">
<span class="player-stat-mini stat-gol">⚽<span x-text="getStats(player).goles"></span></span>
<span class="player-stat-mini stat-asis">🎯<span x-text="getStats(player).asistencias"></span></span>
<template x-if="getStats(player).mvp > 0"><span class="mvp-mini-badge">MVP</span></template>
</div>
<template x-if="notaJugador(player) !== null">
<span class="ss-badge mt-1 inline-block" :class="getSSBadgeClass(player)" x-text="notaJugador(player).toFixed(1)"></span>
</template>
<?php if (!empty($esAdmin) || !empty($esEntrenador)): ?>
<template x-if="puedeEditarCategoria(cat.key) && modoEdicion">
<div class="flex items-center justify-center gap-1 mt-1.5 flex-wrap" @click.stop>
<button class="btn-edit-mini" @click="editarDorsal(player)" title="Asignar o cambiar dorsal (#)"><i class="fas fa-shirt"></i></button>
<button class="btn-edit-mini" @click="editarEdadJugadorDirecto(player)" title="Cambiar edad del jugador (auto-reasigna categoría)"><i class="fas fa-calendar-alt"></i></button>
<button x-show="esAdmin" class="btn-edit-mini" @click="renombrarJugador(player)" title="Cambiar nombre"><i class="fas fa-pen"></i></button>
<div class="stat-editor"><button @click="cambiarStat(player,'goles',-1)">−</button><span x-text="getStats(player).goles"></span><button @click="cambiarStat(player,'goles',1)">+</button></div>
<div class="stat-editor"><button @click="cambiarStat(player,'asistencias',-1)">−</button><span x-text="getStats(player).asistencias"></span><button @click="cambiarStat(player,'asistencias',1)">+</button></div>
<div class="stat-editor"><button @click="cambiarStat(player,'mvp',-1)">−</button><span x-text="getStats(player).mvp"></span><button @click="cambiarStat(player,'mvp',1)">+</button></div>
<button x-show="esAdmin" class="btn-del-mini" @click="eliminarJugador(player)" title="Eliminar jugador"><i class="fas fa-trash"></i></button>
</div>
</template>
<?php endif; ?>
</div>
</div>
</div>
</template>
</div>
<p x-show="playersDe(cat.key).length === 0" class="text-center text-gray-500 italic text-xs py-6">Aún no hay jugadores en esta categoría.</p>
</div>
</div>
</template>
</div>
</div>

