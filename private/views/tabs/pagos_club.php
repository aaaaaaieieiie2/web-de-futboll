<!-- ==================== SECCIÓN 8: PAGOS DEL CLUB (Estética Mejorada) ==================== -->
<div x-show="mainTab === 'pagosClub' && esAdmin" class="space-y-6 anim-entry">
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
<div class="flex items-center gap-3"><button @click="recordatorioMasivo()" class="bg-green-500/20 text-green-500 border border-green-500/50 hover:bg-green-500 hover:text-white transition-colors px-4 py-2 rounded-xl text-[10px] font-black uppercase flex items-center gap-2 shadow-[0_0_15px_rgba(34,197,94,0.3)]" title="Abrir y enviar recordatorios autom�ticamente en pesta�as consecutivas"><i class="fab fa-whatsapp text-base"></i> Recordatorio Masivo</button><div class="text-center bg-black/50 border border-white/10 rounded-2xl px-6 py-2">
<div class="text-3xl font-black text-white drop-shadow-[0_0_10px_rgba(255,255,255,0.3)]" x-text="diasParaQuincena()"></div>
<div class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Días restantes</div>
</div>
</div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- COLUMNA EFECTIVO -->
        <div class="admin-card-glow p-5 rounded-3xl border border-nico-green/40 space-y-4 bg-gradient-to-b from-black/80 to-nico-green/5 flex flex-col h-[500px]">
            <div class="flex justify-between items-start">
                <h4 class="text-xs font-black text-nico-green uppercase flex items-center gap-2"><i class="fas fa-hand-holding-dollar"></i> Cobros Efectivo</h4>
                <div class="flex gap-1 text-[9px] font-bold">
                    <button @click="filtroEfectivo = 'todos'" :class="filtroEfectivo === 'todos' ? 'bg-white/20 text-white' : 'text-gray-400 hover:text-white'" class="px-2 py-1 rounded transition-colors">Todos</button>
                    <button @click="filtroEfectivo = 'aldia'" :class="filtroEfectivo === 'aldia' ? 'bg-nico-green/20 text-nico-green' : 'text-gray-400 hover:text-nico-green'" class="px-2 py-1 rounded transition-colors">Verdes</button>
                    <button @click="filtroEfectivo = 'pendientes'" :class="filtroEfectivo === 'pendientes' ? 'bg-gray-500/30 text-gray-300' : 'text-gray-400 hover:text-gray-300'" class="px-2 py-1 rounded transition-colors">Grises</button>
                    <button @click="filtroEfectivo = 'vencidos'" :class="filtroEfectivo === 'vencidos' ? 'bg-nico-red/20 text-nico-red' : 'text-gray-400 hover:text-nico-red'" class="px-2 py-1 rounded transition-colors">Rojos</button>
                </div>
            </div>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-500"></i>
                <input type="text" class="admin-input bg-black/60 pl-9 w-full" placeholder="Buscar padre..." x-model="busquedaPagoPadreEf">
            </div>
            <div class="scroll-container-sm pr-2 flex-1">
                <template x-if="padresParaCobroEfectivoFiltrados.length === 0"><div class="text-xs text-gray-500 text-center mt-4">No hay padres aquí.</div></template>
                <template x-for="p in padresParaCobroEfectivoFiltrados" :key="p.id">
                    <div class="bg-black/60 p-3 rounded-xl border border-white/10 mb-2 flex justify-between items-center hover:border-nico-green/30 transition-colors">
                        <div>
                            <p class="text-xs font-black text-white" x-text="p.nombre"></p>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-white/10 text-gray-300 uppercase" x-text="p.hijoCat || 'Sin Cat'"></span>
                            <div class="text-[9px] mt-1 font-bold" :class="p.estadoPago === 'amarillo' ? 'text-amber-400 animate-pulse' : (p.estadoPago === 'aldia' ? 'text-nico-green' : (p.estadoPago === 'vencido' ? 'text-nico-red' : 'text-gray-400'))" x-text="p.estadoPago === 'amarillo' ? 'EN REVISIÓN (Solicitud)' : (p.estadoPago === 'aldia' ? 'PAGADO EFECTIVO' : (p.estadoPago === 'vencido' ? 'VENCIDO / MOROSO' : 'PENDIENTE (Gracia)'))"></div>
                        </div>
                        <div class="flex gap-1 shrink-0 items-center bg-black/40 p-1 rounded-xl border border-white/5">
                            <button @click="cambiarMetodoPago(p.id, 'linea')" class="text-[9px] text-nico-blue hover:text-white px-2 uppercase font-bold" title="Cambiar a Cobro en Línea">Pasar a Línea <i class="fas fa-arrow-right"></i></button>
                            <template x-if="p.estadoPago === 'vencido'">
                                <button @click="toggleBloqueo(p.id)" class="w-8 h-8 rounded transition-colors flex items-center justify-center" :class="p.bloqueado === 1 ? 'bg-gray-500/20 text-gray-400 hover:bg-nico-green/20 hover:text-nico-green' : 'bg-nico-red/20 text-nico-red hover:bg-nico-red hover:text-white'" :title="p.bloqueado === 1 ? 'Desbloquear Acceso' : 'Bloquear Acceso'">
                                    <i class="fas" :class="p.bloqueado === 1 ? 'fa-unlock' : 'fa-lock'"></i>
                                </button>
                            </template>
                            <template x-if="p.estadoPago !== 'aldia'">
                                <button @click="abrirModalPago(p.id)" class="w-8 h-8 rounded bg-nico-green/20 text-nico-green hover:bg-nico-green hover:text-white transition-colors flex items-center justify-center" title="Registrar Pago Manual"><i class="fas fa-dollar-sign"></i></button>
                            </template>
                            <template x-if="p.estadoPago === 'aldia'">
                                <button @click="deshacerPago(p.id)" class="w-8 h-8 rounded bg-gray-500/20 text-gray-400 hover:bg-gray-500 hover:text-white transition-colors flex items-center justify-center" title="Deshacer Pago (Marcar No Pagado)"><i class="fas fa-undo"></i></button>
                            </template>
                            <button @click="recordatorioWhatsapp(p.id, 'efectivo')" class="w-8 h-8 rounded bg-green-500/20 text-green-500 hover:bg-green-500 hover:text-white transition-colors flex items-center justify-center" title="Enviar WhatsApp"><i class="fab fa-whatsapp text-lg"></i></button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
<!-- COLUMNA LÍNEA -->
        <div class="admin-card-glow p-5 rounded-3xl border border-nico-blue/40 space-y-4 bg-gradient-to-b from-black/80 to-nico-blue/5 flex flex-col h-[500px]">
            <div class="flex justify-between items-start">
                <h4 class="text-xs font-black text-nico-blue uppercase flex items-center gap-2"><i class="fas fa-credit-card"></i> Cobros en Línea <span class="text-[8px] font-normal text-gray-500 ml-2">(Comprobantes retenidos por 30 días)</span></h4>
                <div class="flex gap-1 text-[9px] font-bold">
                    <button @click="filtroLinea = 'todos'" :class="filtroLinea === 'todos' ? 'bg-white/20 text-white' : 'text-gray-400 hover:text-white'" class="px-2 py-1 rounded transition-colors">Todos</button>
                    <button @click="filtroLinea = 'aldia'" :class="filtroLinea === 'aldia' ? 'bg-nico-green/20 text-nico-green' : 'text-gray-400 hover:text-nico-green'" class="px-2 py-1 rounded transition-colors">Verdes</button>
                    <button @click="filtroLinea = 'pendientes'" :class="filtroLinea === 'pendientes' ? 'bg-gray-500/30 text-gray-300' : 'text-gray-400 hover:text-gray-300'" class="px-2 py-1 rounded transition-colors">Grises</button>
                    <button @click="filtroLinea = 'vencidos'" :class="filtroLinea === 'vencidos' ? 'bg-nico-red/20 text-nico-red' : 'text-gray-400 hover:text-nico-red'" class="px-2 py-1 rounded transition-colors">Rojos</button>
                    <button @click="filtroLinea = 'solicitudes'" :class="filtroLinea === 'solicitudes' ? 'bg-amber-400/20 text-amber-400' : 'text-gray-400 hover:text-amber-400'" class="px-2 py-1 rounded transition-colors flex items-center gap-1"><i class="fas fa-bell"></i> Solicitudes</button>
                </div>
            </div>
            
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-500"></i>
                <input type="text" class="admin-input bg-black/60 pl-9 w-full" placeholder="Buscar padre..." x-model="busquedaPagoPadreLi">
            </div>
            <div class="scroll-container-sm pr-2 flex-1">
                <template x-if="padresParaCobroLineaFiltrados.length === 0"><div class="text-xs text-gray-500 text-center mt-4">No hay padres aquí.</div></template>
                <template x-for="p in padresParaCobroLineaFiltrados" :key="p.id">
                    <div class="bg-black/60 p-3 rounded-xl border border-white/10 mb-2 flex justify-between items-center hover:border-nico-blue/30 transition-colors">
                        <div>
                            <p class="text-xs font-black text-white" x-text="p.nombre"></p>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-white/10 text-gray-300 uppercase" x-text="p.hijoCat || 'Sin Cat'"></span>
                            <div class="text-[9px] mt-1 font-bold" :class="p.estadoPago === 'amarillo' ? 'text-amber-400 animate-pulse' : (p.estadoPago === 'aldia' ? 'text-nico-blue' : (p.estadoPago === 'vencido' ? 'text-nico-red' : 'text-gray-400'))" x-text="p.estadoPago === 'amarillo' ? 'EN REVISIÓN (Solicitud)' : (p.estadoPago === 'aldia' ? 'PAGADO EN LÍNEA' : (p.estadoPago === 'vencido' ? 'VENCIDO / MOROSO' : 'PENDIENTE (Gracia)'))"></div>
                        </div>
                        <div class="flex gap-1 shrink-0 items-center bg-black/40 p-1 rounded-xl border border-white/5">
                            <button @click="cambiarMetodoPago(p.id, 'efectivo')" class="text-[9px] text-nico-green hover:text-white px-2 uppercase font-bold" title="Cambiar a Cobro Efectivo"><i class="fas fa-arrow-left"></i> Pasar a Efectivo</button>
                            <template x-if="p.estadoPago !== 'aldia'">
                                <button @click="abrirModalPago(p.id)" class="w-8 h-8 rounded bg-nico-green/20 text-nico-green hover:bg-nico-green hover:text-white transition-colors flex items-center justify-center" title="Registrar Pago Manual"><i class="fas fa-dollar-sign"></i></button>
                            </template>
                            <template x-if="p.estadoPago === 'aldia'">
                                <button @click="deshacerPago(p.id)" class="w-8 h-8 rounded bg-gray-500/20 text-gray-400 hover:bg-gray-500 hover:text-white transition-colors flex items-center justify-center" title="Deshacer Pago (Marcar No Pagado)"><i class="fas fa-undo"></i></button>
                            </template>
                            <template x-if="p.estadoPago === 'vencido'">
                                <button @click="toggleBloqueo(p.id)" class="w-8 h-8 rounded transition-colors flex items-center justify-center" :class="p.bloqueado === 1 ? 'bg-gray-500/20 text-gray-400 hover:bg-nico-green/20 hover:text-nico-green' : 'bg-nico-red/20 text-nico-red hover:bg-nico-red hover:text-white'" :title="p.bloqueado === 1 ? 'Desbloquear Acceso' : 'Bloquear Acceso'">
                                    <i class="fas" :class="p.bloqueado === 1 ? 'fa-unlock' : 'fa-lock'"></i>
                                </button>
                            </template>
                              <button @click="recordatorioWhatsapp(p.id, 'linea')" class="w-8 h-8 rounded bg-green-500/20 text-green-500 hover:bg-green-500 hover:text-white transition-colors flex items-center justify-center" title="Enviar WhatsApp"><i class="fab fa-whatsapp text-lg"></i></button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
        <!-- BANDEJA DE SOLICITUDES INFERIOR -->
    <div class="mt-8 admin-card-glow p-5 rounded-3xl border border-amber-400/40 space-y-4 bg-gradient-to-b from-black/80 to-amber-400/5">
        <h4 class="text-sm font-black text-amber-400 uppercase flex items-center gap-2"><i class="fas fa-inbox"></i> Bandeja de Solicitudes de Pago en Línea</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="p in estadoDeCuentas.filter(x => x.estadoPago === 'amarillo')" :key="p.id">
                <div class="bg-black/60 p-4 rounded-2xl border border-amber-400/20 hover:border-amber-400/50 transition-all flex flex-col gap-3 shadow-lg relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-amber-400/10 blur-[20px] rounded-full"></div>
                    
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <p class="text-sm font-black text-white" x-text="p.nombre"></p>
                            <p class="text-[10px] text-gray-400 uppercase mt-0.5">Familia / Jugador <span class="text-white font-bold" x-text="p.hijoCat || 'N/A'"></span></p>
                        </div>
                        <span class="bg-amber-400/20 text-amber-400 text-[9px] font-black px-2 py-1 rounded animate-pulse">EN REVISIÓN</span>
                    </div>
                    
                    <div class="flex gap-2 relative z-10 mt-auto pt-2 border-t border-white/5">
                        <button @click="verComprobante(p.id)" class="flex-1 py-2 rounded-xl bg-amber-400/10 text-amber-400 hover:bg-amber-400 hover:text-black transition-colors text-xs font-bold flex justify-center items-center gap-1" title="Abrir y Descargar"><i class="fas fa-file-invoice"></i> Ver</button>
                        <button @click="procesarComprobanteDirecto(p.id, true)" class="w-10 rounded-xl bg-nico-green/20 text-nico-green hover:bg-nico-green hover:text-white transition-colors flex items-center justify-center" title="Aprobar"><i class="fas fa-check"></i></button>
                        <button @click="procesarComprobanteDirecto(p.id, false)" class="w-10 rounded-xl bg-nico-red/20 text-nico-red hover:bg-nico-red hover:text-white transition-colors flex items-center justify-center" title="Rechazar"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            </template>
            <template x-if="estadoDeCuentas.filter(x => x.estadoPago === 'amarillo').length === 0">
                <div class="col-span-full text-center py-6 text-gray-500 text-xs italic">No hay solicitudes de pago pendientes de revisión.</div>
            </template>
        </div>
    </div>

        <!-- CONFIGURACIÓN DE TARIFAS (Compacta inferior) -->
    <div class="mt-4 bg-black/50 border border-white/10 rounded-2xl p-4 flex flex-col md:flex-row justify-between items-center gap-4">
        <h4 class="text-xs font-black text-white uppercase flex items-center gap-2"><i class="fas fa-cog text-nico-orange"></i> Configuración Oficial</h4>
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-[10px] text-gray-400 font-bold uppercase">Mensualidad ($)</label>
                <input type="number" step="0.01" class="bg-black/60 border border-white/10 rounded-lg px-2 py-1 text-white text-xs w-20 text-center focus:border-nico-green outline-none" x-model.number="tarifaOficialMensualidad">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-[10px] text-gray-400 font-bold uppercase">Inscripción ($)</label>
                <input type="number" step="0.01" class="bg-black/60 border border-white/10 rounded-lg px-2 py-1 text-white text-xs w-20 text-center focus:border-nico-green outline-none" x-model.number="tarifaOficialInscripcion">
            </div>
            <button @click="guardarTarifas()" class="bg-white/10 hover:bg-white/20 border border-white/20 hover:border-nico-green transition-colors text-white font-bold px-4 py-1.5 rounded-lg text-[10px] uppercase flex items-center gap-2"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
    <!-- MODAL VER COMPROBANTE -->
    <div x-show="comprobanteActivo" x-transition class="fixed inset-0 z-[70] bg-black/95 backdrop-blur-md flex items-center justify-center p-4" style="display: none;">
        <template x-if="comprobanteActivo">
            <div class="glass-box p-6 sm:p-8 rounded-3xl max-w-md w-full border border-amber-400/50 relative shadow-2xl space-y-4">
                <button @click="cerrarComprobante()" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg"><i class="fas fa-times"></i></button>
                <h3 class="text-xl font-black text-white uppercase"><i class="fas fa-file-invoice text-amber-400"></i> Validar Pago</h3>
                
                <div class="flex justify-between items-center bg-black/50 p-3 rounded-xl border border-white/5">
                    <div>
                        <p class="text-xs text-gray-400 uppercase">Familia</p>
                        <p class="text-sm font-black text-white" x-text="comprobanteActivo.padreNombre"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400 uppercase" x-text="comprobanteActivo.concepto"></p>
                        <p class="text-lg font-black text-nico-green" x-text="'$' + comprobanteActivo.monto.toFixed(2)"></p>
                    </div>
                </div>
                
                <div class="text-[10px] text-gray-400 font-mono space-y-1">
                    <p><i class="fas fa-calendar-day w-4"></i> Fecha: <span class="text-white" x-text="comprobanteActivo.fecha"></span></p>
                    <p><i class="fas fa-hashtag w-4"></i> Ref: <span class="text-white" x-text="comprobanteActivo.referencia || 'N/A'"></span></p>
                </div>
                
                <div class="w-full bg-black/80 rounded-xl border border-white/10 p-2 flex justify-center items-center overflow-hidden" style="max-height: 250px;">
                    <img :src="comprobanteActivo.comprobante" alt="Comprobante" class="max-w-full max-h-full object-contain cursor-pointer hover:scale-105 transition-transform">
                </div>
                
                <div class="flex gap-3 pt-2">
                    <a :href="comprobanteActivo.comprobante" download class="flex-1 bg-nico-blue/20 hover:bg-nico-blue text-nico-blue hover:text-white border border-nico-blue/50 py-3 rounded-xl text-[10px] font-black uppercase transition-all shadow-lg text-center leading-[3] block"><i class="fas fa-download mr-1"></i> Descargar</a>
                    <button @click="procesarComprobante(false)" class="flex-1 bg-nico-red/20 hover:bg-nico-red text-nico-red hover:text-white border border-nico-red/50 py-3 rounded-xl text-[10px] font-black uppercase transition-all shadow-lg"><i class="fas fa-times mr-1"></i> Rechazar</button>
                    <button @click="procesarComprobante(true)" class="flex-1 bg-nico-green/20 hover:bg-nico-green text-nico-green hover:text-white border border-nico-green/50 py-3 rounded-xl text-[10px] font-black uppercase transition-all shadow-lg"><i class="fas fa-check mr-1"></i> Aprobar Pago</button>
                </div>
            </div>
        </template>
    </div>
    <!-- MODAL DE PAGO MANUAL -->
    <div x-show="modalPagoActivo" x-transition class="fixed inset-0 z-[60] bg-black/95 backdrop-blur-md flex items-center justify-center p-4" style="display: none;">
        <div class="glass-box p-6 sm:p-8 rounded-3xl max-w-sm w-full border border-nico-green/50 relative shadow-2xl space-y-4" @click.outside="cerrarModalPago()">
            <button @click="cerrarModalPago()" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg"><i class="fas fa-times"></i></button>
            <h3 class="text-xl font-black text-white uppercase"><i class="fas fa-dollar-sign text-nico-green"></i> Registrar Pago</h3>
            <p class="text-[11px] text-gray-400">El pago se aplicará al usuario seleccionado y su estado pasará automáticamente a AL DÍA.</p>
            
            <div class="space-y-3">
                <select class="admin-input !bg-black/60 w-full" x-model="pagoActual.concepto">
                    <option value="inscripcion">Inscripción</option>
                    <option value="mensualidad">Mensualidad</option>
                    <option value="otro">Otro concepto</option>
                </select>
                <div class="flex gap-2">
                    <div class="w-1/2 relative">
                        <span class="absolute left-3 top-2.5 text-gray-400">$</span>
                        <input type="number" step="0.01" class="admin-input !bg-black/60 pl-6 w-full" placeholder="Monto" x-model="pagoActual.monto">
                    </div>
                    <input type="date" class="admin-input !bg-black/60 w-1/2" x-model="pagoActual.fecha">
                </div>
                <input type="text" class="admin-input !bg-black/60 w-full" placeholder="Referencia / N° de Recibo" x-model="pagoActual.referencia">
            </div>
            
            <button @click="guardarPagoModal()" class="w-full bg-nico-green hover:bg-nico-greenDark text-white font-black py-3 rounded-xl text-xs uppercase shadow-[0_0_15px_rgba(16,185,129,0.3)]"><i class="fas fa-check-circle mr-1"></i> Guardar Pago</button>
        </div>
    </div></div>

