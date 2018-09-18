<?php
/** 
 * Configuración básica de WordPress.
 *
 * Este archivo contiene las siguientes configuraciones: ajustes de MySQL, prefijo de tablas,
 * claves secretas, idioma de WordPress y ABSPATH. Para obtener más información,
 * visita la página del Codex{@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} . Los ajustes de MySQL te los proporcionará tu proveedor de alojamiento web.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** Ajustes de MySQL. Solicita estos datos a tu proveedor de alojamiento web. ** //
/** El nombre de tu base de datos de WordPress */
define('DB_NAME', 'mys');

/** Tu nombre de usuario de MySQL */
define('DB_USER', 'root');

/** Tu contraseña de MySQL */
define('DB_PASSWORD', '');

/** Host de MySQL (es muy probable que no necesites cambiarlo) */
define('DB_HOST', 'localhost');

/** Codificación de caracteres para la base de datos. */
define('DB_CHARSET', 'utf8mb4');

/** Cotejamiento de la base de datos. No lo modifiques si tienes dudas. */
define('DB_COLLATE', '');

/**#@+
 * Claves únicas de autentificación.
 *
 * Define cada clave secreta con una frase aleatoria distinta.
 * Puedes generarlas usando el {@link https://api.wordpress.org/secret-key/1.1/salt/ servicio de claves secretas de WordPress}
 * Puedes cambiar las claves en cualquier momento para invalidar todas las cookies existentes. Esto forzará a todos los usuarios a volver a hacer login.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'LI@{=I8;HLXtgK*9/@5&@KgA1&SH,k*0nb?vx;Qg[dr V}a~S5R1c~}4x![Bs6#l');
define('SECURE_AUTH_KEY', 'U~ )/=Kltb_5h3!Jfp;`ne@yL3fn@u+3@9Y](-fJ.YYHDA;7M(~]>|2EYY(-ZU]z');
define('LOGGED_IN_KEY', 't<Hn`>gHNh.<qw^,z]weE4NU|G.2y06U4<50v`Hh+e}t+=F}]..L~1]{o|O|S=(f');
define('NONCE_KEY', '6D8!9,;%5^]$<$*4#x0fe*E6xx6c+(rIm]^e=vo[f%`%<]P$r}X1%Le:Rpi4jH41');
define('AUTH_SALT', 'p3]%Ggbw+0]Ys]emk*f;<#?@oEJfi^ga?1Bi.`yE]H}MSGeRK*:#g6Afhn2C)<r7');
define('SECURE_AUTH_SALT', '!NWBrpt>uW%*+pZN5&4#xv2Wjp; e5tvLNf?ptu&MqYU/7sgB!$?@NgcrnAN[-Ql');
define('LOGGED_IN_SALT', 'TlU?t@[j <wYO!uo3F_b;.dH^I}$2=[{zGX)4XE%#77hgYM:g|I7O{x+OUD=a-l|');
define('NONCE_SALT', 'kiWy!8~,qBV+c|AuU{z[>V+B&nF3*se-6dO-#//_vs!_?VMTFtNGc#m3)`N?_E@d');

/**#@-*/

/**
 * Prefijo de la base de datos de WordPress.
 *
 * Cambia el prefijo si deseas instalar multiples blogs en una sola base de datos.
 * Emplea solo números, letras y guión bajo.
 */
$table_prefix  = 'wp_';


/**
 * Para desarrolladores: modo debug de WordPress.
 *
 * Cambia esto a true para activar la muestra de avisos durante el desarrollo.
 * Se recomienda encarecidamente a los desarrolladores de temas y plugins que usen WP_DEBUG
 * en sus entornos de desarrollo.
 */
define('WP_DEBUG', false);

/* ¡Eso es todo, deja de editar! Feliz blogging */

/** WordPress absolute path to the Wordpress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

