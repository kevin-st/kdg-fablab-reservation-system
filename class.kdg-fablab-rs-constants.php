<?php
  class KdGFablab_RS_Constants {
    private static $_initiated = FALSE;
    private static $_site_url = "";
    private static $_admin_url = "";
    private static $_new_reservations_url = "NEW_RESERVATIONS_URL";
    private static $_blog_name = "BLOGNAME";
    private static $_message_on_submission = "";
    private static $_message_on_approval = "";
    private static $_message_on_denial = "";

    public static function init() {
      if (!self::$_initiated) {
        self::$_site_url = site_url();
        self::$_admin_url = admin_url();
        self::$_new_reservations_url = admin_url("edit.php?post_type=reservation&reservation-approved=-1");

        self::$_blog_name = get_bloginfo("name");

        self::$_initiated = TRUE;
      }
    }

    public static function get_site_url() {
      return self::$_site_url;
    }

    public static function get_admin_url() {
      return self::$_admin_url;
    }

    public static function get_new_reservations_url() {
      return self::$_new_reservations_url;
    }

    public static function get_blog_name() {
      return self::$_blog_name;
    }

    public static function get_message_on_submission() {
      if (empty(self::$_message_on_submission)) {
        return "Hey admin\r\n\nIemand diende een nieuwe reservatie in op "
        . self::$_blog_name .
        ". Om de nieuwe reservaties te bekijken, klik op onderstaande link:\r\n\n"
        . self::$_new_reservations_url .
        "\r\n\nMet vriendelijke groeten\r\nHet KdG Fablab team";
      } else {
        return self::$_message_on_submission;
      }
    }
  }
