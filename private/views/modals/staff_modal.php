<!-- ==================== MODAL STAFF ==================== -->
<div x-show="staffModal !== null" x-transition class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4" style="display: none;">
<div class="glass-box p-6 sm:p-8 rounded-3xl max-w-md w-full border border-white/20 relative shadow-2xl space-y-6" @click.outside="staffModal = null">
<button @click="staffModal = null" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg">✕</button>
<div class="flex items-center gap-4">
<div class="w-16 h-16 rounded-2xl bg-gradient-to-br flex items-center justify-center text-2xl text-white shadow-md border border-white/10" :class="staffModal ? staffModal.color : ''"><i class="fas" :class="staffModal ? staffModal.fotoIcon : ''"></i></div>
<div>
<h3 class="text-xl font-black text-white" x-text="staffModal ? staffModal.nombre : ''"></h3>
<span class="text-xs font-bold text-gray-300 block" x-text="staffModal ? staffModal.cargo : ''"></span>
<span class="text-[10px] text-gray-500 font-semibold uppercase" x-text="staffModal ? staffModal.licencia : ''"></span>
</div>
</div>
<div class="space-y-3 text-xs text-gray-300 bg-black/60 p-4 rounded-2xl border border-white/10">
<p><strong class="text-white">Especialidad:</strong> <span x-text="staffModal ? staffModal.especialidad : ''"></span></p>
<p><strong class="text-white">Experiencia:</strong> <span x-text="staffModal ? staffModal.exp : ''"></span></p>
<p class="leading-relaxed text-gray-400"><strong class="text-white">Biografía:</strong> <span x-text="staffModal ? staffModal.bio : ''"></span></p>
</div>
<button @click="staffModal = null" class="w-full bg-white text-black font-black py-3 rounded-xl text-xs uppercase">Cerrar Ficha</button>
</div>
</div>