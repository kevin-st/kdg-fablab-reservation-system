<?php
  class KdGFablab_RS_Constants {
    private static $_initiated = FALSE;
    private static $_message_on_submission = "";
    private static $_message_on_approval = "";
    private static $_message_on_denial = "";
    private static $_KEY_WORDS = [];

    /**
     * Initialize values
     */
    public static function init() {
      if (!self::$_initiated) {
        self::$_KEY_WORDS = [
          "ADMIN" => "admin",
          "MY_RESERVATIONS_URL" => site_url("/mijn-profiel/reservaties"),
          "NEW_RESERVATIONS_URL" => admin_url("edit.php?post_type=reservation&reservation-approved=-1"),
          "RESERVE_URL" => site_url("/reserveren"),
          "SITENAME" => get_bloginfo("name"),
          "SITE_URL" => site_url("/"),
          "USERNAME" => ""
        ];

        $_message_on_submission = get_option("kdg_fablab_rs_email_content_on_submission");
        $_message_on_approval = get_option("kdg_fablab_rs_email_content_on_approval");
        $_message_on_denial = get_option("kdg_fablab_rs_email_content_on_denial");

        self::$_initiated = TRUE;
      }
    }

    /**
     * Get the URL to the new reservations list (admin)
     */
    public static function kdg_fablab_rs_get_new_reservations_url() {
      return self::$_KEY_WORDS["NEW_RESERVATIONS_URL"];
    }

    /**
     * Get the URL to the reservations page (frontend)
     */
    public static function kdg_fablab_rs_get_reserve_url() {
      return self::$_KEY_WORDS["RESERVE_URL"];
    }

    /**
     * Get the URL to the my reservations page (frontend)
     */
    public static function kdg_fablab_rs_get_my_reservations_url() {
      return self::$_KEY_WORDS["MY_RESERVATIONS_URL"];
    }

    /**
     * Get constant defined keywords
     */
    public static function kdg_fablab_rs_get_key_words() {
      return self::$_KEY_WORDS;
    }

    /**
     * Reset the reservation session
     */
    public static function kdg_fablab_rs_reset_reservation_process() {
      if (isset($_SESSION["reservation"])) {
        $_SESSION["reservation"] = [];
      }
    }

    /**
     * Get the content of the message when a reservation is submitted (defined in admin)
     */
    public static function kdg_fablab_rs_get_message_on_submission() {
      if (empty(self::$_message_on_submission)) {
        self::$_message_on_submission = "Hey ADMIN\r\n\n"
        ."Iemand diende een nieuwe reservatie in op SITENAME. Om de nieuwe reservaties te bekijken, klik op onderstaande link:\r\n\n"
        ."NEW_RESERVATIONS_URL\r\n\n"
        ."Met vriendelijke groeten\r\n"
        ."Het KdG Fablab team";
      }

      return self::$_message_on_submission;
    }

    /**
     * Get the content of the message when a reservation is approved (defined in admin)
     */
    public static function kdg_fablab_rs_get_message_on_approval() {
      if (empty(self::$_message_on_approval)) {
        self::$_message_on_approval = "Hey USERNAME\r\n\n"
        ."Jouw reservatie werd goedgekeurd!\r\n"
        ."Bekijk het overzicht van je reservaties via volgende link:\r\n\n"
        ."MY_RESERVATIONS_URL\r\n\n"
        ."Met vriendelijke groeten\r\n"
        ."Het KdG Fablab team";
      }

      return self::$_message_on_approval;
    }

    /**
     * Get the content of the message when a reservation is denied (defined in admin)
     */
    public static function kdg_fablab_rs_get_message_on_denial() {
      if (empty(self::$_message_on_denial)) {
        self::$_message_on_denial = "Hey USERNAME\r\n\nJouw reservatie werd afgewezen. Dit komt vast omdat er een dubbele boeking heeft plaatsgevonden.\r\n"
        ."Bekijk het overzicht van je reservaties:\r\n\n"
        ."MY_RESERVATIONS_URL\r\n\n"
        ."Of als je liever een nieuwe reservatie maakt, dan kan je dat hier doen:\r\n"
        ."RESERVE_URL\r\n\n"
        ."Met vriendelijke groeten\r\n"
        ."Het KdG Fablab team";
      }

      return self::$_message_on_denial;
    }

    /**
     * Process the selected message to send on default keywords
     */
    public static function kdg_fablab_rs_process_message($message, $user = NULL) {
      if ($user != NULL) {
        if (strpos($message, "USERNAME")) {
          $message = str_replace("USERNAME", get_userdata($user)->user_firstname, $message);
        }
      }

      foreach(self::$_KEY_WORDS as $key_word => $key_word_value) {
        $message = str_replace($key_word , $key_word_value, $message);
      }

      return $message;
    }
  }
