<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link https://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'jwerkau17_wp1');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'I3[7ch|K+LVN,M+q*llB*f@F7m{*z7^&wD]z/vOc2ze~B+RHVmQ|7Iw-[@o8s!2O');
define('SECURE_AUTH_KEY',  'tKQr$fOp}Sez_YDU_cl(>-9l3zD$&m&z%0rF-}$q@tz%*WYi% wP}>c)HfDwL#3B');
define('LOGGED_IN_KEY',    'AEwY]A(SP&e*AJ:l,^Q+bWlnJ-,Ux$A{8zhRdtVU>MUR~h6B[+brJn(JgB7xrYcA');
define('NONCE_KEY',        '-G<UbEng{j?b~ug^e@ec!>x& .GISK^(J%P<|!|V}O+mV@I%Z6o6l9nz;L?2SG+^');
define('AUTH_SALT',        ',^eH2a,2Kt_Ly7%|2>Z!bk>_SocgMIa<$.^.Mxr5/Ucb+5M=Cen>|Ti(3K7W/%C%');
define('SECURE_AUTH_SALT', 'E&ghhycD+5~=*tcEH7;1-K(`k7=]9c|Zxd@ yzYI-2!Jc~G<l=5{Jjy;vL$O$H._');
define('LOGGED_IN_SALT',   '.LMP_>{?Y2Ade<a4^;^ (f}[?L ZPXdOYh[+K[^|+QTcXCO,0.GcoE3i7Q-5/F=J');
define('NONCE_SALT',       'J{J;RYHfrBNpgHaWQl->-naV#$]2?eZC).RoL0lIZ):(Rj5]a3M+v~GFYd^8JSW]');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
