<!-- ==================== SECCIÓN 1: HISTORIA ==================== -->
<div x-show="mainTab === 'historia'" class="space-y-8 anim-entry">
<div class="grid md:grid-cols-12 gap-6 items-stretch">
<div class="md:col-span-5 glass-box p-8 rounded-3xl text-center flex flex-col items-center justify-center animated-border cursor-pointer hover:scale-[1.02] transition-transform duration-300 shadow-lg relative group" @click="triggerParticles($event.clientX, $event.clientY)">
<div class="w-48 h-48 sm:w-56 sm:h-56 rounded-3xl bg-black border-2 border-white flex flex-col items-center justify-center shadow-[0_0_50px_rgba(255,85,0,0.6)] mb-4 relative overflow-hidden p-4">
<div class="absolute inset-0 bg-gradient-to-tr from-nico-red/30 via-nico-orange/20 to-transparent"></div>
<div x-html="renderMediaGlobal('media_escudo_historia', 'img/escudo.png', 'w-full h-full object-contain relative z-10 transition-transform duration-500', adminPadresVersion)"></div>
</div>
<h4 class="text-2xl font-black text-white uppercase tracking-tight">Simbología del Escudo</h4>
<span class="text-xs font-bold text-nico-orange uppercase tracking-widest mt-1">Identidad & Orgullo Institucional</span>
</div>
<div class="md:col-span-7 glass-box p-8 rounded-3xl space-y-4 border-t-4 border-t-nico-orange hover:border-white/30 transition-colors">
<h4 class="text-lg font-black text-white uppercase border-b border-white/10 pb-3 flex items-center justify-between">
<span>Elementos de Nuestra Insignia</span>
<i class="fas fa-award text-nico-orange animate-pulse"></i>
</h4>
<div class="space-y-3 text-xs text-gray-400">
<div class="flex items-start gap-3 p-3 glass-card rounded-xl border border-white/5 hover:border-nico-orange hover:scale-[1.01] transition-all">
<i class="fas fa-palette text-nico-orange text-lg mt-1 shrink-0"></i>
<div>
<strong class="text-white block text-sm">Franjas Naranjas, Red & Black:</strong>
<span data-edit="insignia-franjas">El naranja y rojo representan la energía y pasión en la cancha; el negro la seriedad, fuerza y disciplina.</span>
</div>
</div>
<div class="flex items-start gap-3 p-3 glass-card rounded-xl border border-white/5 hover:border-nico-green hover:scale-[1.01] transition-all">
<i class="fas fa-leaf text-nico-green text-lg mt-1 shrink-0"></i>
<div>
<strong class="text-white block text-sm">Hojas de Laurel:</strong>
<span data-edit="insignia-laurel">Simbolizan el honor, la perseverancia y el triunfo alcanzado a través del juego limpio y constante.</span>
</div>
</div>
<div class="flex items-start gap-3 p-3 glass-card rounded-xl border border-white/5 hover:border-nico-blue hover:scale-[1.01] transition-all">
<i class="fas fa-futbol text-nico-blue text-lg mt-1 shrink-0"></i>
<div>
<strong class="text-white block text-sm">Balón 2008:</strong>
<span data-edit="insignia-balon">Marca el punto de partida de nuestro legado y amor incondicional por la formación deportiva base.</span>
</div>
</div>
</div>
</div>
</div>
<div class="glass-box p-6 sm:p-8 rounded-3xl border-l-4 border-l-nico-orange space-y-6 cursor-pointer hover:border-nico-orange hover:shadow-lg transition-all" @click="triggerParticles($event.clientX, $event.clientY)">
<div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/10 pb-4">
<div>
<span class="text-xs font-black text-nico-orange uppercase tracking-widest flex items-center gap-2">
<i class="fas fa-sparkles"></i> Reseña Histórica Oficial
</span>
<h3 class="text-2xl sm:text-3xl font-black text-white uppercase mt-1">Nuestra Historia (2008 - Presente)</h3>
</div>
<span class="bg-black text-nico-orange text-xs font-black px-4 py-1.5 rounded-full border border-nico-orange/50 shadow-lg">18 Años de Legado</span>
</div>
<div class="space-y-4 text-sm sm:text-base text-gray-300 leading-relaxed">
<p data-edit="hist-1">La <strong class="text-white">Academia Nico Sport</strong> nació formalmente en el año <strong class="text-nico-orange">2008</strong></p>
<p data-edit="hist-2">academia_nicosport Academia Nico Sports es una institución deportiva de alto compromiso social y técnico, fundada en 2008 con la misión de transformar la vida de niños y jóvenes a través del fútbol. Recibimos a atletas en formación desde los 6 hasta los 16 años, ofreciendo un entorno seguro, profesional y motivador. <em class="text-nico-green font-semibold">"Formamos jugadores"</em>.</p>
<p data-edit="hist-3">Nuestra Filosofía de Formación En Nico Sports, entendemos que el fútbol es mucho más que un juego; es una herramienta de vida.</p>
<p data-edit="hist-4">En Academia Nico Sports, el éxito no solo se mide en goles, sino en el carácter y la integridad de nuestros deportistas.</p>
<p data-edit="hist-5">Un hito clave en la consolidación del club ha sido el respaldo institucional de <strong class="text-nico-blue">G&M Deportes S.A.</strong>, patrocinador oficial que apostó por el impacto formativo del fútbol base. Esta alianza nos permite dotar a las categorías de equipamiento de primer nivel.</p>
</div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-white/10 text-center">
<div class="p-4 glass-card rounded-2xl border-b-2 border-transparent hover:border-nico-orange hover:-translate-y-1 transition-all">
<span class="text-3xl font-black text-white block" data-edit="stat-anio">2008</span>
<span class="text-[10px] text-nico-orange font-bold uppercase" data-edit="stat-anio-label">Año de Fundación</span>
</div>
<div class="p-4 glass-card rounded-2xl border-b-2 border-transparent hover:border-nico-green hover:-translate-y-1 transition-all">
<span class="text-3xl font-black text-white block" data-edit="stat-jug">+500</span>
<span class="text-[10px] text-nico-green font-bold uppercase" data-edit="stat-jug-label">Jugadores Formados</span>
</div>
<div class="p-4 glass-card rounded-2xl border-b-2 border-transparent hover:border-nico-blue hover:-translate-y-1 transition-all">
<span class="text-3xl font-black text-white block" data-edit="stat-cats">4</span>
<span class="text-[10px] text-nico-blue font-bold uppercase" data-edit="stat-cats-label">Categorías Oficiales</span>
</div>
<div class="p-4 glass-card rounded-2xl border-b-2 border-transparent hover:border-amber-400 hover:-translate-y-1 transition-all">
<span class="text-3xl font-black text-white block" data-edit="stat-copas">0</span>
<span class="text-[10px] text-amber-400 font-bold uppercase" data-edit="stat-copas-label">Copas Ganadas</span>
</div>
</div>
</div>
<div class="glass-box p-6 sm:p-8 rounded-3xl border-t-4 border-t-nico-green space-y-6">
<div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/10 pb-4">
<div>
<span class="text-xs font-black text-nico-green uppercase tracking-widest flex items-center gap-2">
<i class="fas fa-bullhorn"></i> Comunicados de la Junta Directiva
</span>
<h3 class="text-2xl font-black text-white uppercase mt-1">Anuncios Oficiales del Club</h3>
</div>
<span class="bg-black text-nico-green text-xs font-black px-3 py-1 rounded-full border border-nico-green/50 shadow-[0_0_15px_rgba(16,185,129,0.3)]">Temporada 2026</span>
</div>
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
<div class="lg:col-span-7 space-y-6" id="leftAnnCol">
<div class="w-full rounded-2xl overflow-hidden border-2 animated-border shine-sweep glass-card shadow-2xl p-4 sm:p-6 space-y-5">
<div class="w-full flex justify-center bg-black/40 rounded-xl p-2 border border-white/10 shadow-2xl">
<div x-html="renderMediaGlobal('media_comunicado', 'img/formulario.jpeg', 'w-full h-auto max-h-[480px] rounded-lg object-contain shadow-2xl hover:scale-[1.01] transition-transform duration-300', adminPadresVersion)"></div>
</div>
<div class="space-y-4">
<div>
<span class="px-3 py-1 bg-orange-500/20 text-orange-400 border border-orange-500/30 text-xs font-bold rounded-full uppercase tracking-wider inline-block shadow-[0_0_15px_rgba(255,85,0,0.2)]">Comunicados Oficiales 2026</span>
<h3 class="text-xl sm:text-2xl font-black text-white mt-2 uppercase tracking-wide">Academia Nico Sports</h3>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
<div class="bg-black/60 p-3.5 rounded-xl border border-white/10 space-y-1 hover:border-orange-500/40 hover:shadow-[0_0_15px_rgba(255,85,0,0.15)] transition-all">
<div class="flex items-center gap-2 text-orange-400 font-black text-xs uppercase tracking-wider">
<i class="fas fa-bullseye text-sm"></i><span>Misión</span>
</div>
<p class="text-gray-300 text-xs leading-relaxed" data-edit="mision">Formar niños y jóvenes a través del fútbol, desarrollando sus habilidades deportivas, valores y disciplina en un ambiente estructurado y positivo.</p>
</div>
<div class="bg-black/60 p-3.5 rounded-xl border border-white/10 space-y-1 hover:border-orange-500/40 hover:shadow-[0_0_15px_rgba(255,85,0,0.15)] transition-all">
<div class="flex items-center gap-2 text-orange-400 font-black text-xs uppercase tracking-wider">
<i class="fas fa-mountain text-sm"></i><span>Visión</span>
</div>
<p class="text-gray-300 text-xs leading-relaxed" data-edit="vision">Ser una academia reconocida a nivel regional por la formación integral, destacando por la calidad de entrenamientos y talento deportivo.</p>
</div>
</div>
<div class="space-y-2 pt-1">
<span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block">Pilares Fundamentales</span>
<div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
<div class="bg-black/50 border border-white/10 p-2.5 rounded-xl text-center hover:border-orange-500 hover:bg-orange-500/10 hover:shadow-[0_0_12px_rgba(255,85,0,0.25)] transition-all group/pillar">
<i class="fas fa-shield-alt text-orange-400 text-sm mb-1 block group-hover/pillar:scale-110 transition-transform"></i>
<span class="text-white text-[11px] font-bold uppercase tracking-wider block">Disciplina</span>
</div>
<div class="bg-black/50 border border-white/10 p-2.5 rounded-xl text-center hover:border-orange-500 hover:bg-orange-500/10 hover:shadow-[0_0_12px_rgba(255,85,0,0.25)] transition-all group/pillar">
<i class="fas fa-handshake text-orange-400 text-sm mb-1 block group-hover/pillar:scale-110 transition-transform"></i>
<span class="text-white text-[11px] font-bold uppercase tracking-wider block">Respeto</span>
</div>
<div class="bg-black/50 border border-white/10 p-2.5 rounded-xl text-center hover:border-orange-500 hover:bg-orange-500/10 hover:shadow-[0_0_12px_rgba(255,85,0,0.25)] transition-all group/pillar">
<i class="fas fa-users text-orange-400 text-sm mb-1 block group-hover/pillar:scale-110 transition-transform"></i>
<span class="text-white text-[11px] font-bold uppercase tracking-wider block">Trabajo</span>
</div>
<div class="bg-black/50 border border-white/10 p-2.5 rounded-xl text-center hover:border-orange-500 hover:bg-orange-500/10 hover:shadow-[0_0_12px_rgba(255,85,0,0.25)] transition-all group/pillar">
<i class="fas fa-fire text-orange-400 text-sm mb-1 block group-hover/pillar:scale-110 transition-transform"></i>
<span class="text-white text-[11px] font-bold uppercase tracking-wider block">Pasión</span>
</div>
</div>
</div>
<div class="pt-2">
<div class="relative group overflow-hidden rounded-xl p-[1px] bg-gradient-to-r from-orange-500 via-red-500 to-amber-500 shadow-[0_0_20px_rgba(255,85,0,0.35)]">
<div class="bg-black/90 p-3 rounded-[11px] flex items-center justify-between gap-3 relative z-10 backdrop-blur-md">
<div class="flex items-center gap-2.5">
<span class="w-2.5 h-2.5 rounded-full bg-orange-500 animate-pulse shadow-[0_0_10px_#ff5500]"></span>
<span class="text-white font-black text-xs sm:text-sm uppercase tracking-wider drop-shadow-[0_0_10px_rgba(255,255,255,0.5)]">¡Únete a la Familia Nico Sports!</span>
</div>
<span class="text-orange-400 text-xs font-black tracking-widest uppercase flex items-center gap-1 group-hover:translate-x-1 transition-transform">
Temporada 2026 <i class="fas fa-arrow-right text-[10px]"></i>
</span>
</div>
</div>
</div>
</div>
</div>
<div class="w-full rounded-2xl overflow-hidden border-2 animated-border shine-sweep glass-card shadow-2xl p-4 sm:p-6 space-y-5">
<div class="w-full flex justify-center bg-black/40 rounded-xl p-2 border border-white/10 shadow-2xl">
<div x-html="renderMediaGlobal('media_visoria', 'img/visoria.jpg', 'w-full h-auto max-h-[480px] rounded-lg object-contain shadow-2xl hover:scale-[1.01] transition-transform duration-300', adminPadresVersion)"></div>
</div>
<div class="space-y-4">
<div>
<span class="px-3 py-1 bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-bold rounded-full uppercase tracking-wider inline-block shadow-[0_0_15px_rgba(245,158,11,0.3)]">Visorías & Captación 2026</span>
<h3 class="text-xl sm:text-2xl font-black text-white mt-2 uppercase tracking-wide">Convocatoria Oficial de Talentos</h3>
<p class="text-gray-300 text-xs sm:text-sm mt-2 leading-relaxed" data-edit="visoria-desc">Sesiones de evaluación deportiva abiertas para seleccionar a los nuevos jugadores que formarán parte de nuestros planteles competitivos.</p>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
<div class="bg-black/60 p-3.5 rounded-xl border border-white/10 space-y-1">
<div class="flex items-center gap-2 text-amber-400 font-black text-xs uppercase tracking-wider">
<i class="fas fa-users"></i><span>Categorías</span>
</div>
<p class="text-white text-xs font-bold" data-edit="visoria-cats">Desde Sub-6 hasta Sub-14 y Mayor</p>
</div>
<div class="bg-black/60 p-3.5 rounded-xl border border-white/10 space-y-1">
<div class="flex items-center gap-2 text-amber-400 font-black text-xs uppercase tracking-wider">
<i class="fas fa-star"></i><span>Evaluación</span>
</div>
<p class="text-gray-300 text-xs leading-relaxed" data-edit="visoria-eval">Pruebas técnicas, perfilamiento y lectura táctica en campo.</p>
</div>
</div>
</div>
</div>
</div>
<div class="lg:col-span-5 flex flex-col gap-3 h-full" id="rightMediaCol">
<div class="glass-box p-4 rounded-3xl border border-white/10 space-y-3 relative overflow-hidden animated-border shadow-2xl flex-[3] flex flex-col">
<div class="absolute inset-0 bg-gradient-to-r from-nico-blue/10 via-black to-nico-orange/10 pointer-events-none"></div>
<div class="flex items-center justify-between gap-2 border-b border-white/10 pb-2 relative z-10 shrink-0">
<div>
<span class="text-[10px] font-black text-nico-orange uppercase tracking-widest flex items-center gap-1.5" data-edit="media-tag"><i class="fas fa-video"></i> Multimedia</span>
<h3 class="text-base font-black text-white uppercase mt-0.5" data-edit="media-title">Resumen Temporada</h3>
</div>
<span class="bg-black text-white text-[10px] font-black px-2.5 py-0.5 rounded-full border border-white/20">HD</span>
</div>
<div class="relative rounded-2xl overflow-hidden bg-black border border-white/20 shadow-2xl w-full flex-1 min-h-[140px]">
<div x-html="renderMediaGlobal('media_video_temporada', 'video/resumen-temporada.mp4', 'w-full h-full object-cover rounded-2xl', adminPadresVersion)"></div>
</div>
</div>
<div class="glass-box p-4 rounded-3xl border border-amber-400/40 space-y-3 relative overflow-hidden animated-border shadow-2xl group flex-[2] flex flex-col">
<div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 via-black to-nico-orange/10 pointer-events-none"></div>
<div class="flex items-center justify-between gap-2 border-b border-white/10 pb-2 relative z-10 shrink-0">
<div>
<span class="text-[10px] font-black text-amber-400 uppercase tracking-widest flex items-center gap-1.5" data-edit="gira-tag"><i class="fas fa-plane-departure"></i> Anuncio Futuro</span>
<h3 class="text-base font-black text-white uppercase mt-0.5" data-edit="gira-title">Próximamente Gira</h3>
</div>
<span class="bg-amber-500/20 text-amber-400 text-[10px] font-black px-2.5 py-0.5 rounded-full border border-amber-500/30 animate-pulse">2026</span>
</div>
<div class="w-full flex-1 min-h-[90px] rounded-2xl overflow-hidden border border-white/10 relative shadow-2xl bg-black">
<div class="absolute inset-0 z-0" x-html="renderMediaGlobal('media_gira', 'img/fooy.jpg', 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500', adminPadresVersion)"></div>
<div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent flex flex-col justify-end p-3 pointer-events-none [&>span]:pointer-events-auto">
<span class="text-[10px] font-black text-amber-400 uppercase tracking-widest drop-shadow" data-edit="gira-overlay1">Evento Especial</span>
<span class="text-white text-xs font-black uppercase drop-shadow" data-edit="gira-overlay2">Detalles Próximamente</span>
</div>
</div>
<div class="bg-black/60 p-2.5 rounded-xl border border-white/10 text-[11px] text-gray-300 relative z-10 flex items-center gap-2 shrink-0">
<i class="fas fa-bullhorn text-amber-400 text-xs shrink-0"></i>
<p class="leading-tight" data-edit="gira-nota">Atento a redes sociales para fechas e inscripciones.</p>
</div>
</div>
<div class="glass-box p-4 rounded-3xl border border-nico-orange/40 relative overflow-hidden animated-border shadow-2xl group flex-[3] flex flex-col space-y-3">
<div class="absolute inset-0 bg-gradient-to-br from-nico-orange/10 via-black to-nico-red/10 pointer-events-none"></div>
<div class="flex items-center justify-between gap-2 border-b border-white/10 pb-2 relative z-10 shrink-0">
<div>
<span class="text-[10px] font-black text-nico-orange uppercase tracking-widest flex items-center gap-1.5" data-edit="uniforme-tag"><i class="fas fa-shirt"></i> Indumentaria 2026</span>
<h3 class="text-base font-black text-white uppercase mt-0.5" data-edit="uniforme-title">Uniforme Oficial</h3>
</div>
<span class="bg-nico-orange/20 text-nico-orange text-[10px] font-black px-2.5 py-0.5 rounded-full border border-nico-orange/30">Oficial</span>
</div>
<div class="w-full flex-1 min-h-[140px] rounded-2xl overflow-hidden border border-white/10 relative shadow-2xl bg-gradient-to-b from-gray-300 to-gray-800">
<div class="absolute inset-0 z-0" x-html="renderMediaGlobal('media_uniforme', 'img/uniforme.png', 'w-full h-full object-contain object-center group-hover:scale-105 transition-transform duration-500', adminPadresVersion)"></div>
<div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent flex flex-col justify-end p-3 pointer-events-none [&>span]:pointer-events-auto">
<span class="text-[10px] font-black text-nico-orange uppercase tracking-widest drop-shadow" data-edit="uniforme-overlay1">G&M Deportes S.A.</span>
<span class="text-white text-xs font-black uppercase drop-shadow" data-edit="uniforme-overlay2">Equipación de Temporada</span>
</div>
</div>
<div class="bg-black/60 p-2.5 rounded-xl border border-white/10 text-[11px] text-gray-300 relative z-10 flex items-center gap-2 shrink-0">
<i class="fas fa-shield-halved text-nico-orange text-xs shrink-0"></i>
<p class="leading-tight" data-edit="uniforme-nota">Indumentaria oficial de entreno y competencia para las categorías del club.</p>
</div>
</div>
</div>
</div>
</div>
</div>