<?php 
/*
Plugin Name: Automatic Teachable Student Enrollment for WooCommerce
Requires Plugins: woocommerce
Plugin URI: https://www.wooxperto.com/plugins/
Description: Automatic Teachable Student Enrollment for WooCommerce plugin works to connect woocommerce store to Teachable platform. 

Version: 1.1.3
Author: WooXperto
Author URI: https://www.wooxperto.com/
License: GPLv2 or Later
Text Domain: wx-teachable
*/

// Exit if accessed directly

if ( ! defined( 'ABSPATH' ) ) {

    exit;

}

define( 'ATSEW_TCM_ACC_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__ ) ) . '/' );
define( 'ATSEW_TCM_ACC_PATH', plugin_dir_path( __FILE__ ) );

$atsew_apiKeyShow = get_option('teachable_fild_teachable_api_key');

define("ATSEW_TEACHABLEAPIKEY", $atsew_apiKeyShow );  
define("ATSEW_PLUGIN_BASENAME",plugin_basename(__FILE__));
if( is_admin() ){
    require_once( ATSEW_TCM_ACC_PATH . 'process/wx-admin-settings.php' );
}

require_once( ATSEW_TCM_ACC_PATH . 'process/wx-teachable.php' );


register_deactivation_hook(__FILE__, "atsew_action_after_deactivation_plugin");
register_activation_hook(__FILE__, "atsew_action_after_activation_plugin");

function atsew_remove_site_url_under_license_key_after_deactivation_plugin($license_key){
    $site_url = get_site_url();


    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://www.wooxperto.com/api/api-site-remove.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('license_key' => $license_key,'added_sites' => $site_url),
    ));
    $response = curl_exec($curl);
    curl_close($curl);

    return $response;
    
}

function atsew_action_after_deactivation_plugin(){
    $license_key = get_option('teachable_fild_atsew_license_key');
    if($license_key){
        atsew_remove_site_url_under_license_key_after_deactivation_plugin($license_key);   
    }
}

function atsew_action_after_activation_plugin() {
    $license_key = get_option('teachable_fild_atsew_license_key');
    if($license_key){
        atsew_license_key_get($license_key);
    }
}

