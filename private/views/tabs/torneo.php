<!-- ==================== SECCIÓN 4: TORNEO & TABLA ==================== -->
<div x-show="mainTab === 'torneo'" class="space-y-6 anim-entry">
<div class="flex justify-center items-center gap-2 flex-wrap">
<template x-for="(cat, idx) in categoriasTorneoVisibles" :key="cat.key">
<button @click="goToTorneo(idx)"
:class="idx === currentTorneoIdx ? 'bg-nico-orange text-white shadow-[0_0_15px_rgba(255,85,0,0.6)] scale-105' : 'bg-black text-gray-400 hover:text-white border border-white/10'"
class="px-4 py-2 rounded-full text-xs font-black uppercase tracking-wider transition-all duration-300">
<span x-text="cat.label"></span>
</button>
</template>
</div>
<div class="relative max-w-5xl mx-auto">
<div class="overflow-hidden rounded-3xl">
<div class="flex transition-transform duration-700 ease-out" :style="`transform: translateX(-${currentTorneoIdx * 100}%)`">
<template x-for="(cat, idx) in categoriasTorneoVisibles" :key="cat.key">
<div class="w-full flex-shrink-0">
<div class="glass-box p-5 sm:p-8 rounded-3xl border border-white/10 space-y-5">
<div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-4">
<div>
<span class="text-xs font-black text-gray-400 uppercase tracking-widest">Torneo Clausura 2026</span>
<h4 class="text-xl sm:text-2xl font-black text-white uppercase mt-0.5">Tabla de Posiciones (<span class="text-nico-orange" x-text="cat.label"></span>)</h4>
<p class="text-[11px] text-gray-500 mt-1" x-text="cat.descripcion"></p>
</div>
<div class="flex items-center gap-2">
<span :class="cat.tabla.length > 0 ? 'bg-nico-green/20 text-nico-green border-nico-green/40' : 'bg-nico-orange/20 text-nico-orange border-nico-orange/40 animate-pulse'" class="text-xs font-extrabold px-3 py-1 rounded-full border" x-text="cat.tabla.length > 0 ? 'EN CURSO' : 'PRÓXIMAMENTE'"></span>
<button x-show="esAdmin && modoEdicion" @click="agregarEquipo(cat)" class="btn-edit-mini" title="Agregar equipo"><i class="fas fa-plus"></i></button>
</div>
</div>
<div class="scroll-x-container rounded-2xl border border-white/10">
<div x-show="cat.tabla.length === 0" class="overflow-x-auto">
<table class="w-full text-left text-xs">
<thead><tr class="text-gray-500 border-b border-white/10 uppercase font-black text-[10px]"><th class="py-3 px-2">Pos</th><th class="py-3 px-4">Equipo</th><th class="py-3 px-2 text-center">PJ</th><th class="py-3 px-2 text-center">PG</th><th class="py-3 px-2 text-center">PE</th><th class="py-3 px-2 text-center">PP</th><th class="py-3 px-2 text-center">GF</th><th class="py-3 px-2 text-center">GC</th><th class="py-3 px-2 text-center font-bold">PTS</th></tr></thead>
<tbody><tr><td colspan="9" class="py-10 text-center text-gray-500 italic"><i class="fas fa-calendar-xmark text-2xl text-gray-600 mb-2 block"></i>Torneo no iniciado — Datos próximamente</td></tr></tbody>
</table>
</div>
<div x-show="cat.tabla.length > 0" class="overflow-x-auto">
<table class="w-full text-left text-xs">
<thead><tr class="text-gray-500 border-b border-white/10 uppercase font-black text-[10px]"><th class="py-3 px-2">Pos</th><th class="py-3 px-4">Equipo</th><th class="py-3 px-2 text-center">PJ</th><th class="py-3 px-2 text-center">PG</th><th class="py-3 px-2 text-center">PE</th><th class="py-3 px-2 text-center">PP</th><th class="py-3 px-2 text-center">GF</th><th class="py-3 px-2 text-center">GC</th><th class="py-3 px-2 text-center font-bold">PTS</th><th x-show="modoEdicion" class="py-3 px-2"></th></tr></thead>
<tbody class="divide-y divide-white/5 font-semibold">
<template x-for="(team, tIdx) in tablaOrdenada(cat)" :key="cat.key + '-' + tIdx">
<tr :class="team.nombre.includes('Nico Sport') ? 'bg-nico-orange/10 text-white border-l-4 border-l-nico-orange' : 'hover:bg-white/5'" class="transition-colors">
<td class="py-3 px-2 font-black" :class="tIdx === 0 ? 'text-amber-400' : (team.nombre.includes('Nico Sport') ? 'text-nico-orange' : 'text-gray-400')" x-text="tIdx + 1"></td>
<td class="py-3 px-4" :class="team.nombre.includes('Nico Sport') ? 'font-black text-nico-orange' : 'text-gray-300'">
<span class="flex items-center gap-2" :class="(modoEdicion && esAdmin) ? 'cursor-pointer hover:text-nico-orange' : ''" @click="editarNombreEquipo(team)">
<i class="fas" :class="team.nombre.includes('Nico Sport') ? 'fa-shield-halved text-nico-orange drop-shadow-[0_0_10px_rgba(255,85,0,0.7)]' : 'fa-shield text-white/60'"></i>
<span x-text="team.nombre"></span>
</span>
</td>
<td class="py-3 px-2 text-center text-gray-300" :class="(modoEdicion && esAdmin) ? 'cursor-pointer hover:text-nico-orange' : ''" @click="editarNum(team,'pj')" x-text="team.pj"></td>
<td class="py-3 px-2 text-center text-gray-300" :class="(modoEdicion && esAdmin) ? 'cursor-pointer hover:text-nico-orange' : ''" @click="editarNum(team,'pg')" x-text="team.pg"></td>
<td class="py-3 px-2 text-center text-gray-300" :class="(modoEdicion && esAdmin) ? 'cursor-pointer hover:text-nico-orange' : ''" @click="editarNum(team,'pe')" x-text="team.pe"></td>
<td class="py-3 px-2 text-center text-gray-300" :class="(modoEdicion && esAdmin) ? 'cursor-pointer hover:text-nico-orange' : ''" @click="editarNum(team,'pp')" x-text="team.pp"></td>
<td class="py-3 px-2 text-center text-gray-300" :class="(modoEdicion && esAdmin) ? 'cursor-pointer hover:text-nico-orange' : ''" @click="editarNum(team,'gf')" x-text="team.gf"></td>
<td class="py-3 px-2 text-center text-gray-300" :class="(modoEdicion && esAdmin) ? 'cursor-pointer hover:text-nico-orange' : ''" @click="editarNum(team,'gc')" x-text="team.gc"></td>
<td class="py-3 px-2 text-center font-black text-white" :class="(modoEdicion && esAdmin) ? 'cursor-pointer hover:text-nico-orange' : ''" @click="editarNum(team,'pts')" x-text="team.pts"></td>
<td x-show="modoEdicion && esAdmin" class="py-3 px-2"><button class="btn-del-mini" @click="eliminarEquipo(cat, team)" title="Eliminar equipo"><i class="fas fa-trash"></i></button></td>
</tr>
</template>
</tbody>
</table>
</div>
</div>
<div class="grid md:grid-cols-2 gap-4">
<div class="glass-card p-4 rounded-2xl border border-white/10 space-y-2 hover:border-nico-green transition-colors">
<h5 class="text-xs font-black text-white uppercase border-b border-white/10 pb-2 flex items-center justify-between">
<span>Último Resultado</span>
<template x-if="cat.ultimoResultado">
<span class="text-[10px] font-bold px-2 py-1 rounded" :class="cat.ultimoResultado.golesNico > cat.ultimoResultado.golesRival ? 'text-nico-green bg-nico-green/10' : (cat.ultimoResultado.golesNico === cat.ultimoResultado.golesRival ? 'text-amber-400 bg-amber-400/10' : 'text-nico-red bg-nico-red/10')" x-text="cat.ultimoResultado.golesNico > cat.ultimoResultado.golesRival ? 'Victoria' : (cat.ultimoResultado.golesNico === cat.ultimoResultado.golesRival ? 'Empate' : 'Derrota')"></span>
</template>
<template x-if="!cat.ultimoResultado"><span class="text-[10px] text-gray-500 font-bold bg-black/60 px-2 py-1 rounded">Sin datos</span></template>
</h5>
<template x-if="cat.ultimoResultado">
<div>
<div class="flex items-center justify-around py-3">
<div class="text-center">
<span class="font-black text-sm text-white block">Nico Sport</span>
<span class="text-3xl font-black" :class="cat.ultimoResultado.golesNico > cat.ultimoResultado.golesRival ? 'text-nico-green' : 'text-gray-500'" x-text="cat.ultimoResultado.golesNico" @click="editarNum(cat.ultimoResultado,'golesNico')" :style="(modoEdicion && esAdmin) ? 'cursor:pointer' : ''"></span>
</div>
<span class="text-xs font-black text-gray-600 uppercase">VS</span>
<div class="text-center">
<span class="font-black text-sm text-gray-500 block" x-text="cat.ultimoResultado.rival" @click="editarTextoObj(cat.ultimoResultado,'rival','Rival:')" :style="(modoEdicion && esAdmin) ? 'cursor:pointer' : ''"></span>
<span class="text-3xl font-black text-gray-500" x-text="cat.ultimoResultado.golesRival" @click="editarNum(cat.ultimoResultado,'golesRival')" :style="(modoEdicion && esAdmin) ? 'cursor:pointer' : ''"></span>
</div>
</div>
<p class="text-[11px] text-gray-500 text-center">
<span @click="editarTextoObj(cat.ultimoResultado,'goleadores','Goleadores:')" :style="(modoEdicion && esAdmin) ? 'cursor:pointer' : ''" x-text="'Goles: ' + cat.ultimoResultado.goleadores"></span> •
<span @click="editarTextoObj(cat.ultimoResultado,'sede','Sede:')" :style="(modoEdicion && esAdmin) ? 'cursor:pointer' : ''" x-text="cat.ultimoResultado.sede"></span>
</p>
</div>
</template>
<template x-if="!cat.ultimoResultado"><div class="text-center text-gray-500 italic py-3"><i class="fas fa-clock-rotate-left text-xl mb-1 block text-gray-600"></i>Sin partidos jugados</div></template>
</div>
<div class="glass-card p-4 rounded-2xl border border-white/10 space-y-2 hover:border-nico-orange transition-colors">
<h5 class="text-xs font-black text-white uppercase border-b border-white/10 pb-2 flex items-center justify-between">
<span>Próximo Partido</span>
<template x-if="cat.proximoPartido"><span class="text-[10px] text-nico-orange font-bold bg-nico-orange/10 px-2 py-1 rounded" x-text="cat.proximoPartido.fecha" @click="editarTextoObj(cat.proximoPartido,'fecha','Fecha:')" :style="(modoEdicion && esAdmin) ? 'cursor:pointer' : ''"></span></template>
<template x-if="!cat.proximoPartido"><span class="text-[10px] text-gray-500 font-bold bg-black/60 px-2 py-1 rounded">Por definir</span></template>
</h5>
<template x-if="cat.proximoPartido">
<div>
<div class="flex items-center justify-around py-3">
<div class="text-center">
<span class="font-black text-sm text-white block">Nico Sport</span>
<span class="text-xs text-gray-400 font-bold block" x-text="cat.proximoPartido.condicion" @click="editarTextoObj(cat.proximoPartido,'condicion','Condición (Local/Visita):')" :style="(modoEdicion && esAdmin) ? 'cursor:pointer' : ''"></span>
</div>
<span class="text-xs font-black text-gray-600 uppercase">VS</span>
<div class="text-center">
<span class="font-black text-sm text-gray-500 block" x-text="cat.proximoPartido.rival" @click="editarTextoObj(cat.proximoPartido,'rival','Rival:')" :style="(modoEdicion && esAdmin) ? 'cursor:pointer' : ''"></span>
<span class="text-xs text-gray-600 font-bold block">Rival</span>
</div>
</div>
<p class="text-[11px] text-gray-500 text-center" x-text="cat.proximoPartido.sede + ' • Transmisión en directo'" @click="editarTextoObj(cat.proximoPartido,'sede','Sede:')" :style="(modoEdicion && esAdmin) ? 'cursor:pointer' : ''"></p>
</div>
</template>
<template x-if="!cat.proximoPartido"><div class="text-center text-gray-500 italic py-3"><i class="fas fa-calendar-days text-xl mb-1 block text-gray-600"></i>Por definir (Fecha y rival)</div></template>
</div>
</div>
<div x-show="esAdmin" class="p-4 rounded-2xl border border-nico-orange/30 bg-gradient-to-r from-nico-orange/5 to-transparent space-y-3">
<p class="text-xs text-gray-400 uppercase font-black flex items-center gap-2">
<i class="fas fa-flag-checkered text-nico-orange"></i> Fase Actual del Torneo — <span class="text-nico-orange" x-text="cat.label"></span>
</p>
<div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
<template x-for="fase in fasesTorneo" :key="fase.key">
<button @click="setFaseTorneo(fase.key)"
:class="cat.faseActual === fase.key ? 'bg-nico-orange text-white shadow-[0_0_15px_rgba(255,85,0,0.5)] border-nico-orange' : 'bg-black/40 text-gray-400 border-white/10 hover:border-nico-orange/50 hover:text-white'"
class="px-2 py-2.5 rounded-xl text-[10px] sm:text-xs font-black border transition-all duration-300 flex items-center justify-center gap-1.5 uppercase">
<i :class="fase.icon" class="text-[10px]"></i>
<span x-text="fase.label"></span>
</button>
</template>
</div>
</div>
<div x-show="!esAdmin" class="p-4 rounded-2xl border border-white/10 bg-black/30">
<p class="text-xs text-gray-400 uppercase font-black flex items-center gap-2">
<i class="fas fa-flag-checkered text-gray-500"></i> Fase Actual: <span class="text-white" x-text="fasesTorneo.find(f => f.key === cat.faseActual)?.label || 'Sin torneo'"></span>
</p>
</div>
<div class="p-4 rounded-2xl border border-amber-400/20 bg-gradient-to-r from-amber-400/5 to-transparent space-y-3">
<p class="text-xs text-gray-400 uppercase font-black flex items-center gap-2">
<i class="fas fa-trophy text-amber-400"></i> Camino al Título — <span class="text-nico-orange" x-text="cat.label"></span>
</p>
<div class="overflow-x-auto pb-1">
<div class="relative min-w-[560px]">
<div class="grid grid-cols-5">
<div class="px-1.5">
<p class="text-center text-[9px] font-black uppercase tracking-wider mb-2" :class="roundState(cat,0)==='current' ? 'text-nico-orange' : (roundState(cat,0)==='done' ? 'text-nico-green' : 'text-gray-500')">Octavos</p>
<div class="flex flex-col justify-between h-44 gap-2">
<div class="h-9 rounded-lg border flex items-center justify-center text-[8px] font-bold uppercase" :class="roundClass(cat,0)" x-text="roundText(cat,0)"></div>
<div class="h-9 rounded-lg border flex items-center justify-center text-[8px] font-bold uppercase" :class="roundClass(cat,0)" x-text="roundText(cat,0)"></div>
<div class="h-9 rounded-lg border flex items-center justify-center text-[8px] font-bold uppercase" :class="roundClass(cat,0)" x-text="roundText(cat,0)"></div>
<div class="h-9 rounded-lg border flex items-center justify-center text-[8px] font-bold uppercase" :class="roundClass(cat,0)" x-text="roundText(cat,0)"></div>
</div>
</div>
<div class="px-1.5">
<p class="text-center text-[9px] font-black uppercase tracking-wider mb-2" :class="roundState(cat,1)==='current' ? 'text-nico-orange' : (roundState(cat,1)==='done' ? 'text-nico-green' : 'text-gray-500')">Cuartos</p>
<div class="flex flex-col justify-around h-44 gap-2">
<div class="h-9 rounded-lg border flex items-center justify-center text-[8px] font-bold uppercase" :class="roundClass(cat,1)" x-text="roundText(cat,1)"></div>
<div class="h-9 rounded-lg border flex items-center justify-center text-[8px] font-bold uppercase" :class="roundClass(cat,1)" x-text="roundText(cat,1)"></div>
</div>
</div>
<div class="px-1.5">
<p class="text-center text-[9px] font-black uppercase tracking-wider mb-2" :class="roundState(cat,2)==='current' ? 'text-nico-orange' : (roundState(cat,2)==='done' ? 'text-nico-green' : 'text-gray-500')">Semis</p>
<div class="flex flex-col justify-center h-44">
<div class="h-9 rounded-lg border flex items-center justify-center text-[8px] font-bold uppercase" :class="roundClass(cat,2)" x-text="roundText(cat,2)"></div>
</div>
</div>
<div class="px-1.5">
<p class="text-center text-[9px] font-black uppercase tracking-wider mb-2" :class="roundState(cat,3)==='current' ? 'text-nico-orange' : (roundState(cat,3)==='done' ? 'text-nico-green' : 'text-gray-500')">Final</p>
<div class="flex flex-col justify-center h-44">
<div class="h-9 rounded-lg border flex items-center justify-center text-[8px] font-bold uppercase" :class="roundClass(cat,3)" x-text="roundText(cat,3)"></div>
</div>
</div>
<div class="px-1.5">
<p class="text-center text-[9px] font-black uppercase tracking-wider mb-2 text-amber-400">Campeón</p>
<div class="flex flex-col items-center justify-center h-44">
<i class="fas fa-trophy text-3xl transition-all duration-500" :class="bracketIdx(cat) === 4 ? 'text-amber-300 animate-bounce drop-shadow-[0_0_25px_rgba(245,158,11,1)]' : (bracketIdx(cat) === 3 ? 'text-amber-300 animate-pulse drop-shadow-[0_0_15px_rgba(245,158,11,0.8)]' : 'text-amber-400/50')"></i>
<span class="text-[8px] font-bold uppercase mt-2 transition-colors" :class="bracketIdx(cat) === 4 ? 'text-amber-300' : 'text-gray-600'" x-text="bracketIdx(cat) === 4 ? '¡NICO SPORT!' : 'Por definir'"></span>
</div>
</div>
</div>
<div class="absolute top-[55%] -translate-x-1/2 -translate-y-1/2 z-10 pointer-events-none transition-all duration-700 ease-out"
:class="bracketIdx(cat) >= 0 ? 'opacity-100' : 'opacity-0'"
:style="`left: ${bracketIdx(cat) >= 0 ? (bracketIdx(cat) * 20 + 10) : 50}%`">
<div class="w-12 h-12 rounded-full bg-black border-2 border-nico-orange p-1 shadow-[0_0_25px_rgba(255,85,0,0.9)]">
<img src="img/escudo.png" alt="Escudo Nico Sport" class="w-full h-full object-contain">
</div>
</div>
</div>
</div>
<p class="text-[10px] text-gray-500 italic text-center">El escudo avanza por el diagrama según la fase. Si llega a Campeón, ¡se posa sobre la copa! 🏆</p>
</div>
</div>
</div>
</template>
</div>
</div>
<button @click="prevTorneo()" class="absolute left-2 top-1/2 -translate-y-1/2 bg-nico-orange/90 hover:bg-nico-orange text-white w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center shadow-[0_0_20px_rgba(255,85,0,0.6)] transition-all hover:scale-110 z-10">
<i class="fas fa-chevron-left text-lg"></i>
</button>
<button @click="nextTorneo()" class="absolute right-2 top-1/2 -translate-y-1/2 bg-nico-orange/90 hover:bg-nico-orange text-white w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center shadow-[0_0_20px_rgba(255,85,0,0.6)] transition-all hover:scale-110 z-10">
<i class="fas fa-chevron-right text-lg"></i>
</button>
</div>
<div class="text-center text-gray-500 text-xs font-bold">
<span x-text="currentTorneoIdx + 1"></span> / <span x-text="categoriasTorneoVisibles.length"></span> Categorías
</div>
</div>