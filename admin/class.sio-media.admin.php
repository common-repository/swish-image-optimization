<?php
defined('SIO_FTP_VERSION') OR exit('No direct script access allowed');

class SIO_MEDIA_admin {

    private static $initiated = false;

    public static function init() {
        if( !self::$initiated ) {
            self::$initiated = true;
            add_filter( 'media_view_strings', array(__CLASS__, 'sio_media_view_strings'), 10, 2 );

            add_action( 'wp_ajax_query-external-attachments', array( __CLASS__, 'sio_ajax_query_external_attachments' ) );

            add_action( 'wp_ajax_sio_ajax_upload_to_ftp', array( __CLASS__, 'sio_ajax_upload_to_ftp' ) );
            add_action( 'wp_ajax_nopriv_sio_ajax_upload_to_ftp', array( __CLASS__, 'sio_ajax_upload_to_ftp' ) );

            add_action( 'wp_ajax_sio-get-post-thumbnail-html', array( __CLASS__, 'sio_get_post_thumbnail_html' ) );
            add_filter( 'ajax_query_attachments_args', array( __CLASS__, 'sio_ajax_query_attachments_args' ), 10, 1 );

            add_action( 'wp_enqueue_media', array(__CLASS__, 'sio_enqueue_media'), 10);
        }
    }

    public static function sio_enqueue_media() {
        wp_enqueue_style( 'sio_media_upload_view', SIO_FTP_PLUGIN_URL . 'css/media-views.css' );
        wp_enqueue_script( 'sio_media_upload_view', SIO_FTP_PLUGIN_URL . 'js/media-views.js', array( 'jquery' ), '0.1.2', true );
    }

    public static function sio_ajax_query_external_attachments() {
        wp_ajax_query_attachments();
    }

    public static function sio_ajax_query_attachments_args( $query ) {
        if( isset($_REQUEST['query']) && isset($_REQUEST['query']['isExternal'])){
            $query['meta_key'] = '_sio_file_to_ftp';
        } else {
            $query['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key' => '_sio_file_to_ftp',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key' => '_sio_file_to_ftp',
                    'value' => 0
                )
            );
        }
        return $query;
    }

    public static function sio_ajax_upload_to_ftp() {
        if(!is_array($_POST['selected'])) {
            $attachments[] = $_POST['selected']; 
        } else {
            $attachments = $_POST['selected'];
        }
        $response = apply_filters( 'sio_upload_to_ftp', $attachments );
        wp_send_json_success($response);
    }

    /**
     * Media screent strings
     */
    public static function sio_media_view_strings( $strings, $post ) {
        if(!empty($post)){
            $strings['sioExternalLibrary'] = array(
                'insertOptimisedImageIntoPost' => 'Insert optimised image into ' . $post->post_type,
                'setOptimisedFeaturedImage' => 'Set optimised featured image'
            );
        }

        return $strings;
    }
    
    public static function sio_get_post_thumbnail_html() {
        
        // set the size for featured image.
        if ( isset( $_REQUEST['size'] ) ) {
            $size = $_REQUEST['size'];
            $post_id = $_POST['post_id'];
            update_post_meta( $post_id, '_wp_featured_image_size', $size );
        }
        
        wp_ajax_get_post_thumbnail_html();
    }
}