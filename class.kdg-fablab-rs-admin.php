<?php
  class KdGFablab_RS_Admin {
    private static $initiated = FALSE;

    public static function init() {
      if (!self::$initiated) {
        self::init_hooks();
      }
    }

    private static function init_hooks() {
      self::$initiated = TRUE;

      add_action("admin_init", array("KdGFablab_RS_Admin", "kdg_fablab_rs_admin_register_fablab_settings"));
      add_action("admin_menu", array("KdGFablab_RS_Admin", "kdg_fablab_rs_admin_settings_menu"));
      add_action("edit_user_profile", array("KdGFablab_RS_Admin", "kdg_fablab_rs_show_custom_profile_fields"));
      add_action("show_user_profile", array("KdGFablab_RS_Admin", "kdg_fablab_rs_show_custom_profile_fields"));
      add_action('admin_notices', array('KdGFablab_RS_Admin', 'kdg_fablab_rs_admin_notice'));

      add_filter("manage_users_custom_column", array("KdGFablab_RS_Admin", "kdg_fablab_rs_modify_user_table_row"), 10, 3);
      add_filter("manage_users_columns", array("KdGFablab_RS_Admin", "kdg_fablab_rs_modify_user_table"));
    }

    /**
     * Register settings for Inventory plugin
     */
    public static function kdg_fablab_rs_admin_register_fablab_settings() {
      register_setting("kdg_fablab_rs_option-group", "kdg_fablab_rs_start_opening_hour");
      register_setting("kdg_fablab_rs_option-group", "kdg_fablab_rs_end_opening_hour");
      register_setting("kdg_fablab_rs_option-group", "kdg_fablab_rs_time_slot");
      register_setting("kdg_fablab_rs_option-group", "kdg_fablab_rs_open_in_weekends");
    }

    /**
     * Add a settings menu to the default settings menu from WordPress
     */
    public static function kdg_fablab_rs_admin_settings_menu() {
      add_options_page(
        "KdG Fablab Reservatie Instellingen",
        "Fablab Reservaties",
        "manage_options",
        "kdg-fablab-reservaties",
        ["KdGFablab_RS_Admin", "kdg_fablab_rs_admin_display_settings_page"]
      );
    }

    /**
     * Display content for the settings page of this plugin
     */
    public static function kdg_fablab_rs_admin_display_settings_page() {
      if (!current_user_can("manage_options")) {
        wp_die("You do not have sufficient permissions to access this page.");
      }
    ?>
    <div class="wrap">
      <h2>KdG Fablab Reservatie Instellingen</h2>
      <form method="post" action="options.php">
        <?php
          settings_fields("kdg_fablab_rs_option-group");
          do_settings_fields("kdg_fablab_rs_option-group", "");
        ?>
        <table class="form-table">
          <tbody>
            <tr>
              <th scope="row">
                Openingsuren
              </th>
              <td>
                <label for="kdg_fablab_rs_start_opening_hour">van</label>
                <input name="kdg_fablab_rs_start_opening_hour" type="number" min="0" max="23" step="1" value="<?php echo get_option("kdg_fablab_rs_start_opening_hour"); ?>" />
                <label for="kdg_fablab_rs_end_opening_hour">tot</label>
                <input name="kdg_fablab_rs_end_opening_hour" type="number" min="0" max="23" step="1" value="<?php echo get_option("kdg_fablab_rs_end_opening_hour"); ?>" />
              </td>
            </tr>
            <tr>
              <th scope="row">
                Tijdslot reservatie
              </th>
              <td>
                <input name="kdg_fablab_rs_time_slot" type="number" min="0" max="60" step="5" value="<?php echo get_option("kdg_fablab_rs_time_slot"); ?>" />
                minuten
              </td>
            </tr>
            <tr>
              <th scope="row">
                Open in weekends?
              </th>
              <td>
                <input
                  name="kdg_fablab_rs_open_in_weekends"
                  type="checkbox"
                  value="<?php echo (get_option("kdg_fablab_rs_open_in_weekends") === "false") ? "true" : "false"; ?>"
                  <?php echo (get_option("kdg_fablab_rs_open_in_weekends") === "false") ? "" : "checked"; ?>
                />
              </td>
            </tr>
          </tbody>
        </table>
        <?php
          submit_button();
        ?>
      </form>
    </div>
    <?php
    }

    /**
     * Inform the admin on how to add new machines
     */
    public static function kdg_fablab_rs_admin_notice() {
      if (get_transient('kdg-fablab-rs-admin-notice-page-reserveren-made')) {
      ?>
        <div class="updated notice-is-dismissible">
          <p>
            Reserveren-pagina aangemaakt!
          </p>
          <p>
            Om deze pagina toe te voegen aan het menu, ga naar: Weegave > Menu's.
          </p>
        </div>
      <?php
        // delete the transient, so it only gets displayed once
        delete_transient('kdg-fablab-rs-admin-notice-page-reserveren-made');
      }

      if (get_transient('kdg-fablab-rs-admin-notice-page-profile-made')) {
      ?>
        <div class="updated notice-is-dismissible">
          <p>
            "Mijn profiel"-pagina aangemaakt!
          </p>
          <p>
            Om deze pagina toe te voegen aan het menu, ga naar: Weegave > Menu's.
          </p>
        </div>
      <?php
        // delete the transient, so it only gets displayed once
        delete_transient('kdg-fablab-rs-admin-notice-page-profile-made');
      }

      if (get_transient('kdg-fablab-rs-admin-notice-page-edit-profile-made')) {
      ?>
        <div class="updated notice-is-dismissible">
          <p>
            "Profiel bewerken"-pagina aangemaakt!
          </p>
          <p>
            Om deze pagina toe te voegen aan het menu, ga naar: Weegave > Menu's.
          </p>
        </div>
      <?php
        // delete the transient, so it only gets displayed once
        delete_transient('kdg-fablab-rs-admin-notice-page-edit-profile-made');
      }

      if (get_transient('kdg-fablab-rs-admin-notice-page-profile-reservations-made')) {
      ?>
        <div class="updated notice-is-dismissible">
          <p>
            "Mijn reservaties"-pagina aangemaakt!
          </p>
          <p>
            Om deze pagina toe te voegen aan het menu, ga naar: Weegave > Menu's.
          </p>
        </div>
      <?php
        // delete the transient, so it only gets displayed once
        delete_transient('kdg-fablab-rs-admin-notice-page-profile-reservations-made');
      }
    }

    /**
     * Add custom columns to the users admin lay-out.
     */
    public static function kdg_fablab_rs_modify_user_table($column) {
      $column['who_are_you'] = "Wie ben je?";
      $column["company_name"] = "Naam bedrijf";
      $column['address'] = 'Adres';
      $column['postal_code'] = 'Postcode';
      $column['city'] = 'Gemeente';
      $column['tel_number'] = 'Telefoonnummer';
      $column['VAT_number'] = 'BTW-nummer';

      return $column;
    }

    /**
     * Show custom profile fields when admin is editing or looking up a profile
     */
    public static function kdg_fablab_rs_show_custom_profile_fields($user) {
     ?>
     <h3><?php esc_html_e("Extra informatie"); ?></h3>
     <table class="form-table">
       <tr>
         <th>
           <label for="who_are_you"><?php esc_html_e("Wie ben je?"); ?></label>
           <td>
             <?php echo esc_html(get_the_author_meta("who_are_you", $user->ID)); ?>
           </td>
         </th>
       </tr>

       <tr>
         <th>
           <label for="address"><?php esc_html_e("Adres"); ?></label>
           <td>
             <?php echo esc_html(get_the_author_meta("address", $user->ID)); ?>
           </td>
         </th>
       </tr>

       <tr>
         <th>
           <label for="tel_number"><?php esc_html_e("Telefoonnummer"); ?></label>
           <td>
             <?php echo esc_html(get_the_author_meta("tel_number", $user->ID)); ?>
           </td>
         </th>
       </tr>

       <tr>
         <th>
           <label for="postal_code"><?php esc_html_e("Postcode"); ?></label>
           <td>
             <?php echo esc_html(get_the_author_meta("postal_code", $user->ID)); ?>
           </td>
         </th>
       </tr>

       <tr>
         <th>
           <label for="city"><?php esc_html_e("Gemeente"); ?></label>
           <td>
             <?php echo esc_html(get_the_author_meta("city", $user->ID)); ?>
           </td>
         </th>
       </tr>

       <?php if(metadata_exists("user", $user->ID, "VAT_number")) { ?>
       <tr>
         <th>
           <label for="VAT_number"><?php esc_html_e("BTW-nummer"); ?></label>
           <td>
             <?php echo esc_html(get_the_author_meta("VAT_number", $user->ID)); ?>
           </td>
         </th>
       </tr>
      <?php } ?>

      <?php if(metadata_exists("user", $user->ID, "company_name")) { ?>
      <tr>
        <th>
          <label for="company_name"><?php esc_html_e("Naam bedrijf"); ?></label>
          <td>
            <?php echo esc_html(get_the_author_meta("company_name", $user->ID)); ?>
          </td>
        </th>
      </tr>
     <?php } ?>
     </table>
     <?php
    }

    /**
     * Get the user data in the column fields lay-out in the admin area.
     */
    public static function kdg_fablab_rs_modify_user_table_row($val, $column_name, $user_id) {
      global $wpdb;

      if ($column_name == 'who_are_you') {
        $who_are_you = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = 'who_are_you' AND user_id = %s", $user_id));
        return $who_are_you;
      }

      if ($column_name == 'address') {
        $address = $wpdb->get_var($wpdb->prepare( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = 'address' AND user_id = %s", $user_id));
        return $address;
      }

      if ($column_name == 'postal_code') {
        $postal_code = $wpdb->get_var($wpdb->prepare( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = 'postal_code' AND user_id = %s", $user_id));
        return $postal_code;
      }

      if ($column_name == 'city') {
        $city = $wpdb->get_var($wpdb->prepare( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = 'city' AND user_id = %s", $user_id));
        return $city;
      }

      if ($column_name == 'tel_number') {
        $tel_number = $wpdb->get_var($wpdb->prepare( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = 'tel_number' AND user_id = %s", $user_id));
        return $tel_number;
      }

      if ($column_name == 'VAT_number') {
        $VAT_number = $wpdb->get_var($wpdb->prepare( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = 'VAT_number' AND user_id = %s", $user_id));
        return $VAT_number;
      }

      if ($column_name == 'company_name') {
        $company_name = $wpdb->get_var($wpdb->prepare( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = 'company_name' AND user_id = %s", $user_id));
        return $company_name;
      }

      return $val;
    }
  }
