<!-- ==================== MODAL VIDEO ==================== -->
<div x-show="videoModal" x-transition class="fixed inset-0 z-50 bg-black/95 backdrop-blur-md flex items-center justify-center p-4" style="display: none;">
<div class="glass-box p-4 rounded-3xl max-w-md w-full border border-white/20 relative shadow-2xl space-y-4" @click.outside="videoModal = false">
<div class="flex justify-between items-center px-2">
<span class="text-xs font-black text-white uppercase tracking-wider">Video Highlights • Temporada 2026</span>
<button @click="videoModal = false" class="text-gray-400 hover:text-white text-lg font-bold">✕</button>
</div>
<div class="aspect-video bg-black rounded-2xl overflow-hidden border border-white/10 relative shadow-inner flex items-center justify-center">
<template x-if="videoModal">
<video class="w-full h-full object-cover" controls autoplay>
<source src="video/resumen-temporada.mp4" type="video/mp4">
Tu navegador no soporta el formato de video.
</video>
</template>
</div>
</div>
</div>