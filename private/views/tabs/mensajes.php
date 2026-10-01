<!-- ==================== SECCION 7: MENSAJES ==================== -->
<div x-show="mainTab === 'mensajes' && esAdmin" class="space-y-6 anim-entry">

  <div class="text-center space-y-2">
    <span class="text-xs font-black text-white border border-white/20 bg-black px-4 py-1 rounded-full uppercase tracking-widest shadow-lg">Bandeja de Entrada</span>
    <h3 class="text-2xl font-black text-white uppercase">Buzón: Voz del Padre</h3>
  </div>

  <template x-if="comentariosPadres.length === 0">
    <div class="glass-box p-10 rounded-3xl border border-white/10 text-center text-gray-500 italic"><i class="fas fa-box-open text-3xl mb-3 block text-gray-600"></i>No hay mensajes recientes en el buzón.</div>
  </template>

  <div class="space-y-4 scroll-container-sm pr-2 overflow-y-auto" style="max-height: 600px;">
    <template x-for="c in comentariosPadres" :key="c.id">
      <div class="glass-box p-5 rounded-3xl border border-white/10 space-y-3 relative group" :class="c.estado === 'leido' ? 'opacity-70' : ''">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-white/10 pb-2">
          <div class="flex items-center gap-3">
            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full border" :class="estadoComentarioColor(c.tipo)" x-text="c.tipo.replace('_', ' ')"></span>
            <span class="text-xs font-bold text-white" x-text="c.es_anonimo == 1 ? 'Anónimo' : (c.padre_nombre || 'Padre de Familia')"></span>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-[10px] text-gray-500 font-bold" x-text="c.created_at ? new Date(c.created_at).toLocaleString() : ''"></span>
            <button @click="borrarComentario(c.id)" class="text-red-500 hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity"><i class="fas fa-trash"></i></button>
          </div>
        </div>
        <div class="space-y-1">
          <p class="text-sm font-bold text-white" x-text="c.asunto"></p>
          <p class="text-xs text-gray-400 whitespace-pre-wrap" x-text="c.mensaje"></p>
        </div>
        <div class="border-t border-white/5 pt-2 text-right">
          <button x-show="c.estado === 'nuevo'" @click="window.DB.cambiarEstadoComentario(c.id, 'leido'); comentariosVersion++" class="text-[10px] text-nico-orange font-bold hover:text-white transition-colors"><i class="fas fa-check-double"></i> Marcar como leído</button>
          <button x-show="c.estado === 'leido'" @click="window.DB.cambiarEstadoComentario(c.id, 'nuevo'); comentariosVersion++" class="text-[10px] text-gray-500 font-bold hover:text-white transition-colors"><i class="fas fa-eye-slash"></i> Marcar como no leído</button>
        </div>
      </div>
    </template>
  </div>
</div>