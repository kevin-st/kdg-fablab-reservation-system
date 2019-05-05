<?php
  class KdGFablab_RS {
    private static $_initiated = FALSE;

    /**
     * Initialize the plugin
     */
    public static function init() {
      if (!self::$_initiated) {
        self::init_hooks();
      }
    }

    /**
     * Initialize WordPress hooks
     */
    private static function init_hooks() {
      self::$_initiated = TRUE;

      add_action('admin_init', array("KdGFablab_RS", "kdg_fablab_rs_redirect_to_front_end"));
      add_action('register_form', array("KdGFablab_RS", "kdg_fablab_rs_registration_form"));
      add_action("template_redirect", array("KdGFablab_RS", "kdg_fablab_rs_redirect"));
      add_action('template_redirect', array("KdGFablab_RS", "kdg_fablab_rs_author_page_redirect"));
      add_action('user_register', array("KdGFablab_RS", "kdg_fablab_rs_user_register"));
      add_action('wp_loaded', array("KdGFablab_RS", "kdg_fablab_rs_hide_admin_bar_sub"), 1);

      add_filter("generate_rewrite_rules", array("KdGFablab_RS", "kdg_fablab_rs_custom_rewrite_rules"));
      add_filter("query_vars", array("KdGFablab_RS", "kdg_fablab_rs_query_vars"));
      add_filter('registration_errors', array("KdGFablab_RS", "kdg_fablab_rs_registration_errors"), 10, 3);
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
     * Recognize custom query vars
     */
    public static function kdg_fablab_rs_query_vars($vars) {
      $vars[] = "id";

      return $vars;
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
  }
