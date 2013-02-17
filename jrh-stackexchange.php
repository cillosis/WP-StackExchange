<?php
/*
    Plugin Name: StackExchange Questions
    Plugin URI: http://www.jeremyharris.me
    Description: Embed StackExchange questions related to your post.
    Version: 0.1
    Author: Jeremy Harris
    Author URI: http://www.jeremyharris.me
    License: MIT http://opensource.org/licenses/MIT
*/

// Register StackExchange Questions Widget
include_once( plugin_dir_path( __FILE__ ) . 'jrh-stackexchange-widget.php' );
add_action( 'widgets_init', create_function( '', 'return register_widget("jrh_stackexchange_widget");' ) );

// Register Admin Interface
include_once( plugin_dir_path( __FILE__ ) . 'jrh-stackexchange-admin.php' );
add_action( 'admin_menu', create_function( '', 'return register_widget("jrh_stackexchange_admin");' ) );