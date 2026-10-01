<!-- ==================== SECCION: BUZÓN DE PADRES ==================== -->
<div x-show="mainTab === 'buzon'" class="space-y-6 anim-entry max-w-2xl mx-auto">
  <div class="text-center space-y-2">
    <span class="text-xs font-black text-white border border-white/20 bg-black px-4 py-1 rounded-full uppercase tracking-widest shadow-lg">Contacto Directo</span>
    <h3 class="text-2xl font-black text-white uppercase">Voz del Padre</h3>
  </div>

  <div class="glass-box p-8 rounded-3xl border border-nico-orange/40 space-y-6 shadow-[0_0_20px_rgba(255,85,0,0.15)] relative overflow-hidden">
    <div class="absolute top-0 right-0 w-32 h-32 bg-nico-orange/10 blur-[50px] rounded-full"></div>
    
    <div class="relative z-10 space-y-2 text-center border-b border-white/10 pb-6">
        <i class="fas fa-bullhorn text-4xl text-nico-orange mb-2"></i>
        <p class="text-sm text-gray-300">Envíanos tus comentarios, críticas constructivas, inconformidades o sugerencias. Este canal es directo con la administración del club.</p>
    </div>
    
    <div class="space-y-4 relative z-10">
      <div class="space-y-1">
        <label class="text-xs font-bold text-nico-orange uppercase pl-2">Tipo de Mensaje</label>
        <select x-model="feedbackForm.tipo" class="admin-input !bg-black/60 focus:!border-nico-orange w-full">
          <option value="">-- Selecciona una opción --</option>
          <option value="sugerencia">Sugerencia</option>
          <option value="critica_constructiva">Crítica Constructiva</option>
          <option value="inconformidad">Inconformidad / Queja</option>
          <option value="felicitacion">Felicitación</option>
        </select>
      </div>
      
      <div class="space-y-1">
        <label class="text-xs font-bold text-nico-orange uppercase pl-2">Asunto / Título</label>
        <input type="text" x-model="feedbackForm.asunto" class="admin-input !bg-black/60 focus:!border-nico-orange w-full" placeholder="Ej. Sobre los entrenamientos de la Sub-10">
      </div>
      
      <div class="space-y-1">
        <label class="text-xs font-bold text-nico-orange uppercase pl-2">Tu Mensaje</label>
        <textarea x-model="feedbackForm.mensaje" class="admin-input !bg-black/60 h-32 resize-none focus:!border-nico-orange w-full" placeholder="Explica detalladamente tu comentario..."></textarea>
      </div>
      
      <div class="flex items-center justify-between pt-2">
          <label class="flex items-center gap-2 text-xs font-bold text-gray-300 cursor-pointer hover:text-white transition-colors">
            <input type="checkbox" x-model="feedbackForm.es_anonimo" class="accent-nico-orange w-5 h-5 rounded">
            Enviar de forma anónima
          </label>
          <span class="text-[10px] text-gray-500 italic" x-show="!feedbackForm.es_anonimo">Se adjuntará tu nombre</span>
          <span class="text-[10px] text-nico-orange font-bold italic" x-show="feedbackForm.es_anonimo">Tu identidad será oculta</span>
      </div>
      
            <div x-show="feedbackStatus.show" x-transition.opacity.duration.300ms class="p-3 rounded-xl border text-center text-xs font-bold" :class="feedbackStatus.error ? 'bg-red-500/20 border-red-500/50 text-red-300' : 'bg-nico-green/20 border-nico-green/50 text-nico-green'">
          <i class="fas" :class="feedbackStatus.error ? 'fa-triangle-exclamation' : 'fa-circle-check'"></i> <span x-text="feedbackStatus.msg"></span>
      </div>
      
      <button @click.prevent="enviarFeedback()" class="w-full btn-save flex justify-center items-center gap-2 mt-4 py-4 text-sm"><i class="fas fa-paper-plane"></i> ENVIAR MENSAJE AL CLUB</button>
    </div>
  </div>
</div>
