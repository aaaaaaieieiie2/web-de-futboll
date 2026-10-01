<!-- ==================== SECCIÓN 11: PORTAL DE PADRES (MODIFICADA) ==================== -->
<div x-show="mainTab === 'portal'" class="space-y-6 anim-entry">
<div class="text-center space-y-2">
<span class="text-xs font-black text-white border border-white/20 bg-black px-4 py-1 rounded-full uppercase tracking-widest shadow-lg">Portal de Familias</span>
<h3 class="text-2xl font-black text-white uppercase">Mi Cuenta</h3>
</div>
<div class="glass-box p-6 rounded-3xl border border-nico-orange/40 space-y-4 shadow-[0_0_20px_rgba(255,85,0,0.15)] relative overflow-hidden">
<div class="absolute top-0 right-0 w-32 h-32 bg-nico-orange/10 blur-[50px] rounded-full"></div>
<h4 class="text-xs font-black text-nico-orange uppercase flex items-center gap-2 relative z-10"><i class="fas fa-child-reaching"></i> Trayectoria Deportiva de mi Hijo(a)</h4>
<template x-if="!hijoPlayer">
<div class="text-center text-gray-500 italic text-xs py-10 relative z-10">
<i class="fas fa-user-slash text-4xl mb-3 block text-gray-600 opacity-50"></i>
Tu cuenta no tiene un jugador asignado.<br>Contacta al administrador para vincular a tu hijo(a).
</div>
</template>
<template x-if="hijoPlayer">
<div class="space-y-4 relative z-10">
<div class="flex items-center gap-4 flex-wrap bg-black/50 p-4 rounded-2xl border border-white/5">
<div class="w-20 h-24 rounded-xl bg-gradient-to-b from-[#1a1a1a] to-black border border-white/15 p-1.5 flex items-end justify-center overflow-hidden shrink-0 shadow-lg relative">
<img onerror="this.onerror=null;this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iIzY2NiI+PHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00cy0xLjc5LTQtNC00LTQgMS43OS00IDQgMS43OSA0IDQgNHptMCAyYy0yLjY3IDAtOCAxLjM0LTggNHYyaDE2di0yYzAtMi42Ni01LjMzLTQtOC00eiIvPjwvc3ZnPg==';" :src="getMediaUrl(hijoPlayer.foto, 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iIzY2NiI+PHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00cy0xLjc5LTQtNC00LTQgMS43OS00IDQgMS43OSA0IDQgNHptMCAyYy0yLjY3IDAtOCAxLjM0LTggNHYyaDE2di0yYzAtMi42Ni01LjMzLTQtOC00eiIvPjwvc3ZnPg==')" alt="Foto de mi hijo" class="h-full object-contain relative z-10">
</div>
<div class="flex-1 min-w-[160px]">
<p class="text-xl font-black text-white uppercase drop-shadow" x-text="hijoPlayer.name"></p>
<p class="text-[11px] text-gray-300 font-semibold mt-0.5">Categoría <span class="text-nico-orange font-bold px-1.5 py-0.5 bg-nico-orange/10 rounded" x-text="hijoCatLabel"></span> • Dorsal <span class="text-white font-black" x-text="'#'+hijoPlayer.dorsal"></span> • <span class="text-amber-400" x-text="hijoPlayer.pos"></span></p>
<div class="flex items-center gap-2 mt-2.5 flex-wrap">
<span class="player-stat-mini stat-gol text-xs px-2 py-1">⚽ <span x-text="getStats(hijoPlayer).goles"></span></span>
<span class="player-stat-mini stat-asis text-xs px-2 py-1">🎯 <span x-text="getStats(hijoPlayer).asistencias"></span></span>
<span class="player-stat-mini stat-mvp text-xs px-2 py-1">🏅 MVP ×<span x-text="getStats(hijoPlayer).mvp"></span></span>
</div>
</div>
<template x-if="notaJugador(hijoPlayer) !== null">
<div class="text-center px-4">
<span class="text-[9px] text-gray-400 font-bold uppercase block mb-1">Calificación</span>
<span class="ss-badge px-3 py-1.5 text-sm shadow-lg" :class="getSSBadgeClass(hijoPlayer)" x-text="'SS ' + notaJugador(hijoPlayer).toFixed(1)"></span>
</div>
</template>
</div>
<div class="grid grid-cols-2 gap-4">
<button @click="playerModal = hijoPlayer; triggerParticles($event.clientX, $event.clientY)" class="w-full bg-white/5 hover:bg-white/10 text-white border border-white/20 hover:border-white font-black py-3 rounded-xl text-xs uppercase transition-all shadow-lg flex items-center justify-center gap-2"><i class="fas fa-id-card text-nico-orange"></i> Ver Ficha Completa</button>
<button @click="abrirEvalVer(hijoPlayer); triggerParticles($event.clientX, $event.clientY)" class="w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:scale-[1.02] text-black font-black py-3 rounded-xl text-xs uppercase transition-all shadow-[0_0_15px_rgba(245,158,11,0.4)] flex items-center justify-center gap-2"><i class="fas fa-file-lines"></i> Hoja de Evaluación</button>
</div>
</div>
</template>
</div>
<div class="mt-6 border-t border-white/10 pt-6">
<h4 class="text-xs font-black text-gray-400 uppercase flex items-center gap-2 mb-4"><i class="fas fa-wallet"></i> Facturación y Pagos</h4>
<div class="grid sm:grid-cols-3 gap-4">
<div class="glass-box p-5 rounded-2xl border border-white/5 text-center bg-black/60">
<i class="fas fa-calendar-xmark text-2xl text-nico-orange mb-2 opacity-80"></i>
<p class="text-[10px] text-gray-400 uppercase font-bold">Fecha Límite</p>
<p class="text-xl font-black text-white mt-1" x-text="proximaQuincena()"></p>
</div>
<div class="glass-box p-5 rounded-2xl border border-white/5 text-center bg-black/60">
<i class="fas fa-circle-dollar-to-slot text-2xl text-nico-green mb-2 opacity-80"></i>
<p class="text-[10px] text-gray-400 uppercase font-bold">Cuota Regular</p>
<p class="text-xl font-black text-white mt-1">$15.00 <span class="text-[9px] text-gray-500 block">Quincenal</span></p>
</div>
<div class="glass-box p-5 rounded-2xl text-center shadow-lg transition-colors" :class="estadoCuentaPadre() === 'Vigente' ? 'border-nico-green/40 bg-nico-green/5' : 'border-nico-red/40 bg-nico-red/5'">
<i class="fas" :class="estadoCuentaPadre() === 'Vigente' ? 'fa-check-circle text-nico-green' : 'fa-exclamation-triangle text-nico-red animate-pulse'" class="text-2xl mb-2"></i>
<p class="text-[10px] text-gray-400 uppercase font-bold">Estado Actual</p>
<p class="text-xl font-black mt-1 uppercase" :class="estadoCuentaPadre() === 'Vigente' ? 'text-nico-green' : 'text-nico-red'" x-text="estadoCuentaPadre()"></p>
</div>
</div>
</div>
<div class="grid md:grid-cols-2 gap-4 mt-6">
<div class="glass-box p-6 rounded-3xl border border-nico-blue/40 space-y-4">
<h4 class="text-xs font-black text-nico-blue uppercase flex items-center gap-2"><i class="fas fa-building-columns"></i> Información Bancaria</h4>
<div class="bg-black/60 p-4 rounded-2xl border border-white/10 space-y-2 text-[11px]">
<p><strong class="text-white">Banco:</strong> <span class="text-gray-300">Banco General</span></p>
<p><strong class="text-white">Tipo de Cuenta:</strong> <span class="text-gray-300">Ahorros</span></p>
<p><strong class="text-white">Número:</strong> <span class="text-nico-orange font-mono">123-456-7890</span></p>
<p><strong class="text-white">Titular:</strong> <span class="text-gray-300">Academia Nico Sport</span></p>
</div>

</div>
<div class="glass-box p-6 rounded-3xl border border-nico-green/30 space-y-4">
<h4 class="text-xs font-black text-nico-green uppercase flex items-center gap-2"><i class="fas fa-file-invoice-dollar"></i> Reportar Pago</h4>
<div class="space-y-3">
<select class="admin-input bg-black/60 cursor-not-allowed opacity-80" disabled><option selected>Mensualidad Oficial</option></select>
<div class="flex gap-2">
<input type="text" class="admin-input w-1/2 bg-black/60 cursor-not-allowed opacity-80 text-nico-green font-black" :value="'$' + tarifaOficialMensualidad.toFixed(2)" readonly>
<input type="text" class="admin-input w-1/2 bg-black/60 cursor-not-allowed opacity-80 text-gray-400" :value="new Date().toLocaleDateString('es-PA')" readonly>
</div>
<input type="text" class="admin-input" placeholder="N° operación / Referencia" x-model="reportePago.referencia">
<div class="bg-black/60 p-3 rounded-xl border border-dashed border-white/20 text-center hover:border-nico-green transition-colors">
<label class="cursor-pointer block"><i class="fas fa-camera text-xl text-nico-green mb-1"></i><p class="text-[10px] text-white font-bold">Adjuntar foto comprobante</p><input type="file" class="hidden" accept="image/*" @change="handleComprobantePadre($event)"></label>
<div x-show="reportePago.comprobanteNombre" class="mt-2 text-[10px] text-nico-green font-bold"><i class="fas fa-check-circle"></i> <span x-text="reportePago.comprobanteNombre"></span></div>
</div>
<button @click="enviarReportePago()" class="admin-btn w-full bg-gradient-to-r from-nico-green to-emerald-600"><i class="fas fa-paper-plane"></i> Enviar y Notificar</button>
<template x-if="comprobantesDelPadre().length > 0">
    <div class="bg-amber-400/20 border border-amber-400/50 text-amber-400 p-3 rounded-xl text-center text-[10px] font-bold mt-3 shadow-lg">
        <i class="fas fa-clock mr-1"></i> Comprobante enviado, en revisión por administración.
    </div>
</template>
</div>
</div>
</div>
<div class="glass-box p-5 rounded-3xl border border-white/10 space-y-4 mt-6">
<h4 class="text-xs font-black text-white uppercase flex items-center gap-2"><i class="fas fa-history"></i> Mi Historial de Pagos</h4>
<div class="scroll-container rounded-2xl">
<template x-if="misPagos().length === 0"><div class="text-center text-gray-500 italic py-8">Sin pagos registrados</div></template>
<template x-for="p in misPagos()" :key="p.id">
<div class="bg-black/60 p-3 rounded-xl border border-white/10 mb-2 flex justify-between items-center hover:border-white/30 transition-colors">
<div>
<p class="text-xs font-black text-white" x-text="p.concepto"></p>
<p class="text-[10px] text-gray-400">Ref: <span x-text="p.referencia || '—'"></span> · <span x-text="p.fecha"></span></p>
</div>
<div class="text-right">
<p class="text-sm font-black text-nico-green" x-text="'$' + p.monto.toFixed(2)"></p>
<span class="text-[8px] px-2 py-0.5 rounded font-bold uppercase tracking-widest block mb-1" :class="p.estado === 'validado' ? 'bg-nico-green/20 text-nico-green border border-nico-green/30' : (p.estado === 'rechazado' ? 'bg-nico-red/20 text-nico-red border border-nico-red/30' : 'bg-amber-400/20 text-amber-400 border border-amber-400/30')" x-text="(p.estado === 'en_revision' || p.estado === 'pendiente') ? 'En espera de verificación' : p.estado"></span>
<template x-if="(p.estado === 'en_revision' || p.estado === 'pendiente')">
    <a :href="'https://wa.me/50760000000?text=' + encodeURIComponent('Hola, envié mi pago por ' + p.concepto + ' (' + p.referencia + ') y sigo a la espera de validación.')" target="_blank" class="text-[9px] text-green-500 hover:text-green-400 flex items-center gap-1 justify-end mt-1"><i class="fab fa-whatsapp"></i> Notificar Admin</a>
</template>
</div>
</div>
</template>
</div>
</div>

  </div>
