<!DOCTYPE html>
<html lang="es" class="scroll-smooth" style="scroll-padding-top: 110px;">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="description" content="Academia Nico Sport — Plataforma oficial. Historia, categorías, cuerpo técnico, torneos y vitrina de campeonatos. Fundada en 2008.">
<meta name="theme-color" content="#000000">
<title>Academia Nico Sport - Plataforma Oficial</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
window.CSRF_TOKEN = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';
tailwind.config = {
theme: {
extend: {
fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
colors: {
nico: {
orange: '#FF5500', orangeDark: '#D04400', red: '#DC2626',
green: '#10B981', greenDark: '#059669', blue: '#0B4F9C',
blueDark: '#052a55', gold: '#F59E0B', dark: '#000000',
card: '#080808', cardHover: '#121212'
}
}
}
}
}
</script>
<link rel="stylesheet" href="./css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<script src="./js/db.js?v=21" defer></script>
<script src="./js/app.js?v=21" defer></script>
<script src="./js/particles.js" defer></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
<style>
/* SCROLL INTERNO PARA CONTENEDORES ESPECÍFICOS */
.scroll-container { max-height: 520px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #FF5500 #000; }
.scroll-container::-webkit-scrollbar { width: 8px; }
.scroll-container::-webkit-scrollbar-track { background: #000; border-radius: 8px; }
.scroll-container::-webkit-scrollbar-thumb { background: linear-gradient(180deg,#FF5500,#D04400); border-radius: 8px; }
.scroll-container::-webkit-scrollbar-thumb:hover { background: #FF5500; }
.scroll-container-sm { max-height: 340px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #FF5500 #000; }
.scroll-container-sm::-webkit-scrollbar { width: 6px; }
.scroll-container-sm::-webkit-scrollbar-track { background: #000; border-radius: 6px; }
.scroll-container-sm::-webkit-scrollbar-thumb { background: #FF5500; border-radius: 6px; }
.scroll-x-container { max-height: 420px; overflow: auto; scrollbar-width: thin; scrollbar-color: #F59E0B #000; }
.scroll-x-container::-webkit-scrollbar { width: 8px; height: 8px; }
.scroll-x-container::-webkit-scrollbar-track { background: #000; }
.scroll-x-container::-webkit-scrollbar-thumb { background: #F59E0B; border-radius: 8px; }

/* ESTADOS DE PAGO */
.badge-vigente { background: rgba(16,185,129,0.15); color: #10B981; border: 1px solid rgba(16,185,129,0.4); }
.badge-vencido { background: rgba(220,38,38,0.15); color: #DC2626; border: 1px solid rgba(220,38,38,0.4); animation: pulse 2s infinite; }
.badge-pendiente { background: rgba(245,158,11,0.15); color: #F59E0B; border: 1px solid rgba(245,158,11,0.4); }
.badge-validado { background: rgba(16,185,129,0.2); color: #10B981; border: 1px solid rgba(16,185,129,0.5); }
.badge-rechazado { background: rgba(220,38,38,0.2); color: #DC2626; border: 1px solid rgba(220,38,38,0.5); }
.timer-box { background: linear-gradient(135deg, rgba(255,85,0,0.1), rgba(220,38,38,0.05)); border: 1px solid rgba(255,85,0,0.3); }

/* ESTILOS PROFESIONALES NUEVOS PARA ADMIN Y PAGOS */
.admin-card-glow { box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5), inset 0 0 10px rgba(255, 255, 255, 0.02); backdrop-filter: blur(10px); }
.modern-table th { background: rgba(0,0,0,0.8); border-bottom: 2px solid rgba(255,255,255,0.1); color: #a1a1aa; }
.modern-table td { padding-top: 0.8rem; padding-bottom: 0.8rem; }
.modern-table tr:not(:last-child) td { border-bottom: 1px solid rgba(255,255,255,0.03); }
.modern-table tr:hover { background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.03), transparent); }
input[type="radio"].accent-nico-orange { accent-color: #FF5500; width: 14px; height: 14px; }

/* FIX RESPONSIVE GLOBAL + MODALES + BANNERS */
img { max-width: 100%; }
.fixed.inset-0 .glass-box { max-height: 92vh; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #FF5500 #000; }
.fixed.inset-0 .glass-box::-webkit-scrollbar { width: 6px; }
.fixed.inset-0 .glass-box::-webkit-scrollbar-track { background: #000; border-radius: 6px; }
.fixed.inset-0 .glass-box::-webkit-scrollbar-thumb { background: #FF5500; border-radius: 6px; }
@media (max-width: 640px) {
button.w-7.h-7 { width: 2.4rem !important; height: 2.4rem !important; }
.fixed.inset-0 .flex.items-center.justify-between { flex-wrap: wrap; gap: 6px; }
}
</style>
</head>
<body class="bg-black text-gray-100 font-sans overflow-x-hidden min-h-screen selection:bg-nico-orange selection:text-white cinematic-pattern" x-data="nicoApp">
<canvas id="particleCanvas" class="fixed inset-0 pointer-events-none z-50"></canvas>
<div x-show="modoEdicion" class="edit-hint">✏️ Modo edición activo — <span x-text="esAdmin ? 'clic en textos naranjas para reescribir · botones ✎/🗑 en jugadores, equipos y staff' : 'DT: edita ⚽ 🎯 🏅 de tu categoría'"></span></div>

<!-- INTRO -->
<div x-show="loading"
x-transition:leave="intro-disintegrate"
x-transition:leave-start="opacity-100 scale-100 blur-0"
x-transition:leave-end="opacity-0 scale-150 blur-2xl pointer-events-none"
class="fixed inset-0 z-[100] bg-black flex flex-col items-center justify-center overflow-hidden">
<div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(220,38,38,0.25)_0%,rgba(0,0,0,1)_70%)]"></div>
<div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,85,0,0.3)_0%,transparent_50%)]"></div>
<div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,rgba(11,79,156,0.25)_0%,transparent_50%)]"></div>
<div class="intro-aura absolute w-96 h-96 bg-gradient-to-r from-nico-orange via-nico-red to-nico-blue rounded-full blur-[90px] opacity-40"></div>
<div class="relative z-10 flex flex-col items-center justify-center space-y-3">
<div class="anim-logo-entry w-28 h-28 sm:w-36 sm:h-36 rounded-full bg-black/60 border-2 border-white/80 p-3 shadow-[0_0_50px_rgba(255,85,0,0.8)] overflow-hidden flex items-center justify-center mb-2">
<img src="img/escudo.png" onerror="this.onerror=null;this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iIzY2NiI+PHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00cy0xLjc5LTQtNC00LTQgMS43OS00IDQgMS43OSA0IDQgNHptMCAyYy0yLjY3IDAtOCAxLjM0LTggNHYyaDE2di0yYzAtMi42Ni01LjMzLTQtOC00eiIvPjwvc3ZnPg==';" alt="Escudo Nico Sport" class="w-full h-full object-contain filter drop-shadow">
</div>
<h1 class="anim-disney text-5xl sm:text-7xl font-black text-transparent bg-clip-text bg-gradient-to-b from-white via-gray-200 to-gray-500 uppercase tracking-tight leading-none text-center drop-shadow-2xl">Nico Sport</h1>
<span class="anim-disney text-nico-orange font-extrabold text-xs sm:text-sm uppercase tracking-[0.5em] block">Academia de Fútbol</span>
<div class="anim-bounce mt-4 relative flex flex-col items-center">
<div class="text-white text-5xl sm:text-6xl flex items-center justify-center drop-shadow-[0_0_20px_rgba(255,255,255,0.9)]">
<i class="fas fa-futbol animate-spin" style="animation-duration: 4s;"></i>
</div>
<div class="w-12 h-2 bg-black/80 blur-[4px] rounded-full mt-2"></div>
</div>
</div>
<div class="absolute bottom-10 flex flex-col items-center z-10">
<span class="text-xs font-mono font-bold text-white tracking-widest drop-shadow" x-text="'CARGANDO ' + progress + '%'"></span>
<div class="w-48 h-1.5 bg-white/10 rounded-full mt-3 overflow-hidden border border-white/10">
<div class="h-full bg-gradient-to-r from-nico-red via-nico-orange via-white to-nico-blue transition-all duration-100" :style="`width: ${progress}%`"></div>
</div>
</div>
</div>

<!-- HEADER -->
<header class="sticky top-0 w-full z-40 bg-black/90 backdrop-blur-xl border-b-2 animated-border py-3 px-4 shadow-2xl transition-all">
<div class="max-w-6xl mx-auto flex justify-between items-center">
<div class="flex items-center gap-3 cursor-pointer" @click="switchTab('historia', $event); window.scrollTo({top: 0, behavior: 'smooth'})">
<div class="w-10 h-10 rounded-xl bg-black border border-white/20 flex items-center justify-center shadow-lg relative group p-1 overflow-hidden">
<img src="img/escudo.png" onerror="this.onerror=null;this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iIzY2NiI+PHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00cy0xLjc5LTQtNC00LTQgMS43OS00IDQgMS43OSA0IDQgNHptMCAyYy0yLjY3IDAtOCAxLjM0LTggNHYyaDE2di0yYzAtMi42Ni01LjMzLTQtOC00eiIvPjwvc3ZnPg==';" alt="Escudo Header" data-edit-media="img-escudo-header" class="w-full h-full object-contain group-hover:scale-110 transition-transform">
</div>
<div>
<h1 class="font-black text-xs sm:text-sm tracking-wider text-white leading-none hover:text-nico-orange transition-colors">ACADEMIA NICO SPORT</h1>
<span class="text-[9px] font-bold text-gray-400 tracking-widest uppercase flex items-center gap-1 mt-0.5">
<i class="fas fa-award text-amber-400"></i> G&M Deportes S.A.
</span>
</div>
</div>
<div x-show="!currentUser">
<button @click="loginModal = true; triggerParticles($event.clientX, $event.clientY)" class="relative overflow-hidden group bg-black border border-white/20 hover:border-nico-orange hover:scale-105 text-white font-black px-4 py-2 rounded-xl text-xs uppercase tracking-wider transition-all shadow-lg active:scale-95 flex items-center gap-2">
<div class="absolute inset-0 bg-gradient-to-r from-nico-red/30 via-nico-orange/30 to-nico-blue/30 opacity-0 group-hover:opacity-100 transition-opacity"></div>
<i class="fas fa-user-shield text-nico-orange relative z-10 group-hover:animate-bounce"></i>
<span class="relative z-10">Portal Privado</span>
</button>
</div>
<div x-show="currentUser" class="flex items-center gap-2">
<span class="px-3 py-2 rounded-xl text-[10px] font-black uppercase border"
:class="esAdmin ? 'border-nico-orange text-nico-orange bg-nico-orange/10' : (esEntrenador ? 'border-nico-blue text-nico-blue bg-nico-blue/10' : 'border-nico-green text-nico-green bg-nico-green/10')"
x-text="(esAdmin ? 'ADMIN • ' : (esEntrenador ? 'DT • ' : 'FAMILIA • ')) + (currentUser?.nombre || '')"></span>
<button x-show="esAdmin || esEntrenador" @click="toggleModoEdicion()" :class="modoEdicion ? 'bg-nico-green text-black border-nico-green' : 'bg-black text-gray-300 border-white/20 hover:text-white'" class="border font-black px-3 py-2 rounded-xl text-[10px] uppercase transition-all" :title="esAdmin ? 'Modo edición de textos' : 'Modo edición DT (estadísticas)'">
<i class="fas fa-pencil"></i>
</button>
<button @click="cerrarSesion()" class="bg-black border border-white/20 hover:border-nico-red text-gray-300 hover:text-white font-black px-3 py-2 rounded-xl text-[10px] uppercase transition-all">
<i class="fas fa-right-from-bracket"></i> Salir
</button>
</div>
</div>
</header>
<main class="relative z-10 pt-8 pb-28 md:pb-16 px-4 max-w-6xl mx-auto">
<div class="text-center max-w-3xl mx-auto mb-6">
<div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-black border border-white/15 text-gray-300 text-xs font-black tracking-wider uppercase shadow-2xl hover:border-nico-orange transition-colors">
<i class="fas fa-star text-nico-orange animate-pulse"></i> <span data-edit="hero-lema">DISCIPLINA • RESPETO • ESFUERZO • PASIÓN</span>
</div>
</div>

<!-- NAVEGACIÓN CON ROLES -->
<div x-data="{
navTabs: [
{ id:'historia',    icon:'fa-shield-halved',  label:'Escudo & Historia',  active:'text-nico-orange', role:'all'   },
{ id:'categorias',  icon:'fa-users',          label:'Categorías',         active:'text-nico-green',  role:'all'   },
{ id:'staff',       icon:'fa-user-ninja',     label:'Cuerpo Técnico',     active:'text-nico-blue',   role:'all'   },
{ id:'torneo',      icon:'fa-list-ol',        label:'Torneo & Tabla',     active:'text-amber-400',   role:'all'   },
{ id:'vitrina',     icon:'fa-trophy',         label:'Vitrina Campeonato', active:'text-amber-400',   role:'all'   },
{ id:'asistencia',  icon:'fa-clipboard-list', label:'Asistencia',         active:'text-nico-green',  role:'coach' },
{ id:'mensajes',    icon:'fa-inbox',          label:'Mensajes',           active:'text-nico-orange', role:'admin' },
{ id:'pagosClub',   icon:'fa-money-bill-wave',label:'Pagos Club',         active:'text-nico-green',  role:'admin' },
{ id:'pagosPlat',   icon:'fa-credit-card',    label:'Pago Plataforma',    active:'text-amber-400',   role:'admin' },
{ id:'entrenadores',icon:'fa-user-tie',       label:'Entrenadores',       active:'text-nico-blue',   role:'admin' },
{ id:'portal',      icon:'fa-wallet',         label:'Mi Cuenta',          active:'text-nico-green',  role:'padre' },
{ id:'admin',       icon:'fa-user-gear',      label:'Panel Admin',        active:'text-nico-orange', role:'admin' }
]
}">
<!-- DESKTOP -->
<div class="hidden md:grid grid-cols-5 gap-2 p-2 bg-black/80 rounded-2xl border-b-2 animated-border mb-8 max-w-5xl mx-auto backdrop-blur-md shadow-2xl hover:shadow-[0_0_30px_rgba(255,85,0,0.15)] transition-shadow duration-500">
<template x-for="t in navTabs" :key="'desk-'+t.id">
<button x-show="tabVisible(t)"
@click="switchTab(t.id, $event)"
:class="[mainTab === t.id ? 'bg-white/15 text-white font-extrabold shadow-inner scale-[1.02]' : 'text-gray-400 hover:text-white hover:bg-white/5', t.id === 'admin' ? 'col-span-2 sm:col-span-1' : '']"
class="py-3 px-2 rounded-xl text-sm transition-all duration-200 flex flex-col items-center gap-1.5 border border-transparent">
<i class="fas text-base transition-transform duration-300" :class="[t.icon, mainTab === t.id ? t.active + ' scale-110' : '']"></i>
<span x-text="t.label"></span>
</button>
</template>
</div>
<!-- MÓVIL ESTILO TIKTOK -->
<nav id="mobileNav" class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-black/95 backdrop-blur-xl border-t-2 border-nico-orange/50 shadow-[0_-8px_30px_rgba(255,85,0,0.18)]" style="padding-bottom: env(safe-area-inset-bottom);">
<p class="text-center text-[8px] font-black uppercase tracking-[0.3em] text-gray-600 pt-1.5 select-none">⟵ Desliza para ver más secciones ⟶</p>
<div class="nav-scroll no-scrollbar flex overflow-x-auto snap-x snap-mandatory gap-1.5 px-2 py-2 text-[10px] font-black uppercase tracking-wide">
<template x-for="t in navTabs.filter(t => tabVisible(t))" :key="'mob-'+t.id">
<button :data-mobtab="t.id"
@click="switchTab(t.id, $event); window.scrollTo({top:0, behavior:'smooth'})"
class="snap-start shrink-0 flex flex-col items-center gap-1 py-2 px-3 min-w-[74px] rounded-xl transition-all border"
:class="mainTab === t.id ? 'text-white bg-white/10 border-white/20 shadow-inner' : 'text-gray-500 border-transparent'">
<i class="fas text-lg" :class="[t.icon, mainTab === t.id ? t.active : '']"></i>
<span x-text="t.label.split(' ')[0]"></span>
<span class="h-1 w-6 rounded-full transition-all" :class="mainTab === t.id ? 'bg-nico-orange' : 'bg-transparent'"></span>
</button>
</template>
<button x-show="!currentUser" @click="loginModal = true" class="snap-start shrink-0 flex flex-col items-center gap-1 py-2 px-3 min-w-[74px] rounded-xl text-gray-500 border border-transparent">
<i class="fas fa-user-shield text-lg text-nico-orange"></i>
<span>Portal</span>
<span class="h-1 w-6 rounded-full bg-transparent"></span>
</button>
</div>
</nav>
</div>
<div id="contenido-dinamico" class="min-h-[450px]">