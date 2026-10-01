<?php
class FrontController {
    public static function render() {
        require_once __DIR__ . '/../conexion.php';
        $rol = rol_actual();

        // Sanitizar a la salida (defensa en profundidad anti-XSS)
        if (!function_exists('e')) {
            function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
        }

        include __DIR__ . '/../../private/views/layout/header.php';

        echo '<script>window.CSRF_TOKEN = ' . json_encode(csrf_token()) . ';</script>';

        include __DIR__ . '/../../private/views/tabs/historia.php';
        include __DIR__ . '/../../private/views/tabs/categorias.php';
        include __DIR__ . '/../../private/views/tabs/cuerpo_tecnico.php';
        include __DIR__ . '/../../private/views/tabs/torneo.php';
        include __DIR__ . '/../../private/views/tabs/vitrina.php';

        if ($rol === 'admin' || $rol === 'entrenador') {
            include __DIR__ . '/../../private/views/tabs/asistencia.php';
        }

        if ($rol === 'admin') {
            include __DIR__ . '/../../private/views/tabs/mensajes.php';
            include __DIR__ . '/../../private/views/tabs/pagos_club.php';
            include __DIR__ . '/../../private/views/tabs/entrenadores.php';
            include __DIR__ . '/../../private/views/tabs/admin.php';
        }

        if ($rol === 'admin' || $rol === 'padre') {
            include __DIR__ . '/../../private/views/tabs/portal_padres.php';
            include __DIR__ . '/../../private/views/tabs/buzon_padres.php';
        }

        echo '</div></main>';

        include __DIR__ . '/../../private/views/modals/login_modal.php';
        include __DIR__ . '/../../private/views/modals/imagen_modal.php';
        include __DIR__ . '/../../private/views/modals/jugador_modal.php';
        include __DIR__ . '/../../private/views/modals/staff_modal.php';
        include __DIR__ . '/../../private/views/modals/video_modal.php';
        include __DIR__ . '/../../private/views/modals/hoja_evaluacion_modal.php';

        if ($rol === 'admin' || $rol === 'entrenador') {
            include __DIR__ . '/../../private/views/modals/evaluador_modal.php';
        }

        if ($rol === 'admin') {
            include __DIR__ . '/../../private/views/modals/edit_media_modal.php';
        }

        echo '</body></html>';
    }
}
