<!-- ==================== NAVEGACIÓN MÓVIL ESTILO APP PREMIUM (100% SIMÉTRICA) ==================== -->
<nav id="mobileNav" class="md:hidden fixed bottom-0 inset-x-0 z-[90] bg-black/95 backdrop-blur-2xl border-t border-white/20 shadow-[0_-10px_35px_rgba(0,0,0,0.9)] select-none" style="padding-top: 6px; padding-bottom: max(12px, env(safe-area-inset-bottom));">
  <div class="relative flex items-center px-1">
    <!-- Botón Flecha Izquierda -->
    <button type="button" @click="scrollNavLeft()" class="shrink-0 w-8 h-[64px] flex items-center justify-center text-nico-orange hover:text-white active:scale-90 transition-all z-20 bg-gradient-to-r from-black via-black/95 to-transparent" title="Ver secciones anteriores">
      <i class="fas fa-chevron-left text-sm"></i>
    </button>

    <!-- Contenedor scrollable horizontal fluido -->
    <div id="mobNavScroll" class="nav-scroll no-scrollbar flex-1 flex items-center overflow-x-auto gap-2 px-1 text-[10px] font-bold uppercase tracking-wider relative">
      <template x-for="t in navTabs.filter(t => tabVisible(t))" :key="'mob-'+t.id">
        <button type="button" :data-mobtab="t.id" @click="switchTab(t.id, $event)" class="shrink-0 flex flex-col items-center justify-between py-2 px-3 min-w-[76px] h-[64px] rounded-2xl transition-all border" :class="mainTab === t.id ? 'text-white bg-white/10 border-white/25 shadow-inner' : 'text-gray-400 border-transparent hover:text-white active:scale-95'">
          <i class="fas text-lg leading-none" :class="[t.icon, mainTab === t.id ? t.active : '']"></i>
          <span x-text="t.short || t.label.split(' ')[0]" class="text-[10px] font-extrabold leading-none tracking-normal whitespace-nowrap text-center"></span>
          <span class="h-1 w-6 rounded-full transition-all shrink-0" :class="mainTab === t.id ? 'bg-nico-orange shadow-[0_0_8px_#FF5500]' : 'bg-transparent'"></span>
        </button>
      </template>

      <!-- Botón Portal Login si no ha iniciado sesión -->
      <button x-show="!currentUser" @click="loginModal = true" class="shrink-0 flex flex-col items-center justify-between py-2 px-3 min-w-[76px] h-[64px] rounded-2xl text-gray-400 border border-transparent hover:text-white active:scale-95">
        <i class="fas fa-user-shield text-lg leading-none text-nico-orange"></i>
        <span class="text-[10px] font-extrabold leading-none tracking-normal whitespace-nowrap text-center">Portal</span>
        <span class="h-1 w-6 rounded-full bg-transparent shrink-0"></span>
      </button>
    </div>

    <!-- Botón Flecha Derecha -->
    <button type="button" @click="scrollNavRight()" class="shrink-0 w-8 h-[64px] flex items-center justify-center text-nico-orange hover:text-white active:scale-90 transition-all z-20 bg-gradient-to-l from-black via-black/95 to-transparent" title="Ver más secciones">
      <i class="fas fa-chevron-right text-sm"></i>
    </button>
  </div>
</nav>
