<!-- ==================== SECCIÓN 10: GESTIÓN DE ENTRENADORES ==================== -->
<div x-show="mainTab === 'entrenadores' && esAdmin" class="space-y-6 anim-entry">
<div class="text-center space-y-2">
<span class="text-xs font-black text-white border border-white/20 bg-black px-4 py-1 rounded-full uppercase tracking-widest shadow-lg">Control de Acceso</span>
<h3 class="text-2xl font-black text-white uppercase">Gestión de Entrenadores</h3>
<p class="text-xs text-gray-400">Crea credenciales para DT con acceso restringido a su categoría asignada</p>
</div>
<div class="glass-box p-5 rounded-3xl border border-nico-blue/40 space-y-4">
<h4 class="text-xs font-black text-nico-blue uppercase flex items-center gap-2"><i class="fas fa-user-plus"></i> Registrar Nuevo Entrenador</h4>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<div>
<label class="text-[11px] font-bold text-gray-300 block mb-1">Nombre completo del Entrenador *</label>
<input type="text" class="admin-input" placeholder="Ej: Carlos Pérez" x-model="nuevoEntrenador.nombre" @input="sincronizarUsuarioEntrenador()">
<p class="text-[10px] text-gray-400 mt-1 italic">
<i class="fas fa-user-check text-nico-blue mr-1"></i> El <strong>Nombre</strong> se utiliza automáticamente como su <strong>Usuario</strong> de inicio de sesión.
</p>
</div>
<div>
<label class="text-[11px] font-bold text-gray-300 block mb-1">Categorías / Divisiones Asignadas (Multi-Sub) *</label>
<div class="grid grid-cols-2 gap-1.5 p-2 bg-black/60 rounded-xl border border-white/10 max-h-32 overflow-y-auto">
  <template x-for="cat in categoriasTorneo" :key="'nuevo-dt-'+cat.key">
    <label class="flex items-center gap-1.5 text-[11px] text-gray-200 cursor-pointer p-1 rounded hover:bg-white/5">
      <input type="checkbox" :value="cat.key" x-model="nuevoEntrenador.categorias" class="accent-nico-blue rounded">
      <span x-text="cat.label"></span>
    </label>
  </template>
</div>
<p class="text-[10px] text-gray-400 mt-1 italic">
<i class="fas fa-shield-halved text-nico-orange mr-1"></i> Puedes marcar múltiples categorías (ej: Sub-6 y Sub-12).
</p>
</div>
<div class="sm:col-span-2">
<label class="text-[11px] font-bold text-gray-300 block mb-1">Contraseña Segura (Mín. 12 caracteres, letras y números) *</label>
<form @submit.prevent class="relative" style="margin:0;padding:0;">
<input type="text" name="username" :value="nuevoEntrenador.nombre" autocomplete="username" class="hidden" aria-hidden="true" tabindex="-1">
<input :type="mostrarPassEntrenador ? 'text' : 'password'" autocomplete="new-password" class="admin-input pr-11" placeholder="Mínimo 12 caracteres combinando letras y números" x-model="nuevoEntrenador.password">
<button type="button" @click="mostrarPassEntrenador = !mostrarPassEntrenador" class="absolute inset-y-0 right-0 px-3.5 text-gray-400 hover:text-white flex items-center justify-center cursor-pointer" title="Mostrar / Ocultar contraseña">
<i class="fas" :class="mostrarPassEntrenador ? 'fa-eye-slash' : 'fa-eye'"></i>
</button>
</form>
<div class="flex items-center gap-4 mt-2 text-[10px] font-bold">
<span :class="nuevoEntrenador.password && nuevoEntrenador.password.length >= 12 ? 'text-nico-green' : 'text-gray-500'">
<i class="fas fa-circle-check mr-1"></i> Mínimo 12 caracteres
</span>
<span :class="nuevoEntrenador.password && /[a-zA-Z]/.test(nuevoEntrenador.password) && /[0-9]/.test(nuevoEntrenador.password) ? 'text-nico-green' : 'text-gray-500'">
<i class="fas fa-circle-check mr-1"></i> Combinar letras y números
</span>
</div>
</div>
</div>
<div class="pt-2 flex items-center gap-3 flex-wrap">
<button @click="registrarEntrenador()" class="admin-btn" style="background: linear-gradient(135deg, #0B4F9C, #052a55); box-shadow: 0 0 15px rgba(11,79,156,0.4);"><i class="fas fa-user-tie"></i> Crear Entrenador</button>
<span class="text-xs font-bold" :class="entrenadorMsg.includes('éxito') || entrenadorMsg.includes('Exito') || entrenadorMsg.includes('✓') ? 'text-nico-green' : 'text-nico-red'" x-text="entrenadorMsg" x-show="entrenadorMsg"></span>
</div>
</div>
<div class="glass-box p-5 rounded-3xl border border-white/10 space-y-4">
<h4 class="text-xs font-black text-white uppercase flex items-center gap-2"><i class="fas fa-users"></i> Entrenadores Registrados (<span x-text="listaEntrenadores.length"></span>)</h4>
<template x-if="listaEntrenadores.length === 0">
<p class="text-xs text-gray-500 text-center py-6">No hay entrenadores registrados</p>
</template>
<div class="scroll-container-sm rounded-2xl">
<template x-for="dt in listaEntrenadores" :key="dt.id">
<div class="bg-black/60 p-4 rounded-2xl border border-white/10 mb-2 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
<div class="flex items-center gap-3">
<div class="w-12 h-12 rounded-xl bg-nico-blue/20 border border-nico-blue/40 flex items-center justify-center">
<i class="fas fa-user-tie text-nico-blue text-xl"></i>
</div>
<div>
<p class="text-sm font-black text-white" x-text="dt.nombre"></p>
<p class="text-[10px] text-gray-400">Usuario: <span class="text-nico-blue font-mono" x-text="dt.usuario"></span></p>
<p class="text-[10px] text-gray-400 flex items-center gap-1">
  <i class="fas fa-phone text-emerald-400 text-[9px]"></i> <span x-text="dt.telefono || 'Sin teléfono'"></span>
</p>
<div class="flex items-center gap-1 flex-wrap mt-1">
  <span class="text-[9px] text-gray-400 font-bold">Divisiones:</span>
  <template x-for="c in (Array.isArray(dt.categorias) && dt.categorias.length ? dt.categorias : (dt.catKey ? [dt.catKey] : []))" :key="c">
    <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-nico-blue/20 text-nico-blue border border-nico-blue/40 uppercase" x-text="c"></span>
  </template>
</div>
</div>
</div>
<div class="flex items-center gap-1.5 flex-wrap shrink-0">
  <button type="button" @click="abrirEditarNombreDT(dt)" class="text-blue-400 hover:text-black hover:bg-blue-400 text-[10px] font-black px-2 py-1 rounded-lg bg-blue-400/10 border border-blue-400/30 transition-all flex items-center gap-1 shadow-sm cursor-pointer" title="Modificar nombre del DT">
    <i class="fas fa-user-pen"></i> <span class="hidden sm:inline">Nombre</span>
  </button>
  <button type="button" @click="abrirEditarTelefonoDT(dt)" class="text-emerald-400 hover:text-black hover:bg-emerald-400 text-[10px] font-black px-2 py-1 rounded-lg bg-emerald-400/10 border border-emerald-400/30 transition-all flex items-center gap-1 shadow-sm cursor-pointer" title="Modificar teléfono / WhatsApp">
    <i class="fas fa-phone"></i> <span class="hidden sm:inline">Teléfono</span>
  </button>
  <button type="button" @click="abrirEditarCategoriasDT(dt)" class="text-nico-orange hover:text-black hover:bg-nico-orange text-[10px] font-black px-2 py-1 rounded-lg bg-nico-orange/10 border border-nico-orange/30 transition-all flex items-center gap-1 shadow-sm cursor-pointer" title="Modificar categorías (Multi-Sub)">
    <i class="fas fa-layer-group"></i> <span class="hidden sm:inline">Subs</span>
  </button>
  <button type="button" @click="abrirEditarPasswordDT(dt)" class="text-amber-400 hover:text-black hover:bg-amber-400 text-[10px] font-black px-2 py-1 rounded-lg bg-amber-400/10 border border-amber-400/30 transition-all flex items-center gap-1 shadow-sm cursor-pointer" title="Restablecer contraseña">
    <i class="fas fa-key"></i> <span class="hidden sm:inline">Clave</span>
  </button>
  <button type="button" @click="eliminarEntrenador(dt.id)" class="text-red-400 hover:text-white hover:bg-red-500 text-[10px] font-black p-1.5 rounded-lg bg-red-500/10 border border-red-500/30 transition-all flex items-center justify-center shadow-sm cursor-pointer" title="Eliminar DT">
    <i class="fas fa-trash"></i>
  </button>
</div>
</div>
</template>
</div>
</div>
<div class="glass-box p-5 rounded-3xl border border-amber-400/30 space-y-3">
<h4 class="text-xs font-black text-amber-400 uppercase flex items-center gap-2"><i class="fas fa-shield-halved"></i> Permisos del Rol Entrenador</h4>
<div class="grid sm:grid-cols-2 gap-3 text-[11px]">
<div class="bg-nico-green/10 border border-nico-green/30 p-3 rounded-xl">
<p class="text-nico-green font-black uppercase mb-2"><i class="fas fa-check"></i> Acceso Permitido</p>
<ul class="text-gray-300 space-y-1">
<li>? Vitrina de Campeonatos</li>
<li>? Torneo & Tabla (solo su categoría)</li>
<li>? Cuerpo Técnico</li>
<li>? Categorías (solo la asignada)</li>
<li>? Escudo e Historia</li>
<li>? Asistencia (solo su categoría)</li>
<li>? Evaluación de jugadores (solo su categoría)</li>
</ul>
</div>
<div class="bg-nico-red/10 border border-nico-red/30 p-3 rounded-xl">
<p class="text-nico-red font-black uppercase mb-2"><i class="fas fa-ban"></i> Bloqueado</p>
<ul class="text-gray-300 space-y-1">
<li>? Panel Administrativo general</li>
<li>? Evaluación de Padres</li>
<li>? Mensajes de padres</li>
<li>? Asistencia Global</li>
<li>? Pagos del Club</li>
<li>? Pago de Plataforma</li>
<li>? Gestión de Entrenadores</li>
</ul>
</div>
</div>
</div>
</div>

<!-- ==================== MODAL: EDITAR ENTRENADOR (MULTI-SUB) ==================== -->
<div x-show="modalEditarEntrenador" x-transition class="fixed inset-0 z-[80] bg-black/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto" style="display: none;">
  <div class="glass-box p-5 sm:p-6 rounded-3xl max-w-lg w-full border border-nico-blue/50 relative shadow-2xl space-y-4 my-8" @click.outside="modalEditarEntrenador = false">
    <button @click="modalEditarEntrenador = false" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg cursor-pointer">✕</button>
    <div class="text-center space-y-1">
      <span class="text-[10px] font-black text-nico-blue uppercase tracking-widest"><i class="fas fa-user-tie"></i> Edición de Entrenador</span>
      <h3 class="text-lg font-black text-white uppercase" x-text="entrenadorEditando.nombre"></h3>
      <p class="text-[11px] text-gray-400 font-mono">Usuario: <span class="text-nico-blue font-bold" x-text="entrenadorEditando.usuario"></span></p>
    </div>

    <form @submit.prevent="guardarEdicionEntrenador()" autocomplete="off" class="space-y-3">
      <div>
        <label class="text-xs font-bold text-gray-300 block mb-1">Nombre Completo del DT *</label>
        <input type="text" class="admin-input !text-white !font-bold !py-2" placeholder="Nombre completo" x-model="entrenadorEditando.nombre" required>
      </div>

      <div>
        <label class="text-xs font-bold text-gray-300 block mb-1">Teléfono / WhatsApp de Contacto</label>
        <input type="tel" class="admin-input !py-2" placeholder="Ej: 6332-1779" x-model="entrenadorEditando.telefono">
      </div>

      <div>
        <label class="text-xs font-bold text-gray-300 block mb-1">Categorías / Divisiones Asignadas (Multi-Sub) *</label>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 p-3 bg-black/60 rounded-xl border border-white/10 max-h-40 overflow-y-auto">
          <template x-for="cat in categoriasTorneo" :key="'edit-cat-'+cat.key">
            <label class="flex items-center gap-1.5 text-xs text-gray-200 cursor-pointer p-1 rounded hover:bg-white/5">
              <input type="checkbox" :value="cat.key" x-model="entrenadorEditando.categorias" class="accent-nico-blue rounded">
              <span x-text="cat.label"></span>
            </label>
          </template>
        </div>
        <p class="text-[10px] text-gray-500 italic mt-1">Marca todas las categorías que este entrenador puede dirigir y evaluar.</p>
      </div>

      <div>
        <label class="text-xs font-bold text-gray-300 block mb-1">Restablecer Contraseña (Opcional)</label>
        <div class="relative">
          <input :type="entrenadorEditando.mostrarPass ? 'text' : 'password'" autocomplete="new-password" class="admin-input pr-10 font-mono !py-2" placeholder="Dejar en blanco para conservar la actual" x-model="entrenadorEditando.nuevaPassword">
          <button type="button" @click="entrenadorEditando.mostrarPass = !entrenadorEditando.mostrarPass" class="absolute inset-y-0 right-0 px-3 text-gray-400 hover:text-white text-xs cursor-pointer">
            <i class="fas" :class="entrenadorEditando.mostrarPass ? 'fa-eye-slash' : 'fa-eye'"></i>
          </button>
        </div>
        <p class="text-[10px] text-gray-500 italic mt-1">Si la cambias, debe tener mínimo 12 caracteres combinando letras y números.</p>
      </div>

      <div class="pt-2 flex items-center gap-2">
        <button type="submit" class="admin-btn flex-1 bg-nico-blue hover:bg-blue-600 text-white font-black py-2.5 rounded-xl text-xs uppercase cursor-pointer shadow-lg flex items-center justify-center gap-1.5">
          <i class="fas fa-save"></i> Guardar Cambios
        </button>
        <button type="button" @click="modalEditarEntrenador = false" class="bg-white/10 hover:bg-white/20 text-gray-300 font-black py-2.5 px-4 rounded-xl text-xs uppercase cursor-pointer">
          Cancelar
        </button>
      </div>
    </form>

    <div x-show="msgEditarEntrenador" class="text-center">
      <span class="text-xs font-bold" :class="msgEditarEntrenador.includes('✓') ? 'text-nico-green' : 'text-nico-red'" x-text="msgEditarEntrenador"></span>
    </div>
  </div>
</div>

<!-- ==================== MODAL 1: EDITAR NOMBRE DT ==================== -->
<div x-show="modalEditarNombreDT" x-transition class="fixed inset-0 z-[85] bg-black/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto" style="display: none;">
  <div class="glass-box p-5 sm:p-6 rounded-3xl max-w-md w-full border border-blue-500/50 relative shadow-2xl space-y-4 my-8" @click.outside="modalEditarNombreDT = false">
    <button @click="modalEditarNombreDT = false" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg cursor-pointer">✕</button>
    <div class="text-center space-y-1">
      <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest"><i class="fas fa-user-pen"></i> Nombre del Entrenador</span>
      <h3 class="text-lg font-black text-white uppercase" x-text="dtNombreForm.nombreActual"></h3>
      <p class="text-[11px] text-gray-400">Modifica el nombre completo del entrenador. Su usuario de acceso se sincronizará automáticamente.</p>
    </div>
    <form @submit.prevent="guardarNombreDT()" class="space-y-3">
      <div>
        <label class="text-xs font-bold text-gray-300 block mb-1">Nombre Completo *</label>
        <input type="text" class="admin-input !text-white !font-bold !py-2.5" placeholder="Ej: Carlos Pérez" x-model="dtNombreForm.nombre" required>
      </div>
      <div class="pt-2 flex items-center gap-2">
        <button type="submit" class="admin-btn flex-1 bg-blue-600 hover:bg-blue-700 text-white font-black py-2.5 rounded-xl text-xs uppercase cursor-pointer shadow-lg flex items-center justify-center gap-1.5">
          <i class="fas fa-save"></i> Guardar Nombre
        </button>
        <button type="button" @click="modalEditarNombreDT = false" class="bg-white/10 hover:bg-white/20 text-gray-300 font-black py-2.5 px-4 rounded-xl text-xs uppercase cursor-pointer">
          Cancelar
        </button>
      </div>
    </form>
    <div x-show="msgNombreDT" class="text-center">
      <span class="text-xs font-bold" :class="msgNombreDT.includes('✓') ? 'text-nico-green' : 'text-nico-red'" x-text="msgNombreDT"></span>
    </div>
  </div>
</div>

<!-- ==================== MODAL 2: EDITAR TELÉFONO DT ==================== -->
<div x-show="modalEditarTelefonoDT" x-transition class="fixed inset-0 z-[85] bg-black/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto" style="display: none;">
  <div class="glass-box p-5 sm:p-6 rounded-3xl max-w-md w-full border border-emerald-500/50 relative shadow-2xl space-y-4 my-8" @click.outside="modalEditarTelefonoDT = false">
    <button @click="modalEditarTelefonoDT = false" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg cursor-pointer">✕</button>
    <div class="text-center space-y-1">
      <span class="text-[10px] font-black text-emerald-400 uppercase tracking-widest"><i class="fas fa-phone"></i> Teléfono del Entrenador</span>
      <h3 class="text-lg font-black text-white uppercase" x-text="dtTelefonoForm.nombre"></h3>
      <p class="text-[11px] text-gray-400">Número de WhatsApp / Contacto directo con el cuerpo técnico.</p>
    </div>
    <form @submit.prevent="guardarTelefonoDT()" class="space-y-3">
      <div>
        <label class="text-xs font-bold text-gray-300 block mb-1">Número de Teléfono (WhatsApp) *</label>
        <input type="tel" class="admin-input !text-white !font-bold !py-2.5" placeholder="Ej: 6332-1779" x-model="dtTelefonoForm.telefono" required>
      </div>
      <div class="pt-2 flex items-center gap-2">
        <button type="submit" class="admin-btn flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-2.5 rounded-xl text-xs uppercase cursor-pointer shadow-lg flex items-center justify-center gap-1.5">
          <i class="fas fa-save"></i> Guardar Teléfono
        </button>
        <button type="button" @click="modalEditarTelefonoDT = false" class="bg-white/10 hover:bg-white/20 text-gray-300 font-black py-2.5 px-4 rounded-xl text-xs uppercase cursor-pointer">
          Cancelar
        </button>
      </div>
    </form>
    <div x-show="msgTelefonoDT" class="text-center">
      <span class="text-xs font-bold" :class="msgTelefonoDT.includes('✓') ? 'text-nico-green' : 'text-nico-red'" x-text="msgTelefonoDT"></span>
    </div>
  </div>
</div>

<!-- ==================== MODAL 3: EDITAR CATEGORÍAS (MULTI-SUB) DT ==================== -->
<div x-show="modalEditarCategoriasDT" x-transition class="fixed inset-0 z-[85] bg-black/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto" style="display: none;">
  <div class="glass-box p-5 sm:p-6 rounded-3xl max-w-md w-full border border-nico-orange/50 relative shadow-2xl space-y-4 my-8" @click.outside="modalEditarCategoriasDT = false">
    <button @click="modalEditarCategoriasDT = false" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg cursor-pointer">✕</button>
    <div class="text-center space-y-1">
      <span class="text-[10px] font-black text-nico-orange uppercase tracking-widest"><i class="fas fa-layer-group"></i> Categorías Asignadas</span>
      <h3 class="text-lg font-black text-white uppercase" x-text="dtCategoriasForm.nombre"></h3>
      <p class="text-[11px] text-gray-400">Selecciona las categorías que este entrenador puede dirigir, pasar asistencia y evaluar.</p>
    </div>
    <form @submit.prevent="guardarCategoriasDT()" class="space-y-3">
      <div class="grid grid-cols-2 gap-2 p-3 bg-black/60 rounded-xl border border-white/10 max-h-48 overflow-y-auto">
        <template x-for="cat in categoriasTorneo" :key="'dt-cat-'+cat.key">
          <label class="flex items-center gap-2 text-xs text-gray-200 cursor-pointer p-1.5 rounded hover:bg-white/5">
            <input type="checkbox" :value="cat.key" x-model="dtCategoriasForm.categorias" class="accent-nico-orange rounded">
            <span x-text="cat.label"></span>
          </label>
        </template>
      </div>
      <div class="pt-2 flex items-center gap-2">
        <button type="submit" class="admin-btn flex-1 bg-nico-orange hover:bg-nico-orangeDark text-white font-black py-2.5 rounded-xl text-xs uppercase cursor-pointer shadow-lg flex items-center justify-center gap-1.5">
          <i class="fas fa-save"></i> Guardar Categorías
        </button>
        <button type="button" @click="modalEditarCategoriasDT = false" class="bg-white/10 hover:bg-white/20 text-gray-300 font-black py-2.5 px-4 rounded-xl text-xs uppercase cursor-pointer">
          Cancelar
        </button>
      </div>
    </form>
    <div x-show="msgCategoriasDT" class="text-center">
      <span class="text-xs font-bold" :class="msgCategoriasDT.includes('✓') ? 'text-nico-green' : 'text-nico-red'" x-text="msgCategoriasDT"></span>
    </div>
  </div>
</div>

<!-- ==================== MODAL 4: EDITAR PASSWORD DT ==================== -->
<div x-show="modalEditarPasswordDT" x-transition class="fixed inset-0 z-[85] bg-black/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto" style="display: none;">
  <div class="glass-box p-5 sm:p-6 rounded-3xl max-w-md w-full border border-amber-500/50 relative shadow-2xl space-y-4 my-8" @click.outside="modalEditarPasswordDT = false">
    <button @click="modalEditarPasswordDT = false" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg cursor-pointer">✕</button>
    <div class="text-center space-y-1">
      <span class="text-[10px] font-black text-amber-400 uppercase tracking-widest"><i class="fas fa-key"></i> Restablecer Contraseña</span>
      <h3 class="text-lg font-black text-white uppercase" x-text="dtPasswordForm.nombre"></h3>
      <p class="text-[11px] text-gray-400">Ingresa la nueva contraseña para el DT (Mín. 12 caracteres combinando letras y números).</p>
    </div>
    <form @submit.prevent="guardarPasswordDT()" class="space-y-3">
      <div>
        <label class="text-xs font-bold text-gray-300 block mb-1">Nueva Contraseña Segura *</label>
        <div class="relative">
          <input :type="dtPasswordForm.mostrar ? 'text' : 'password'" autocomplete="new-password" class="admin-input pr-10 font-mono !py-2.5" placeholder="Mínimo 12 alfanumérico" x-model="dtPasswordForm.password" required>
          <button type="button" @click="dtPasswordForm.mostrar = !dtPasswordForm.mostrar" class="absolute inset-y-0 right-0 px-3 text-gray-400 hover:text-white text-xs cursor-pointer">
            <i class="fas" :class="dtPasswordForm.mostrar ? 'fa-eye-slash' : 'fa-eye'"></i>
          </button>
        </div>
      </div>
      <div class="pt-2 flex items-center gap-2">
        <button type="submit" class="admin-btn flex-1 bg-amber-500 hover:bg-amber-600 text-black font-black py-2.5 rounded-xl text-xs uppercase cursor-pointer shadow-lg flex items-center justify-center gap-1.5">
          <i class="fas fa-save"></i> Actualizar Contraseña
        </button>
        <button type="button" @click="modalEditarPasswordDT = false" class="bg-white/10 hover:bg-white/20 text-gray-300 font-black py-2.5 px-4 rounded-xl text-xs uppercase cursor-pointer">
          Cancelar
        </button>
      </div>
    </form>
    <div x-show="msgPasswordDT" class="text-center">
      <span class="text-xs font-bold" :class="msgPasswordDT.includes('✓') ? 'text-nico-green' : 'text-nico-red'" x-text="msgPasswordDT"></span>
    </div>
  </div>
</div>
</div>