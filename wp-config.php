<?php
# Database Configuration
define( 'DB_NAME', 'wp_tmcp' );
define( 'DB_USER', 'oh5oalezlF' );
define( 'DB_PASSWORD', '+zm]=~*{a4EQpEn&#iyY!$w^Y5%b(r' );
define( 'DB_HOST', '127.0.0.1' );
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', 'utf8_unicode_ci');
$table_prefix = 'wp_';

# Security Salts, Keys, Etc
define('AUTH_KEY',         '%ih]7ZT2z&ZVyvd1Cy(+<=8!Z$80LJs+Q;}af_;BZGprYD|<24)[g)VU(Kv2}E``');
define('SECURE_AUTH_KEY',  '=WixY[xv=4]5!P7+1h&_]tBJcDf,uLfq[yG)=Vq!cMHW=u@f#C|#Nc;at3$~Mdz^');
define('LOGGED_IN_KEY',    'yg9D*iN`5eP;kM/r;<dd34Tzj:e63I=c]PmT=E+G$Wa+=CPJ/Vqb%%^SU*2dl!Wp');
define('NONCE_KEY',        'CSc,oFL/rV=gZ|v;()i`.E` 6r>Y9IR/x`hCbE@kPC5>G)MA|A]Za7G+A,0@^Zp/');
define('AUTH_SALT',        'MfoB<1N;uo{X|sedMP^w4HjNn&:-;Y}a*a%i<G|^V*.e?Ss(qKbASYf6-?1W|H4.');
define('SECURE_AUTH_SALT', 'ovzIZ+{+;1O0i8*A<e-J>51/#9YC_y+1^#|/_s12n01nfoz9T,`TD$-ZT$|LUjB`');
define('LOGGED_IN_SALT',   'd2im-9K/wD+s!cbX b](5+)~M>??(6|Gf8<8)F]1c<H((SK.S%-;oY+y-9<%u`d0');
define('NONCE_SALT',       'r:um)3QM?0L^Na0^[.,y+h+26(|Lf)vzDR$1%qNSg@5:U,(r#[H;1vMj14`o<wL(');


# Localized Language Stuff

define( 'WP_CACHE', TRUE );

define( 'WP_AUTO_UPDATE_CORE', true );

define('WPLANG','');

define('WP_DEBUG', FALSE);

# WP Engine ID


# WP Engine Settings

define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', true);
define('DOMAIN_CURRENT_SITE', 'sites.tmcp.com');
define('PATH_CURRENT_SITE', '/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);

define('ADMIN_COOKIE_PATH', '/');
define('COOKIE_DOMAIN', '');
define('COOKIEPATH', '');
define('SITECOOKIEPATH', '');


# That's It. Pencils down
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
require_once(ABSPATH . 'wp-settings.php');

$_wpe_preamble_path = null; if(false){}
