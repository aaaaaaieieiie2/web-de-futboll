<!DOCTYPE html>
<html lang="es" class="scroll-smooth" style="scroll-padding-top: 110px;">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="description" content="Academia Nico Sport — Plataforma oficial. Historia, categorías, cuerpo técnico, torneos y vitrina de campeonatos. Fundada en 2008.">
<?php 
$cspNonce = $_SESSION['csp_nonce'] ?? ''; 
$nonceAttr = !empty($cspNonce) ? ' nonce="' . htmlspecialchars($cspNonce, ENT_QUOTES, 'UTF-8') . '"' : ''; 
?>
<?php if (($rol ?? 'publico') === 'publico'): ?>
<meta name="robots" content="index, follow">
<?php else: ?>
<meta name="robots" content="noindex, nofollow, noarchive">
<?php endif; ?>
<link rel="canonical" href="https://academianicosport.com/">
<meta name="theme-color" content="#000000">
<link rel="icon" href="./img/escudo.png" type="image/png">
<meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<title>Academia Nico Sport | Escuela de Fútbol Base | Nico Sport Panamá</title>
<meta name="keywords" content="academia nico sport, nico sport, nico sport panama, academia nico sport panama, academia, academia de futbol, nico sport academia, escuela de futbol nico sport, futbol base panama, academia nico sports, club nico sport, nico sport futbol">
<meta name="author" content="Academia Nico Sport">
<meta name="geo.region" content="PA">
<meta name="geo.placename" content="Panamá">

<!-- Open Graph / Redes Sociales y Google Discover -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Academia Nico Sport">
<meta property="og:title" content="Academia Nico Sport | Escuela de Fútbol Base | Nico Sport Panamá">
<meta property="og:description" content="Academia Nico Sport (Nico Sport Panamá) — Escuela y academia oficial de fútbol base para niños y jóvenes en Panamá. Fundada en 2008.">
<meta property="og:url" content="https://academianicosport.com/">
<meta property="og:image" content="https://academianicosport.com/img/escudo.png">
<meta property="og:locale" content="es_PA">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="Academia Nico Sport | Escuela de Fútbol Base">
<meta name="twitter:description" content="Academia Nico Sport (Nico Sport Panamá) — Escuela oficial de fútbol base para niños y jóvenes.">
<meta name="twitter:image" content="https://academianicosport.com/img/escudo.png">

<!-- Schema.org Datos Estructurados para Google Search -->
<script<?= $nonceAttr ?> type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": ["SportsClub", "SportsActivityLocation"],
  "name": "Academia Nico Sport",
  "alternateName": ["Nico Sport", "Nico Sport Panamá", "Academia Nico Sport Panamá", "Nico Sports", "Escuela de Fútbol Nico Sport"],
  "url": "https://academianicosport.com/",
  "logo": "https://academianicosport.com/img/escudo.png",
  "image": "https://academianicosport.com/img/escudo.png",
  "description": "Academia oficial de fútbol base y escuela deportiva integral en Panamá para niños y jóvenes desde 2008. Formación en disciplina, respeto, esfuerzo y pasión.",
  "foundingDate": "2008",
  "sport": "Fútbol",
  "address": {
    "@type": "PostalAddress",
    "addressCountry": "PA",
    "addressLocality": "Panamá"
  }
}
</script>
<script<?= $nonceAttr ?>>(function(){var n=function(){};console.log=n;console.info=n;console.debug=n;console.warn=n;console.error=n;})();</script>
<script<?= $nonceAttr ?> src="https://cdn.tailwindcss.com"></script>
<script<?= $nonceAttr ?>>
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
<link rel="stylesheet" href="./css/styles.css?v=134">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<script<?= $nonceAttr ?> charset="UTF-8" src="./js/db.js?v=134" defer></script>
<script<?= $nonceAttr ?> charset="UTF-8" src="./js/app.js?v=134" defer></script>
<script<?= $nonceAttr ?> charset="UTF-8" src="./js/particles.js?v=134" defer></script>
<script<?= $nonceAttr ?> defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
<style>
[x-cloak] { display: none !important; }
/* SCROLL INTERNO PARA CONTENEDORES ESPECÍFICOS */
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
<!-- INTRO -->
<div id="introLoader" x-show="loading" style="position:fixed;inset:0;z-index:100;background:#000;display:flex;flex-direction:column;align-items:center;justify-content:center;overflow:hidden;"
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
<img src="img/escudo.png" alt="Escudo Nico Sport" class="w-full h-full object-contain filter drop-shadow">
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
<span class="text-xs font-mono font-bold text-white tracking-widest drop-shadow" x-text="reloadSpamMsg ? reloadSpamMsg : ('CARGANDO ' + progress + '%')"></span>
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
<img src="img/escudo.png" alt="Escudo Header" data-edit-media="img-escudo-header" class="w-full h-full object-contain group-hover:scale-110 transition-transform">
</div>
<div>
<span class="font-black text-xs sm:text-sm tracking-wider text-white leading-none hover:text-nico-orange transition-colors">ACADEMIA NICO SPORT</span>
<span class="text-[9px] font-bold text-gray-400 tracking-widest uppercase flex items-center gap-1 mt-0.5">
<i class="fas fa-award text-amber-400"></i> G&M Deportes S.A.
</span>
</div>
</div>
<div x-show="!currentUser">
<button @click="loginModal = true; triggerParticles($event.clientX, $event.clientY)" class="relative overflow-hidden group bg-black border border-white/20 hover:border-nico-orange hover:scale-105 text-white font-black px-4 py-2 rounded-xl text-xs uppercase tracking-wider transition-all shadow-lg active:scale-95 flex items-center gap-2">
<div class="absolute inset-0 bg-gradient-to-r from-nico-red/30 via-nico-orange/30 to-nico-blue/30 opacity-0 group-hover:opacity-100 transition-opacity"></div>
<i class="fas fa-user-shield text-nico-orange relative z-10 group-hover:animate-bounce"></i>
<span class="relative z-10">Inicio de sesión</span>
</button>
</div>
<?php if ($esAutenticado): ?>
<div x-cloak x-show="currentUser" class="flex items-center gap-2">
<span class="px-3 py-2 rounded-xl text-[10px] font-black uppercase border"
:class="esAdmin ? 'border-nico-orange text-nico-orange bg-nico-orange/10' : (esEntrenador ? 'border-nico-blue text-nico-blue bg-nico-blue/10' : 'border-nico-green text-nico-green bg-nico-green/10')"
x-text="(esAdmin ? 'ADMIN — ' : (esEntrenador ? 'DT — ' : 'FAMILIA — ')) + (currentUser?.nombre || '')"></span>
<?php if ($esAdmin || $esEntrenador): ?>
<button x-show="esAdmin || esEntrenador" @click="toggleModoEdicion()" :class="modoEdicion ? 'bg-nico-green text-black border-nico-green' : 'bg-black text-gray-300 border-white/20 hover:text-white'" class="border font-black px-3 py-2 rounded-xl text-[10px] uppercase transition-all" :title="esAdmin ? 'Modo edición de textos' : 'Modo edición DT (estadísticas)'">
<i class="fas fa-pencil"></i>
</button>
<?php endif; ?>
<?php if ($esAdmin): ?>
<button x-show="modoEdicion && esAdmin" @click="guardarCambiosCMS()" class="bg-gradient-to-r from-nico-green to-emerald-500 hover:from-emerald-400 hover:to-nico-green text-black font-black px-3 py-2 rounded-xl text-[10px] uppercase transition-all shadow-[0_0_15px_rgba(16,185,129,0.5)] flex items-center gap-1.5 active:scale-95 cursor-pointer" title="Guardar todos los cambios en la base de datos">
<i class="fas fa-floppy-disk text-xs"></i> <span>Guardar</span>
</button>
<?php endif; ?>
<button @click="cerrarSesion()" class="bg-black border border-white/20 hover:border-nico-red text-gray-300 hover:text-white font-black px-3 py-2 rounded-xl text-[10px] uppercase transition-all">
<i class="fas fa-right-from-bracket"></i> Salir
</button>
</div>
<?php endif; ?>
</div>
</header>
<main class="relative z-10 pt-8 pb-12 md:pb-16 px-4 max-w-6xl mx-auto">
<div class="text-center max-w-3xl mx-auto mb-6">
<div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-black border border-white/15 text-gray-300 text-xs font-black tracking-wider uppercase shadow-2xl hover:border-nico-orange transition-colors">
<i class="fas fa-star text-nico-orange animate-pulse"></i> <span data-edit="hero-lema">DISCIPLINA • RESPETO • ESFUERZO • PASIÓN</span>
</div>
</div>

<!-- NAVEGACIÓN DESKTOP -->
<div class="hidden md:grid grid-cols-5 gap-2 p-2 bg-black/80 rounded-2xl border-b-2 animated-border mb-8 max-w-5xl mx-auto backdrop-blur-md shadow-2xl hover:shadow-[0_0_30px_rgba(255,85,0,0.15)] transition-shadow duration-500">
<template x-for="t in navTabs" :key="'desk-'+t.id">
<a :href="'#tab=' + t.id" x-show="tabVisible(t)" @click.prevent="switchTab(t.id, $event)" :class="[mainTab === t.id ? 'bg-white/15 text-white font-extrabold shadow-inner scale-[1.02]' : 'text-gray-400 hover:text-white hover:bg-white/5', '']" class="py-3 px-2 rounded-xl text-sm transition-all duration-200 flex flex-col items-center gap-1.5 border border-transparent">
<i class="fas text-base transition-transform duration-300" :class="[t.icon, mainTab === t.id ? t.active + ' scale-110' : '']"></i>
<span x-text="t.label"></span>
</a>
</template>
</div>

<div id="contenido-dinamico" class="min-h-[75vh]">
