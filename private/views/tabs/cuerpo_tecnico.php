<!-- ==================== SECCIÓN 3: CUERPO TÉCNICO ==================== -->
<div x-show="mainTab === 'staff'" class="space-y-6 anim-entry">
<div class="text-center max-w-xl mx-auto space-y-2">
<span class="text-xs font-black text-white border border-white/20 bg-black px-4 py-1 rounded-full uppercase tracking-widest shadow-lg">Liderazgo & Metodología</span>
<h3 class="text-2xl font-black text-white uppercase mt-4">Nuestro Cuerpo Técnico</h3>
<p class="text-xs text-gray-400" data-edit="staff-intro"> dedicados a la enseñanza técnica y formación en valores.</p>
<button x-show="esAdmin && modoEdicion" @click="agregarStaff()" class="mt-2 border-2 border-dashed border-nico-green/40 text-nico-green rounded-xl px-4 py-2 text-xs font-black uppercase hover:bg-nico-green/10 transition-all">+ Agregar Integrante</button>
</div>
<div class="grid sm:grid-cols-2 gap-6">
<template x-for="staff in staffList" :key="staff.id">
<div class="glass-box p-6 rounded-3xl border border-white/10 flex flex-col justify-between hover:border-white/30 hover:scale-[1.02] transition-all shadow-xl group cursor-pointer relative" @click="if (!modoEdicion) { staffModal = staff; triggerParticles($event.clientX, $event.clientY); }">
<template x-if="esAdmin && modoEdicion">
<div class="absolute top-3 right-3 z-20 flex gap-1" @click.stop>
<button class="btn-edit-mini" @click="editarStaff(staff)" title="Editar ficha"><i class="fas fa-pen"></i></button>
<button class="btn-del-mini" @click="eliminarStaff(staff)" title="Eliminar"><i class="fas fa-trash"></i></button>
</div>
</template>
<div class="space-y-4">
<div class="flex items-center gap-4">
<div class="w-16 h-16 rounded-2xl bg-gradient-to-br flex items-center justify-center text-2xl text-white shadow-[0_0_15px_rgba(255,255,255,0.2)] shrink-0 group-hover:scale-110 transition-transform duration-300 border border-white/20" :class="staff.color">
<i class="fas" :class="staff.fotoIcon"></i>
</div>
<div>
<h4 class="text-lg font-black text-white group-hover:text-amber-400 transition-colors" x-text="staff.nombre"></h4>
<span class="text-xs font-bold text-gray-300 block" x-text="staff.cargo"></span>
<span class="text-[10px] text-gray-500 font-semibold uppercase" x-text="staff.licencia"></span>
</div>
</div>
<p class="text-xs text-gray-400 leading-relaxed" x-text="staff.bio"></p>
</div>
<div class="pt-4 mt-4 border-t border-white/10 flex items-center justify-between">
<span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider" x-text="staff.exp"></span>
<span class="text-xs font-black text-white group-hover:text-amber-400 transition-colors flex items-center gap-1">
Ver Ficha <i class="fas fa-arrow-right text-[10px]"></i>
</span>
</div>
</div>
</template>
</div>
</div>