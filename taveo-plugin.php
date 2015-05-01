<?php
/**
 * Plugin Name: Taveo Click Tracking
 * Plugin URI:  https://github.com/mox1/taveo-wordpress-plugin 
 * Description: Taveo is a click tracking and URL shortening platform. When you use Taveo short urls, Taveo tracks the number of clicks, their location, language and referrer. This plugin integrates Taveo with Wordpress, allowing 1 click URL shortening and click tracking. ***A Taveo Account is required (Register free at: http://taveo.net) 
 * Author:      taveo
 * Author URI:  http://taveo.net/
 * Version:     1.0.1
 * License: 	GPL2
 *  
 */

/*  Copyright 2015 Taveo  (email : admin@taveo.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/ 
 
 
//Completed ajax base retrieval of data from taveo,and addition of button to the publish box.
//TODO : Enhance performace,if any and sort out any bugs.

/**
 * Define plugin constants
 */

//Security Enhancement
defined( 'ABSPATH' ) or die( 'Go Away.' );

// Plugin directory path and URL.
define( 'TAVEO_PLUGIN_DIR_PATH', dirname( __FILE__ ) );
define( 'TAVEO_PLUGIN_DIR_URL',  plugin_dir_url( __FILE__ ) );

// Plugin version
define( 'TAVEO_PLUGIN_VERSION', '1.0' );

define( 'TAVEO_API_CREATE_URL',  'https://api.taveo.net/1/create' );
define( 'TAVEO_API_OVERVIEW_URL',  'https://api.taveo.net/1/overview' );
define( 'TAVEO_API_BYDEST_URL',  'https://api.taveo.net/1/links/bydest' );
define( 'TAVEO_API_LINKS_URL',  'https://api.taveo.net/1/links/all' );

// Verify SSL requests 
// Set to false during testing , true in production
define ('TAVEO_SSL_VERIFY', false);



/**
 * Load includes
 */
require( TAVEO_PLUGIN_DIR_PATH . '/includes/config_screen.php' );
require( TAVEO_PLUGIN_DIR_PATH . '/includes/add_to_taveo.php' );


function taveo_on_activation()
{
    if ( ! current_user_can( 'activate_plugins' ) )
        return;
    $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
    check_admin_referer( "activate-plugin_{$plugin}" );

    
}

function taveo_on_deactivation()
{
    if ( ! current_user_can( 'activate_plugins' ) )
        return;
    $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
    check_admin_referer( "deactivate-plugin_{$plugin}" );

   
}

function taveo_on_uninstall()
{
    if ( ! current_user_can( 'activate_plugins' ) )
        return;
    check_admin_referer( 'bulk-plugins' );

    if(is_multisite()){
    	delete_blog_option(null,'taveo_api_key');
    }
    else {
    	delete_option('taveo_api_key');
    }

   
}

register_activation_hook(__FILE__, 'taveo_on_activation' );
register_deactivation_hook(__FILE__, 'taveo_on_deactivation' );
register_uninstall_hook(__FILE__, 'taveo_on_uninstall' );



add_action('admin_init', 'taveo_admin_init');
add_action('admin_menu', 'taveo_admin_menu');

function taveo_admin_init() {
	wp_register_style('JQUIcss',plugins_url( '/css/jq-ui-min.css', __FILE__ ),array(),TAVEO_PLUGIN_VERSION);
	wp_register_style('TaveoOptionsCSS',plugins_url( '/css/config_screen.css', __FILE__ ),array('wp-jquery-ui-dialog'),TAVEO_PLUGIN_VERSION);
	wp_register_style('jq-datatablescss',plugins_url( '/css/jq-datatables.css', __FILE__ ),array('wp-jquery-ui-dialog'),TAVEO_PLUGIN_VERSION);
	wp_register_script('jq-datatablesjs', plugins_url( '/js/jq-datatables.js', __FILE__ ), array( 'jquery'), TAVEO_PLUGIN_VERSION, true );	
	wp_register_script('TaveoOptionsJS',plugins_url( '/js/config_screen.js', __FILE__ ), array( 'jquery', 'jquery-ui-tooltip', 'jq-datatablesjs' ), TAVEO_PLUGIN_VERSION, true );

}

function taveo_admin_menu() { 
	$my_page=add_menu_page( 'Taveo','Taveo' ,'manage_options','taveo_dashboard',
							'taveo_build_config_screen', plugin_dir_url( __FILE__ ) . 'includes/images/icon16.png' );
	add_action('admin_print_styles-' . $my_page, 'taveo_admin_styles');
}

function taveo_admin_styles() {
/*
 * It will be called only on your plugin admin page, enqueue our stylesheet here
 */
	wp_enqueue_style('JQUIcss');
 	wp_enqueue_style( 'jq-datatablescss' );
	wp_enqueue_style( 'TaveoOptionsCSS' );
	wp_enqueue_script('jq-datatablesjs');
	wp_enqueue_script('TaveoOptionsJS');
}


/* load scripts and do things on our options / Dashboard page */
function taveo_enqueue_admin(){
	//wp_enqueue_script('jquery-ui-dialog');
	//wp_enqueue_script('jquery-ui-tooltip');
	wp_enqueue_script('jq-impromptujs', plugins_url( '/js/jq-impromptu.min.js', __FILE__ ), array( 'jquery','jquery-ui-core' ), TAVEO_PLUGIN_VERSION, true );
	wp_enqueue_script('jq-datatablesjs', plugins_url( '/js/jq-datatables.js', __FILE__ ), array( 'jquery'), TAVEO_PLUGIN_VERSION, true );
	wp_enqueue_script('TaveoMainJS', plugins_url( '/js/taveo.js', __FILE__ ), array( 'jq-impromptujs','jquery-ui-tooltip' ), TAVEO_PLUGIN_VERSION, true );
	wp_enqueue_style('wp-jquery-ui-dialog');
	wp_enqueue_style('jq-impromptucss',plugins_url( '/css/jq-impromptu.min.css', __FILE__ ),array('wp-jquery-ui-dialog'),TAVEO_PLUGIN_VERSION);
	wp_enqueue_style('jq-datatablescss',plugins_url( '/css/jq-datatables.css', __FILE__ ),array('wp-jquery-ui-dialog'),TAVEO_PLUGIN_VERSION);
	wp_enqueue_style('TaveoMainCss',plugins_url( '/css/taveo.css', __FILE__ ),array('wp-jquery-ui-dialog'),TAVEO_PLUGIN_VERSION);
	wp_enqueue_style('JQUIcss',plugins_url( '/css/jq-ui-min.css', __FILE__ ),array(),TAVEO_PLUGIN_VERSION);
    

	if(is_multisite()){
    	$taveo_api_key=get_blog_option( null , 'taveo_api_key' );
	}
	else {
		$taveo_api_key=get_option( 'taveo_api_key' );
	}
	$data = add_query_arg( array(
	    'apikey' => $taveo_api_key,
	    'destination'     => get_pagepost_url()
	), TAVEO_API_CREATE_URL );


    wp_localize_script('TaveoMainJS', 'taveossdata', array(            
        'api_key_url' => $data,
        'api_key' => $taveo_api_key,
        'pagepost_id' => get_pagepost_id(),
    	'pagepost_url' => get_pagepost_url(),
        'create_api_url' => TAVEO_API_CREATE_URL,
        'by_dest_url' => TAVEO_API_BYDEST_URL
    ) );
	
	//make the thickbox available
	add_thickbox();

}
function taveo_add_meta_box() {

	add_meta_box('taveo_meta_links',__( 'Taveo Analytics - Current Links for this page', 
			'myplugin_textdomain' ),'taveo_metabox_callback','page','normal');
	add_meta_box('taveo_meta_links',__( 'Taveo Analytics - Current Links for this post', 
			'myplugin_textdomain' ),'taveo_metabox_callback','post','normal');
	
}
add_action( 'add_meta_boxes', 'taveo_add_meta_box' );

/* this function gets call every time an Admin page loads,
 * we verify this is a page we are interested in and load our crap here */
 
function taveo_load_stuff($hook) {
	//Test for "edit post"
	if ($hook == 'post.php') {
		taveo_enqueue_admin();   	
    }	
}
add_action('admin_enqueue_scripts', 'taveo_load_stuff');

/*this function gets added by the "init" hook, so it is available everywhere*/
function get_pagepost_url(){
	if(isset($_GET['post'])){
	   return get_permalink($_GET['post']);
	 }
	 else {
	 	return "ERROR Can't get URL...";
	 }
}
function get_pagepost_id(){
	if(isset($_GET['post'])){
		return $_GET['post'];
	}
	else {
		return -1;
	}
}


//Enqueues the scripts

/*add a "settings" link to the plugins page */
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_action_links' );

function add_action_links ( $links ) {
 $mylinks = array(
 '<a href="' . admin_url( 'admin.php?page=taveo_dashboard' ) . '">Settings</a>',
 );
return array_merge( $mylinks,$links );
}



?>
