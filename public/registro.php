<?php
/* public/registro.php — Subweb Blindada de Auto-Inscripción (Nombre Padre = Usuario + Pass 12+ Alfanumérica + Auto-Sub por Edad) */
if (file_exists(__DIR__ . '/core/conexion.php')) {
    require_once __DIR__ . '/core/conexion.php';
} else {
    require_once __DIR__ . '/../core/conexion.php';
}
header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet');

$token = trim((string)($_GET['t'] ?? ''));
$linkInfo = obtenerOGenerarTokenCiclo7Dias();

$linkValido = true; // El enlace oficial de inscripción ahora es permanente e indefinido
$mapaCats = obtenerMapaLabelsCategorias();
$expiraTexto = date('d/m/Y h:i A', (int)($linkInfo['expira'] ?? (time() + 7 * 86400)));
$csrfToken = $_SESSION['csrf_token'] ?? '';
$cspNonce  = $_SESSION['csp_nonce'] ?? '';
$nonceAttr = !empty($cspNonce) ? ' nonce="' . htmlspecialchars($cspNonce, ENT_QUOTES, 'UTF-8') . '"' : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow, noarchive, nosnippet" />
  <title>Inscripción Oficial de Jugadores — Academia Nico Sport</title>
  <link rel="icon" type="image/png" href="img/escudo.png" />
  <script<?= $nonceAttr ?> src="https://cdn.tailwindcss.com"></script>
  <script<?= $nonceAttr ?>>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            nico: {
              orange: '#FF5500',
              orangeDark: '#CC4400',
              green: '#00E676',
              greenDark: '#00B359',
              red: '#FF1744',
              blue: '#0A3D7A'
            }
          }
        }
      }
    };
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Montserrat', sans-serif; background: radial-gradient(circle at top, #141414 0%, #050505 100%); color: #fff; min-height: 100vh; }
    .glass-card { background: rgba(15, 15, 15, 0.92); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.12); }
    .reg-input {
      width: 100%; background: rgba(0, 0, 0, 0.75); border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 0.75rem; padding: 0.75rem 0.95rem; font-size: 0.85rem; color: #fff; font-weight: 600;
      transition: all 0.2s; outline: none;
    }
    .reg-input:focus { border-color: #FF5500; box-shadow: 0 0 0 3px rgba(255, 85, 0, 0.2); }
  </style>
</head>
<body class="flex items-center justify-center p-4 sm:p-6">

  <div class="w-full max-w-xl my-6">
    <!-- CABECERA DEL CLUB -->
    <div class="text-center mb-6 space-y-2">
      <div class="w-20 h-20 mx-auto rounded-full bg-black/80 border-2 border-nico-orange p-2 shadow-[0_0_25px_rgba(255,85,0,0.35)]">
        <img src="img/escudo.png" alt="Escudo Nico Sport" class="w-full h-full object-contain" />
      </div>
      <span class="inline-block text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full bg-nico-orange/15 text-nico-orange border border-nico-orange/30">
        Academia de Fútbol Nico Sport
      </span>
      <h1 class="text-2xl sm:text-3xl font-black uppercase tracking-tight text-white">Portal de Auto-Inscripción</h1>
    </div>

    <?php if (!$linkValido): ?>
      <!-- LINK EXPIRADO O ACCESO DIRECTO BLOQUEADO -->
      <div class="glass-card rounded-3xl p-6 sm:p-8 text-center border-nico-red/40 space-y-4 shadow-2xl">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-nico-red/15 border border-nico-red/30 flex items-center justify-center text-nico-red text-2xl">
          <i class="fas fa-shield-halved"></i>
        </div>
        <h2 class="text-xl font-black uppercase text-white">Acceso Restringido o Enlace Expirado</h2>
        <p class="text-xs sm:text-sm text-gray-400 leading-relaxed">
          No se puede acceder a este formulario sin un enlace oficial activo o el tiempo de vigencia (48 horas) ya finalizó.
          Solicita el enlace y el Token de Confirmación a la administración de la Academia Nico Sport.
        </p>
        <div class="pt-2">
          <a href="index.php" class="inline-flex items-center gap-2 bg-nico-orange hover:bg-nico-orangeDark text-white font-black text-xs uppercase px-6 py-3 rounded-xl transition-all">
            <i class="fas fa-home"></i> Ir a la Web del Club
          </a>
        </div>
      </div>
    <?php else: ?>
      <!-- FORMULARIO ACTIVO DE INSCRIPCIÓN -->
      <div id="bloqueFormulario" class="glass-card rounded-3xl p-5 sm:p-7 space-y-5 shadow-2xl border-nico-orange/30">
        <div class="flex items-center justify-between flex-wrap gap-2 pb-3 border-b border-white/10">
          <div>
            <h2 class="text-sm sm:text-base font-black uppercase text-white flex items-center gap-2">
              <i class="fas fa-user-plus text-nico-orange"></i> Registro Unificado: Jugador y Familia
            </h2>
            <p class="text-[11px] text-gray-400">Completa los datos para inscribir al jugador y crear tu acceso de padre/madre.</p>
          </div>
          <span class="text-[10px] font-bold bg-amber-400/10 text-amber-400 border border-amber-400/30 px-2.5 py-1 rounded-lg">
            <i class="far fa-clock mr-1"></i> Vence: <?= htmlspecialchars($expiraTexto) ?>
          </span>
        </div>

        <!-- Trampa Anti-Bots Invisible (Honeypot) -->
        <div style="position:absolute;left:-9999px;top:-9999px;opacity:0;pointer-events:none;" aria-hidden="true">
          <input type="text" id="regWebsiteHp" name="website_hp" tabindex="-1" autocomplete="off" value="" />
        </div>

        <!-- 1. DATOS DE LOS HIJOS / JUGADORES A INSCRIBIR -->
        <div class="p-4 rounded-2xl border border-nico-orange/30 bg-nico-orange/5 space-y-3">
          <div class="flex items-center justify-between flex-wrap gap-2">
            <p class="text-[11px] font-black text-nico-orange uppercase tracking-widest flex items-center gap-2">
              <i class="fas fa-running"></i> 1. Datos de los Hijos / Jugadores a Inscribir
            </p>
            <button type="button" id="btnAgregarHijo" class="text-[11px] font-bold text-nico-orange hover:text-white bg-nico-orange/20 hover:bg-nico-orange px-3 py-1.5 rounded-lg border border-nico-orange/40 transition-all flex items-center gap-1.5 cursor-pointer shadow-sm active:scale-95">
              <i class="fas fa-plus"></i> + Inscribir a otro hijo / hermano
            </button>
          </div>

          <div id="contenedorHijos" class="space-y-3">
            <!-- Se cargan dinámicamente los hijos aquí -->
          </div>

          <p class="text-[10px] text-amber-300/90 bg-amber-400/10 border border-amber-400/20 rounded-lg px-3 py-1.5 flex items-center gap-2">
            <i class="fas fa-shirt"></i>
            <span>La <strong>Categoría (Sub)</strong> se asigna sola según la edad de cada hijo y el <strong>Dorsal (#)</strong> lo asigna el Profesor en su categoría.</span>
          </p>
        </div>

        <!-- 2. CREDENCIALES DEL PADRE/MADRE (NOMBRE = USUARIO DE ACCESO) -->
        <div class="p-4 rounded-2xl border border-nico-green/30 bg-nico-green/5 space-y-3">
          <p class="text-[11px] font-black text-nico-green uppercase tracking-widest flex items-center gap-2">
            <i class="fas fa-user-shield"></i> 2. Datos y Acceso del Padre/Madre
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="text-[11px] font-bold text-gray-300 block mb-1">Nombre del Padre/Madre (Será tu Usuario) *</label>
              <input type="text" id="regPadreNombre" maxlength="80" class="reg-input focus:!border-nico-green" placeholder="Ej: Carlos Pérez" required />
            </div>
            <div>
              <label class="text-[11px] font-bold text-gray-300 block mb-1">Número de Teléfono (WhatsApp) *</label>
              <input type="tel" id="regTelefono" maxlength="20" class="reg-input focus:!border-nico-green" placeholder="Ej: 6900-0000" required />
            </div>
            <div class="sm:col-span-2">
              <label class="text-[11px] font-bold text-gray-300 block mb-1">Contraseña Segura de Acceso (Mín. 12 caracteres, letras y números) *</label>
              <div class="relative">
                <input type="password" id="regPassword" maxlength="64" class="reg-input focus:!border-nico-green pr-11" placeholder="Mínimo 12 caracteres combinando letras y números" required />
                <button type="button" id="btnToggleOjito" class="absolute inset-y-0 right-0 px-3.5 text-gray-400 hover:text-white flex items-center justify-center" title="Mostrar / Ocultar contraseña">
                  <i id="iconoOjito" class="fas fa-eye text-base"></i>
                </button>
              </div>
              <div class="flex items-center gap-4 mt-1.5 text-[10px] font-bold">
                <span id="chkPassLen" class="text-gray-500"><i class="fas fa-circle-dot mr-1"></i>Mínimo 12 caracteres</span>
                <span id="chkPassMix" class="text-gray-500"><i class="fas fa-circle-dot mr-1"></i>Letras y números</span>
              </div>
            </div>
          </div>
          <p class="text-[10px] text-gray-400 italic">
            <i class="fas fa-user-check text-nico-green mr-1"></i> Tu mismo <strong>Nombre de Padre/Madre</strong> será tu <strong>Usuario</strong> para iniciar sesión en la web.
          </p>
        </div>

        <!-- 3. CÓDIGO DE VERIFICACIÓN DEL CLUB (CICLO DE 7 DÍAS) -->
        <div class="p-4 rounded-2xl border border-amber-400/40 bg-amber-400/5 space-y-2">
          <p class="text-[11px] font-black text-amber-400 uppercase tracking-widest flex items-center gap-2">
            <i class="fas fa-key"></i> 3. Código de Verificación del Club (7 Días) *
          </p>
          <p class="text-[10px] text-gray-400">
            Ingresa el código oficial de confirmación semanal proporcionado por la Academia Nico Sport que acredita tu pertenencia al club:
          </p>
          <input type="text" id="regCodigoAcademia" maxlength="15" class="reg-input !border-amber-400/50 focus:!border-amber-400 text-center font-mono text-base uppercase tracking-widest text-amber-300" placeholder="Ej: NS-4829" autocomplete="off" required />
        </div>

        <div id="regMensajeError" class="hidden text-xs font-bold text-nico-red bg-nico-red/10 border border-nico-red/30 rounded-xl p-3 text-center"></div>

        <button type="button" id="btnEnviarRegistro" class="w-full bg-gradient-to-r from-nico-orange to-red-600 hover:from-nico-orangeDark hover:to-red-700 text-white font-black uppercase text-sm py-3.5 rounded-xl shadow-[0_0_20px_rgba(255,85,0,0.4)] transition-all flex items-center justify-center gap-2">
          <i class="fas fa-check-circle"></i> Completar Registro Oficial
        </button>
      </div>

      <!-- PANTALLA FINAL: TARJETA PARA CAPTURA DE PANTALLA / FOTO -->
      <div id="bloqueExitoCaptura" class="hidden glass-card rounded-3xl p-6 sm:p-8 border-2 border-nico-green shadow-[0_0_35px_rgba(0,230,118,0.25)] space-y-5">
        <div class="bg-amber-400 text-black font-black text-xs sm:text-sm uppercase tracking-wider p-3.5 rounded-2xl text-center shadow-lg flex items-center justify-center gap-2">
          <i class="fas fa-camera text-lg"></i>
          <span>¡Tómale Captura de Pantalla o Foto a tus Credenciales!</span>
        </div>

        <div class="text-center space-y-1">
          <span class="inline-flex items-center gap-1.5 text-nico-green font-black text-xs uppercase tracking-widest">
            <i class="fas fa-circle-check"></i> Inscripción Completada con Éxito
          </span>
          <h2 class="text-xl font-black text-white uppercase">Ficha de Acceso Familiar</h2>
        </div>

        <!-- Resumen de Jugadores Inscritos -->
        <div id="resContenedorHijos" class="space-y-2.5">
          <!-- Tarjetas de cada hijo inscrito generadas dinámicamente -->
        </div>

        <!-- Recuadro Grande de Usuario y Contraseña para la Foto -->
        <div class="bg-gradient-to-br from-nico-green/15 to-black border-2 border-nico-green rounded-2xl p-5 space-y-4 text-center">
          <p class="text-[11px] font-black uppercase tracking-widest text-nico-green">
            <i class="fas fa-key mr-1"></i> Tus Datos para Iniciar Sesión en la Web
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="bg-black/80 border border-white/15 rounded-xl p-3">
              <span class="text-[10px] text-gray-400 font-bold uppercase block">Usuario de Acceso (Tu Nombre)</span>
              <span id="resUsuario" class="text-lg sm:text-xl font-black text-nico-orange font-mono tracking-wide select-all"></span>
            </div>
            <div class="bg-black/80 border border-white/15 rounded-xl p-3">
              <span class="text-[10px] text-gray-400 font-bold uppercase block">Contraseña de Acceso</span>
              <span id="resPassword" class="text-lg sm:text-xl font-black text-nico-green font-mono tracking-wide select-all"></span>
            </div>
          </div>
          <p class="text-[10px] text-gray-300 font-semibold">
            Guarda esta imagen en tu galería. Con tu nombre y esta contraseña entrarás a la web normal del club.
          </p>
        </div>

        <a href="index.php" class="w-full bg-nico-green hover:bg-nico-greenDark text-black font-black uppercase text-xs sm:text-sm py-3.5 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
          <i class="fas fa-right-to-bracket"></i> Ir a la Web del Club para Iniciar Sesión
        </a>
      </div>

      <script<?= $nonceAttr ?>>
        const TOKEN_LINK = <?= json_encode($token) ?>;
        const CSRF_TOKEN = <?= json_encode($csrfToken) ?>;
        const MAPA_CATS  = <?= json_encode($mapaCats, JSON_UNESCAPED_UNICODE) ?>;
        let contadorHijos = 0;

        function calcularSubParaEdad(edad) {
          if (isNaN(edad) || edad < 3 || edad > 50) return null;
          const claves = Object.keys(MAPA_CATS);
          const numericas = [];
          claves.forEach(k => {
            const m = k.match(/^sub_?(\d+)$/i);
            if (m) numericas.push({ lim: parseInt(m[1], 10), key: k });
          });
          numericas.sort((a, b) => a.lim - b.lim);
          for (let i = 0; i < numericas.length; i++) {
            if (edad <= numericas[i].lim) return numericas[i].key;
          }
          return claves.includes('submayor') ? 'submayor' : (claves[claves.length - 1] || 'sub6');
        }

        function crearFilaHijo() {
          contadorHijos++;
          const idFila = 'hijo_card_' + contadorHijos;
          const div = document.createElement('div');
          div.id = idFila;
          div.className = 'p-3.5 rounded-xl bg-black/60 border border-white/10 space-y-3 relative hijo-card';

          div.innerHTML = `
            <div class="flex items-center justify-between">
              <span class="text-[11px] font-black text-nico-orange flex items-center gap-1.5">
                <i class="fas fa-child"></i> <span class="label-hijo-num">Hijo / Jugador</span>
              </span>
              <button type="button" data-del-fila="${idFila}" class="btn-eliminar-hijo text-red-400 hover:text-red-300 text-xs px-2 py-0.5 rounded bg-red-500/10 hover:bg-red-500/20 transition-colors cursor-pointer" title="Quitar este hijo">
                <i class="fas fa-trash"></i> Eliminar
              </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div class="sm:col-span-2">
                <label class="text-[10px] font-bold text-gray-300 block mb-1">Nombre y Apellido del Jugador *</label>
                <input type="text" maxlength="80" class="reg-input inp-hijo-nombre" placeholder="Ej: Mateo Pérez" required />
              </div>
              <div>
                <label class="text-[10px] font-bold text-gray-300 block mb-1">Edad (Años) *</label>
                <input type="number" min="3" max="50" data-edad-fila="${idFila}" class="reg-input inp-hijo-edad" placeholder="Ej: 10" required />
              </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="text-[10px] font-bold text-gray-300 block mb-1">Categoría (Asignada por Edad)</label>
                <input type="hidden" class="inp-hijo-cat" value="" />
                <div class="box-hijo-cat reg-input !bg-black/90 flex items-center justify-between text-gray-400 select-none">
                  <span class="txt-hijo-cat">Escribe la edad del jugador...</span>
                  <i class="fas fa-wand-magic-sparkles text-nico-orange text-xs"></i>
                </div>
              </div>
              <div>
                <label class="text-[10px] font-bold text-gray-300 block mb-1">Posición en Cancha *</label>
                <select class="reg-input inp-hijo-pos">
                  <option value="POR">Portero (POR)</option>
                  <option value="DEF">Defensa (DEF)</option>
                  <option value="MED" selected>Mediocampo (MED)</option>
                  <option value="DEL">Delantero (DEL)</option>
                </select>
              </div>
            </div>
          `;

          document.getElementById('contenedorHijos').appendChild(div);
          actualizarNumerosHijos();
        }

        function agregarHijoOnline() {
          crearFilaHijo();
        }

        function eliminarFilaHijo(idFila) {
          const cards = document.querySelectorAll('.hijo-card');
          if (cards.length <= 1) {
            alert('Debes registrar al menos un hijo.');
            return;
          }
          const el = document.getElementById(idFila);
          if (el) el.remove();
          actualizarNumerosHijos();
        }

        function actualizarNumerosHijos() {
          const cards = document.querySelectorAll('.hijo-card');
          cards.forEach((card, index) => {
            const lbl = card.querySelector('.label-hijo-num');
            if (lbl) lbl.textContent = `Hijo / Jugador #${index + 1}`;
            const btnDel = card.querySelector('.btn-eliminar-hijo');
            if (btnDel) {
              btnDel.style.display = cards.length > 1 ? 'inline-block' : 'none';
            }
          });
        }

        function autoAsignarSubFila(idFila) {
          const card = document.getElementById(idFila);
          if (!card) return;
          const inEdad = card.querySelector('.inp-hijo-edad');
          const inCat  = card.querySelector('.inp-hijo-cat');
          const txtCat = card.querySelector('.txt-hijo-cat');
          const boxCat = card.querySelector('.box-hijo-cat');

          const edad = parseInt(inEdad.value, 10);
          const sub = calcularSubParaEdad(edad);
          if (!sub) {
            inCat.value = '';
            txtCat.textContent = 'Escribe una edad válida (3 a 50)...';
            boxCat.className = 'box-hijo-cat reg-input !bg-black/90 flex items-center justify-between text-gray-400 select-none';
            return;
          }
          inCat.value = sub;
          txtCat.textContent = '✓ ' + (MAPA_CATS[sub] || sub.toUpperCase());
          boxCat.className = 'box-hijo-cat reg-input !bg-nico-orange/15 !border-nico-orange flex items-center justify-between text-nico-orange font-black select-none';
        }

        function verificarFuerzaPass() {
          const val = document.getElementById('regPassword').value;
          const okLen = val.length >= 12;
          const okMix = /[a-zA-Z]/.test(val) && /[0-9]/.test(val);
          const elLen = document.getElementById('chkPassLen');
          const elMix = document.getElementById('chkPassMix');
          elLen.className = okLen ? 'text-nico-green' : 'text-gray-500';
          elLen.innerHTML = (okLen ? '<i class="fas fa-check-circle mr-1"></i>' : '<i class="fas fa-circle-dot mr-1"></i>') + 'Mínimo 12 caracteres (' + val.length + '/12)';
          elMix.className = okMix ? 'text-nico-green' : 'text-gray-500';
          elMix.innerHTML = (okMix ? '<i class="fas fa-check-circle mr-1"></i>' : '<i class="fas fa-circle-dot mr-1"></i>') + 'Combinación de letras y números';
        }

        function toggleOjitoPass() {
          const inp = document.getElementById('regPassword');
          const ico = document.getElementById('iconoOjito');
          if (inp.type === 'password') {
            inp.type = 'text';
            ico.className = 'fas fa-eye-slash text-base text-nico-green';
          } else {
            inp.type = 'password';
            ico.className = 'fas fa-eye text-base';
          }
        }

        function detectarInyeccionCliente(str) {
          return /(\b(SELECT|UNION|INSERT|UPDATE|DELETE|DROP|ALTER|EXEC|SLEEP)\b|--|\/\*|\*\/|;|<script|javascript:|onerror=|onload=)/i.test(str);
        }

        async function enviarAutoRegistro() {
          const errBox = document.getElementById('regMensajeError');
          const btn = document.getElementById('btnEnviarRegistro');
          errBox.classList.add('hidden');

          const cards = document.querySelectorAll('.hijo-card');
          const hijos = [];

          for (let i = 0; i < cards.length; i++) {
            const card = cards[i];
            const nom = (card.querySelector('.inp-hijo-nombre').value || '').trim();
            const ed  = parseInt(card.querySelector('.inp-hijo-edad').value, 10);
            const cat = card.querySelector('.inp-hijo-cat').value;
            const pos = card.querySelector('.inp-hijo-pos').value;

            if (!nom) {
              errBox.textContent = `Por favor ingresa el nombre del Hijo / Jugador #${i + 1}.`;
              errBox.classList.remove('hidden');
              return;
            }
            if (detectarInyeccionCliente(nom)) {
              errBox.textContent = `Alerta de Seguridad: Se detectaron caracteres no permitidos en el Hijo #${i + 1}.`;
              errBox.classList.remove('hidden');
              return;
            }
            if (isNaN(ed) || ed < 3 || ed > 50 || !cat) {
              errBox.textContent = `Ingresa una edad válida (3 a 50 años) para el Hijo #${i + 1}.`;
              errBox.classList.remove('hidden');
              return;
            }
            hijos.push({ nombre: nom, edad: ed, catKey: cat, pos: pos });
          }

          if (hijos.length === 0) {
            errBox.textContent = 'Debes inscribir al menos a un hijo/jugador.';
            errBox.classList.remove('hidden');
            return;
          }

          const nombrePadre = document.getElementById('regPadreNombre').value.trim();
          const passVal     = document.getElementById('regPassword').value.trim();
          const telVal      = document.getElementById('regTelefono').value.trim();
          const codAcademia = document.getElementById('regCodigoAcademia').value.trim();

          if (!nombrePadre || !telVal || !passVal || !codAcademia) {
            errBox.textContent = 'Por favor completa todos los datos del padre/madre y el Token de la Academia.';
            errBox.classList.remove('hidden');
            return;
          }

          if (detectarInyeccionCliente(nombrePadre) || detectarInyeccionCliente(telVal)) {
            errBox.textContent = 'Alerta de Seguridad: Se detectaron caracteres no permitidos.';
            errBox.classList.remove('hidden');
            return;
          }

          if (passVal.length < 12 || !(/[a-zA-Z]/.test(passVal) && /[0-9]/.test(passVal))) {
            errBox.textContent = 'Por seguridad, la contraseña debe tener al menos 12 caracteres y combinar letras y números.';
            errBox.classList.remove('hidden');
            return;
          }

          const primerHijo = hijos[0];
          const payload = {
            token: TOKEN_LINK,
            website_hp: document.getElementById('regWebsiteHp').value,
            codigoAcademia: codAcademia,
            hijos: hijos,
            jugadorNombre: primerHijo.nombre,
            jugadorEdad: primerHijo.edad,
            catKey: primerHijo.catKey,
            jugadorPos: primerHijo.pos,
            padreNombre: nombrePadre,
            padreUsuario: nombrePadre,
            telefonoPadre: telVal,
            padrePassword: passVal
          };

          btn.disabled = true;
          btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando y Registrando...';

          try {
            const apiUrl = window.location.pathname.includes('/public/') ? '../api/registro_publico.php' : 'api/registro_publico.php';
            const resp = await fetch(apiUrl, {
              method: 'POST',
              credentials: 'include',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
              },
              body: JSON.stringify(payload)
            });
            let data = null;
            try {
              data = await resp.json();
            } catch (jsonErr) {
              throw new Error(`Respuesta del servidor (${resp.status}): ${await resp.text().catch(() => '')}`);
            }
            if (!data || !data.ok) {
              errBox.textContent = (data && data.error) ? data.error : 'No se pudo completar el registro.';
              errBox.classList.remove('hidden');
              btn.disabled = false;
              btn.innerHTML = '<i class="fas fa-check-circle"></i> Completar Registro Oficial';
              return;
            }

            const resContainer = document.getElementById('resContenedorHijos');
            resContainer.innerHTML = '';
            const listaFinal = (data.resumen && Array.isArray(data.resumen.hijos) && data.resumen.hijos.length > 0)
              ? data.resumen.hijos
              : [primerHijo];

            listaFinal.forEach((h, idx) => {
              const cardRes = document.createElement('div');
              cardRes.className = 'bg-black/70 border border-white/15 rounded-2xl p-3.5 space-y-1.5 text-xs';
              cardRes.innerHTML = `
                <div class="flex justify-between items-center border-b border-white/10 pb-1.5">
                  <span class="text-nico-orange font-black uppercase text-[11px]"><i class="fas fa-child mr-1"></i> Hijo #${idx + 1}:</span>
                  <span class="text-white font-black text-sm uppercase">${h.nombre}</span>
                </div>
                <div class="flex justify-between items-center text-[11px]">
                  <span class="text-gray-400 font-bold">Edad & Categoría:</span>
                  <span class="text-white font-bold">${h.edad} años • <span class="text-nico-orange font-black">${h.catLabel || h.catKey || ''}</span></span>
                </div>
                <div class="flex justify-between items-center text-[11px]">
                  <span class="text-gray-400 font-bold">Posición & Dorsal:</span>
                  <span class="text-amber-400 font-bold">${h.pos} • <span class="text-gray-300 italic">Dorsal por asignar</span></span>
                </div>
              `;
              resContainer.appendChild(cardRes);
            });

            document.getElementById('resUsuario').textContent = data.resumen.usuario;
            document.getElementById('resPassword').textContent = passVal || '••••••••';

            document.getElementById('bloqueFormulario').classList.add('hidden');
            document.getElementById('bloqueExitoCaptura').classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
          } catch (e) {
            errBox.textContent = e.message || 'Error de conexión. Intenta nuevamente.';
            errBox.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Completar Registro Oficial';
          }
        }

        document.addEventListener('DOMContentLoaded', () => {
          const btnAdd = document.getElementById('btnAgregarHijo');
          if (btnAdd) {
            btnAdd.addEventListener('click', agregarHijoOnline);
          }

          const inpPass = document.getElementById('regPassword');
          if (inpPass) {
            inpPass.addEventListener('input', verificarFuerzaPass);
          }

          const btnOjito = document.getElementById('btnToggleOjito');
          if (btnOjito) {
            btnOjito.addEventListener('click', toggleOjitoPass);
          }

          const btnSend = document.getElementById('btnEnviarRegistro');
          if (btnSend) {
            btnSend.addEventListener('click', enviarAutoRegistro);
          }

          const contHijos = document.getElementById('contenedorHijos');
          if (contHijos) {
            contHijos.addEventListener('click', (e) => {
              const btnDel = e.target.closest('[data-del-fila]');
              if (btnDel) {
                const targetId = btnDel.getAttribute('data-del-fila');
                if (targetId) eliminarFilaHijo(targetId);
              }
            });
            contHijos.addEventListener('input', (e) => {
              if (e.target.matches('[data-edad-fila]')) {
                const targetId = e.target.getAttribute('data-edad-fila');
                if (targetId) autoAsignarSubFila(targetId);
              }
            });
          }

          crearFilaHijo();
        });
      </script>
    <?php endif; ?>
    <!-- PIE DE PÁGINA INSTITUCIONAL Y LEY 81 -->
    <div class="mt-6 text-center space-y-1.5 px-2">
      <p class="text-[10px] text-gray-400 leading-relaxed">
        <i class="fas fa-user-lock text-nico-green mr-1"></i>
        <strong>Protección de Datos (Ley 81 de 2019 — Panamá):</strong> Tus datos y los de tu hijo(a) son de uso exclusivo deportivo interno de la Academia Nico Sport. Jamás se venden, prestan ni comparten con terceros, y son eliminados definitivamente si el jugador deja de pertenecer al club.
      </p>
      <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">
        &copy; 2008–<?= date('Y') ?> Academia de Fútbol Nico Sport
      </p>
    </div>
  </div>

</body>
</html>