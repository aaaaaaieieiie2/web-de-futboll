<!-- ==================== MODAL EDICIÓN Y CREACIÓN DE STAFF (PANEL ADMINISTRATIVO) ==================== -->
<div x-show="staffFormModal" x-transition class="fixed inset-0 z-[9999] bg-black/90 backdrop-blur-md flex items-center justify-center p-4 overflow-y-auto" style="display: none;">
  <div class="glass-box p-6 sm:p-8 rounded-3xl max-w-lg w-full border border-nico-orange/40 relative shadow-2xl space-y-5 my-auto mx-auto text-left" @click.outside="staffFormModal = false">
    
    <!-- Encabezado del Modal -->
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br flex items-center justify-center text-xl text-white shadow-lg border border-white/20" :class="staffForm.color || 'from-nico-orange to-amber-600'">
          <i class="fas" :class="staffForm.fotoIcon || 'fa-user-tie'"></i>
        </div>
        <div>
          <span class="text-[10px] font-black uppercase tracking-widest text-nico-orange">Panel del Administrador</span>
          <h3 class="text-lg font-black text-white uppercase" x-text="staffForm.id ? 'Editar Ficha del Entrenador' : 'Registrar Nuevo Integrante'"></h3>
        </div>
      </div>
      <button type="button" @click="staffFormModal = false" class="w-8 h-8 rounded-xl bg-white/5 hover:bg-white/15 text-gray-400 hover:text-white flex items-center justify-center transition-colors">
        ✕
      </button>
    </div>

    <!-- Formulario de Campos -->
    <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-1 scroll-container-sm">
      
      <!-- Nombre y Cargo -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="text-[11px] font-bold text-gray-300 block mb-1">Nombre Completo *</label>
          <input type="text" x-model="staffForm.nombre" placeholder="Ej: Prof. Luis Muñoz" class="admin-input text-xs w-full">
        </div>
        <div>
          <label class="text-[11px] font-bold text-gray-300 block mb-1">Cargo en el Club *</label>
          <input type="text" x-model="staffForm.cargo" placeholder="Ej: Director Técnico Principal" class="admin-input text-xs w-full">
        </div>
      </div>

      <!-- Licencia y Experiencia -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="text-[11px] font-bold text-gray-300 block mb-1">Licencia o Grado</label>
          <input type="text" x-model="staffForm.licencia" placeholder="Ej: Licencia A FEPAFUT" class="admin-input text-xs w-full">
        </div>
        <div>
          <label class="text-[11px] font-bold text-gray-300 block mb-1">Años de Experiencia</label>
          <input type="text" x-model="staffForm.exp" placeholder="Ej: 10 Años de Experiencia" class="admin-input text-xs w-full">
        </div>
      </div>

      <!-- Especialidad -->
      <div>
        <label class="text-[11px] font-bold text-gray-300 block mb-1">Especialidad Deportiva</label>
        <input type="text" x-model="staffForm.especialidad" placeholder="Ej: Acondicionamiento Físico y Táctica" class="admin-input text-xs w-full">
      </div>

      <!-- Biografía / Reseña -->
      <div>
        <label class="text-[11px] font-bold text-gray-300 block mb-1">Biografía / Perfil Profesional</label>
        <textarea x-model="staffForm.bio" rows="3" placeholder="Reseña formativa y trayectoria en el fútbol..." class="admin-input text-xs w-full resize-none"></textarea>
      </div>

      <!-- Selector de Icono -->
      <div>
        <label class="text-[11px] font-bold text-gray-300 block mb-1.5">Icono Representativo</label>
        <div class="flex items-center gap-2 flex-wrap">
          <template x-for="ic in ['fa-user-tie', 'fa-stopwatch', 'fa-brain', 'fa-chalkboard-user', 'fa-medal', 'fa-futbol', 'fa-user', 'fa-award', 'fa-heart-pulse']" :key="ic">
            <button type="button" @click="staffForm.fotoIcon = ic" class="w-9 h-9 rounded-xl flex items-center justify-center text-sm border transition-all" :class="staffForm.fotoIcon === ic ? 'border-nico-orange bg-nico-orange/20 text-nico-orange scale-110 shadow-lg' : 'border-white/10 bg-black/60 text-gray-400 hover:text-white hover:border-white/30'">
              <i class="fas" :class="ic"></i>
            </button>
          </template>
        </div>
      </div>

      <!-- Selector de Color / Gradiente -->
      <div>
        <label class="text-[11px] font-bold text-gray-300 block mb-1.5">Color del Distintivo</label>
        <div class="flex items-center gap-2 flex-wrap">
          <template x-for="grad in [
            { id: 'from-nico-orange to-amber-600', label: 'Naranja' },
            { id: 'from-emerald-500 to-teal-700', label: 'Verde' },
            { id: 'from-blue-600 to-indigo-800', label: 'Azul' },
            { id: 'from-purple-600 to-violet-900', label: 'Púrpura' },
            { id: 'from-red-600 to-rose-900', label: 'Rojo' },
            { id: 'from-amber-400 to-yellow-600', label: 'Dorado' }
          ]" :key="grad.id">
            <button type="button" @click="staffForm.color = grad.id" class="px-2.5 py-1.5 rounded-xl text-[10px] font-black uppercase border transition-all flex items-center gap-1.5" :class="staffForm.color === grad.id ? 'border-white text-white bg-white/15 scale-105 shadow-md' : 'border-white/10 text-gray-400 bg-black/40 hover:text-white'">
              <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-r" :class="grad.id"></span>
              <span x-text="grad.label"></span>
            </button>
          </template>
        </div>
      </div>

    </div>

    <!-- Botones de Acción -->
    <div class="pt-3 border-t border-white/10 flex items-center justify-end gap-3">
      <button type="button" @click="staffFormModal = false" class="px-4 py-2.5 rounded-xl border border-white/15 bg-black/60 text-gray-300 hover:text-white text-xs font-black uppercase transition-all">
        Cancelar
      </button>
      <button type="button" @click="guardarStaffForm()" class="bg-gradient-to-r from-nico-green to-emerald-500 hover:from-emerald-400 hover:to-nico-green text-black font-black px-6 py-2.5 rounded-xl text-xs uppercase transition-all shadow-[0_0_15px_rgba(16,185,129,0.4)] flex items-center gap-2 active:scale-95 cursor-pointer">
        <i class="fas fa-floppy-disk"></i>
        <span>Guardar Cambios</span>
      </button>
    </div>

  </div>
</div>
