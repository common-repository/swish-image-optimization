<?php
defined('SIO_FTP_VERSION') OR exit('No direct script access allowed');

class SIO_FTP_admin_html {
	public static function setting_page_header($now_tab) {
		?>
		<div class="wrap">
		<h2><?=__('Swish Image Optimization', SIO_FTP::$textdomain) ?></h2>
		<nav class="nav-tab-wrapper">
			<?php foreach( SIO_FTP_admin::$tab_list as $tag => $tag_info ) { ?>
				<a href="options-general.php?page=swish-image-optimization&tab=<?=$tag ?>" class="nav-tab<?=(($now_tab == $tag) ? ' nav-tab-active' : '') ?>"><?=$tag_info['name'] ?></a>
			<?php } ?>
		</nav>
		<?php
	}

	public static function setting_page_footer() {
		?>
		</div>
		<?php
	}

	public static function show_ftp_setting_page() {
		?>
		<form method="post" action="" novalidate="novalidate">
			<table class="form-table">
				<tr>
					<th scope="row"><?=__('FTP Status', SIO_FTP::$textdomain) ?></th>
					<td>
						<p>
							<strong style="color: green;" class="upload-status <?=SIO_FTP::$options['ftp_uplode_ok'] ? '' : 'hidden' ?>"><?=__('Connected', SIO_FTP::$textdomain) ?></strong>
                            <strong style="color: red;" class="upload-status-error <?=SIO_FTP::$options['ftp_uplode_ok'] ? 'hidden' : '' ?>"><?=__('Not connected', SIO_FTP::$textdomain) ?></strong>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="<?=esc_attr('sio_ftp_host') ?>"><?=__('FTP Host', SIO_FTP::$textdomain) ?></label></th>
					<td>
						<select id="<?=esc_attr('sio_ftp_host_mode') ?>" name="<?=esc_attr('sio_ftp_host_mode') ?>">
							<option value="ftp">ftp://</option>
							<option value="ftps">ftps://</option>
							<option value="sftp">sftp://</option>
						</select>
                        <input type="text" id="<?=esc_attr('sio_ftp_host') ?>" name="<?=esc_attr('sio_ftp_host') ?>" value="<?=str_repeat('*', strlen(esc_attr(SIO_FTP::$options['ftp_host'])));?>" size="30" placeholder="example.com">
					</td>
				</tr>
				<?php self::input_text(__('FTP Port', SIO_FTP::$textdomain), 'sio_ftp_port', SIO_FTP::$options['ftp_port'], 6, '21'); ?>
				<?php self::input_text(__('FTP Timeout', SIO_FTP::$textdomain), 'sio_ftp_timeout', SIO_FTP::$options['ftp_timeout'], 4, '5'); ?>
                <?php self::input_text(__('FTP Username', SIO_FTP::$textdomain), 'sio_ftp_username', str_repeat('*', strlen(SIO_FTP::$options['ftp_username'])), 30, 'FTP Username '); ?>
                <?php self::input_password(__('FTP Password', SIO_FTP::$textdomain), 'sio_ftp_password', str_repeat('*', strlen(SIO_FTP::$options['ftp_password'])), 30, 'FTP Password'); ?>
				<?php self::input_text(__('FTP Directory', SIO_FTP::$textdomain), 'sio_ftp_dir', SIO_FTP::$options['ftp_dir'], 60, '/public_html/	'); ?>
				<?php self::input_text(__('HTML link url', SIO_FTP::$textdomain), 'sio_html_link_url', SIO_FTP::$options['html_link_url'], 60, 'http://example.com/'); ?>
				<tr class="hidden">
					<th scope="row"><?=__('Test Status', SIO_FTP::$textdomain) ?></th>
					<td>
						<p><?=__('Connect and Login:', SIO_FTP::$textdomain) ?> <span class="testing"><?=__('Testing', SIO_FTP::$textdomain) ?></span><span class="test-result"><?=__('Test Complete', SIO_FTP::$textdomain) ?></span></p>
					</td>
				</tr>
			</table>
			<p class="submit"><button type="button" class="button-primary sio_Test_ftpsetting"><?=__('Save & Test Changes', SIO_FTP::$textdomain) ?></button></p>
		</form>
		<?php
	}

	public static function show_base_setting_page() {
		?>
		<form method="post" action="" novalidate="novalidate">
			<table class="form-table">
				<tr>
					<th scope="row"><?=__('Rename file', SIO_FTP::$textdomain) ?></th>
					<td>
						<select name="crename_file" size="1">
							<option value="0"<?php selected('0', SIO_FTP::$options['rename_file']); ?>><?=__('disable', SIO_FTP::$textdomain) ?></option>
							<option value="1"<?php selected('1', SIO_FTP::$options['rename_file']); ?>><?=__('enable', SIO_FTP::$textdomain) ?></option>
						</select>
						<br><em><?=__('Proposal enabled! Because the file name to avoid some of the resulting error can not be expected', SIO_FTP::$textdomain) ?></em>
					</td>
				</tr>
				<tr>
					<th scope="row"><?=__('Delete Auto build local file', SIO_FTP::$textdomain) ?></th>
					<td>
						<select name="sio_delete_local_auto_build" size="1">
							<option value="0"<?php selected('0', SIO_FTP::$options['delete_local_auto_build']); ?>><?=__('disable', SIO_FTP::$textdomain) ?></option>
							<option value="1"<?php selected('1', SIO_FTP::$options['delete_local_auto_build']); ?>><?=__('enable', SIO_FTP::$textdomain) ?></option>
						</select>
						<br><em><?=__('Only enable the when you local storage space have limited.', SIO_FTP::$textdomain) ?></em>
					</td>
				</tr>
			</table>
			<p class="submit"><button type="submit" name="sio_Update_setting" class="button-primary"><?=__('Save Changes', SIO_FTP::$textdomain) ?></button></p>
		</form>
		<?php
	}

	public static function show_advanced_setting_page() {
		?>
		<form method="post" action="" novalidate="novalidate">
			<p>
				<?=__('This setting is ONLY set the mark of the media.', SIO_FTP::$textdomain) ?><br>
				<?=__('You NEED move the file to you ftp by youself.', SIO_FTP::$textdomain) ?><br>
				<?=__('And all the post you had will stile use the file from this webserver until you reimport the media to the post.', SIO_FTP::$textdomain) ?>
			</p>
			<p class="submit"><button type="submit" name="SetExistFile" class="button-primary"><?=__('Set Exists File In FTP', SIO_FTP::$textdomain) ?></button></p>
		</form>
		<?php
	}
    
    public static function show_imageoptions_setting_page() {
    ?>

        <form method="post" action="" novalidate="novalidate">
            <p>Image optimization settings options allows you to control the optimization settings for the images.</p>
            <table class="form-table" style="width: 50%;">
                <tr>
                    <th scope="row"><label for="sio_jpeg_quality">JPEG Image Quality</label></th>
                    <td style="position:relative;">
                        <input type="range" id="sio_jpeg_quality" name="sio_jpeg_quality" value="<?=esc_attr(SIO_FTP::$options['jpeg_quality']) ?>" min="10" max="100" step="5" data-rangeslider>
                        <span class="rangevalue"><?php echo SIO_FTP::$options['jpeg_quality'];?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="sio_png_quality">PNG Image Quality</label></th>
                    <td style="position:relative;">
                        <input type="range" id="sio_png_quality" name="sio_png_quality" value="<?=esc_attr(SIO_FTP::$options['png_quality']) ?>" min="1" max="9" step="1" data-rangeslider>
                        <span class="rangevalue"><?php echo SIO_FTP::$options['png_quality'];?></span>
                    </td>
                </tr>
            </table>
           
            <p class="submit">
                <button type="submit" name="sio_SetImageOptions" class="button-primary"><?=__('Save Image Options', SIO_FTP::$textdomain) ?></button>
            </p>
        </form>
    <?php
        }

    public static function show_featuredimage_setting_page() {
    ?>
    	<form method="post" action="" novalidate="novalidate">
    		<p>Set Featured image size settings allows you to control the size that will be used on featured image.</p>
            <table class="form-table" style="width: 50%;">
                <tr>
                    <th scope="row"><label for="width_featuredimage_size">Width</label></th>
                    <td style="position:relative;">
                        <input type="text" size="5" id="width_featuredimage_size" name="width_featuredimage_size" value="<?=esc_attr(SIO_FTP::$options['width_ft_size']) ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="height_featuredimage_size">Height</label></th>
                    <td style="position:relative;">
                        <input type="text" size="5" id="height_featuredimage_size" name="height_featuredimage_size" value="<?=esc_attr(SIO_FTP::$options['height_ft_size']) ?>">
                    </td>
                </tr>
            </table>
            
            <p>Enable Srcset attribute for images?</p>
            <table class="form-table" style="width: 50%;">
                <tr>
                    <th scope="row"><label for="sio_srcset_enabled">Srcset Enable?</label></th>
                    <td style="position:relative;">
                        <select id="sio_srcset_enabled" name="sio_srcset_enabled">
                            <?php $srcset = esc_attr(SIO_FTP::$options['srcset_enabled']);?>
                            <option value="1" <?php echo $srcset == 1 ? 'selected' : '';?>>Yes</option>
                            <option value="0" <?php echo $srcset == 0 ? 'selected' : '';?>>No</option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" name="sio_SetFeaturedImageSize"class="button-primary"><?=__('Save Image Options', SIO_FTP::$textdomain) ?></button>
            </p>
    	</form>	

    <?php
    }
    
	private static function input_text($label, $input_name, $input_value, $input_size = 30, $placeholder = '', $note = '') {
		?>
		<tr>
			<th scope="row"><label for="<?=esc_attr($input_name) ?>"><?=$label ?></label></th>
			<td>
				<input type="text" id="<?=esc_attr($input_name) ?>" name="<?=esc_attr($input_name) ?>" value="<?=esc_attr($input_value) ?>" size="<?=intval($input_size) ?>" <?=empty($placeholder) ? '' : ('placeholder="' . esc_attr($placeholder) . '"') ?>>
				<?=empty($note) ? '' : ('<br>' . $note) ?>
			</td>
		</tr>
		<?php
	}
    
    private static function input_password($label, $input_name, $input_value, $input_size = 30, $placeholder = '', $note = '') {
    ?>
        <tr>
            <th scope="row"><label for="<?=esc_attr($input_name) ?>"><?=$label ?></label></th>
            <td>
                <input type="password" id="<?=esc_attr($input_name) ?>" name="<?=esc_attr($input_name) ?>" value="<?=esc_attr($input_value) ?>" size="<?=intval($input_size) ?>" <?=empty($placeholder) ? '' : ('placeholder="' . esc_attr($placeholder) . '"') ?>>
                <?=empty($note) ? '' : ('<br>' . $note) ?>
            </td>
        </tr>
    <?php
                                                                                                                               }
    }
