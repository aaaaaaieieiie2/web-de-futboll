<!-- ==================== MODAL LOGIN ==================== -->
<div x-show="loginModal" x-transition class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4" style="display: none;">
<div class="glass-box p-6 sm:p-8 rounded-3xl max-w-sm w-full border border-nico-orange/50 relative shadow-2xl space-y-6" @click.outside="loginModal = false">
<button @click="loginModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg">✕</button>
<div class="text-center space-y-2">
<div class="w-12 h-12 mx-auto rounded-2xl bg-black border border-nico-orange flex items-center justify-center text-nico-orange text-xl shadow-lg"><i class="fas fa-user-shield"></i></div>
<h3 class="text-xl font-black text-white uppercase">Inicio de sesión</h3>
</div>
<div class="space-y-3">
<form @submit.prevent style="margin:0;padding:0;display:contents;">
<input type="text" x-model="loginUsuario" @input="loginError = ''; esBloqueado = false;" autocomplete="username" placeholder="Usuario" class="w-full bg-black/80 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-nico-orange">
<input type="password" x-model="loginPassword" @input="loginError = ''; esBloqueado = false;" autocomplete="current-password" @keydown.enter="intentarLogin()" placeholder="Contraseña" class="w-full bg-black/80 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-nico-orange">
</form>
<p x-show="loginError" x-text="loginError" class="text-[10px] font-bold rounded-lg px-3 py-2 border transition-all" :class="loginError.includes('Conectando') ? 'text-nico-green bg-nico-green/10 border-nico-green/30 animate-pulse' : 'text-nico-red bg-nico-red/10 border-nico-red/30'"></p>
</div>
<button @click="intentarLogin()" class="w-full bg-nico-orange hover:bg-nico-orangeDark text-white font-black py-3 rounded-xl text-xs uppercase">Iniciar Sesión</button>
</div>
</div>