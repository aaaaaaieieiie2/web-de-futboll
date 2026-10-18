<!-- ==================== MODAL STAFF ==================== -->
<div x-show="staffModal !== null" x-transition class="fixed inset-0 z-[9999] bg-black/90 backdrop-blur-md flex items-center justify-center p-4 overflow-y-auto" style="display: none;">
  <div class="glass-box p-6 sm:p-8 rounded-3xl max-w-sm sm:max-w-md w-full border border-white/20 relative shadow-2xl space-y-6 my-auto mx-auto text-left" @click.outside="staffModal = null">
    <button type="button" @click="staffModal = null" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors">✕</button>
    <div class="flex items-center gap-4">
      <div class="w-16 h-16 rounded-2xl bg-gradient-to-br flex items-center justify-center text-2xl text-white shadow-md border border-white/10 shrink-0" :class="staffModal ? staffModal.color : ''">
        <i class="fas" :class="staffModal ? staffModal.fotoIcon : ''"></i>
      </div>
      <div class="min-w-0 flex-1">
        <h3 class="text-lg sm:text-xl font-black text-white leading-tight break-words" x-text="staffModal ? staffModal.nombre : ''"></h3>
        <span class="text-xs font-bold text-gray-300 block mt-0.5 break-words" x-text="staffModal ? staffModal.cargo : ''"></span>
        <span class="text-[10px] text-gray-500 font-semibold uppercase block mt-1" x-text="staffModal ? staffModal.licencia : ''"></span>
      </div>
    </div>
    <div class="space-y-3 text-xs text-gray-300 bg-black/60 p-4 rounded-2xl border border-white/10">
      <p><strong class="text-white">Especialidad:</strong> <span class="break-words" x-text="staffModal ? staffModal.especialidad : ''"></span></p>
      <p><strong class="text-white">Experiencia:</strong> <span class="break-words" x-text="staffModal ? staffModal.exp : ''"></span></p>
      <p class="leading-relaxed text-gray-400"><strong class="text-white">Biografía:</strong> <span class="break-words" x-text="staffModal ? staffModal.bio : ''"></span></p>
    </div>
    <button type="button" @click="staffModal = null" class="w-full bg-white hover:bg-gray-200 text-black font-black py-3 rounded-xl text-xs uppercase tracking-wider transition-all active:scale-95 shadow-lg">Cerrar Ficha</button>
  </div>
</div>

