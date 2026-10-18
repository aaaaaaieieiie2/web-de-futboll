<!-- ==================== FOOTER OFICIAL: COPYRIGHT & PROTECCIÓN DE DATOS (LEY 81) ==================== -->
<div x-data="{ modalLey81: false }">
  <footer class="mt-16 border-t border-white/10 bg-black/95 pt-8 pb-32 sm:pb-8 px-4 sm:px-8 relative z-10">
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6 text-center md:text-left">
      <!-- Identidad Institucional -->
      <div class="flex flex-col sm:flex-row items-center gap-3.5">
        <img src="./img/escudo.png" alt="Escudo Academia Nico Sport" class="w-11 h-11 object-contain rounded-full bg-black p-1 border border-nico-orange/40 shadow-lg" />
        <div>
          <p class="text-xs font-black text-white uppercase tracking-wider">
            &copy; 2008–<?= date('Y') ?> Academia de Fútbol Nico Sport
          </p>
          <p class="text-[11px] text-gray-400 mt-0.5">
            Formando campeones con valores, disciplina y pasión deportiva · <span class="text-amber-400 font-bold"><i class="fas fa-award"></i> G&amp;M Deportes S.A.</span>
          </p>
        </div>
      </div>

      <!-- Canales Oficiales: WhatsApp & Instagram -->
      <div class="flex items-center gap-2.5 shrink-0">
        <a href="https://wa.me/50763321779?text=%C2%A1Hola%20Academia%20Nico%20Sport!%20%E2%9A%BD%F0%9F%91%8B%20Deseo%20informaci%C3%B3n%20para%20inscribir%20a%20mi%20hijo(a).%20%C2%BFCu%C3%A1les%20son%20los%20requisitos,%20horarios%20y%20categor%C3%ADas%20disponibles%3F"
           :href="obtenerEnlaceWhatsappInscripcion()"
           target="_blank"
           rel="noopener noreferrer"
           class="inline-flex items-center gap-1.5 text-xs font-black px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 hover:bg-[#25D366] hover:text-black transition-all shadow-sm"
           title="Inscribir hijo por WhatsApp">
          <i class="fab fa-whatsapp text-sm"></i>
          <span>WhatsApp Inscripciones</span>
        </a>
        <a href="https://www.instagram.com/academia_nicosport/"
           :href="obtenerEnlaceInstagram()"
           target="_blank"
           rel="noopener noreferrer"
           class="inline-flex items-center gap-1.5 text-xs font-black px-3 py-1.5 rounded-xl bg-pink-500/10 text-pink-400 border border-pink-500/30 hover:bg-gradient-to-tr hover:from-[#f09433] hover:via-[#dc2743] hover:to-[#bc1888] hover:text-white transition-all shadow-sm"
           title="Instagram Oficial @academia_nicosport">
          <i class="fab fa-instagram text-sm"></i>
          <span>@academia_nicosport</span>
        </a>
      </div>

      <!-- Sello Ley 81 y Botón de Políticas de Privacidad -->
      <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0">
        <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider px-3 py-1.5 rounded-xl bg-nico-green/10 text-nico-green border border-nico-green/30">
          <i class="fas fa-user-lock"></i> Ley 81 de Panamá
        </span>
        <button type="button" @click="modalLey81 = true" class="text-xs font-black text-nico-orange hover:text-white underline underline-offset-4 transition-colors flex items-center gap-1.5">
          <i class="fas fa-scale-balanced"></i> Políticas Legales y Protección de Datos
        </button>
      </div>
    </div>

    <!-- Aviso Legal de Protección de Datos en el pie -->
    <div class="max-w-6xl mx-auto mt-4 pt-4 border-t border-white/5 text-center space-y-1">
      <p class="text-[10px] text-gray-400 leading-relaxed">
        <i class="fas fa-shield-halved text-nico-green mr-1"></i>
        <strong>Aviso Legal de Privacidad (Ley 81 de 2019 — República de Panamá):</strong> Los datos registrados en esta plataforma son de uso estrictamente deportivo y administrativo interno de la Academia Nico Sport.
        <strong>Jamás se venden, prestan ni comparten con terceros.</strong> Cuando un padre de familia o jugador deja de formar parte de nuestra academia, todos sus datos personales y los del menor son eliminados de forma total y definitiva de nuestros registros.
      </p>
    </div>
  </footer>

  <!-- ==================== WIDGET FLOTANTE DE CONTACTO RÁPIDO (WHATSAPP & INSTAGRAM) ==================== -->
  <aside x-show="!currentUser || (currentUser && currentUser.rol === 'padre')" aria-label="Canales de contacto y redes sociales" class="fixed bottom-24 md:bottom-6 right-4 sm:right-6 z-[80] flex flex-col items-end gap-3 pointer-events-auto select-none">
    <!-- Botón Flotante Instagram -->
    <a href="https://www.instagram.com/academia_nicosport/"
       :href="obtenerEnlaceInstagram()"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="Ver perfil oficial de Instagram de Academia Nico Sport"
       title="Instagram Oficial @academia_nicosport"
       class="group relative flex items-center justify-center w-12 h-12 sm:w-13 sm:h-13 rounded-full bg-gradient-to-tr from-[#f09433] via-[#dc2743] to-[#bc1888] text-white shadow-[0_8px_25px_rgba(220,39,67,0.45)] hover:shadow-[0_12px_30px_rgba(220,39,67,0.7)] hover:scale-110 active:scale-95 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-pink-500/50">
      <!-- Tooltip Desktop -->
      <span class="pointer-events-none absolute right-full mr-3 hidden md:group-hover:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-black/95 text-white text-xs font-black whitespace-nowrap shadow-xl border border-white/10 backdrop-blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-200">
        <i class="fab fa-instagram text-pink-400 text-sm"></i> @academia_nicosport
      </span>
      <i class="fab fa-instagram text-2xl group-hover:rotate-12 transition-transform duration-300"></i>
    </a>

    <!-- Botón Flotante WhatsApp Inscribir Hijo -->
    <a href="https://wa.me/50763321779?text=%C2%A1Hola%20Academia%20Nico%20Sport!%20%E2%9A%BD%F0%9F%91%8B%20Deseo%20informaci%C3%B3n%20para%20inscribir%20a%20mi%20hijo(a).%20%C2%BFCu%C3%A1les%20son%20los%20requisitos,%20horarios%20y%20categor%C3%ADas%20disponibles%3F"
       :href="obtenerEnlaceWhatsappInscripcion()"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="Inscribir a mi hijo por WhatsApp en la Academia Nico Sport"
       title="Inscribir a mi hijo por WhatsApp"
       class="group relative flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-[#25D366] hover:bg-[#20ba5a] text-white shadow-[0_10px_30px_rgba(37,211,102,0.5)] hover:shadow-[0_14px_36px_rgba(37,211,102,0.75)] hover:scale-105 active:scale-95 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-emerald-400/50">
      <!-- Indicador Ping Animado -->
      <span class="absolute -top-1 -right-1 flex h-4 w-4">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-300 opacity-80"></span>
        <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border-2 border-black"></span>
      </span>
      <!-- Tooltip Desktop -->
      <span class="pointer-events-none absolute right-full mr-3 hidden md:group-hover:inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-black/95 text-white text-xs font-black whitespace-nowrap shadow-2xl border border-emerald-500/40 backdrop-blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-200">
        <i class="fab fa-whatsapp text-emerald-400 text-base"></i>
        <span>¡Inscribir a mi hijo aquí! ⚽</span>
      </span>
      <i class="fab fa-whatsapp text-3xl sm:text-4xl text-white group-hover:scale-110 transition-transform duration-300"></i>
    </a>
  </aside>

  <!-- MODAL DE POLÍTICA DE PRIVACIDAD (LEY 81 DE PANAMÁ) -->
  <div x-show="modalLey81" x-transition class="fixed inset-0 z-[9999] bg-black/90 backdrop-blur-md flex items-center justify-center p-4 overflow-y-auto" style="display: none;">
    <div class="glass-box p-6 sm:p-8 rounded-3xl max-w-2xl w-full border border-nico-green/40 relative shadow-2xl space-y-5 text-left my-8" @click.outside="modalLey81 = false">
      <button type="button" @click="modalLey81 = false" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg">✕</button>

      <div class="flex items-center gap-3 border-b border-white/10 pb-4">
        <div class="w-12 h-12 rounded-2xl bg-nico-green/15 border border-nico-green/30 flex items-center justify-center text-nico-green text-xl shrink-0">
          <i class="fas fa-shield-halved"></i>
        </div>
        <div>
          <span class="text-[10px] font-black uppercase tracking-widest text-nico-green">Marco Legal y Protección de la Niñez</span>
          <h3 class="text-lg sm:text-xl font-black text-white uppercase">Políticas de Privacidad y Ley 81 de Panamá</h3>
        </div>
      </div>

      <div class="space-y-4 text-xs text-gray-300 leading-relaxed max-h-[62vh] overflow-y-auto pr-2 scroll-container-sm">
        <!-- CLÁUSULA 1: LEY 81 DE PANAMÁ -->
        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 space-y-1.5">
          <h4 class="font-black text-nico-green uppercase text-xs flex items-center gap-2">
            <i class="fas fa-gavel"></i> 1. Cumplimiento de la Ley 81 de 2019 (República de Panamá)
          </h4>
          <p>
            En estricto cumplimiento de la <strong>Ley 81 del 26 de marzo de 2019 sobre Protección de Datos Personales de la República de Panamá</strong>,
            toda la información registrada en esta plataforma (nombres, edad, categoría deportiva, teléfono, asistencia, evaluaciones formativas y comprobantes de pago)
            se utiliza <strong>única y exclusivamente</strong> para la gestión deportiva, formativa y administrativa interna de la <strong>Academia de Fútbol Nico Sport</strong>.
          </p>
        </div>

        <!-- CLÁUSULA 2: PROHIBICIÓN ABSOLUTA DE VENTA O PRÉSTAMO DE DATOS -->
        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 space-y-1.5">
          <h4 class="font-black text-white uppercase text-xs flex items-center gap-2">
            <i class="fas fa-ban text-nico-red"></i> 2. Prohibición Absoluta de Venta, Préstamo o Comercialización de Datos
          </h4>
          <p>
            Los datos personales de los padres de familia, tutores y deportistas menores de edad <strong>jamás se prestan, alquilan, venden ni se transfieren a terceros</strong>
            bajo ninguna circunstancia o motivo publicitario ajeno a las actividades deportivas oficiales del club.
          </p>
        </div>

        <!-- CLÁUSULA 3: ELIMINACIÓN TOTAL Y DERECHO AL OLVIDO AL RETIRARSE -->
        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 space-y-1.5">
          <h4 class="font-black text-amber-400 uppercase text-xs flex items-center gap-2">
            <i class="fas fa-trash-can"></i> 3. Eliminación Total y Definitiva de Datos al Retirarse del Club
          </h4>
          <p>
            Cuando un padre de familia, tutor o jugador <strong>deja de pertenecer a la Academia Nico Sport</strong>, su cuenta familiar y todos sus registros vinculados
            —incluyendo los datos de contacto del acudiente y todo el historial formativo, evaluaciones, asistencias y fichas del hijo(a)— <strong>son eliminados de forma total y definitiva</strong>
            de nuestras bases de datos activas en garantía de su derecho de cancelación y protección integral.
          </p>
        </div>

        <!-- CLÁUSULA 4: SEGURIDAD Y PROTECCIÓN DE DATOS DE MENORES -->
        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 space-y-1.5">
          <h4 class="font-black text-nico-orange uppercase text-xs flex items-center gap-2">
            <i class="fas fa-user-shield"></i> 4. Seguridad, Confidencialidad y Resguardo Técnico
          </h4>
          <p>
            La plataforma cuenta con cifrado, contraseñas protegidas con algoritmos de alta seguridad y controles estrictos de acceso para garantizar que únicamente el personal autorizado de la academia y cada familia individual puedan consultar su propia información deportiva.
          </p>
        </div>

        <!-- CLÁUSULA 5: REGISTRO DE EVENTOS DE SEGURIDAD Y HONEYPOT (DEFENSA TÉCNICA PASIVA) -->
        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 space-y-1.5">
          <h4 class="font-black text-cyan-400 uppercase text-xs flex items-center gap-2">
            <i class="fas fa-network-wired"></i> 5. Registro Técnico de Seguridad, Firewall y Detección Pasiva (Honeypot)
          </h4>
          <p>
            En virtud del interés legítimo y el deber legal de resguardar los datos de los menores de edad, el sistema registra direcciones IP y eventos técnicos ante intentos de intrusión, fuerza bruta o interacción con rutas trampa (honeypot). Estos registros de seguridad se conservan con retención temporal estrictamente limitada a la mitigación de ataques y defensa técnica pasiva, bajo acceso restringido y sin fines comerciales ni transferencia externa.
          </p>
        </div>
      </div>

      <div class="pt-2 border-t border-white/10 flex justify-end">
        <button type="button" @click="modalLey81 = false" class="bg-nico-green hover:bg-emerald-600 text-black font-black uppercase text-xs px-6 py-2.5 rounded-xl transition-all">
          Entendido y Conforme
        </button>
      </div>
    </div>
  </div>
</div>