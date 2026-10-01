<!-- ==================== MODAL IMAGEN ==================== -->
<div x-show="imageModal !== null" x-transition class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4" style="display: none;">
<div class="glass-box p-6 sm:p-8 rounded-3xl max-w-lg w-full border border-white/20 relative shadow-2xl space-y-6 text-center" @click.outside="imageModal = null">
<button @click="imageModal = null" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg">✕</button>
<div class="w-16 h-16 mx-auto rounded-2xl bg-black border border-nico-orange flex items-center justify-center text-2xl text-nico-orange shadow-lg animate-pulse"><i class="fas fa-camera"></i></div>
<div>
<h3 class="text-xl font-black text-white uppercase" x-text="imageModal"></h3>
<p class="text-xs text-gray-400 mt-1">Fotografía oficial e independiente de la plantilla de la temporada.</p>
</div>
<div class="h-48 rounded-2xl bg-gradient-to-tr from-black via-gray-900 to-black border border-white/10 flex flex-col items-center justify-center p-4 relative overflow-hidden">
<img src="img/escudo.png" alt="Escudo Nico Sport" class="w-16 h-16 object-contain mb-2">
<span class="text-xs font-mono font-bold text-gray-300 uppercase tracking-widest">Academia Nico Sport - Plantilla Oficial</span>
</div>
<button @click="imageModal = null" class="w-full bg-white text-black font-black py-3 rounded-xl text-xs uppercase">Entendido</button>
</div>
</div>