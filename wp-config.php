<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'njoy_web' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '=0D`>i,3n.|%RGl2@!4u@+mnF^BND:cV5gG<)mo:5i?n+%S(M4W/fmV.B|Oe8x`,' );
define( 'SECURE_AUTH_KEY',  '1^|e0;zsS*xwie|(VCC{5AMHio_v&{@zvuP[=f|+3rER>N6~ff)UQ&d@&+zwz^di' );
define( 'LOGGED_IN_KEY',    '=oB/|F*_?rOGzZ%G|V#k=bvu.h_oq|-*U?Kn6z)MR>,lfDI}ek41Ccj)R!Kcxf+3' );
define( 'NONCE_KEY',        'IENiZ]kx#Bmz)l.mF?S jW(-OjlFt}x{>qcEm/Rj|#]mdtWPmBd,Q3fy+8~ZXy5n' );
define( 'AUTH_SALT',        '`|2;wL7LT6#2z)6ZWdO6gY0uV7CGl0L)<+.<lHXFW44v4s{Ty&m@S:`wg|T1,0qk' );
define( 'SECURE_AUTH_SALT', '@J(B_KIr}q!.4c]8*6lKKn4R(pqy{yAD8k9Qt[TyD4&`AtK&na1s$)G21DQg>se.' );
define( 'LOGGED_IN_SALT',   '2{yJdlU}2@.Mb{wSW`|@8vr!Z92vd/]D[9^=>|/PCbhc|/HUn:-/pEX(249<M+%6' );
define( 'NONCE_SALT',       'tkb^50E_()aW?),?=GAnL?i8qrWsQI/z>DX}Tm$POZvkSaqyqBBu5guj=}h9~,=<' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
