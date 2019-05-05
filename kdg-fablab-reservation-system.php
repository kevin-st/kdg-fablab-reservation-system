<?php
  /**
   * @package KdG_Fablab_Reservation_System
   */
  /*
    Plugin Name: KdG Fablab Reservation System
    Description: Registreer gebruikers, maak reservaties en bekijk ze.
    Author: K3
    Version: 1.0.0
    Liscense: MIT
  */

  // make sure direct access to the plugin is blocked
  if (!defined('ABSPATH')) {
    die;
  }

  // define constants
  define('KDG_FABLAB_RS_VERSION', '1.0.0');
  define('KDG_FABLAB_RS_PLUGIN_DIR', plugin_dir_path(__FILE__));
  define('KDG_FABLAB_RS_PLUGIN_PREFIX', 'kdg_fablab_rs_');

  // define hooks
  register_activation_hook(__FILE__, 'kdg_fablab_rs_plugin_activation'); // execute on activation
  register_deactivation_hook(__FILE__, 'kdg_fablab_rs_plugin_deactivation'); // execute on deactivation

  // requirements
  require_once(KDG_FABLAB_RS_PLUGIN_DIR . 'class.kdg-fablab-rs.php');

  // execute KdGFablab_RS.init() when plugin is initialized
  add_action('init', array('KdGFablab_RS', 'init'));

  // when the current user is an admin
  if (is_admin() || (defined('WP_CLI') && WP_CLI)) {
    require_once(KDG_FABLAB_RS_PLUGIN_DIR . 'class.kdg-fablab-rs-admin.php');

    // execute KdGFablab_Admin.init() when plugin is initialized
    add_action('init', array('KdGFablab_RS_Admin', 'init'));
  }

  // functions
  /**
   * Actions to perform on plugin activation
   */
  function kdg_fablab_rs_plugin_activation() {
    // code to be executed when plugin is activated
  }

  /**
   * Actions to perform on plugin deactivation
   */
  function kdg_fablab_rs_plugin_deactivation() {
    // code to be executed when plugin is deactivated
  }
