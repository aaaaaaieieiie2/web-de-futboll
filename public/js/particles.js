/* ============================================================
   particles.js — EFECTO DE PARTÍCULAS INTERACTIVO ✔ REFACTORIZADO
   Optimizado para móviles (ahorro de batería), pantallas Retina
   y accesibilidad (respeta la reducción de movimiento).
============================================================ */
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('particleCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    
    let particles = [];
    let isAnimating = false;
    
    // UX / Accesibilidad: Respetar si el usuario prefiere reducir movimiento
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    
    // Soporte para pantallas Retina / Alta densidad (móviles)
    let dpr = window.devicePixelRatio || 1;

    function resizeCanvas() {
        dpr = window.devicePixelRatio || 1;
        // El canvas interno es más grande para verse nítido en móviles
        canvas.width = window.innerWidth * dpr;
        canvas.height = window.innerHeight * dpr;
        // El tamaño CSS sigue siendo el de la ventana
        canvas.style.width = window.innerWidth + 'px';
        canvas.style.height = window.innerHeight + 'px';
        
        ctx.setTransform(1, 0, 0, 1, 0, 0); // Resetear transformaciones previas
        ctx.scale(dpr, dpr);
    }
    
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    class Particle {
        constructor(x, y) {
            this.x = x;
            this.y = y;
            this.size = Math.random() * 4 + 2; // Ligeramente más pequeñas para mejor rendimiento
            this.speedX = (Math.random() - 0.5) * 6;
            this.speedY = (Math.random() - 0.5) * 6;
            // Paleta oficial de Nico Sport
            const colors = ['#FF5500', '#DC2626', '#10B981', '#0B4F9C', '#F59E0B', '#FFFFFF'];
            this.color = colors[Math.floor(Math.random() * colors.length)];
            this.alpha = 1;
        }
        
        update() {
            this.x += this.speedX;
            this.y += this.speedY;
            this.alpha -= 0.025; // Velocidad de desvanecimiento
        }
        
        draw() {
            ctx.save();
            ctx.globalAlpha = Math.max(0, this.alpha);
            ctx.fillStyle = this.color;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
        }
    }

    function animateParticles() {
        // OPTIMIZACIÓN CRÍTICA: Si no hay partículas, detener el loop.
        // Esto ahorra batería y CPU en los celulares de los padres.
        if (particles.length === 0) {
            isAnimating = false;
            ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);
            return; 
        }

        ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);

        // Iteración hacia atrás: permite usar .splice() de forma segura
        // sin saltarse partículas ni romper los índices del array.
        for (let i = particles.length - 1; i >= 0; i--) {
            particles[i].update();
            particles[i].draw();
            if (particles[i].alpha <= 0) {
                particles.splice(i, 1);
            }
        }
        
        requestAnimationFrame(animateParticles);
    }

    // Función global para disparar partículas desde app.js o botones
    window.triggerParticles = function(x, y) {
        if (prefersReducedMotion) return; // Accesibilidad
        
        for (let i = 0; i < 18; i++) {
            particles.push(new Particle(x, y));
        }
        
        // Solo iniciar el loop si estaba dormido
        if (!isAnimating) {
            isAnimating = true;
            animateParticles();
        }
    };

    // CLIC INTERACTIVO GLOBAL EN TODA LA PÁGINA
    window.addEventListener('click', (e) => {
        window.triggerParticles(e.clientX, e.clientY);
    });
});