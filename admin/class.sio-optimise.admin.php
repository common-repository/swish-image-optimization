<?php

defined('SIO_FTP_VERSION') OR exit('No direct script access allowed');

class SIO_OPTIMISE_admin {
    private static $initiated = false;
    private static $options = array();

    public static function init() {
        if( !self::$initiated ) {
            self::$initiated = true;
            self::$options = SIO_FTP::get_option('options');
            add_filter( 'admin_post_thumbnail_html', array( __CLASS__, 'add_optimised_featured_image_link'), 10, 3);
        }
    }

    public static function add_optimised_featured_image_link( $content, $post_id, $attachment_id ) {
        if(empty($attachment_id))
            return $content .='<a href="#" id="set-optimised-post-thumbnail">Set optimised featured image</a>';

        return $content;
    }

    /**
     * Check if Imagick is available or not
     *
     * @return bool True/False Whether Imagick is available or not
     *
     */
    public static function supports_imagick() {
        if ( ! class_exists( 'Imagick' ) ) {
            return false;
        }
        return true;
    }

    /**
     * Check if GD is loaded
     *
     * @return bool True/False Whether GD is available or not
     *
     */
    public static function supports_GD() {
        if ( ! function_exists( 'gd_info' ) ) {
            return false;
        }
        return true;
    }

    public static function do_gd_optimisation($source, $destination) {
        $info = getimagesize($source);
        $pathinfo = pathinfo($source);
        
        if ($info['mime'] == 'image/jpeg' || in_array($pathinfo['extension'], array('jpg', 'jpeg'))) {
            $image = imagecreatefromjpeg($source);
            imagejpeg($image, $destination.'/'.$pathinfo['basename'], self::$options['jpeg_quality']);
        } elseif ($info['mime'] == 'image/gif') {
            $image = imagecreatefromgif($source);
            imagegif($image, $destination.'/'.$pathinfo['basename']);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);

            $width = imagesx($image);
            $height = imagesy($image);

            $newImage = imagecreatetruecolor($width, $height);
            imagealphablending($newImage, false);
            imagesavealpha($newImage,true);
            $transparency = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $width, $height, $transparency);

            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $width, $height);
            imagepng($newImage, $destination.'/'.$pathinfo['basename'], self::$options['png_quality']);
        }

        return $destination.'/'.$pathinfo['basename'];
    }

    public static function do_imagick_optimisation() {

    }

    public static function optimise($source, $destination) {
        return self::do_gd_optimisation($source, $destination);
    }
}