<?php

/*
Contributors: christian.loelkes
Plugin Name: 3DHubs
Plugin URI: https://wordpress.org/plugins/3dhubs/
Description: 3DHubs Plugin for Wordpress
Stable tag: 0.2
Version: 0.3
Tags: 3d, 3dprinting, 3dhubs
Requires at least: 3.0
Tested up to: 4.1
Author: Christian Lölkes
Author URI: http://www.db4cl.com
License: GPLv2

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

// --------------------------
// Plugin class starting here
// --------------------------

if(!class_exists( 'ThreeDHubsPlugin' )) {
	class ThreeDHubsPlugin {
		public function __construct() {	// Initialize Settings
			require_once( sprintf( "%s/settings.php", dirname( __FILE__ )));
			$ThreeDHubsSettings = new ThreeDHubsSettings();

			function insert_3DHubs( $atts ) {
				extract( shortcode_atts( array(
					'url' => get_option( '3dhubs_url' ),
				), $atts ) );

				generate3DHubsButton($url);
			}

			// Function for the shortcode
			add_shortcode( '3DHubs', 'insert_3DHubs' );

		} // END public function __construct

		public static function activate() {}
		public static function deactivate() {}
	}
}

// ------------------------
// Plugin class ending here
// ------------------------

function generate3DHubsButton($url, $color = "red" ) {
	$class = "hubs-btn hubs-btn-".$color;
	if($color != 'white')   $file = "logo-heart-white-gradient-30px.png";
	else                    $file = "logo-heart-30px.png";
	return '<a href="'.$url.'" class="'.$class.'"><img src="'.plugins_url( $file , __FILE__ ).'" width="30px"><span>3D Print</span></a>';
}

function ThreeDHubsCSS() {
    wp_enqueue_style('3d-hubs-button', plugins_url( '3dhubs.css' , __FILE__ ));
}

// Add the settings link to the plugins page
function ThreeDHubsWidget_settings_link( $links ) {
    $ThreeDHubs_settings_link = '<a href="options-general.php?page=3dhubs">Settings</a>';
    array_unshift( $links, $ThreeDHubs_settings_link );
    return $links;
}

// --------------------------
// Widget class starting here
// --------------------------

class ThreeDHubsWidget extends WP_Widget {

	// This array contains all the form fields
	public $widget_form_fields = array(
		array( 'title' 	=> 'Title', 	'type' => 'text' ),
		array( 'title' 	=> 'URL',		'type' => 'text' ),
		array( 'title' 	=> 'Free text', 'type' => 'text' )
	);

	function ThreeDHubsWidget() {
		$widget_ops = array( 'classname' => 'ThreeDHubsWidget', 'description' => 'Displays link to 3DHubs.' );
		$this->WP_Widget( 'ThreeDHubsWidget', '3DHubs.com Widget', $widget_ops );
	}

	function form( $instance ) {
		foreach( $this->widget_form_fields as $field ) {
			echo '<p><label for="'.$this->get_field_id( $field[ 'title' ] ).'"></label>';
			echo $field[ 'title' ].': <input class="widefat" id="'.$this->get_field_id( $field[ 'title' ] ).'" ';
			echo ' name="'.$this->get_field_name( $field[ 'title' ] ).'" type="'.$field[ 'type' ];
			echo '" value="'.attribute_escape( $instance[ $field[ 'title' ] ] ).'" />';
			echo '</p>';
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		foreach( $this->widget_form_fields as $field ) { 
			$instance[ $field[ 'title' ] ] = $new_instance[ $field[ 'title' ] ]; 
		}
		return $instance;
	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		echo $before_widget;
		$title = apply_filters( 'widget_title', $instance[ 'Title' ] );
		if ( !empty( $title )) {
			echo $before_title . $title . $after_title;;
			echo generate3DHubsButton($instance[ "URL" ]);
			echo '<p>'.$instance[ "Free text" ].'</p>';
			echo $after_widget;
		}
	}
}

// ------------------------
// Widget class ending here
// ------------------------


if( class_exists( 'ThreeDHubsPlugin' )) {

	// Installation and uninstallation hooks
	register_activation_hook( __FILE__, array( 'ThreeDHubsPlugin', 'activate' ));
	register_deactivation_hook( __FILE__, array( 'ThreeDHubsPlugin', 'deactivate' ));

	// Instantiate the plugin class
	$ThreeDHubsPlugin = new ThreeDHubsPlugin();

	// Add a link to the settings page onto the plugin page
	if( isset( $ThreeDHubsPlugin )) {

		// Add the widget and the script for the live update
		add_action( 'widgets_init', create_function( '', 'return register_widget( "ThreeDHubsWidget" );' ) );
		add_action( 'wp_enqueue_scripts', 'ThreeDHubsCSS' );

		$plugin = plugin_basename( __FILE__ );
		add_filter( "plugin_action_links_".$plugin, 'ThreeDHubsWidget_settings_link' );
	}
}
