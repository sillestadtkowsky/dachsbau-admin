<?php

/**
 * Required Styles and Scripts
 */
function wp_equeue() {
  
    // JS
    $javascript_uri = MC_PLUGIN_PLUGIN_PATH . 'admin/js/admin.js';
    $javascript = MC_PLUGIN_PLUGIN_DIR.  '/admin/js/admin.js';

    wp_register_script( 
      'script-admin', 
      $javascript_uri,
      array( 'jquery' ), 
      filemtime( $javascript ) 
    );
    wp_enqueue_script( 'script-admin' );

    // CSS
    $stylesheet_uri = MC_PLUGIN_PLUGIN_PATH .  'admin/css/admin.css';
    $stylesheet = MC_PLUGIN_PLUGIN_DIR .  '/admin/css/admin.css';

    wp_register_style( 
      'style', 
      $stylesheet_uri,
      array(), 
      filemtime( $stylesheet ) 
    );
    wp_enqueue_style( 'style' ); 
}
add_action( 'admin_enqueue_scripts', 'wp_equeue' , 99999);