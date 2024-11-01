<?php
defined('SIO_FTP_VERSION') OR exit('No direct script access allowed');

class SIO_FTP_update {
	public static function update() {
		$now_version = SIO_FTP::get_option('version');

		if( $now_version === false ) {
			$now_version = '0.0.0';
		}
		if( $now_version == SIO_FTP_VERSION ) {
			return;
		}

		if( version_compare($now_version, '1.0.0', '<' ) ) {
			$old_option = get_option('U2FTP_options');
			if( $old_option !== false ) {
				$old_option['delete_local_auto_build'] = $old_option['auto_delete_local'];
				unset($old_option['save_original_file']);
				unset($old_option['auto_delete_local']);
				SIO_FTP::update_option('options', $old_option);
				delete_option('U2FTP_options');
			}
			$old_version = get_option('U2FTP_version');
			if( $old_version !== false ) {
				SIO_FTP::update_option('version', $old_version);
				delete_option('U2FTP_version');
			}

			SIO_FTP::update_option('version', '1.0.0');
		}

		if( version_compare($now_version, '1.0.1', '<' ) ) {
			SIO_FTP::update_option('version', '1.0.1');
		}

		if( version_compare($now_version, '1.0.2', '<' ) ) {
			SIO_FTP::update_option('version', '1.0.2');
		}

		if( version_compare($now_version, '1.0.3', '<' ) ) {
			SIO_FTP::update_option('version', '1.0.3');
		}

		if( version_compare($now_version, '1.0.4', '<' ) ) {
			SIO_FTP::update_option('version', '1.0.4');
		}
		
		if( version_compare($now_version, '1.0.5', '<' ) ) {
			$options = SIO_FTP::get_option('options');
			$options['ftp_host_mode'] = 'ftp';
			SIO_FTP::update_option('options', $options);
			SIO_FTP::update_option('version', '1.0.5');
		}

		if( version_compare($now_version, '1.0.6', '<' ) ) {
			$options = SIO_FTP::get_option('options');
			$options['ftp_mkdir_ok'] = false;

			if( $options['ftp_uplode_ok'] == true ) {
				SIO_FTP::load_ftp_class();
				$ftp_system = new WP_Filesystem_FTPext(array(
					'connection_type' => $options['ftp_host_mode'],
					'hostname' => $options['ftp_host'],
					'port' => $options['ftp_port'],
					'username' => $options['ftp_username'],
					'password' => $options['ftp_password']
				));
				if ( !defined('FS_CONNECT_TIMEOUT') ) {
					define('FS_CONNECT_TIMEOUT', $ftp_timeout);
				}
				
				do {
					$test_new_dir = 'mkdir-test-' . wp_rand();
				} while( $ftp_system->is_dir($options['ftp_dir'] . $test_new_dir) );
				$ftp_system->mkdir($options['ftp_dir'] . $test_new_dir);
				if( $ftp_system->is_dir($options['ftp_dir'] . $test_new_dir) ) {
					$options['ftp_mkdir_ok'] = true;
				}
			}

			SIO_FTP::update_option('options', $options);
			SIO_FTP::update_option('version', '1.0.6');
		}

		if( version_compare($now_version, '1.0.7', '<' ) ) {
			SIO_FTP::update_option('version', '1.0.7');
		}
	}
}
