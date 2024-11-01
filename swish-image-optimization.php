<?php
/*
Plugin Name: Swish Image Optimization
Plugin URI: https://codecaste.com/swish-image-optimization
Description: File upload using ftp
Version: 1.0.0
Text Domain: swish-image-optimization
*/

function_exists('plugin_dir_url') OR exit('No direct script access allowed');

define('SIO_FTP_VERSION', '0.1.1');
define('SIO_FTP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SIO_FTP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SIO_FTP_PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once(SIO_FTP_PLUGIN_DIR . 'class.sio-ftp.php');

register_activation_hook(__FILE__, array('SIO_FTP', 'plugin_activation'));
register_deactivation_hook(__FILE__, array('SIO_FTP', 'plugin_deactivation'));

add_action('init', array('SIO_FTP', 'init'));
