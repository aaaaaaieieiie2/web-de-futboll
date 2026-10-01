<!-- ==================== MODAL LOGIN ==================== -->
<div x-show="loginModal" x-transition class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4" style="display: none;">
<div class="glass-box p-6 sm:p-8 rounded-3xl max-w-sm w-full border border-nico-orange/50 relative shadow-2xl space-y-6" @click.outside="loginModal = false">
<button @click="loginModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg">✕</button>
<div class="text-center space-y-2">
<div class="w-12 h-12 mx-auto rounded-2xl bg-black border border-nico-orange flex items-center justify-center text-nico-orange text-xl shadow-lg"><i class="fas fa-user-shield"></i></div>
<h3 class="text-xl font-black text-white uppercase">Portal Privado</h3>
<p class="text-xs text-gray-400">Acceso a módulo de administración, entrenadores y familias.</p>
</div>
<div class="space-y-3">
<input type="text" x-model="loginUsuario" placeholder="Usuario" class="w-full bg-black/80 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-nico-orange"></form>
<form onsubmit="return false" style="margin:0;padding:0"><input type="password" x-model="loginPassword" autocomplete="current-password" @keydown.enter="intentarLogin()" placeholder="Contraseña" class="w-full bg-black/80 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-nico-orange"></form>
<p x-show="loginError" x-text="loginError" class="text-[10px] font-bold text-nico-red bg-nico-red/10 border border-nico-red/30 rounded-lg px-3 py-2"></p>
</div>
<button @click="intentarLogin()" class="w-full bg-nico-orange hover:bg-nico-orangeDark text-white font-black py-3 rounded-xl text-xs uppercase">Iniciar Sesión</button>
<p class="text-[9px] text-gray-500 text-center">Cuentas de prueba: admin / nico2026 · dt.sub6 / dt2026 · familia.aguirre / 1234</p>
</div>
</div>