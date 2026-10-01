<!-- ==================== SECCIÓN 8: PAGOS DEL CLUB (Estética Mejorada) ==================== -->
<div x-show="mainTab === 'pagosClub'" class="space-y-6 anim-entry">
<div class="text-center space-y-2">
<span class="text-xs font-black text-white border border-white/20 bg-black px-4 py-1 rounded-full uppercase tracking-widest shadow-lg">Gestión Financiera</span>
<h3 class="text-2xl font-black text-white uppercase">Pagos del Club</h3>
<p class="text-xs text-gray-400">Registra cobros presenciales y valida comprobantes digitales · Cuota: $15.00 quincenal</p>
</div>
<div class="timer-box p-5 rounded-3xl flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
<div class="flex items-center gap-4">
<div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-nico-orange to-nico-red border border-nico-orange/40 flex items-center justify-center shadow-lg">
<i class="fas fa-clock text-white text-2xl"></i>
</div>
<div>
<h4 class="text-sm font-black text-white uppercase tracking-wider">Ciclo de Cobro Quincenal</h4>
<p class="text-[11px] text-gray-300 mt-1">Próximo corte oficial: <span class="text-white bg-nico-orange/20 px-2 py-0.5 rounded border border-nico-orange/50 font-bold ml-1" x-text="proximaQuincena()"></span></p>
</div>
</div>
<div class="text-center bg-black/50 border border-white/10 rounded-2xl px-6 py-2">
<div class="text-3xl font-black text-white drop-shadow-[0_0_10px_rgba(255,255,255,0.3)]" x-text="diasParaQuincena()"></div>
<div class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Días restantes</div>
</div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
<div class="admin-card-glow p-5 rounded-3xl border border-nico-green/40 space-y-4 bg-gradient-to-b from-black/80 to-nico-green/5">
<h4 class="text-xs font-black text-nico-green uppercase flex items-center gap-2"><i class="fas fa-hand-holding-dollar"></i> Cobro Efectivo (Presencial)</h4>
<div class="space-y-3">
<select class="admin-input bg-black/60" x-model="pagoEfectivo.padreId">
<option value="">Seleccionar padre de familia...</option>
<template x-for="p in listaPadres" :key="p.id"><option :value="p.id" x-text="p.nombre + ' (' + p.usuario + ')'"></option></template>
</select>
<div class="flex gap-2">
<select class="admin-input bg-black/60 w-2/3" x-model="pagoEfectivo.concepto">
<option value="inscripcion">Inscripción ($15.00)</option><option value="mensualidad">Mensualidad ($15.00)</option><option value="otro">Otro monto</option>
</select>
<input type="number" step="0.01" class="admin-input bg-black/60 w-1/3" placeholder="Monto $" x-model="pagoEfectivo.monto">
</div>
<div class="flex gap-2">
<input type="date" class="admin-input bg-black/60 w-1/2" x-model="pagoEfectivo.fecha">
<input type="text" class="admin-input bg-black/60 w-1/2" placeholder="Referencia / Observación" x-model="pagoEfectivo.referencia">
</div>
<button @click="registrarPagoEfectivo()" class="w-full bg-gradient-to-r from-nico-green to-emerald-600 text-white font-black py-2.5 rounded-xl text-xs uppercase shadow-[0_0_15px_rgba(16,185,129,0.4)] hover:scale-[1.02] transition-transform"><i class="fas fa-check-circle mr-1"></i> Registrar Pago</button>
</div>
</div>
<div class="admin-card-glow p-5 rounded-3xl border border-amber-400/40 space-y-4 bg-gradient-to-b from-black/80 to-amber-400/5">
<h4 class="text-xs font-black text-amber-400 uppercase flex items-center gap-2"><i class="fas fa-file-invoice-dollar"></i> Comprobantes por Validar</h4>
<div class="scroll-container-sm rounded-2xl pr-2">
<template x-if="comprobantesPendientes().length === 0">
<div class="text-center text-gray-500 italic py-12"><i class="fas fa-shield-check text-4xl mb-3 block text-gray-600 opacity-50"></i>Todo al día. No hay pendientes.</div>
</template>
<template x-for="c in comprobantesPendientes()" :key="c.id">
<div class="bg-black/60 p-4 rounded-2xl border border-white/10 mb-2 hover:border-amber-400/50 transition-colors shadow-lg">
<div class="flex justify-between items-start mb-2">
<div>
<p class="text-xs font-black text-white" x-text="c.padreNombre"></p>
<span class="text-[9px] px-2 py-0.5 rounded bg-nico-orange/20 text-nico-orange font-bold uppercase tracking-wider" x-text="c.concepto"></span>
</div>
<span class="text-lg font-black text-nico-green" x-text="'$' + c.monto.toFixed(2)"></span>
</div>
<p class="text-[10px] text-gray-400"><i class="fas fa-calendar-day mr-1 text-gray-500"></i><span x-text="c.fecha"></span> | Ref: <span class="text-white" x-text="c.referencia || 'N/A'"></span></p>
<div class="flex gap-2 mt-3 pt-3 border-t border-white/5">
<button @click="validarComprobante(c.id, true)" class="flex-1 bg-nico-green/20 hover:bg-nico-green text-nico-green hover:text-white border border-nico-green/50 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all"><i class="fas fa-check mr-1"></i> Aprobar</button>
<button @click="validarComprobante(c.id, false)" class="flex-1 bg-nico-red/20 hover:bg-nico-red text-nico-red hover:text-white border border-nico-red/50 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all"><i class="fas fa-times mr-1"></i> Rechazar</button>
</div>
</div>
</template>
</div>
</div>
</div>
<div class="glass-box p-6 rounded-3xl border border-white/10 space-y-4 shadow-2xl">
<h4 class="text-xs font-black text-white uppercase flex items-center gap-2"><i class="fas fa-history text-nico-blue"></i> Historial General de Pagos</h4>
<div class="scroll-container rounded-2xl border border-white/5 bg-black/40">
<template x-if="historialPagosClub().length === 0">
<div class="text-center text-gray-500 italic py-10"><i class="fas fa-receipt text-3xl mb-2 block text-gray-600"></i>Sin movimientos registrados</div>
</template>
<table class="w-full text-xs modern-table" x-show="historialPagosClub().length > 0">
<thead class="sticky top-0 bg-black/90 backdrop-blur-md z-10 shadow-md">
<tr class="text-gray-400 uppercase font-black text-[9px] tracking-widest border-b border-white/10">
<th class="py-3 px-4 text-left">Fecha</th>
<th class="py-3 px-4 text-left">Padre / Familia</th>
<th class="py-3 px-4 text-left">Concepto</th>
<th class="py-3 px-4 text-center">Monto</th>
<th class="py-3 px-4 text-center">Método</th>
<th class="py-3 px-4 text-center">Estado</th>
</tr>
</thead>
<tbody class="divide-y divide-white/5">
<template x-for="p in historialPagosClub()" :key="p.id">
<tr class="hover:bg-white/5 transition-colors group">
<td class="py-3 px-4 text-gray-400 font-mono text-[10px]" x-text="p.fecha"></td>
<td class="py-3 px-4 text-white font-bold group-hover:text-nico-orange transition-colors" x-text="p.padreNombre"></td>
<td class="py-3 px-4 text-gray-300 font-semibold" x-text="p.concepto"></td>
<td class="py-3 px-4 text-center text-nico-green font-black" x-text="'$' + p.monto.toFixed(2)"></td>
<td class="py-3 px-4 text-center"><span class="text-[9px] px-2 py-1 rounded font-black uppercase tracking-wider" :class="p.metodo === 'efectivo' ? 'bg-nico-green/20 text-nico-green border border-nico-green/30' : 'bg-nico-blue/20 text-nico-blue border border-nico-blue/30'" x-text="p.metodo"></span></td>
<td class="py-3 px-4 text-center"><span class="text-[9px] px-2 py-1 rounded font-black uppercase tracking-wider" :class="p.estado === 'validado' ? 'bg-nico-green/20 text-nico-green border border-nico-green/30' : (p.estado === 'rechazado' ? 'bg-nico-red/20 text-nico-red border border-nico-red/30' : 'bg-amber-400/20 text-amber-400 border border-amber-400/30')" x-text="p.estado"></span></td>
</tr>
</template>
</tbody>
</table>
</div>
</div>
</div>