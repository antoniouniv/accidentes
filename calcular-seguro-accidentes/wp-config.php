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
define('DB_NAME', 'calcular_seguro_accidentes');

/** Tu nombre de usuario de MySQL */
define('DB_USER', 'root');

/** Tu contraseña de MySQL */
define('DB_PASSWORD', '');

/** Host de MySQL (es muy probable que no necesites cambiarlo) */
define('DB_HOST', 'localhost');

/** Codificación de caracteres para la base de datos. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'y(7<wKzNt#4=3+`kU)C0}S`}u}xTK/ZE{=yD %CDcBFoJyL//yXsQ-On.5xYa?nT');
define('SECURE_AUTH_KEY',  'L[i_4+|bkA4kY3=<d|*Oyfs>#Z)j+1gi{eUwTl_$?`UQ73w63[!=@ceZhm,-88Hr');
define('LOGGED_IN_KEY',    ')b%bWM;?Yvq!G? Myj^@u,KKoefWUi/d- .!+z*$gT_o@]LQu0g-e;T60|p+L[7k');
define('NONCE_KEY',        'jZo-uB]X n[-2eu9E&ZHCP$FXJ*q_F|@x:NYtJggKdg+#JD5.k)tAYI<XW/-Rs,|');
define('AUTH_SALT',        'jzSX-K-$:3e;VTZ<=JGUjo`vqhlaS.%:h->Jqe=iO]v>lMW}kR(o?E:LUz:og@?b');
define('SECURE_AUTH_SALT', '%bA*sAj8w_fe:q$:OUi+i8+kObQWiux%+P4%/C-jolz}`_^kO|oK#IJoZ)vqDrRX');
define('LOGGED_IN_SALT',   'tn/Q-w%Z]R+OAmR:0+|t_/%Vju7Wr)}N=&vNSLODF[b/!m|^aU-`sZMvp~~-ay4z');
define('NONCE_SALT',       ' i&4[?qQNYXv9|]U[UOFr.=<^ /]>E]466+47[T)laN$<DMi5cF>RH0{f9 qVG|T');
/**#@-*/

/**
 * Prefijo de la base de datos de WordPress.
 *
 * Cambia el prefijo si deseas instalar multiples blogs en una sola base de datos.
 * Emplea solo números, letras y guión bajo.
 */
$table_prefix  = 'TgR_';


/**
 * Para desarrolladores: modo debug de WordPress.
 *
 * Cambia esto a true para activar la muestra de avisos durante el desarrollo.
 * Se recomienda encarecidamente a los desarrolladores de temas y plugins que usen WP_DEBUG
 * en sus entornos de desarrollo.
 */
define('WP_DEBUG', false);

define('ACL_AUTHORIZATION',false);
define('AUTH_USER','');
define('AUTH_PASS','');

define('DISABLE_WP_CRON', true);

define('WP_HOME','http://madppvmatser01/calcular-seguro-accidentes');
define('WP_SITEURL','http://madppvmatser01/calcular-seguro-accidentes');

define('TC_SUBDIRECTORY','/calcular-seguro-accidentes');

/*Publicación final */

define('INI_FOLDER','previs');

define('END_FOLDER','final');

define('END_CONFIG_FOLDER','final/es/app/pages/');

define('RENAME_UPLOADS', 'media');

define('END_MEDIA_FOLDER',END_FOLDER.'/es/'.RENAME_UPLOADS);

define('PUBLICAR', 'publicar/');


#define('DISALLOW_FILE_MODS', true);
define('FS_METHOD', 'direct');

/* ¡Eso es todo, deja de editar! Feliz blogging */

/** WordPress absolute path to the Wordpress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

define( 'WP_MAX_MEMORY_LIMIT', '256M' );
define( 'WP_MEMORY_LIMIT', '256M' );

define('TC','previs/es/');

define('ALLOW_UNFILTERED_UPLOADS', true);

/** Required Code for showing correct IP address */

if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$xffaddrs = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
$_SERVER['REMOTE_ADDR'] = $xffaddrs[0];
}

define('HEADER_NAME', 'headerQuote-v1.0.0');
define('FOOTER_NAME', 'footerQuote-v3.0.0');

