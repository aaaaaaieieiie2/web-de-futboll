<!-- ==================== MODAL EDITAR MEDIA (URL) ==================== -->
<div x-show="editMediaModal.open" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
  <div x-show="editMediaModal.open" x-transition.opacity class="absolute inset-0 bg-black/90 backdrop-blur-sm" @click="closeMediaEditModal()"></div>
  <div x-show="editMediaModal.open" x-transition.scale.duration.300ms class="relative w-full max-w-md bg-gradient-to-b from-gray-900 to-black rounded-3xl border-2 border-nico-orange shadow-[0_0_40px_rgba(255,85,0,0.4)] overflow-hidden">
    <!-- Header -->
    <div class="bg-black/60 p-5 border-b border-white/10 flex justify-between items-center relative">
      <h3 class="text-lg font-black text-white uppercase tracking-wider flex items-center gap-2">
        <i class="fas fa-link text-nico-orange"></i> <span x-text="editMediaModal.title"></span>
      </h3>
      <button @click="closeMediaEditModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <!-- Body -->
    <div class="p-6 space-y-4">
      <p class="text-xs text-gray-400">Ingresa la URL pública de la imagen (debe terminar en .jpg, .png, etc.). El sistema guardar el enlace directamente.</p>
      
      <div class="space-y-1">
        <label class="text-[10px] font-black text-nico-orange uppercase tracking-widest">URL de la Imagen</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-globe text-gray-500"></i>
          </div>
          <input type="text" x-model="editMediaModal.url" placeholder="https://ejemplo.com/foto.jpg" class="w-full bg-black/50 border border-white/10 rounded-xl py-3 pl-10 pr-4 text-white text-sm focus:border-nico-orange focus:ring-1 focus:ring-nico-orange outline-none transition-all placeholder-gray-600">
        </div>
      </div>

      <!-- Preview (Optional) -->
      <div x-show="editMediaModal.url && editMediaModal.type === 'image'" class="mt-4 border border-white/10 rounded-xl p-2 bg-black/50">
        <span class="text-[10px] text-gray-500 font-bold uppercase mb-2 block text-center">Vista Previa</span>
        <img :src="editMediaModal.url" class="w-full h-32 object-contain rounded-lg" onerror="this.onerror=null;this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iIzY2NiI+PHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00cy0xLjc5LTQtNC00LTQgMS43OS00IDQgMS43OSA0IDQgNHptMCAyYy0yLjY3IDAtOCAxLjM0LTggNHYyaDE2di0yYzAtMi42Ni01LjMzLTQtOC00eiIvPjwvc3ZnPg==';">
      </div>
    </div>
    
    <!-- Footer -->
    <div class="p-5 border-t border-white/10 bg-black/40 flex gap-3">
      <button @click="closeMediaEditModal()" class="flex-1 py-3 rounded-xl border border-white/20 text-gray-300 font-black uppercase text-xs hover:bg-white/5 transition-colors">
        Cancelar
      </button>
      <button @click="saveMediaEditModal()" class="flex-1 py-3 rounded-xl bg-gradient-to-r from-nico-orange to-red-600 text-white font-black uppercase text-xs shadow-[0_0_15px_rgba(255,85,0,0.4)] hover:scale-[1.02] transition-transform">
        <i class="fas fa-save mr-1"></i> Guardar URL
      </button>
    </div>
  </div>
</div>
