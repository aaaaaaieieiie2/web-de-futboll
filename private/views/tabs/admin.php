<div x-show="mainTab === 'admin' && esAdmin" class="space-y-6">
<!-- ==================== SECCIÓN 12: PANEL ADMIN (UNIFICADO) ==================== -->
<div class="glass-box p-6 rounded-3xl border border-nico-orange/40 text-center space-y-2">
<span class="text-xs font-black text-white border border-white/20 bg-black px-4 py-1 rounded-full uppercase tracking-widest shadow-lg">Panel de Administración</span>
<h3 class="text-2xl font-black text-white uppercase">Gestión del Club</h3>
<p class="text-xs text-gray-400">Registro unificado 2 en 1 · edita estadísticas y dorsales en plantillas</p>
</div>

<!-- BLOQUE: ENLACE PERMANENTE DE INSCRIPCIÓN + CÓDIGO DE VERIFICACIÓN (CICLO DE 7 DÍAS) -->
<div class="admin-section relative overflow-hidden bg-gradient-to-br from-[#0b1528] to-black border border-nico-green/40">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
    <div>
      <h3 class="flex items-center gap-2 text-white"><i class="fas fa-link text-nico-green"></i> Enlace Permanente de Inscripción & Código de Verificación (7 Días)</h3>
      <p class="text-[11px] text-gray-400">El enlace a la página de registro es indefinido. El código de verificación oficial se actualiza automáticamente cada 7 días para validar que el padre pertenece al club.</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
      <button type="button"
              @click="generarLinkRegistroPadres(168)"
              class="admin-btn bg-nico-green hover:bg-nico-greenDark !text-black shadow-[0_0_15px_rgba(0,230,118,0.3)] font-black px-4 py-2 rounded-xl text-xs transition-all">
        <i class="fas fa-rotate mr-1"></i>
        <span>Rotar Código Ahora</span>
      </button>
    </div>
  </div>

  <div class="bg-black/70 border border-nico-green/30 rounded-2xl p-4 space-y-3">
    <div class="flex items-center justify-between flex-wrap gap-2 text-[11px]">
      <span class="text-nico-green font-black uppercase flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-nico-green animate-ping"></span> Enlace Permanente Activo
      </span>
      <span class="text-amber-400 font-bold">
        <i class="far fa-clock mr-1"></i> Próxima renovación automática: <span x-text="linkRegistroExpiraStr || 'Cada 7 días'"></span>
      </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-stretch">
      <!-- URL Permanente del Registro -->
      <div class="md:col-span-2 bg-black/80 border border-white/10 rounded-xl p-2.5 flex flex-col justify-between gap-2">
        <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider"><i class="fas fa-globe text-nico-orange mr-1"></i> 1. Enlace Oficial Indefinido</span>
        <div class="flex items-center gap-2">
          <input type="text" readonly :value="urlLinkRegistroPadres" class="admin-input !mb-0 !bg-black font-mono text-xs text-nico-orange select-all flex-1" />
          <button type="button" @click="copiarLinkRegistroPadres()" class="admin-btn bg-nico-orange hover:bg-nico-orangeDark px-3.5 py-2 rounded-xl text-xs font-black shrink-0">
            <i class="fas fa-copy mr-1"></i> <span x-text="copiadoLinkMsg || 'Copiar Enlace'"></span>
          </button>
        </div>
      </div>

      <!-- Token / Código de Verificación de 7 Días -->
      <div class="bg-amber-400/10 border border-amber-400/40 rounded-xl p-2.5 flex flex-col justify-between gap-2 text-center">
        <span class="text-[10px] font-black text-amber-400 uppercase tracking-wider"><i class="fas fa-key mr-1"></i> 2. Código Semanal (7 Días)</span>
        <div class="flex items-center justify-center gap-2">
          <span class="text-lg font-black font-mono text-white tracking-widest bg-black/80 px-3 py-1 rounded-lg border border-amber-400/30 select-all" x-text="linkRegistroCodigoAcademia || 'Cargando...'"></span>
          <button type="button" @click="copiarCodigoAcademia()" class="admin-btn bg-amber-400 hover:bg-amber-500 !text-black px-3 py-2 rounded-xl text-xs font-black shrink-0">
            <i class="fas fa-copy"></i> <span x-text="copiadoTokenMsg || 'Copiar'"></span>
          </button>
        </div>
      </div>
    </div>

    <div class="flex items-center justify-between flex-wrap gap-2 pt-1">
      <p class="text-[10px] text-gray-400 italic">
        <i class="fas fa-shield-check text-nico-green mr-1"></i> El padre solo necesita ingresar este <strong>Código Semanal</strong> al completar su registro en el enlace permanente.
      </p>
      <a :href="'https://wa.me/?text=' + encodeURIComponent('⚽ *ACADEMIA NICO SPORT — Inscripción Oficial*\n\n1️⃣ Entra a nuestro enlace de registro:\n' + urlLinkRegistroPadres + '\n\n2️⃣ Al enviar tu inscripción, ingresa el *Código de Verificación del Club (Válido esta semana)*:\n🔑 *' + linkRegistroCodigoAcademia + '*\n\n📌 _Este código confirma tu pertenencia al club._')" target="_blank" class="admin-btn bg-[#25D366] hover:bg-[#1ebe57] !text-black font-black px-4 py-2 rounded-xl text-xs flex items-center gap-1.5">
        <i class="fab fa-whatsapp text-sm"></i> Compartir Enlace + Código por WhatsApp
      </a>
    </div>
  </div>
</div>

  <!-- FORMULARIO MANUAL DE ADMINISTRADOR (ACTUALIZADO COMO EL AUTOMÁTICO) -->
<div class="admin-section relative overflow-hidden bg-gradient-to-br from-black to-[#0a0a0a]">
<div class="absolute top-0 right-0 w-40 h-40 bg-nico-orange/5 blur-[60px] rounded-full pointer-events-none"></div>
<h3 class="flex items-center gap-2"><i class="fas fa-user-plus text-nico-orange"></i> Registro Manual: Jugador y Familia</h3>
<p class="text-[11px] text-gray-400 mb-4">Inscribe al jugador (su categoría se asigna sola al poner la edad) y crea el acceso del padre/madre usando su mismo nombre como usuario.</p>
<div class="flex items-center gap-6 mb-5 bg-black/40 p-3 rounded-xl border border-white/5 inline-flex">
<label class="text-xs text-white font-black uppercase tracking-wider cursor-pointer flex items-center gap-2"><input type="radio" value="con_padre" x-model="adminRegistro.tipo" class="accent-nico-orange"> Menor de Edad (Con Tutor)</label>
<label class="text-xs text-white font-black uppercase tracking-wider cursor-pointer flex items-center gap-2"><input type="radio" value="sin_padre" x-model="adminRegistro.tipo" class="accent-nico-orange"> Adulto (Sin Tutor)</label>
</div>
<div x-show="adminRegistro.tipo === 'con_padre'" class="p-4 rounded-2xl border border-nico-orange/30 bg-nico-orange/5 space-y-4 mb-4 shadow-lg">
  <div class="flex items-center justify-between flex-wrap gap-2">
    <p class="text-[10px] font-black text-nico-orange uppercase tracking-widest flex items-center gap-2">
      <i class="fas fa-children"></i> 1. Hijos a Inscribir (<span x-text="(adminRegistro.hijos || []).length"></span>)
    </p>
    <button type="button" @click="agregarHijoAdmin()" class="bg-nico-orange/20 hover:bg-nico-orange text-nico-orange hover:text-white font-black text-xs px-3 py-1.5 rounded-xl border border-nico-orange/40 transition-all flex items-center gap-1.5">
      <i class="fas fa-plus-circle"></i> Inscribir a otro hijo / hermano
    </button>
  </div>
  <template x-for="(hijo, hIdx) in adminRegistro.hijos" :key="hIdx">
    <div class="p-3 rounded-xl bg-black/60 border border-white/10 space-y-2 relative">
      <div class="flex items-center justify-between text-xs font-bold text-gray-300 pb-1 border-b border-white/5">
        <span class="text-amber-400 flex items-center gap-1">
          <i class="fas fa-child-reaching"></i> Hijo #<span x-text="hIdx + 1"></span>
        </span>
        <button type="button" x-show="adminRegistro.hijos && adminRegistro.hijos.length > 1" @click="quitarHijoAdmin(hIdx)" class="text-red-400 hover:text-red-300 text-xs flex items-center gap-1 font-normal">
          <i class="fas fa-trash"></i> Quitar
        </button>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <input type="text" class="admin-input !bg-black/80 focus:!border-nico-orange !mb-0" placeholder="Nombre y apellido del hijo *" x-model="hijo.nombre">
        <input type="number" min="3" max="50" class="admin-input !bg-black/80 focus:!border-nico-orange !mb-0" placeholder="Edad en años (ej: 10) *" x-model="hijo.edad" @input="autoAsignarSubHijoAdmin(hijo)">
        <div class="admin-input !bg-black/90 flex items-center justify-between select-none !mb-0" :class="hijo.catKey ? '!border-nico-orange text-nico-orange font-black' : 'text-gray-500'">
          <span x-text="hijo.catKey ? ('✓ ' + ((categoriasTorneo.find(c => c.key === hijo.catKey) || {}).label || hijo.catKey.toUpperCase())) : 'Categoría (por edad)'"></span>
          <i class="fas fa-wand-magic-sparkles text-nico-orange text-xs"></i>
        </div>
        <select class="admin-input !bg-black/80 focus:!border-nico-orange !mb-0" x-model="hijo.pos">
          <option value="POR">Portero (POR)</option><option value="DEF">Defensa (DEF)</option><option value="MED">Mediocampo (MED)</option><option value="DEL">Delantero (DEL)</option>
        </select>
      </div>
    </div>
  </template>
  <p class="text-[10px] text-amber-300/90 italic"><i class="fas fa-shirt mr-1"></i> El dorsal (#) queda libre (#S/N) para que el Profesor o tú lo asignen en la pestaña Categorías.</p>
</div>

<div x-show="adminRegistro.tipo === 'sin_padre'" class="p-4 rounded-2xl border border-nico-orange/30 bg-nico-orange/5 space-y-3 mb-4 shadow-lg">
  <p class="text-[10px] font-black text-nico-orange uppercase tracking-widest flex items-center gap-2"><i class="fas fa-running"></i> 1. Datos del Jugador Adulto a Inscribir</p>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
    <input type="text" class="admin-input !bg-black/60 focus:!border-nico-orange" placeholder="Nombre y apellido del jugador *" x-model="adminRegistro.jugadorNombre">
    <input type="number" min="3" max="50" class="admin-input !bg-black/60 focus:!border-nico-orange" placeholder="Edad en años (ej: 18) *" x-model="adminRegistro.jugadorEdad" @input="autoAsignarSubAdmin()">
    <div class="admin-input !bg-black/80 flex items-center justify-between select-none" :class="adminRegistro.catKey ? '!border-nico-orange text-nico-orange font-black' : 'text-gray-500'">
      <span x-text="adminRegistro.catKey ? ('✓ ' + ((categoriasTorneo.find(c => c.key === adminRegistro.catKey) || {}).label || adminRegistro.catKey.toUpperCase())) : 'Categoría (se asigna por edad)'"></span>
      <i class="fas fa-wand-magic-sparkles text-nico-orange text-xs"></i>
    </div>
    <select class="admin-input !bg-black/60 focus:!border-nico-orange" x-model="adminRegistro.jugadorPos">
      <option value="POR">Portero (POR)</option><option value="DEF">Defensa (DEF)</option><option value="MED" selected>Mediocampo (MED)</option><option value="DEL">Delantero (DEL)</option>
    </select>
  </div>
  <p class="text-[10px] text-amber-300/90 italic"><i class="fas fa-shirt mr-1"></i> El dorsal (#) queda libre (#S/N) para que el Profesor o tú lo asignen en la pestaña Categorías.</p>
</div>
<div x-show="adminRegistro.tipo === 'con_padre'" x-transition class="p-4 rounded-2xl border border-nico-green/30 bg-nico-green/5 space-y-3 mb-4 shadow-lg">
<p class="text-[10px] font-black text-nico-green uppercase tracking-widest flex items-center gap-2"><i class="fas fa-user-shield"></i> 2. Datos y Acceso del Padre/Madre (Nombre = Usuario)</p>
<form @submit.prevent class="grid grid-cols-1 sm:grid-cols-3 gap-3">
<input type="text" name="username" autocomplete="username" class="admin-input !bg-black/60 focus:!border-nico-green" placeholder="Nombre del Padre/Madre (Será su Usuario) *" x-model="adminRegistro.padreNombre">
<input type="text" name="telefono" autocomplete="tel" class="admin-input !bg-black/60 focus:!border-nico-green" placeholder="Número de Teléfono (WhatsApp) *" x-model="adminRegistro.telefonoPadre">
<div class="relative">
  <input :type="verPassManual ? 'text' : 'password'" name="password" autocomplete="new-password" class="admin-input !bg-black/60 focus:!border-nico-green !pr-10 !mb-0" placeholder="Contraseña (Mín. 12 caract. letras+números) *" x-model="adminRegistro.padrePassword">
  <button type="button" @click="verPassManual = !verPassManual" class="absolute inset-y-0 right-0 px-3 text-gray-400 hover:text-white flex items-center justify-center" title="Mostrar / Ocultar contraseña">
    <i class="fas text-sm" :class="verPassManual ? 'fa-eye-slash text-nico-green' : 'fa-eye'"></i>
  </button>
</div>
</form>
<div class="flex items-center justify-between flex-wrap gap-2 text-[10px]">
  <div class="flex items-center gap-4 font-bold">
    <span :class="(adminRegistro.padrePassword || '').length >= 12 ? 'text-nico-green' : 'text-gray-500'">
      <i class="fas mr-1" :class="(adminRegistro.padrePassword || '').length >= 12 ? 'fa-check-circle' : 'fa-circle-dot'"></i>Mínimo 12 caracteres (<span x-text="(adminRegistro.padrePassword || '').length"></span>/12)
    </span>
    <span :class="(/[a-zA-Z]/.test(adminRegistro.padrePassword || '') && /[0-9]/.test(adminRegistro.padrePassword || '')) ? 'text-nico-green' : 'text-gray-500'">
      <i class="fas mr-1" :class="(/[a-zA-Z]/.test(adminRegistro.padrePassword || '') && /[0-9]/.test(adminRegistro.padrePassword || '')) ? 'fa-check-circle' : 'fa-circle-dot'"></i>Letras y números
    </span>
  </div>
  <span class="text-gray-400 italic">El nombre del padre/madre será directamente su usuario para iniciar sesión.</span>
</div>
</div>

<div x-show="adminRegistro.tipo === 'sin_padre'" x-transition class="p-4 rounded-2xl border border-cyan-400/30 bg-cyan-400/5 space-y-3 mb-4 shadow-lg">
<p class="text-[10px] font-black text-cyan-400 uppercase tracking-widest flex items-center gap-2"><i class="fas fa-id-badge"></i> 2. Acceso del Jugador Adulto (Nombre del Jugador = Usuario)</p>
<form @submit.prevent class="grid grid-cols-1 sm:grid-cols-2 gap-3">
<input type="text" name="telefono" autocomplete="tel" class="admin-input !bg-black/60 focus:!border-cyan-400" placeholder="Número de Teléfono / WhatsApp del Jugador *" x-model="adminRegistro.telefonoPadre">
<div class="relative">
  <input :type="verPassManual ? 'text' : 'password'" name="password" autocomplete="new-password" class="admin-input !bg-black/60 focus:!border-cyan-400 !pr-10 !mb-0" placeholder="Contraseña de Acceso (Mín. 12 caract. letras+números) *" x-model="adminRegistro.padrePassword">
  <button type="button" @click="verPassManual = !verPassManual" class="absolute inset-y-0 right-0 px-3 text-gray-400 hover:text-white flex items-center justify-center" title="Mostrar / Ocultar contraseña">
    <i class="fas text-sm" :class="verPassManual ? 'fa-eye-slash text-cyan-400' : 'fa-eye'"></i>
  </button>
</div>
</form>
<div class="flex items-center justify-between flex-wrap gap-2 text-[10px]">
  <div class="flex items-center gap-4 font-bold">
    <span :class="(adminRegistro.padrePassword || '').length >= 12 ? 'text-cyan-400' : 'text-gray-500'">
      <i class="fas mr-1" :class="(adminRegistro.padrePassword || '').length >= 12 ? 'fa-check-circle' : 'fa-circle-dot'"></i>Mínimo 12 caracteres (<span x-text="(adminRegistro.padrePassword || '').length"></span>/12)
    </span>
    <span :class="(/[a-zA-Z]/.test(adminRegistro.padrePassword || '') && /[0-9]/.test(adminRegistro.padrePassword || '')) ? 'text-cyan-400' : 'text-gray-500'">
      <i class="fas mr-1" :class="(adminRegistro.padrePassword || '') && /[0-9]/.test(adminRegistro.padrePassword || '') ? 'fa-check-circle' : 'fa-circle-dot'"></i>Letras y números
    </span>
  </div>
  <span class="text-gray-400 italic">El nombre del jugador será directamente su usuario para ingresar a su portal personal.</span>
</div>
</div>
<div class="flex items-center gap-4 flex-wrap pt-2 border-t border-white/10">
<button class="admin-btn bg-gradient-to-r from-nico-orange to-red-600 px-6 py-2.5 rounded-xl shadow-[0_0_15px_rgba(255,85,0,0.3)] hover:scale-[1.02] transition-transform" @click="registrarUnificado()"><i class="fas fa-check-circle mr-1"></i> Completar Registro</button>
<div class="text-xs font-bold px-3 py-1.5 rounded-lg border" :class="adminRegistroMsg.includes('éxito') || adminRegistroMsg.includes('Exito') || adminRegistroMsg.includes('\u2713') ? 'text-nico-green bg-nico-green/10 border-nico-green/30' : 'text-nico-red bg-nico-red/10 border-nico-red/30'" x-text="adminRegistroMsg" x-show="adminRegistroMsg" x-transition></div>
</div>
</div>
<div class="admin-section">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <h3><i class="fas fa-users"></i> Familias Registradas (<span x-text="listaPadresFiltrados.length"></span>)</h3>
    <select x-model="filtroPadresCat" @change="paginaPadres = 1" class="admin-input !bg-black/60 focus:!border-nico-green w-full sm:w-auto text-xs py-1.5 h-auto">
        <option value="">Todas las Categorías</option>
        <template x-for="cat in categoriasTorneo" :key="cat.key">
            <option :value="cat.key" x-text="cat.label"></option>
        </template>
    </select>
</div>
<template x-if="listaPadresFiltrados.length === 0">
    <p class="text-xs text-gray-500 text-center py-6 italic border border-white/5 rounded-xl bg-black/30">No hay familias registradas aún en esta categoría.</p>
</template>
<div class="scroll-container-sm pr-2 overflow-y-auto" style="max-height: 400px;">
    <div class="grid sm:grid-cols-2 gap-3">
        <template x-for="(padre, idx) in listaPadresFiltrados" :key="padre.id">
            <div class="bg-black/40 border border-white/10 rounded-xl p-3 flex justify-between items-center hover:border-nico-green/30 transition-colors group gap-3">
                <div class="flex-1 min-w-0">
                    <div class="font-black text-white text-sm" x-text="(((paginaPadres - 1) * porPaginaPadres) + idx + 1) + '. ' + padre.nombre"></div>
                    <div class="text-[10px] text-gray-400 font-mono">Usuario: <span class="text-nico-orange" x-text="padre.usuario"></span></div>
                    <div class="text-[10px] text-gray-400 font-mono" x-show="padre.telefono">Tel: <span class="text-nico-green" x-text="padre.telefono"></span></div>
                    <div class="mt-1 space-y-1">
                        <template x-if="padre.hijos && padre.hijos.length > 0">
                            <div class="space-y-0.5">
                                <template x-for="(h, hIdx) in padre.hijos" :key="h.id || hIdx">
                                    <div class="text-[10px] font-bold text-nico-green flex items-center gap-1.5 flex-wrap">
                                        <i class="fas fa-child-reaching text-amber-400 text-[9px]"></i>
                                        <span x-text="(h.nombre || 'Hijo') + (h.edad ? ' (' + h.edad + ' años)' : '') + ' · ' + (h.catLabel || h.sub || (h.catKey ? h.catKey.toUpperCase() : ''))"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="(!padre.hijos || padre.hijos.length === 0) && padre.hijoId">
                            <div class="text-[10px] font-bold text-nico-green flex items-center gap-1.5">
                                <i class="fas fa-child-reaching text-amber-400 text-[9px]"></i>
                                <span x-text="(padre.hijoNombre || ((playersDe(padre.hijoCat).find(p => p.id === padre.hijoId) || {}).name) || 'Inscrito') + (padre.hijoEdad ? ' (' + padre.hijoEdad + ' años)' : '') + ' · ' + (padre.sub || padre.hijoCat)"></span>
                            </div>
                        </template>
                        <template x-if="(!padre.hijos || padre.hijos.length === 0) && !padre.hijoId">
                            <div class="text-[10px] text-gray-500 italic">Sin jugador vinculado</div>
                        </template>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 shrink-0 flex-wrap sm:flex-nowrap">
                    <button type="button" class="text-amber-400 hover:text-black hover:bg-amber-400 text-[10px] font-black px-2 py-1 rounded-lg bg-amber-400/10 border border-amber-400/30 transition-all flex items-center gap-1 shadow-sm" title="Añadir otro hijo a esta familia" @click="abrirAgregarHijoModal(padre)">
                        <i class="fas fa-plus text-[9px]"></i> Hijo
                    </button>
                    <button type="button" class="text-emerald-400 hover:text-black hover:bg-emerald-400 text-[10px] font-black px-2 py-1 rounded-lg bg-emerald-400/10 border border-emerald-400/30 transition-all flex items-center gap-1 shadow-sm cursor-pointer" title="Actualizar número de teléfono / WhatsApp" @click="abrirModalTelefonoPadre(padre)">
                        <i class="fab fa-whatsapp text-[10px]"></i> Teléfono
                    </button>
                    <button type="button" class="text-amber-400 hover:text-black hover:bg-amber-400 text-[10px] font-black px-2 py-1 rounded-lg bg-amber-400/10 border border-amber-400/30 transition-all flex items-center gap-1 shadow-sm cursor-pointer" title="Restablecer contraseña de acceso del papá" @click="abrirModalPasswordPadre(padre)">
                        <i class="fas fa-key text-[9px]"></i> Clave
                    </button>
                    <button x-show="esSuperAdmin" class="text-red-500 hover:text-white text-xs w-8 h-8 rounded-lg bg-red-500/10 hover:bg-red-500 transition-all flex items-center justify-center opacity-50 group-hover:opacity-100 shrink-0 cursor-pointer" title="Eliminar familia (Solo Super Admin)" @click="eliminarPadre(padre.id)"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </template>
    </div>
</div>
<div class="flex items-center justify-between mt-3 px-2 border-t border-white/5 pt-3" x-show="totalPaginasPadres > 1">
    <button @click="paginaPadres = Math.max(1, paginaPadres - 1)" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-nico-orange hover:text-white transition-colors flex items-center justify-center text-gray-400" :disabled="paginaPadres === 1" :class="paginaPadres === 1 ? 'opacity-50 cursor-not-allowed' : ''"><i class="fas fa-chevron-left"></i></button>
    <span class="text-xs font-mono font-bold text-gray-400">Página <span x-text="paginaPadres" class="text-white"></span> de <span x-text="totalPaginasPadres" class="text-white"></span></span>
    <button @click="paginaPadres = Math.min(totalPaginasPadres, paginaPadres + 1)" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-nico-orange hover:text-white transition-colors flex items-center justify-center text-gray-400" :disabled="paginaPadres === totalPaginasPadres" :class="paginaPadres === totalPaginasPadres ? 'opacity-50 cursor-not-allowed' : ''"><i class="fas fa-chevron-right"></i></button>
</div>
</div>
<div class="admin-section">
<h3><i class="fas fa-layer-group"></i> Gestión de Categorías</h3>
<p class="text-[11px] text-gray-400 mb-3">Crea, edita o elimina divisiones del club. (Las oficiales no se pueden borrar).</p>
<template x-for="(cat, i) in categoriasAdmin" :key="cat.key">
<div class="bg-black/40 border border-white/10 rounded-xl p-2.5 mb-2 flex justify-between items-center hover:border-nico-orange/30 transition-colors">
<div class="flex-1 mr-2 min-w-0">
<div class="font-black text-white text-xs truncate uppercase tracking-wide" x-text="cat.label"></div>
<div class="text-[10px] text-gray-500 truncate mt-0.5" x-text="cat.descripcion"></div>
</div>
<div class="flex items-center gap-1.5 shrink-0">
<button @click="subirCategoria(i)" :disabled="i === 0" class="btn-edit-mini disabled:opacity-20" title="Subir orden"><i class="fas fa-arrow-up"></i></button>
<button @click="bajarCategoria(i)" :disabled="i === categoriasAdmin.length - 1" class="btn-edit-mini disabled:opacity-20" title="Bajar orden"><i class="fas fa-arrow-down"></i></button>
<button @click="editarCategoria(cat)" class="btn-edit-mini bg-white/5" title="Editar detalles"><i class="fas fa-pen"></i></button>
<button x-show="!cat.core" @click="eliminarCategoria(cat)" class="btn-del-mini" title="Eliminar categoría"><i class="fas fa-trash"></i></button>
<span x-show="cat.core" class="text-[8px] text-amber-400 font-black px-1.5 py-0.5 bg-amber-400/10 rounded border border-amber-400/20" title="Categoría base del sistema">OFICIAL</span>
</div>
</div>
</template>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-4 p-3 border border-white/10 rounded-xl bg-black/20">
<input type="text" class="admin-input mb-0" placeholder="Nombre nueva categoría (ej: Sub-Femenina)" x-model="catNueva.label">
<input type="text" class="admin-input mb-0" placeholder="Descripción corta" x-model="catNueva.descripcion">
<button class="admin-btn sm:col-span-2 mt-1 bg-gradient-to-r from-nico-blue to-[#052a55]" @click="crearCategoria()"><i class="fas fa-plus"></i> Crear Nueva Categoría</button>
</div>
</div>
<!-- BLOQUE: SEGURIDAD DE LA CUENTA — CAMBIO DE USUARIO Y CONTRASEÑA ADMINISTRATIVA -->
<div class="admin-section relative overflow-hidden bg-gradient-to-br from-[#120a1c] via-black to-[#0d0914] border border-nico-orange/40">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <div>
      <h3 class="flex items-center gap-2 text-white">
        <i class="fas fa-user-shield text-nico-orange"></i> Seguridad de la Cuenta — Credenciales Administrativas
      </h3>
      <p class="text-[11px] text-gray-400">
        Modifica tu nombre de usuario administrativo o tu contraseña directamente en la base de datos con cifrado de alta seguridad. Requiere tu contraseña actual.
      </p>
    </div>
    <div class="text-[11px] text-gray-400 font-mono bg-black/60 px-3 py-1.5 rounded-xl border border-white/10 shrink-0">
      <span class="text-gray-500">Usuario actual:</span> <strong class="text-white" x-text="currentUser ? currentUser.usuario : 'admin'"></strong>
    </div>
  </div>

  <form @submit.prevent="cambiarCredencialesAdmin()" class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <!-- Nuevo Nombre de Usuario -->
      <div>
        <label class="text-[10px] font-black uppercase text-gray-300 block mb-1">
          Nuevo Nombre de Usuario <span class="text-gray-500 font-normal">(Modificable cada 7 días)</span>
        </label>
        <input type="text" x-model="adminCredsForm.usuarioNuevo" placeholder="Ej: nuevo.admin" autocomplete="username" class="admin-input !mb-0 text-xs w-full font-mono">
      </div>

      <!-- Contraseña Actual (Requerida) -->
      <div>
        <label class="text-[10px] font-black uppercase text-amber-400 block mb-1">
          Contraseña Actual * <span class="text-gray-500 font-normal">(Obligatoria para autorizar)</span>
        </label>
        <div class="relative">
          <input :type="adminCredsForm.verPass ? 'text' : 'password'" x-model="adminCredsForm.passActual" placeholder="Tu contraseña actual" autocomplete="current-password" required class="admin-input !mb-0 text-xs w-full pr-10 font-mono">
          <button type="button" @click="adminCredsForm.verPass = !adminCredsForm.verPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white text-xs">
            <i class="fas" :class="adminCredsForm.verPass ? 'fa-eye-slash' : 'fa-eye'"></i>
          </button>
        </div>
      </div>

      <!-- Nueva Contraseña -->
      <div>
        <label class="text-[10px] font-black uppercase text-nico-green block mb-1">
          Nueva Contraseña Segura <span class="text-gray-500 font-normal">(Mín. 12 caracteres: letras y números)</span>
        </label>
        <input :type="adminCredsForm.verPass ? 'text' : 'password'" x-model="adminCredsForm.passNuevo" placeholder="Mínimo 12 caracteres alfanuméricos" minlength="12" autocomplete="new-password" class="admin-input !mb-0 text-xs w-full font-mono">
      </div>

      <!-- Confirmar Nueva Contraseña -->
      <div>
        <label class="text-[10px] font-black uppercase text-nico-green block mb-1">
          Confirmar Nueva Contraseña
        </label>
        <input :type="adminCredsForm.verPass ? 'text' : 'password'" x-model="adminCredsForm.passConfirm" placeholder="Repite la nueva contraseña" minlength="12" autocomplete="new-password" class="admin-input !mb-0 text-xs w-full font-mono">
      </div>
    </div>

    <!-- Mensaje de Feedback -->
    <template x-if="adminCredsForm.msg">
      <div :class="adminCredsForm.msgTipo === 'error' ? 'bg-red-950/60 border-red-500/50 text-red-300' : 'bg-emerald-950/60 border-emerald-500/50 text-emerald-300'" class="p-3 rounded-xl border text-xs font-bold flex items-center gap-2">
        <i class="fas" :class="adminCredsForm.msgTipo === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-check'"></i>
        <span x-text="adminCredsForm.msg"></span>
      </div>
    </template>

    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
      <p class="text-[10px] text-gray-500 italic">
        <i class="fas fa-shield-alt text-nico-orange mr-1"></i> El cambio se aplica de forma inmediata en MySQL mediante hash seguro Bcrypt.
      </p>
      <button type="submit" :disabled="adminCredsForm.guardando" class="w-full sm:w-auto bg-gradient-to-r from-nico-orange to-red-600 hover:from-nico-orangeDark hover:to-red-700 text-white font-black px-6 py-2.5 rounded-xl text-xs uppercase flex items-center justify-center gap-2 shadow-lg transition-all disabled:opacity-50">
        <i class="fas" :class="adminCredsForm.guardando ? 'fa-spinner fa-spin' : 'fa-save'"></i>
        <span x-text="adminCredsForm.guardando ? 'Guardando en BD...' : 'Actualizar Mis Credenciales'"></span>
      </button>
    </div>
  </form>
</div>

<!-- BLOQUE: DESCARGA Y RESPALDO COMPLETO DE LA BASE DE DATOS (PORTABILIDAD DE DATOS DEL CLIENTE) -->
<div class="admin-section bg-gradient-to-br from-[#0a1912] to-black border border-nico-green/40">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h3 class="flex items-center gap-2 text-white"><i class="fas fa-database text-nico-green"></i> Descargar Información Completa de la Base de Datos</h3>
      <p class="text-[11px] text-gray-400">Exporta en un clic todos los jugadores por categoría, padres de familia, teléfonos, estadísticas, asistencias e historial de pagos en Excel o en respaldo completo SQL.</p>
    </div>
    <div class="flex items-center gap-2.5 flex-wrap shrink-0">
      <a href="javascript:void(0)" @click.prevent="exportarDatos('excel')"
         class="admin-btn bg-nico-green hover:bg-nico-greenDark !text-black font-black px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 shadow-[0_0_15px_rgba(16,185,129,0.3)] cursor-pointer">
        <i class="fas fa-file-excel text-sm"></i> Descargar Todo en Excel (.xls)
      </a>
      <a href="javascript:void(0)" @click.prevent="exportarDatos('sql')"
         class="admin-btn bg-white/10 hover:bg-nico-orange border border-white/20 text-white font-black px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 transition-colors cursor-pointer">
        <i class="fas fa-server text-sm"></i> Descargar Base de Datos (.sql)
      </a>
    </div>
  </div>
</div>

<!-- ==================== SECCIÓN: EQUIPO DE ADMINISTRADORES (MÁXIMO 3) ==================== -->
<div class="admin-section relative overflow-hidden bg-gradient-to-br from-[#120a1c] via-black to-[#090b14] border border-nico-orange/40">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <div>
      <h3 class="flex items-center gap-2 text-white">
        <i class="fas fa-users-gear text-nico-orange"></i> Equipo de Administradores del Sistema
      </h3>
      <p class="text-[11px] text-gray-400">
        Gestiona los administradores con acceso total al panel. Máximo 3 administradores permitidos por seguridad.
      </p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
      <span class="text-xs font-black px-3 py-1.5 rounded-xl border font-mono"
            :class="listaAdmins.length >= 3 ? 'bg-amber-500/10 text-amber-400 border-amber-500/30' : 'bg-nico-green/10 text-nico-green border-nico-green/30'">
        <i class="fas fa-user-shield mr-1"></i> <span x-text="listaAdmins.length + ' / 3 Administradores'"></span>
      </span>
    </div>
  </div>

  <!-- Aviso para Administradores Secundarios sin permiso de gestión de admins -->
  <div x-show="!esSuperAdmin" class="bg-black/40 border border-white/5 rounded-2xl p-3.5 mb-4 text-xs text-gray-400 italic flex items-center gap-2">
    <i class="fas fa-shield-halved text-nico-orange text-sm shrink-0"></i>
    <span>Solo el <strong>Administrador Principal (Super Admin)</strong> tiene privilegios para registrar nuevos administradores o configurar políticas y permisos de acceso.</span>
  </div>

  <!-- Formulario para Registrar Nuevo Administrador (solo Super Admin y si hay menos de 3) -->
  <div x-show="esSuperAdmin && listaAdmins.length < 3" class="bg-black/60 border border-white/10 rounded-2xl p-4 space-y-3 mb-4">
    <h4 class="text-xs font-black text-white uppercase flex items-center gap-1.5">
      <i class="fas fa-user-plus text-nico-orange"></i> Registrar Nuevo Administrador
    </h4>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div>
        <label class="text-[10px] font-bold text-gray-400 block mb-1">Nombre Completo *</label>
        <input type="text" class="admin-input !text-xs !py-2" placeholder="Ej: Juan Pérez" x-model="nuevoAdmin.nombre" @input="if(!nuevoAdmin.usuarioManual) nuevoAdmin.usuario = nuevoAdmin.nombre.toLowerCase().replace(/[^a-z0-9]/g,'.').replace(/\.+/g,'.').replace(/^\.|\.$/g,'')">
      </div>
      <div>
        <label class="text-[10px] font-bold text-gray-400 block mb-1">Usuario de Acceso *</label>
        <input type="text" class="admin-input !text-xs !py-2 font-mono" placeholder="Ej: juan.admin" x-model="nuevoAdmin.usuario" @input="nuevoAdmin.usuarioManual = true">
      </div>
      <div>
        <label class="text-[10px] font-bold text-gray-400 block mb-1">Contraseña Segura (Mín. 12 car.) *</label>
        <form @submit.prevent autocomplete="off" class="relative" style="margin:0;padding:0;">
          <input :type="mostrarPassNuevoAdmin ? 'text' : 'password'" autocomplete="new-password" class="admin-input !text-xs !py-2 pr-9 font-mono" placeholder="Mínimo 12 alfanumérico" x-model="nuevoAdmin.password">
          <button type="button" @click="mostrarPassNuevoAdmin = !mostrarPassNuevoAdmin" class="absolute inset-y-0 right-0 px-2.5 text-gray-400 hover:text-white text-xs cursor-pointer">
            <i class="fas" :class="mostrarPassNuevoAdmin ? 'fa-eye-slash' : 'fa-eye'"></i>
          </button>
        </form>
      </div>
    </div>
    <div class="flex items-center justify-between gap-3 pt-1 flex-wrap">
      <button type="button" @click="registrarAdmin()" class="admin-btn bg-nico-orange hover:bg-nico-orangeDark text-white font-black px-4 py-2 rounded-xl text-xs uppercase flex items-center gap-1.5 cursor-pointer shadow-md">
        <i class="fas fa-plus"></i> Crear Administrador
      </button>
      <span class="text-xs font-bold" :class="nuevoAdminMsg.includes('✓') ? 'text-nico-green' : 'text-nico-red'" x-text="nuevoAdminMsg" x-show="nuevoAdminMsg"></span>
    </div>
  </div>

  <div x-show="esSuperAdmin && listaAdmins.length >= 3" class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-3 mb-4 text-xs font-bold text-amber-300 flex items-center gap-2">
    <i class="fas fa-lock"></i> Se ha alcanzado el límite máximo de 3 administradores. Para registrar uno nuevo, debes eliminar alguno de los existentes.
  </div>

  <!-- Lista de Administradores Registrados -->
  <div class="space-y-2">
    <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider">Administradores Activos (<span x-text="listaAdmins.length"></span>)</h4>
    <div class="grid sm:grid-cols-3 gap-3">
      <template x-for="adm in listaAdmins" :key="adm.id">
        <div class="bg-black/60 border border-white/10 rounded-2xl p-3.5 flex items-center justify-between gap-3 hover:border-nico-orange/40 transition-colors">
          <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-9 h-9 rounded-xl bg-nico-orange/20 border border-nico-orange/40 flex items-center justify-center shrink-0">
              <i class="fas fa-user-shield text-nico-orange text-sm"></i>
            </div>
            <div class="min-w-0">
              <div class="font-black text-white text-xs truncate" x-text="adm.nombre"></div>
              <div class="text-[10px] text-gray-400 font-mono truncate">@<span class="text-nico-orange" x-text="adm.usuario"></span></div>
              <template x-if="Number(adm.es_top) === 1 || Number(adm.id) === 1 || (adm.usuario || '').toLowerCase() === 'admin'">
                <span class="inline-flex items-center gap-1 text-[9px] font-black text-amber-300 bg-amber-400/20 border border-amber-400/40 px-1.5 py-0.5 rounded-full uppercase tracking-wider mt-0.5">
                  <i class="fas fa-crown text-[8px] text-amber-400"></i> Super Admin
                </span>
              </template>
              <span x-show="currentUser && (currentUser.id == adm.id || currentUser.usuario == adm.usuario)" class="text-[9px] font-black text-nico-green uppercase block">● Tu cuenta actual</span>
            </div>
          </div>
          <div class="flex items-center gap-1.5 shrink-0">
            <button type="button"
                    x-show="esSuperAdmin && Number(adm.es_top) !== 1 && Number(adm.id) !== 1 && (adm.usuario || '').toLowerCase() !== 'admin'"
                    @click="designarComoSuperAdmin(adm)"
                    class="text-amber-400 hover:text-black bg-amber-400/10 hover:bg-amber-400 w-8 h-8 rounded-lg transition-all flex items-center justify-center cursor-pointer shadow-sm"
                    title="Designar como Super Administrador">
              <i class="fas fa-crown text-xs"></i>
            </button>
            <button type="button"
                    x-show="esSuperAdmin && (!currentUser || (currentUser.id != adm.id && currentUser.usuario != adm.usuario))"
                    @click="abrirModalPoliticas(adm)"
                    class="text-nico-orange hover:text-white bg-nico-orange/10 hover:bg-nico-orange w-8 h-8 rounded-lg transition-all flex items-center justify-center cursor-pointer"
                    title="Configurar Políticas y Permisos de este Administrador">
              <i class="fas fa-user-gear text-xs"></i>
            </button>
            <button type="button"
                    x-show="esSuperAdmin"
                    @click="eliminarAdmin(adm.id, adm.usuario)"
                    :disabled="listaAdmins.length <= 1 || (currentUser && (currentUser.id == adm.id || currentUser.usuario == adm.usuario))"
                    :class="listaAdmins.length <= 1 || (currentUser && (currentUser.id == adm.id || currentUser.usuario == adm.usuario)) ? 'opacity-30 cursor-not-allowed bg-gray-500/10 text-gray-500' : 'text-red-400 hover:text-white bg-red-500/10 hover:bg-red-500 cursor-pointer'"
                    class="w-8 h-8 rounded-lg transition-all flex items-center justify-center shrink-0"
                    title="Eliminar administrador">
              <i class="fas fa-trash text-xs"></i>
            </button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>

<!-- ==================== MODAL: ACTUALIZAR TELÉFONO DE FAMILIA ==================== -->
<div x-show="modalTelefonoPadre" x-transition class="fixed inset-0 z-[80] bg-black/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto" style="display: none;">
  <div class="glass-box p-5 sm:p-6 rounded-3xl max-w-md w-full border border-emerald-500/40 relative shadow-2xl space-y-4 my-8" @click.outside="modalTelefonoPadre = false">
    <button @click="modalTelefonoPadre = false" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg cursor-pointer">✕</button>
    <div class="text-center space-y-1">
      <span class="text-[10px] font-black text-emerald-400 uppercase tracking-widest"><i class="fab fa-whatsapp"></i> Actualizar Teléfono / WhatsApp</span>
      <h3 class="text-lg font-black text-white uppercase" x-text="padreTelefonoForm.nombre"></h3>
      <p class="text-[11px] text-gray-400 font-mono">Usuario: <span class="text-nico-green font-bold" x-text="padreTelefonoForm.usuario"></span></p>
    </div>

    <form @submit.prevent="guardarTelefonoPadre()" class="space-y-3">
      <div>
        <label class="text-xs font-bold text-gray-300 block mb-1">Número de Teléfono / WhatsApp *</label>
        <input type="tel" class="admin-input !text-white !font-bold !py-2" placeholder="Ej: 6332-1779" x-model="padreTelefonoForm.telefono" required>
        <p class="text-[10px] text-gray-500 italic mt-1">Este número se utiliza para recordatorios de pago y comunicados por WhatsApp.</p>
      </div>

      <div class="pt-2 flex items-center gap-2">
        <button type="submit" class="admin-btn flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-black py-2.5 rounded-xl text-xs uppercase cursor-pointer shadow-lg flex items-center justify-center gap-1.5">
          <i class="fas fa-check"></i> Guardar Teléfono
        </button>
        <button type="button" @click="modalTelefonoPadre = false" class="bg-white/10 hover:bg-white/20 text-gray-300 font-black py-2.5 px-4 rounded-xl text-xs uppercase cursor-pointer">
          Cancelar
        </button>
      </div>
    </form>

    <div x-show="msgTelefonoPadre" class="text-center">
      <span class="text-xs font-bold" :class="msgTelefonoPadre.includes('✓') ? 'text-nico-green' : 'text-nico-red'" x-text="msgTelefonoPadre"></span>
    </div>
  </div>
</div>

<!-- ==================== MODAL: RESTABLECER CONTRASEÑA DE FAMILIA ==================== -->
<div x-show="modalPasswordPadre" x-transition class="fixed inset-0 z-[80] bg-black/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto" style="display: none;">
  <div class="glass-box p-5 sm:p-6 rounded-3xl max-w-md w-full border border-amber-500/40 relative shadow-2xl space-y-4 my-8" @click.outside="modalPasswordPadre = false">
    <button @click="modalPasswordPadre = false" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg cursor-pointer">✕</button>
    <div class="text-center space-y-1">
      <span class="text-[10px] font-black text-amber-400 uppercase tracking-widest"><i class="fas fa-key"></i> Restablecer Contraseña</span>
      <h3 class="text-lg font-black text-white uppercase" x-text="padrePasswordForm.nombre"></h3>
      <p class="text-[11px] text-gray-400 font-mono">Usuario: <span class="text-nico-green font-bold" x-text="padrePasswordForm.usuario"></span></p>
    </div>

    <form @submit.prevent="guardarPasswordPadre()" autocomplete="off" class="space-y-3">
      <div>
        <label class="text-xs font-bold text-gray-300 block mb-1">Nueva Contraseña (Mín. 12 car.) *</label>
        <div class="relative">
          <input :type="padrePasswordForm.mostrar ? 'text' : 'password'" autocomplete="new-password" class="admin-input pr-10 font-mono !py-2" placeholder="Mínimo 12 caracteres alfanuméricos" x-model="padrePasswordForm.password" required>
          <button type="button" @click="padrePasswordForm.mostrar = !padrePasswordForm.mostrar" class="absolute inset-y-0 right-0 px-3 text-gray-400 hover:text-white text-xs cursor-pointer">
            <i class="fas" :class="padrePasswordForm.mostrar ? 'fa-eye-slash' : 'fa-eye'"></i>
          </button>
        </div>
        <p class="text-[10px] text-gray-500 italic mt-1">Debe tener al menos 12 caracteres combinando letras y números.</p>
      </div>

      <div class="pt-2 flex items-center gap-2">
        <button type="submit" class="admin-btn flex-1 bg-amber-500 hover:bg-amber-600 text-black font-black py-2.5 rounded-xl text-xs uppercase cursor-pointer shadow-lg flex items-center justify-center gap-1.5">
          <i class="fas fa-lock"></i> Restablecer Contraseña
        </button>
        <button type="button" @click="modalPasswordPadre = false" class="bg-white/10 hover:bg-white/20 text-gray-300 font-black py-2.5 px-4 rounded-xl text-xs uppercase cursor-pointer">
          Cancelar
        </button>
      </div>
    </form>

    <div x-show="msgPasswordPadre" class="text-center">
      <span class="text-xs font-bold" :class="msgPasswordPadre.includes('✓') ? 'text-nico-green' : 'text-nico-red'" x-text="msgPasswordPadre"></span>
    </div>
  </div>
</div>

<!-- ==================== MODAL: POLÍTICAS Y PERMISOS DEL ADMINISTRADOR (RBAC) ==================== -->
<div x-show="modalPoliticasAdmin" x-transition class="fixed inset-0 z-[80] bg-black/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto" style="display: none;">
  <div class="glass-box p-5 sm:p-6 rounded-3xl max-w-lg w-full border border-nico-orange/50 relative shadow-2xl space-y-4 my-8" @click.outside="modalPoliticasAdmin = false">
    <button @click="modalPoliticasAdmin = false" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg cursor-pointer">✕</button>
    <div class="text-center space-y-1">
      <span class="text-[10px] font-black text-nico-orange uppercase tracking-widest"><i class="fas fa-shield-halved"></i> Políticas de Acceso Granular</span>
      <h3 class="text-lg font-black text-white uppercase" x-text="adminEditandoPoliticas.nombre"></h3>
      <p class="text-[11px] text-gray-400 font-mono">Usuario: @<span class="text-nico-orange font-bold" x-text="adminEditandoPoliticas.usuario"></span></p>
    </div>

    <form @submit.prevent="guardarPoliticasAdmin()" class="space-y-3">
      <p class="text-xs text-gray-300">Selecciona los módulos a los que este administrador tendrá acceso:</p>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 bg-black/50 rounded-2xl border border-white/10 max-h-60 overflow-y-auto">
        <label class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-white/5 cursor-pointer border border-transparent hover:border-white/10">
          <input type="checkbox" value="categorias" x-model="adminEditandoPoliticas.permisos" class="accent-nico-orange rounded">
          <span class="text-xs text-white font-medium">Gestión de Categorías</span>
        </label>
        <label class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-white/5 cursor-pointer border border-transparent hover:border-white/10">
          <input type="checkbox" value="padres" x-model="adminEditandoPoliticas.permisos" class="accent-nico-orange rounded">
          <span class="text-xs text-white font-medium">Familias Registradas</span>
        </label>
        <label class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-white/5 cursor-pointer border border-transparent hover:border-white/10">
          <input type="checkbox" value="entrenadores" x-model="adminEditandoPoliticas.permisos" class="accent-nico-orange rounded">
          <span class="text-xs text-white font-medium">Gestión de Entrenadores</span>
        </label>
        <label class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-white/5 cursor-pointer border border-transparent hover:border-white/10">
          <input type="checkbox" value="pagos" x-model="adminEditandoPoliticas.permisos" class="accent-nico-orange rounded">
          <span class="text-xs text-white font-medium">Control de Pagos</span>
        </label>
        <label class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-white/5 cursor-pointer border border-transparent hover:border-white/10">
          <input type="checkbox" value="mensajes" x-model="adminEditandoPoliticas.permisos" class="accent-nico-orange rounded">
          <span class="text-xs text-white font-medium">Notificaciones & Buzón</span>
        </label>
        <label class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-white/5 cursor-pointer border border-transparent hover:border-white/10">
          <input type="checkbox" value="torneos" x-model="adminEditandoPoliticas.permisos" class="accent-nico-orange rounded">
          <span class="text-xs text-white font-medium">Torneos & Partidos</span>
        </label>
        <label class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-white/5 cursor-pointer border border-transparent hover:border-white/10">
          <input type="checkbox" value="evaluaciones" x-model="adminEditandoPoliticas.permisos" class="accent-nico-orange rounded">
          <span class="text-xs text-white font-medium">Evaluaciones</span>
        </label>
        <label class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-white/5 cursor-pointer border border-transparent hover:border-white/10">
          <input type="checkbox" value="convocatorias" x-model="adminEditandoPoliticas.permisos" class="accent-nico-orange rounded">
          <span class="text-xs text-white font-medium">Convocatorias</span>
        </label>
      </div>

      <div class="pt-2 flex items-center gap-2">
        <button type="submit" class="admin-btn flex-1 bg-nico-orange hover:bg-nico-orangeDark text-white font-black py-2.5 rounded-xl text-xs uppercase cursor-pointer shadow-lg flex items-center justify-center gap-1.5">
          <i class="fas fa-floppy-disk"></i> Guardar Políticas
        </button>
        <button type="button" @click="modalPoliticasAdmin = false" class="bg-white/10 hover:bg-white/20 text-gray-300 font-black py-2.5 px-4 rounded-xl text-xs uppercase cursor-pointer">
          Cancelar
        </button>
      </div>
    </form>

    <div x-show="msgPoliticasAdmin" class="text-center">
      <span class="text-xs font-bold" :class="msgPoliticasAdmin.includes('✓') ? 'text-nico-green' : 'text-nico-red'" x-text="msgPoliticasAdmin"></span>
    </div>
  </div>
</div>
</div>
