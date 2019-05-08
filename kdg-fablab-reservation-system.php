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
  add_action("admin_enqueue_scripts", "kdg_fablab_rs_enqueue_scripts");

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
    if (!current_user_can('activate_plugins')) {
      return;
    }

    kdg_fablab_rs_update_settings();

    global $wpdb;

    if ($wpdb->get_row("SELECT post_name FROM $wpdb->posts WHERE post_name = 'reserveren'", 'ARRAY_A') === NULL) {
      set_transient("kdg-fablab-rs-admin-notice-page-reserveren-made", true, 5);

      wp_insert_post([
        "post_title"    => "Reserveren",
        "post_status"   => "publish",
        "post_author"   => 1,
        "post_type"     => "page",
        "page_template" => "page-reserveren.php"
      ]);
    }

    if ($wpdb->get_row("SELECT post_name FROM $wpdb->posts WHERE post_name = 'mijn-profiel'", 'ARRAY_A') === NULL) {
      set_transient("kdg-fablab-rs-admin-notice-page-profile-made", true, 5);

      wp_insert_post([
        "post_title"    => "Mijn profiel",
        "post_status"   => "publish",
        "post_author"   => 1,
        "post_type"     => "page",
        "page_template" => "page-profile.php"
      ]);
    }

    if ($wpdb->get_row("SELECT post_name FROM $wpdb->posts WHERE post_name = 'edit'", 'ARRAY_A') === NULL) {
      set_transient("kdg-fablab-rs-admin-notice-page-edit-profile-made", true, 5);

      wp_insert_post([
        "post_title"    => "Profiel bewerken",
        "post_name"     => "edit",
        "post_status"   => "publish",
        "post_author"   => 1,
        "post_parent"   => get_page_by_title("Mijn profiel")->ID,
        "post_type"     => "page",
        "page_template" => "edit-profile.php"
      ]);
    }

    if ($wpdb->get_row("SELECT post_name FROM $wpdb->posts WHERE post_name = 'reservaties'", 'ARRAY_A') === NULL) {
      set_transient("kdg-fablab-rs-admin-notice-page-profile-reservations-made", true, 5);

      wp_insert_post([
        "post_title"    => "Mijn reservaties",
        "post_name"     => "reservaties",
        "post_status"   => "publish",
        "post_author"   => 1,
        "post_parent"   => get_page_by_title("Mijn profiel")->ID,
        "post_type"     => "page",
        "page_template" => "my-reservations.php"
      ]);
    }
  }

  /**
   * Actions to perform on plugin deactivation
   */
  function kdg_fablab_rs_plugin_deactivation() {
    // code to be executed when plugin is deactivated
  }

  /**
   * Add styles and scripts to the admin
   */
  function kdg_fablab_rs_enqueue_scripts() {
    wp_enqueue_style("admin-styles", get_template_directory_uri() . '/css/modules/admin.css');
  }

  /**
   * Initialize and update settings for the reservation plugin
   */
  function kdg_fablab_rs_update_settings() {
    if (!get_option("kdg_fablab_rs_opening_hours")) {
      update_option("kdg_fablab_rs_opening_hours", "");
    }

    if (!get_option("kdg_fablab_rs_time_slot")) {
      update_option("kdg_fablab_rs_time_slot", "15");
    }
  }
