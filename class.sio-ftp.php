<?php
defined('SIO_FTP_VERSION') OR exit('No direct script access allowed');

class SIO_FTP {
	public static $options = array();
	public static $version = '0.1.0';
	public static $textdomain = 'swish-image-optimization';

	private static $option_prefix = 'SIO_FTP_';
	private static $initiated = false;
	private static $ftp_system = false;
	private static $add_list = array();
    
    private static $temp_directory = '';

	public static function init() {
    	if( !self::$initiated ) {
			self::$initiated = true;

            $directories = wp_upload_dir();
            self::$temp_directory = $directories['basedir'].'/sio';
            
			if( is_admin() ) {
				require_once(SIO_FTP_PLUGIN_DIR . 'class.sio-ftp.update.php');
				SIO_FTP_update::update();

				require_once(SIO_FTP_PLUGIN_DIR . 'admin/class.sio-ftp.admin.php');
				SIO_FTP_admin::init();
                
                require_once(SIO_FTP_PLUGIN_DIR . 'admin/class.sio-media.admin.php');
                SIO_MEDIA_admin::init();
                
                require_once(SIO_FTP_PLUGIN_DIR . 'admin/class.sio-optimise.admin.php');
                SIO_OPTIMISE_admin::init();
			}
			
			self::$options = self::get_option('options');
			self::$version = self::get_option('version');

            self::sio_add_new_size_to_image_setup();
			add_filter( 'post_thumbnail_size', array( __CLASS__, 'sio_post_thumbnail_size' ), 10, 2);
            add_filter( 'image_size_names_choose', array(__CLASS__, 'sio_add_new_image_size_to_dropdown'), 10, 1 );
            
			add_action('shutdown', array(__CLASS__, 'shutdown'));
			
            add_filter( 'sio_upload_to_ftp', array(__CLASS__, 'set_upload_file'), 10, 2 );
            add_filter('wp_delete_file', array(__CLASS__, 'sio_delete_file'));
			
			add_filter('load_image_to_edit_path', array(__CLASS__, 'load_file'), 10, 2);
			add_filter('wp_get_attachment_image_attributes', array(__CLASS__, 'resrc_file'), 10, 2);
			add_filter('wp_get_attachment_url', array(__CLASS__, 'reurl_file'), 10, 2);
            add_filter('wp_calculate_image_srcset', array(__CLASS__, 'sio_calculate_image_srcset'), 10, 5);

            if(!empty(SIO_FTP::$options['srcset_enabled']) && SIO_FTP::$options['srcset_enabled'] == 1){
	            add_filter( 'image_send_to_editor',
				    function( $html, $id, $caption, $title, $align, $url, $size, $alt )
				    {    
				        if( $id > 0 )
				        {
				            $image_meta = wp_get_attachment_metadata( $id );
				            $htmlimage=''; $icon = false; $minsize=0; $minmaxsize=array();
				            foreach ( $image_meta['sizes'] as $isize => $value ) {  
				                $image = wp_get_attachment_image_src($id, $isize, $icon);   		                
				                $htmlimage .= $image[0].' '.$image[1].'w,';
				                array_push($minmaxsize,$image[1]);             
				            }

				            $data  = sprintf( ' srcset="%s" ', $htmlimage );
				            $data .= sprintf( ' sizes="%s" ', "(max-width: ".max($minmaxsize)."px) ".min($minmaxsize)."vw, ".max($minmaxsize)."px" );
				            $html = str_replace( "<img src", "<img{$data}src", $html );
				        }
				        return $html;
				    }
				, 10, 8 ); 
             }

			if( (bool) self::$options['rename_file'] ) {
				add_filter('sanitize_file_name', array(__CLASS__, 'sanitize_file_name'));
			}
		}
	}

	public static function sio_add_new_size_to_image_setup() {
        
        $width = esc_attr(isset(SIO_FTP::$options['width_ft_size']) ? SIO_FTP::$options['width_ft_size'] : '');
        $height = esc_attr(isset(SIO_FTP::$options['height_ft_size']) ? SIO_FTP::$options['height_ft_size'] : '');

        if(!empty($width) && !empty($height)) {
            settype($width, 'int');
            settype($height, 'int');
            add_image_size( 'featured-image', $width, $height, true ); // (cropped)
        }
    }
    
    public static function sio_post_thumbnail_size($size) {
        $post = get_post();
        // Step 1 check for featured image size option in swish image optimisation
        $width = esc_attr(isset(SIO_FTP::$options['width_ft_size']) ? SIO_FTP::$options['width_ft_size'] : '');
        $height = esc_attr(isset(SIO_FTP::$options['height_ft_size']) ? SIO_FTP::$options['height_ft_size'] : '');

        if(!empty($width) && !empty($height)) {
            $size = 'featured-image';
        }
        
        // Or Step 2. Check for featured image size meta for given thumbnail id
        $featured_image_size = get_post_meta( $post->ID, '_wp_featured_image_size', true );
        
        if(!empty($featured_image_size)) {
            $size = $featured_image_size;
        }
        
        //echo "Featured Image Size: {$featured_image_size}";
        
        return $size;
    }

    public static function sio_add_new_image_size_to_dropdown( $sizes ) {
        $sizes['full'] = __('Original Size');
        
        return array_merge( $sizes, array(
            'featured-image' => __('Featured Image')
        ) );
    }
    
	public static function shutdown() {
		self::$ftp_system = false;
	}

	public static function set_upload_file($attachment_ids) {
        $attachments = array();       
        foreach($attachment_ids as $attachment_id) {
            $data = wp_get_attachment_metadata($attachment_id);
            $a_meta = get_post_meta($attachment_id, '_sio_file_to_ftp', true);
          
            if(!empty($a_meta)) {
                $attachments[] = $attachment_id;
            }  else {
                $parent_post_id = wp_get_post_parent_id($attachment_id);
                if( $parent_post_id > 0 ) {
                    if( $post = get_post($parent_post_id) ) {
                        if( substr($post->post_date, 0, 4) > 0 ) {
                            $time = $post->post_date;
                        }
                    }
                }

                if( !isset($time) ) {
                    $post = get_post($attachment_id);
                    $time = $post->post_date;
                }

                $uploads = wp_upload_dir($time);
                                
                $local_file = $uploads['basedir'] . '/' . trim( get_post_meta($attachment_id, '_wp_attached_file', true), '/' );

                self::$add_list[] = array(
                    'file_post_id' => $attachment_id,
                    'local_file' => $local_file,
                    'ftp_file' => self::make_local_to_ftp($local_file)
                );
                
                if( isset($data['sizes']) ) {
                    $attached_file_path = trim( get_post_meta($attachment_id, '_wp_attached_file', true), '/' );
                    foreach( $data['sizes'] as $size_data ) {
                        $path = pathinfo($attached_file_path);
                        
                        $local_file = $uploads['basedir'] . '/' . $path['dirname'] . '/' . $size_data['file'];
                        
                        self::$add_list[] = array(
                            'file_post_id' => 0,
                            'local_file' => $local_file,
                            'ftp_file' => self::make_local_to_ftp($local_file)
                        );
                    }
                }
            }
        }
        
        $attachments = array_merge($attachments, self::do_ftp_upload());
        
        return $attachments;
	}

	public static function load_file($file, $attachment_id) {
		$meta_data = get_post_meta($attachment_id, '_sio_file_to_ftp', true);
		if( isset($meta_data['up_time']) && $meta_data['up_time'] >= 1 ) {
			if( is_file($file) && filesize($file) == 0 ) {
				if( function_exists('fopen') && function_exists('ini_get') && true == ini_get('allow_url_fopen') ) {
					$file = self::clear_basedir($file);
					$file = self::$options['html_link_url'] . $file;
				} else {
					return '';
				}
			}
		}
		return $file;
	}

    public static function sio_calculate_image_srcset($sources, $size_array, $image_src, $image_meta, $attachment_id ) {
        $meta_data = get_post_meta($attachment_id, '_sio_file_to_ftp', true);
        if( !empty($meta_data) && count($sources) > 0 ) {
            foreach($sources as $key => $source){
                $file_name = basename($source['url']);
                $sources[$key]['url'] = self::$options['html_link_url'] . $meta_data['up_dir'] . $file_name;
            }
        }
        return $sources;
    }
    
	public static function resrc_file($attr, $att) {
		$file_name = basename($attr['src']);
		$meta_data = get_post_meta($att->ID, '_sio_file_to_ftp', true);
		if( isset($meta_data['up_time']) && $meta_data['up_time'] >= 1 ) {
			$attr['src'] = self::$options['html_link_url'] . $meta_data['up_dir'] . $file_name;
		}
		return $attr;
	}

	public static function reurl_file($url, $att_id) {
        $meta_data = get_post_meta($att_id, '_sio_file_to_ftp', true);
        if(!empty($meta_data)) {
            $file_name = basename($url);
            if( isset($meta_data['up_time']) && $meta_data['up_time'] >= 1 ) {
                $url = self::$options['html_link_url'] . $meta_data['up_dir'] .  $file_name;
            }
            return $url;
        }
        return $url;
	}

	public static function sio_delete_file($file) {       
		if( self::$options['ftp_delete_ok'] && self::ftp_open() ) {
			$ftp_file = self::clear_basedir($file);
			self::$ftp_system->delete($ftp_file);
		}
		return $file;
	}
    
	public static function sanitize_file_name($file_name) {
	    $parts = explode('.', $file_name);
	    if( preg_match('@^[a-z0-9][a-z0-9\-_]*$@i', $parts[0]) ) {
	        $file_name = $parts[0];
	    } else {
	        $file_name = substr(md5($parts[0]), 0, 10);
	    }
	    if( count($parts) < 2 ) {
	        return $file_name;
	    } else {
	        $extension = array_pop($parts);
	        return $file_name . '.' . $extension;
	    }
	}

	private static function clear_basedir($file) {
		if( ($uploads = wp_upload_dir()) && false === $uploads['error'] ) {
            if( 0 === strpos($file, $uploads['path']) ) {
                $file = str_replace($uploads['path'], '', $file);
                $year = date("Y");   
                $month = date("m");
                $file = $year.'/'.$month.'/'.ltrim($file, '/');
            }  else {
                $file = basename($file);
                $year = date("Y");   
                $month = date("m");
                $file = $year.'/'.$month.'/'.ltrim($file, '/'); 
            }
		}
		return $file;
	}

	private static function make_local_to_ftp($local_file) {
		$dir = self::clear_basedir($local_file);
		$dir = '/' . substr($dir, 0, strrpos($dir, '/'));
		$dir = self::ftp_mkdir($dir);
		return $dir . basename($local_file);
	}

	private static function ftp_mkdir($dir) {
		$dir = explode('/', $dir);
		$now_dir = self::$options['ftp_dir'];
		$len = count($dir);
		for( $i = 1; $i < $len; $i++ ) {
			$now_dir .= $dir[$i] . '/';
			if( self::ftp_open() ) {
				if( !self::$ftp_system->is_dir($now_dir) ) {
					self::$ftp_system->mkdir($now_dir);
				}
			}
		}
		return $now_dir;
	}

	private static function do_ftp_upload() {
        $attachments = array();
		if( count(self::$add_list) > 0 ) {
			$up_time = current_time('timestamp');
			foreach( self::$add_list as $file ) {
                $upload_status = self::do_upload_file($file['ftp_file'], $file['local_file']);
                if( $upload_status ) {
					if( $file['file_post_id'] != 0 ) {
						$up_dir = dirname($file['ftp_file']);
						$up_dir = trim($up_dir, '/');
						if( $up_dir != '' ) {
							$up_dir .= '/';
						}
						$metadate = array(
							'up_time' => $up_time,
							'up_dir' => $up_dir
						);
                        
                        $filename =  basename($file['ftp_file']);
						
                        $mime_type = wp_check_filetype( $filename, null );
                        
                        $object = array(
                            'post_title'     => basename( $filename ),
                            'post_content'   => '',
                            'post_mime_type' => $mime_type['type'],
                            'guid'           => ''
                        );

                        $attachment_id = wp_insert_attachment( $object, $file['ftp_file'] );
                        $metadata = get_post_meta($file['file_post_id'], '_wp_attachment_metadata', true);
                        wp_update_attachment_metadata( $attachment_id, $metadata );
                        add_post_meta($attachment_id, '_sio_file_to_ftp', $metadate, true);
                        $attachments[] = $attachment_id;
					} else {
						if( self::$options['delete_local_auto_build'] == 1 ) {
							@unlink($file['local_file']);
						}
					}
				}
			}
		}
        return $attachments;
	}

	private static function do_upload_file($ftp_file, $local_file) {
        global $sioOptimise;
        
        if(!file_exists(self::$temp_directory)) {
            mkdir(self::$temp_directory);
        }
        
        $optimised_image_local_path = SIO_OPTIMISE_admin::optimise($local_file, self::$temp_directory);
        
		if( self::$options['ftp_uplode_ok'] && self::ftp_open() ) {
            $result = self::$ftp_system->put_contents($ftp_file, file_get_contents($optimised_image_local_path));
            @unlink($optimised_image_local_path);
            return $result;
		}
		return false;
	}

    /**
     * Open ftp connection using settings
     */
	private static function ftp_open() {
		if( self::$ftp_system ) {
			return true;
		}
		if( is_callable('set_time_limit') ) {
			set_time_limit(60);
		}

		self::$ftp_system = self::get_ftp_class(
			self::$options['ftp_host_mode'],
			self::$options['ftp_host'],
			self::$options['ftp_port'],
			self::$options['ftp_timeout'],
			self::$options['ftp_username'],
			self::$options['ftp_password']
		);

		return self::$ftp_system->connect();
	}

	public static function get_option($option, $default = false) {
		return get_option(self::$option_prefix . $option, $default);
	}

	public static function update_option($option, $value) {
		return update_option(self::$option_prefix . $option, $value);
	}

	public static function add_option($option, $value = '', $deprecated = '', $autoload = 'yes') {
		return add_option(self::$option_prefix . $option, $value, $deprecated, $autoload);
	}

	public static function delete_option($option) {
		return delete_option(self::$option_prefix . $option);
	}

	public static function load_ftp_class() {
		require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
		require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-ftpext.php');
		require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-ssh2.php');

		if( !defined('FS_CHMOD_DIR') ) {
			define('FS_CHMOD_DIR', (fileperms(ABSPATH) & 0777 | 0755));
		}
		if( !defined('FS_CHMOD_FILE') ) {
			define('FS_CHMOD_FILE', (fileperms(ABSPATH . 'index.php') & 0777 | 0644));
		}
	}

	public static function get_ftp_class($mode, $host, $port, $timeout, $username, $password) {
		self::load_ftp_class();

		if( $mode == 'ftp' || $mode == 'ftps' ) {
			$ftp_system = new WP_Filesystem_FTPext(array(
				'connection_type' => $mode,
				'hostname' => $host,
				'port' => $port,
				'username' => $username,
				'password' => $password
			));
			if ( !defined('FS_CONNECT_TIMEOUT') ) {
				define('FS_CONNECT_TIMEOUT', $timeout);
			}
		} elseif( $mode == 'sftp' ) {
			$ftp_system = new WP_Filesystem_SSH2(array(
				'hostname' => $host,
				'port' => $port,
				'username' => $username,
				'password' => $password
			));
			if ( !defined('FS_TIMEOUT') ) {
				define('FS_TIMEOUT', $timeout);
			}
		}

		return $ftp_system;
	}

	public static function plugin_activation() {
		$old_option = get_option('U2FTP_options');
		if( $old_option !== false ) {
			self::update_option('options', $old_option);
			delete_option('U2FTP_options');
		}
		$old_version = get_option('U2FTP_version');
		if( $old_version !== false ) {
			self::update_option('version', $old_version);
			delete_option('U2FTP_version');
		}

		$options = self::get_option('options', array());
		if( count($options) == 0 ) {
			$options['ftp_host'] = '';
			$options['ftp_host_mode'] = 'ftp';
			$options['ftp_port'] = 21;
			$options['ftp_timeout'] = 5;
			$options['ftp_username'] = '';
			$options['ftp_password'] = '';
			$options['ftp_dir'] = '/';
			$options['ftp_uplode_ok'] = false;
			$options['html_link_url'] = '';
			$options['ftp_delete_ok'] = false;
			$options['html_file_line_ok'] = false;
			$options['ftp_mkdir_ok'] = false;
			$options['rename_file'] = 1;
			$options['delete_local_auto_build'] = 0;   
            $options['jpeg_quality'] = 60;
            $options['png_quality'] = 6;
            $options['srcset_enabled'] = 0;
			self::update_option('options', $options);
		}
	}

	public static function plugin_deactivation( ) {
	}
}
