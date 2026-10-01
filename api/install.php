<?php
/* api/install.php — Instala la base de datos nicosport en XAMPP o Docker (idempotente) */
header('Content-Type: text/html; charset=utf-8');

/* core/conexion.php hace die(json) si la BD aún no existe; capturamos ese fallo
   con un buffer para poder continuar con la instalación desde cero. */
$__abortando = true;
register_shutdown_function(function () use (&$__abortando) {
    if ($__abortando) { ob_end_clean(); }
});
ob_start();
require_once __DIR__ . '/../core/conexion.php'; // define cargar_credenciales()
$__abortando = false;
ob_end_clean();

// Credenciales oficiales del sistema (variables de entorno / core/.env)
$creds = cargar_credenciales();

// Conexión sin seleccionar BD para poder crearla
$c = @new mysqli($creds['host'], $creds['user'], $creds['pass']);
if ($c->connect_errno) {
    http_response_code(500);
    die('<h1 style="font-family:sans-serif">❌ Conexión fallida: ' . htmlspecialchars($c->connect_error) . ' — Inicia MySQL en tu entorno.</h1>');
}
$c->set_charset('utf8mb4');
if (!$c->query("CREATE DATABASE IF NOT EXISTS `{$c->real_escape_string($creds['name'])}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    http_response_code(500);
    die('<h1 style="font-family:sans-serif">❌ No se pudo crear la base de datos: ' . htmlspecialchars($c->error) . '</h1>');
}
$c->select_db($creds['name']);

$sqls = [
"CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY, usuario VARCHAR(80) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL,
  rol VARCHAR(20) NOT NULL, nombre VARCHAR(150) NOT NULL, hijo_cat VARCHAR(40) NULL, hijo_id INT NULL,
  cat_key VARCHAR(40) NULL, cat_label VARCHAR(80) NULL, telefono VARCHAR(20) NULL, metodo_pago VARCHAR(20) DEFAULT 'linea', fecha_vencimiento VARCHAR(12) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS evaluaciones (
  id INT AUTO_INCREMENT PRIMARY KEY, cat_key VARCHAR(40) NOT NULL, player_id INT NOT NULL, fecha VARCHAR(20) DEFAULT '',
  tecnicos TEXT, tacticos TEXT, fisicos TEXT, actitudinales TEXT, UNIQUE KEY uq_eval (cat_key, player_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS asistencias (
  id INT AUTO_INCREMENT PRIMARY KEY, cat_key VARCHAR(40) NOT NULL, fecha VARCHAR(12) NOT NULL,
  player_id INT NOT NULL, estado VARCHAR(15) NOT NULL, UNIQUE KEY uq_asis (cat_key, fecha, player_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS encuestas (
  id INT AUTO_INCREMENT PRIMARY KEY, padre VARCHAR(150), fecha VARCHAR(20), aspectos TEXT,
  gusta_mas TEXT, mejorar TEXT, crecimiento TEXT, crecimiento_why TEXT,
  recomienda TEXT, recomienda_why TEXT, observaciones TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS player_stats (
  id INT AUTO_INCREMENT PRIMARY KEY, cat_key VARCHAR(40) NOT NULL, player_id INT NOT NULL,
  goles INT DEFAULT 0, asistencias INT DEFAULT 0, mvp INT DEFAULT 0, updated_at VARCHAR(40) DEFAULT '',
  UNIQUE KEY uq_stats (cat_key, player_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS jugadores (
  id INT NOT NULL, cat_key VARCHAR(40) NOT NULL, name VARCHAR(150) NOT NULL, dorsal INT NOT NULL,
  pos VARCHAR(10) DEFAULT '', foto LONGTEXT NULL, PRIMARY KEY (cat_key, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS trofeos_custom (
  id VARCHAR(60) PRIMARY KEY, nombre VARCHAR(150), cat_key VARCHAR(40), categoria VARCHAR(120),
  anio VARCHAR(10), icono VARCHAR(60), color VARCHAR(60), descripcion TEXT, evaluacion TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS trofeos_ocultos (id VARCHAR(60) PRIMARY KEY) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS trofeos_editados (id VARCHAR(60) PRIMARY KEY, data LONGTEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS textos (clave VARCHAR(80) PRIMARY KEY, html LONGTEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS documentos (doc_key VARCHAR(40) PRIMARY KEY, value LONGTEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS staff (
  id INT PRIMARY KEY, nombre VARCHAR(150), cargo VARCHAR(150), licencia VARCHAR(150), exp VARCHAR(150),
  especialidad TEXT, bio TEXT, foto_icon VARCHAR(60), color VARCHAR(120), orden INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS categorias (
  id INT AUTO_INCREMENT PRIMARY KEY, cat_key VARCHAR(40) UNIQUE, label VARCHAR(80),
  descripcion TEXT, core TINYINT DEFAULT 0, orden INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS jugadores_reciclados (
  cat_key VARCHAR(40) NOT NULL, dorsal INT NOT NULL, data LONGTEXT, guardado_en VARCHAR(40),
  PRIMARY KEY (cat_key, dorsal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS pagos_club (
  id BIGINT PRIMARY KEY, padre_id INT, padre_nombre VARCHAR(150), concepto VARCHAR(80), monto DECIMAL(10,2),
  fecha VARCHAR(12), referencia VARCHAR(150), metodo VARCHAR(20), estado VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS comprobantes (
  id BIGINT PRIMARY KEY, padre_id INT, padre_nombre VARCHAR(150), concepto VARCHAR(80), monto DECIMAL(10,2),
  fecha VARCHAR(12), referencia VARCHAR(150), observaciones TEXT, comprobante LONGTEXT, estado VARCHAR(20) DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS pagos_padre (
  id BIGINT PRIMARY KEY, padre_id INT, concepto VARCHAR(80), monto DECIMAL(10,2), fecha VARCHAR(12),
  referencia VARCHAR(150), observaciones TEXT, comprobante LONGTEXT, estado VARCHAR(20) DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS pagos_plataforma (
  id BIGINT PRIMARY KEY, tipo VARCHAR(20), monto DECIMAL(10,2), referencia VARCHAR(150), fecha VARCHAR(12),
  observaciones TEXT, comprobante LONGTEXT, estado VARCHAR(20) DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

/* ===== SEMILLAS ===== */
"INSERT IGNORE INTO usuarios (id,usuario,password,rol,nombre,hijo_cat,hijo_id,cat_key,cat_label) VALUES
 (1,'admin','nico2026','admin','Cuerpo Técnico ANS',NULL,NULL,NULL,NULL),
 (2,'familia.aguirre','1234','padre','Familia Aguirre','sub12',4,NULL,NULL),
 (3,'dt.sub6','dt2026','entrenador','DT Sub-6',NULL,NULL,'sub6','Sub-6')",

"INSERT IGNORE INTO categorias (cat_key,label,descripcion,core,orden) VALUES
 ('sub6','Sub-6','Iniciación (4 a 6 años) — Ludotécnica',1,1),
 ('sub12','Sub-12','Desarrollo (10 a 12 años) — Formativa',1,2),
 ('sub16','Sub-16','Competición (14 a 16 años) — Alto rendimiento',1,3),
 ('submayor','Sub-Mayor','Futsal & Tecnificación — Especialización',1,4)",

"INSERT IGNORE INTO staff (id,nombre,cargo,licencia,exp,especialidad,bio,foto_icon,color,orden) VALUES
 (1,'Profe. Niko','Director Técnico Principal & Fundador','Licencia A FEPAFUT','18 Años de Experiencia','Financiera','Fundador de la Academia Nico Sport en 2008. Ha formado a más de 500 jóvenes deportistas y obtenido múltiples títulos en torneos locales.','fa-user-tie','from-nico-orange via-red-600 to-amber-600',1),
 (2,'Prof. Luis Muñoz','Preparador Físico & psicológico','Grado en Ciencias del Deporte','10 Años de Experiencia','Resistencia Aeróbica, Potencia y Prevención de Lesiones','Especialista en acondicionamiento físico adaptado a fútbol base y juvenil.','fa-stopwatch-20','from-nico-green to-emerald-700',2),
 (3,'Abdiel Aguirre','Entrenador de Táctico','Próximamente...','9 Años de Experiencia en el deporte','Reflejos, Juego Aéreo y Salida con los Pies','Jugador amateur enfocado en perfeccionar la técnica de juego, posicionamiento en la cancha y liderazgo.','fa-hands-glove','from-nico-blue to-cyan-700',3),
 (4,'Fernado','Psicóloga Deportiva & Formación Integral','Próximamente...','5 Años de Experiencia','Gestión de la Frustración, Trabajo en Equipo y Concentración','Encargada del desarrollo mental y emocional de los alumnos.','fa-brain','from-purple-600 to-indigo-800',4)",

"INSERT IGNORE INTO jugadores (id,cat_key,name,dorsal,pos) VALUES
 (1,'sub6','M. Aguirre',1,'POR'),(2,'sub6','D. Castro',2,'DEF'),(3,'sub6','S. Pérez',3,'DEF'),(4,'sub6','A. Ruiz',4,'MED'),(5,'sub6','L. Torres',5,'DEL'),(6,'sub6','J. Gómez',6,'DEL'),(7,'sub6','K. Morales',7,'MED'),(8,'sub6','E. Ríos',8,'DEF'),(9,'sub6','C. Vega',9,'DEL'),(10,'sub6','B. Soto',10,'MED'),(11,'sub6','R. Pinto',11,'POR'),(12,'sub6','G. Navas',12,'DEF'),(13,'sub6','H. Molina',13,'MED'),(14,'sub6','J. Cedeño',14,'DEL'),(15,'sub6','F. Benítez',15,'DEF'),(16,'sub6','T. Ortega',16,'MED'),(17,'sub6','N. Duarte',17,'DEL'),(18,'sub6','P. Acuña',18,'MED'),(19,'sub6','I. Vargas',19,'DEF'),(20,'sub6','O. Espino',20,'DEL')",

"INSERT IGNORE INTO jugadores (id,cat_key,name,dorsal,pos) VALUES
 (1,'sub12','E. Morales',1,'POR'),(2,'sub12','C. Herrera',2,'DEF'),(3,'sub12','J. Rivera',3,'MED'),(4,'sub12','M. Aguirre',4,'MED'),(5,'sub12','A. Díaz',5,'DEL'),(6,'sub12','F. Silva',6,'DEL'),(7,'sub12','L. Campos',7,'DEF'),(8,'sub12','S. Ramos',8,'MED'),(9,'sub12','D. Paredes',9,'DEL'),(10,'sub12','K. Nuñez',10,'POR'),(11,'sub12','R. Castillo',11,'DEF'),(12,'sub12','G. Mendoza',12,'MED'),(13,'sub12','H. Rojas',13,'DEL'),(14,'sub12','B. Montero',14,'DEF'),(15,'sub12','V. Quedo',15,'MED'),(16,'sub12','J. León',16,'DEL'),(17,'sub12','P. Sosa',17,'DEF'),(18,'sub12','N. Garza',18,'MED'),(19,'sub12','T. Ponce',19,'DEL'),(20,'sub12','W. Cordero',20,'POR')",

"INSERT IGNORE INTO jugadores (id,cat_key,name,dorsal,pos) VALUES
 (1,'sub16','G. Vargas',1,'POR'),(2,'sub16','K. Ramos',2,'DEF'),(3,'sub16','R. Delgado',3,'DEF'),(4,'sub16','B. Martínez',4,'MED'),(5,'sub16','H. Salazar',5,'DEL'),(6,'sub16','N. Vega',6,'DEL'),(7,'sub16','J. Alveo',7,'MED'),(8,'sub16','S. Bultrón',8,'DEF'),(9,'sub16','L. Samaniego',9,'DEL'),(10,'sub16','D. Quintero',10,'MED'),(11,'sub16','M. Araúz',11,'POR'),(12,'sub16','C. Espinoza',12,'DEF'),(13,'sub16','A. Batista',13,'MED'),(14,'sub16','E. Becker',14,'DEL'),(15,'sub16','O. Jiménez',15,'DEF'),(16,'sub16','F. Wong',16,'MED'),(17,'sub16','I. Pimentel',17,'DEL'),(18,'sub16','Y. Mosquera',18,'DEF'),(19,'sub16','X. Valdés',19,'MED'),(20,'sub16','Z. Guerra',20,'DEL')",

"INSERT IGNORE INTO jugadores (id,cat_key,name,dorsal,pos) VALUES
 (1,'submayor','V. Córdoba',1,'POR'),(2,'submayor','O. Paredes',2,'DEF'),(3,'submayor','I. Navarro',3,'DEF'),(4,'submayor','E. Castillo',4,'MED'),(5,'submayor','T. Meza',5,'MED'),(6,'submayor','P. Fuentes',6,'DEL'),(7,'submayor','R. Jaramillo',7,'DEL'),(8,'submayor','K. Grimaldo',8,'MED'),(9,'submayor','L. Melgar',9,'DEF'),(10,'submayor','J. Caballero',10,'POR'),(11,'submayor','S. Bernal',11,'DEF'),(12,'submayor','A. Cárdenas',12,'MED'),(13,'submayor','D. Hurtado',13,'DEL'),(14,'submayor','F. Cedeño',14,'DEF'),(15,'submayor','M. Patiño',15,'MED'),(16,'submayor','G. Solís',16,'DEL'),(17,'submayor','H. Macías',17,'DEF'),(18,'submayor','N. Broce',18,'MED'),(19,'submayor','B. Ureña',19,'DEL'),(20,'submayor','J. Vergara',20,'MED')"
];

$errores = 0;
foreach ($sqls as $sql) {
    if ($c->query($sql) === false) { $errores++; echo '<p style="color:red">Œ ' . htmlspecialchars($c->error) . '</p>'; }
}
echo '<h1 style="font-family:sans-serif">' . ($errores === 0 ? '✅ Base de datos <code>nicosport</code> instalada correctamente' : 'š ï¸ Instalación con errores') . '</h1>';
echo '<p style="font-family:sans-serif">Ya puedes abrir <a href="http://localhost/nicosport/">http://localhost/nicosport/</a> — la app ahora guarda todo en MySQL.</p>';