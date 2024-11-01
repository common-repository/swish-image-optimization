<?php
defined('SIO_FTP_VERSION') OR exit('No direct script access allowed');

class SIO_FTP_admin {
	public static $tab_list = array();
	private static $initiated = false;
	public static function init() {
		if( !self::$initiated ) {
			self::$initiated = true;
            
			require_once(SIO_FTP_PLUGIN_DIR . 'admin/class.sio-ftp.admin.ajax.php');
			SIO_FTP_admin_ajax::init();

			require_once(SIO_FTP_PLUGIN_DIR . 'admin/class.sio-ftp.admin-html.php');

			add_action('admin_init', array(__CLASS__, 'admin_init'));
			
            add_filter('plugin_action_links', array(__CLASS__, 'plugin_action_links'), 10, 2);
			add_action('admin_menu', array(__CLASS__, 'admin_menu'));

			add_filter('manage_media_columns', array(__CLASS__, 'manage_media_columns'));
			add_action('manage_media_custom_column', array(__CLASS__, 'manage_media_custom_column'), 10, 2);
		}
	}

	public static function admin_init() {
		load_plugin_textdomain(SIO_FTP::$textdomain, false, dirname(SIO_FTP_PLUGIN_BASENAME) . '/languages');
	}

	public static function plugin_action_links($links, $file) {
		if( $file == SIO_FTP_PLUGIN_BASENAME ) {
			$links[] = '<a href="options-general.php?page=swish-image-optimization&tab=ftp">' . __('Settings') . '</a>';
		}
		return $links;
	}

	public static function admin_menu() {
		add_submenu_page('options-general.php', 'Swish Image Optimization', __('Swish Image Optimization', SIO_FTP::$textdomain), 'manage_options', 'swish-image-optimization', array(__CLASS__, 'setting_page'));
	}

	public static function setting_page() {
		self::$tab_list['ftp'] = array(
			'name' => __('FTP Options', SIO_FTP::$textdomain)
		);
        self::$tab_list['imageoptions'] = array(
            'name' => __('Optimization Options', SIO_FTP::$textdomain)
        );
        self::$tab_list['featuredimageoption'] = array(
            'name' => __('Feature image size', SIO_FTP::$textdomain)
        );

		/*self::$tab_list['basic'] = array(
			'name' => __('Basic Options', SIO_FTP::$textdomain)
		);
		self::$tab_list['advanced'] = array(
			'name' => __('Advanced Options', SIO_FTP::$textdomain)
		);*/

        wp_enqueue_style( 'sio_ftp_range_slider_css', SIO_FTP_PLUGIN_URL . 'css/rangeslider.css' );
        wp_enqueue_style( 'SIO_FTP_admin_css', SIO_FTP_PLUGIN_URL . 'css/ftp-styles.css' );
        wp_enqueue_script( 'sio_ftp_range_slider_js', SIO_FTP_PLUGIN_URL . 'js/rangeslider.min.js', array( 'jquery' ), '', true );  
        wp_enqueue_script('SIO_FTP_admin_js', SIO_FTP_PLUGIN_URL . 'js/ftp-setting.js', array( 'jquery' ), false, true);
        
		$now_tab = isset($_GET['tab']) ? trim($_GET['tab']) : '';
		if( !isset(self::$tab_list[$now_tab]) ) {
			$now_tab = 'ftp';
		}

		SIO_FTP_admin_html::setting_page_header($now_tab);
		switch( $now_tab ) {
			case 'basic':
				if( isset($_POST['sio_Update_setting']) ) {
					self::save_setting();
				}
				SIO_FTP_admin_html::show_base_setting_page();
				break;
            case 'imageoptions':
                if( isset($_POST['sio_SetImageOptions']) ) {
                    self::save_image_settings();
                }
                SIO_FTP_admin_html::show_imageoptions_setting_page();
                break;
            case 'featuredimageoption':
            	if ( isset($_POST['sio_SetFeaturedImageSize']) ) {
            		self::save_feature_image_size();
            	}
            	SIO_FTP_admin_html::show_featuredimage_setting_page();
            	break;
			case 'advanced':
				if( isset($_POST['sio_SetExistFile']) ) {
					self::set_exists_file_in_ftp();
				}
				SIO_FTP_admin_html::show_advanced_setting_page();
				break;
			case 'ftp':
			default:
				SIO_FTP_admin_html::show_ftp_setting_page();
				break;
		}
		SIO_FTP_admin_html::setting_page_footer();
	}

	public static function manage_media_columns($attr) {
		$attr['toftp'] = __('to FTP', SIO_FTP::$textdomain);
		return $attr;
	}

	public static function manage_media_custom_column($name, $post_ID) {
		global $post;
		if( $name == 'toftp' ) {
			$metadate = get_post_meta($post_ID, 'file_to_ftp', true);
			if( isset($metadate['up_time']) ) {
				if( $metadate['up_time'] == 1 ) {
					$metadate['up_time'] = strtotime($post->post_date);
					update_post_meta($post_ID, 'file_to_ftp', $metadate);
				}
				if( $metadate['up_time'] ) {
					echo(date('Y/m/d G:i', $metadate['up_time']));
				}
			} else {
				_e('un-upload', SIO_FTP::$textdomain);
			}
		}
	}
    protected static function save_image_settings() {
        SIO_FTP::$options['jpeg_quality'] = $_POST['sio_jpeg_quality'];
        SIO_FTP::$options['png_quality'] = $_POST['sio_png_quality'];
        SIO_FTP::update_option('options', SIO_FTP::$options);
    ?>
    <div class="notice notice-success is-dismissible">
        <p><strong><?=__('Updated image optimisation options.', SIO_FTP::$textdomain) ?></strong></p>
    </div>
<?php
    }

    /* update */
    protected static function save_feature_image_size() {

    	SIO_FTP::$options['width_ft_size'] = $_POST['width_featuredimage_size'];
    	SIO_FTP::$options['height_ft_size'] = $_POST['height_featuredimage_size'];
        SIO_FTP::$options['srcset_enabled'] = $_POST['sio_srcset_enabled'];
    	SIO_FTP::update_option( 'options', SIO_FTP::$options );
    ?>

    <div class="notice notice-success is-dismissible">
        <p><strong><?=__('Updated image optimisation options.', SIO_FTP::$textdomain) ?></strong></p>
    </div>
<?php    

    }
    

	protected static function save_setting() {
		SIO_FTP::$options['rename_file'] = intval($_POST['sio_rename_file']) ? 1 : 0;
		SIO_FTP::$options['delete_local_auto_build'] = intval($_POST['sio_delete_local_auto_build']) ? 1 : 0;
		SIO_FTP::update_option('options', SIO_FTP::$options);
    ?>
		<div class="notice notice-success is-dismissible">
			<p><strong><?=__('Updated Basic Options Success', SIO_FTP::$textdomain) ?></strong></p>
		</div>
<?php
	}

	protected static function set_exists_file_in_ftp() {
		$att_query = new WP_Query(array(
			'post_type' => 'attachment',
			'post_status' => 'inherit,private',
			'posts_per_page' => -1
		));
		global $post;
		while( $att_query->have_posts() ) {
			$att_query->the_post();
			$meta_date = get_post_meta($post->ID, 'file_to_ftp', true);
			if( !isset($meta_date['up_time']) || $meta_date['up_time'] < 1 ) {
				$file_path = get_post_meta($post->ID, '_wp_attached_file', true);
				if( strpos($file_path, '/') === FALSE ) {
					$up_dir = '';
				} else {
					$up_dir = substr($file_path, 0, strrpos($file_path, '/'));
				}
				$up_dir = trim($up_dir, '/');
				if( $up_dir != '' ) {
					$up_dir .= '/';
				}$metadate = array(
					'up_time' => current_time('timestamp'),
					'up_dir' => $up_dir
				);
				add_post_meta($post->ID, 'file_to_ftp', $metadate, true);
			}
		}
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong><?=__('Setted file uploaded Success', SIO_FTP::$textdomain) ?></strong></p>
		</div>
		<?php
	}

	protected static function import_setting() {
		$setting = $_POST['SIO_FTP_setting'];
		$setting = @base64_decode($setting);
		$setting = @json_decode($setting, true);
		if( is_array($setting) && count($setting) == 2 ) {
			if( isset($setting['version']) && isset($setting['option']) && is_array($setting['option']) ) {
				SIO_FTP::update_option('options', $setting['option']);
				SIO_FTP::update_option('version', $setting['version']);
				?>
				<div class="notice notice-success is-dismissible">
					<p><strong><?=__('Implort setting Success', SIO_FTP::$textdomain) ?></strong><br>
						<?=sprintf(__('Place goto <a href="%1$s">%2$s</a> and click "%3$s" button to check this setting can work on this site.', SIO_FTP::$textdomain),
							'options-general.php?page=swish-image-optimization&tab=ftp',
							__('FTP Options', SIO_FTP::$textdomain),
							__('Save & Test Changes', SIO_FTP::$textdomain)
							) ?>
					</p>
				</div>
				<?php
				return;
			}
		}
		?>
		<div class="notice notice-error is-dismissible">
			<p><strong><?=__('The implort Data is error', SIO_FTP::$textdomain) ?></strong></p>
		</div>
		<?php
	}
}
