<?php
  class KdGFablab_RS {
    private static $_initiated = FALSE;

    /**
     * Initialize the plugin
     */
    public static function init() {
      if (!self::$_initiated) {
        self::init_hooks();
        self::kdg_fablab_rs_register_custom_post_types();
      }
    }

    /**
     * Initialize WordPress hooks
     */
    private static function init_hooks() {
      self::$_initiated = TRUE;

      add_action("admin_init", array("KdGFablab_RS", "kdg_fablab_rs_approve"), 10);
      add_action('admin_init', array("KdGFablab_RS", "kdg_fablab_rs_redirect_to_front_end"));
      add_action('admin_menu', array("KdGFablab_RS", "kdg_fablab_rs_reservation_admin_menu"));
      add_action("manage_reservation_posts_custom_column", array("KdGFablab_RS", "kdg_fablab_rs_manage_admin_columns_data"), 10, 2);
      add_action('pre_get_posts', array("KdGFablab_RS", "kdg_fablab_rs_alter_admin_query"));
      add_action('register_form', array("KdGFablab_RS", "kdg_fablab_rs_registration_form"));
      add_action("template_redirect", array("KdGFablab_RS", "kdg_fablab_rs_redirect"));
      add_action('template_redirect', array("KdGFablab_RS", "kdg_fablab_rs_author_page_redirect"));
      add_action('user_register', array("KdGFablab_RS", "kdg_fablab_rs_user_register"));
      add_action('wp_loaded', array("KdGFablab_RS", "kdg_fablab_rs_hide_admin_bar_sub"), 1);

      add_filter("generate_rewrite_rules", array("KdGFablab_RS", "kdg_fablab_rs_custom_rewrite_rules"));
      add_filter("query_vars", array("KdGFablab_RS", "kdg_fablab_rs_query_vars"));
      add_filter('registration_errors', array("KdGFablab_RS", "kdg_fablab_rs_registration_errors"), 10, 3);
      add_filter('manage_reservation_posts_columns', array("KdGFablab_RS", "kdg_fablab_rs_manage_admin_columns"));
      add_filter("post_row_actions", array("KdGFablab_RS", "kdg_fablab_rs_action_links"), 10, 2);
    }

    /**
     * Define approve reservation hook
     */
    public static function kdg_fablab_rs_approve() {
      if (isset($_GET["action"]) && isset($_GET["post"])) {
        $post = get_post($_GET["post"]);

        if ($_GET["action"] === "kdg_fablab_rs_approve") {
          self::kdg_fablab_rs_approve_reservation($post);
        } else if ($_GET["action"] === "kdg_fablab_rs_unapprove") {
          self::kdg_fablab_rs_unapprove_reservation($post);
        }
      }
    }

    /**
     * Create submenu pages for the reservation post type
     */
    public static function kdg_fablab_rs_reservation_admin_menu() {
      add_submenu_page("edit.php?post_type=reservation", "Aanvaarde reservaties", "Aanvaarde reservaties", "manage_options", "edit.php?post_type=reservation&reservation-approved=1");
      add_submenu_page("edit.php?post_type=reservation", "Afgewezen reservaties", "Afgewezen reservaties", "manage_options", "edit.php?post_type=reservation&reservation-approved=0");
      add_submenu_page("edit.php?post_type=reservation", "Nieuwe reservaties", "Nieuwe reservaties", "manage_options", "edit.php?post_type=reservation&reservation-approved=-1");
    }

    /**
     * Hide the admin bar when a subscriber logs in to the website
     */
    public static function kdg_fablab_rs_hide_admin_bar_sub() {
      $current_user = wp_get_current_user();

      if (count($current_user->roles) == 1 && $current_user->roles[0] == "subscriber") {
        show_admin_bar(false);
      }
    }

    /**
     * Redirect subscribers to the home page when logging in
     */
    public static function kdg_fablab_rs_redirect_to_front_end() {
      $current_user = wp_get_current_user();

      if (count($current_user->roles) == 1 && $current_user->roles[0] == "subscriber") {
        $is_user_initialized = get_user_meta(get_current_user_id(), "is_initialized", true);

        if ($is_user_initialized) {
          wp_redirect(site_url('/mijn-profiel/'));
        } else {
          wp_redirect(site_url('/mijn-profiel/edit/'));
        }

        exit;
      }
    }

    /**
     * Add custom action links
     */
    public static function kdg_fablab_rs_action_links($actions, $post) {
      if ($post->post_type == "reservation" && (!isset($_GET["post_status"]))) {
        // store a reference to the edit and delete action links
        $edit = isset($actions["edit"]) ? $actions["edit"] : "";
        $trash = $actions["trash"];

        // store the reference to default actions in the actions array
        $actions = [
          "edit" => $edit,
          "trash" => $trash
        ];

        // fetch all metadata from the current reservation
        $reservation_approved = get_post_meta($post->ID, "reservation_approved", true);

        // build action url
        $url = admin_url("post.php?post=". $post->ID);

        // approve url
        if (intval($reservation_approved) === -1 || intval($reservation_approved) === 0) {
          $approve_link = wp_nonce_url(add_query_arg(["action" => "kdg_fablab_rs_approve"], $url), "approve_reservation");

          // add the url to actions
          $actions["kdg_fablab_rs_approve"] = sprintf('<a href="%1$s">%2$s</a>', esc_url($approve_link), "Aanvaarden");
        }

        // unapprove url
        if (intval($reservation_approved) === -1 || intval($reservation_approved) === 1) {
          $unapprove_link = wp_nonce_url(add_query_arg(["action" => "kdg_fablab_rs_unapprove"], $url), "unapprove_reservation");

          // add the url to actions
          $actions["kdg_fablab_rs_unapprove"] = sprintf('<a href="%1$s">%2$s</a>', esc_url($unapprove_link), "Afwijzen");
        }
      }

      return $actions;
    }

    /**
     * Approve a reservation
     */
    public static function kdg_fablab_rs_approve_reservation($reservation) {
      update_post_meta($reservation->ID, "reservation_approved", 1);
    }

    /**
     * Unapprove a reservation
     */
    private static function kdg_fablab_rs_unapprove_reservation($reservation) {
      update_post_meta($reservation->ID, "reservation_approved", 0);
    }

    /**
     * Recognize custom query vars
     */
    public static function kdg_fablab_rs_query_vars($vars) {
      $vars[] = "id";
      $vars[] = "type";

      $vars[] = "reservation-type";
      $vars[] = "reservation-item";
      $vars[] = "reservation-date";
      $vars[] = "reservation-approved";

      return $vars;
    }

    /**
     * Add admin columns to reservations overview (admin)
     */
    public static function kdg_fablab_rs_manage_admin_columns() {
      $columns["title"] = "Naam";
      $columns["author"] = "Klant";
      $columns['reservation-type'] = "Type reservatie";
      $columns["reservation-item"] = "Toestel/Workshop";
      $columns['reservation-date'] = "Datum";
      $columns["reservation-time-slots"] = "Tijdstippen";
      $columns["reservation-approved"] = "Status";

      return $columns;
    }

    /**
     * Fetch data for each custom column in reservation overview (admin)
     */
    public static function kdg_fablab_rs_manage_admin_columns_data($column, $post_id) {
      switch($column) {
        case "reservation-type":
          $val = get_post_meta($post_id, "reservation_type", true);

          echo '<a href="';
          echo admin_url( 'edit.php?post_type=reservation&reservation-type=' . urlencode($val));
          echo '">';
          echo $val;
          echo '</a>';
          break;

        case "reservation-item":
          $val = get_post_meta($post_id, "reservation_item", true);

          echo '<a href="';
          echo admin_url( 'edit.php?post_type=reservation&reservation-item=' . urlencode($val));
          echo '">';
          echo $val;
          echo '</a>';
          break;

        case "reservation-date":
          $val = get_post_meta($post_id, "reservation_date", true);

          echo '<a href="';
          echo admin_url( 'edit.php?post_type=reservation&reservation-date=' . urlencode($val));
          echo '">';
          echo date_i18n("d F Y", strtotime($val));
          echo '</a>';
          break;

        case "reservation-time-slots":
          $time_slots = get_post_meta($post_id, "reservation_time_slots", true);

          foreach($time_slots as $time_slot) {
            echo "<li>" . $time_slot . "</li>";
          }

          break;

        case "reservation-approved":
          $val = get_post_meta($post_id, "reservation_approved", true);
          $str_rep = "";

          if (intval($val) === 0) {
            $str_rep = "Afgewezen";
          } else if (intval($val) === 1) {
            $str_rep = "Aanvaard";
          } else {
            $str_rep = "In behandeling";
          }

          echo '<a href="';
          echo admin_url( 'edit.php?post_type=reservation&reservation-approved=' . urlencode($val));
          echo '">';
          echo $str_rep;
          echo '</a>';
          break;
      }
    }

    /**
     * Setup custom query vars for reservation overview (admin)
     */
    public static function kdg_fablab_rs_alter_admin_query($query) {
      if (!is_admin() || $query->query['post_type'] !== "reservation") {
        return;
      }

      if (isset($query->query_vars['reservation-type'])) {
        $query->set('meta_key', 'reservation_type');
        $query->set('meta_value', $query->query_vars['reservation-type'] );
      }

      if (isset($query->query_vars['reservation-item'])) {
        $query->set('meta_key', 'reservation_item');
        $query->set('meta_value', $query->query_vars['reservation-item'] );
      }

      if (isset($query->query_vars['reservation-date'])) {
        $query->set('meta_key', 'reservation_date');
        $query->set('meta_value', $query->query_vars['reservation-date'] );
      }

      if (isset($query->query_vars['reservation-approved'])) {
        $query->set('meta_key', 'reservation_approved');
        $query->set('meta_value', $query->query_vars['reservation-approved'] );
      }
    }

    /**
     * Generate custom rewrite rules
     */
    public static function kdg_fablab_rs_custom_rewrite_rules($wp_rewrite) {
      $wp_rewrite->rules = array_merge(
        ["reserveren\/([a-z]+-[a-z]+)/?$" => 'index.php?id=$matches[1]'],
        $wp_rewrite->rules
      );
    }

    /**
     * Redirect to the correct templates when a query-var is added to the URL
     */
    public static function kdg_fablab_rs_redirect() {
      $machine_id = sanitize_text_field(get_query_var('id'));

      if ($machine_id !== "") {
        include(KDG_FABLAB_RS_PLUGIN_DIR . "/templates/template-reserveren.php");
        die();
      }
    }

    /**
     * Add custom fields to registration form.
     */
    public static function kdg_fablab_rs_registration_form() {
      $first_name = (!empty($_POST['first_name'])) ? sanitize_text_field($_POST['first_name']) : '';
      $last_name = (!empty($_POST['last_name'])) ? sanitize_text_field($_POST['last_name']) : '';
      $who_are_you = !empty($_POST['who_are_you']) ? sanitize_text_field($_POST['who_are_you']) : '';
    ?>
    <!-- user name -->
    <p id="user_name_fields">
      <label id="reg_first_name_label" for="reg_first_name">
        <?php esc_html_e('Voornaam'); ?>
        <br />
        <input type="text" name="first_name" id="reg_first_name" class="input" value="<?php echo esc_attr($first_name); ?>">
      </label>

      <label id="reg_last_name_label" for="reg_last_name">
        <?php esc_html_e('Achternaam'); ?>
        <br />
        <input type="text" name="last_name" id="reg_last_name" class="input" value="<?php echo esc_attr($last_name); ?>">
      </label>
    </p>
    <!-- end of user name-->

    <!-- who are you -->
    <p>
      <label for="reg_who_are_you">
        <?php esc_html_e('Wie ben je?') ?>
        <br />
        <select id="reg_who_are_you" name="who_are_you" class="input">
          <option value="" <?php if ($who_are_you == "") echo "selected"; ?>>-- Kiezen --</option>
          <option value="student" <?php if ($who_are_you == "student") echo "selected"; ?>>Student</option>
          <option value="bedrijf" <?php if ($who_are_you == "bedrijf") echo "selected"; ?>>Bedrijf</option>
          <option value="particulier" <?php if ($who_are_you == "particulier") echo "selected"; ?>>Particulier</option>
        </select>
      </label>
    </p>
    <!-- end of who are you -->

    <?php
    }

    /**
     * Validation custom fields registration
     */
    public static function kdg_fablab_rs_registration_errors($errors, $sanitized_user_login, $user_email) {
      if (empty($_POST['first_name'])) {
        $errors->add('first_name_error', __('<strong>FOUT</strong>: Vul je voornaam in.'));
      }

      if (empty($_POST['last_name'])) {
        $errors->add('last_name_error', __('<strong>FOUT</strong>: Vul je achternaam in.'));
      }

      if (empty($_POST['who_are_you'])) {
        $errors->add('who_are_you_error', __('<strong>FOUT</strong>: Vertel me wie je bent'));
      }

      return $errors;
    }

    /**
     * Save custom data when user is being registered
     */
    public static function kdg_fablab_rs_user_register($user_id) {
      if (!empty($_POST['first_name'])) {
        update_user_meta($user_id, 'first_name', sanitize_text_field($_POST['first_name']));
      }

      if (!empty($_POST['last_name'])) {
        update_user_meta($user_id, 'last_name', sanitize_text_field($_POST['last_name']));
      }

      if (!empty($_POST['who_are_you'])) {
        update_user_meta($user_id, 'who_are_you', sanitize_text_field($_POST['who_are_you']));

        if ($_POST['who_are_you'] !== "student" || $_POST["who_are_you"] !== "particulier") {
          add_user_meta($user_id, "VAT_number", "");
          add_user_meta($user_id, "company_name", "");
        }

        add_user_meta($user_id, "address", "");
        add_user_meta($user_id, "tel_number", "");
        add_user_meta($user_id, "postal_code", "");
        add_user_meta($user_id, "city", "");
        add_user_meta($user_id, "is_initialized", 0);
      }
    }

    /**
     * Redirect to the homepage when someone tries to access the page of an author.
     */
    public static function kdg_fablab_rs_author_page_redirect() {
      if (is_author()) {
        wp_redirect(home_url());
      }
    }

    /**
     * Enable custom post types for this plugin.
     */
    private static function kdg_fablab_rs_register_custom_post_types() {
      // machine post type
      register_post_type("reservation",
        [
          "description" => "Reservaties worden gemaakt door een gebruiker, en bevatten informatie over toestellen en workshops.",
          "labels" => [
            "all_items"     => __("Alle reservaties"),
            "edit_item"     => __("Reservatie bijwerken"),
            "name"          => __("Reservaties"),
            "search_items"      => __("Reservatie zoeken"),
            "singular_name" => __("Reservatie")
          ],
          'capabilities' => [
            'create_posts' => 'do_not_allow',
            "edit_post" => "edit_reservation",
            "edit_others_posts" => "edit_other_reservations",
            "publish_posts" => "publish_reservations",
            "read_post" => "read_reservation",
            "read_private_posts" => "read_private_reservations",
            "delete_post" => "delete_reservation"
          ],
          "map_meta_cap" => true,
          "menu_icon" => "dashicons-welcome-write-blog",
          "public" => false,
          "show_ui" => true,
          "show_in_mennu" => "edit.php?post_type=reservation",
          "query_var" => true,
          "supports" => [ "title", "editor" ],
          "rewrite" => ["slug" => "reservaties"]
        ]);

      flush_rewrite_rules();
    }
  }
