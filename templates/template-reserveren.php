<?php /* Template Name: Reserveren Template */ ?>
<?php
  $current_step = isset($_SESSION["reservation"]["reservation-step"]) ? intval($_SESSION["reservation"]["reservation-step"]) : 0;
  $reservation_type = isset($_SESSION["reservation"]["reservation-type"]) ? $_SESSION["reservation"]["reservation-type"] : NULL;
  $reservation_item = isset($_SESSION["reservation"]["reservation-item"]) ? $_SESSION["reservation"]["reservation-item"] : NULL;

  if (count($_GET) > 0) {
    if (isset($_GET["id"])) {
      $all_machines = new WP_Query([
        "posts_per_page" => -1,
        "post_type" => "machine"
      ]);

      while($all_machines->have_posts()) {
        $all_machines->the_post();
        $title = get_the_title();
        $title_lower = strtolower(str_replace(" ", "-", get_the_title()));

        if ($_GET["id"] === $title_lower) {
          $reservation_item = $_SESSION["reservation"]["reservation-item"] = $title;
          break;
        }
      }

      wp_reset_postdata();
    }

    if (isset($_GET["type"])) {
      $reservation_type = $_SESSION["reservation"]["reservation-type"] = $_GET["type"];
    }
  }

  // check if a certain step has errors
  $init_step_error = "";

  if (isset($_POST["submit"])) {
    // When submit value is next
    if (strtolower($_POST["submit"]) === "volgende") {
      $next_step = isset($_POST["step"]) ? intval($_POST["step"]) : 0;

      // Initial step
      if ($current_step === 0) {
        if (!empty($_POST["reservation-type"])) {
          $_SESSION["reservation"]["reservation-type"] = $reservation_type = $_POST["reservation-type"];
        } else {
          $init_step_error = "Maak een keuze";
        }
      } // End of initial step
    }
  }

  get_header();
?>
<main id="reserverenMain">
  <div class="title-content">
    <?php
      while(have_posts()) {
        the_post();
    ?>
    <h1><?php the_title(); ?></h1>
    <?php
        the_content();
      }
    ?>
  </div>

  <div class="progressbar-container">
    <ul class="disp-f progressbar">
      <li class="<?php echo ($current_step === 0) ? "progressbar-active" : ""; ?>">
        Toestel of workshop
      </li>
      <li>Details reservatie</li>
      <li>Bevestiging</li>
   </ul>
  </div>

  <div class="page-reserveren-content">
    <form id="reservation-form" action="<?php the_permalink(); ?>" method="post" novalidate>
      <?php if ($current_step === 0) { ?>
      <!-- Initial step -->
      <div class="input-group">
        <label for="reservation-type">Wat wilt u reserveren?</label>
        <select id="reservation-type" for="reservation-type" name="reservation-type" class="<?php echo (!empty($init_step_error)) ? "error" : "" ; ?>">
          <option value="" <?php echo ($reservation_type == "") ? "selected" : ""; ?>>-- Kiezen --</option>
          <option value="machine" <?php echo ($reservation_type == "machine") ? "selected" : ""; ?>>Toestel</option>
          <option value="workshop" <?php echo ($reservation_type == "workshop") ? "selected" : ""; ?>>Workshop</option>
        </select>
        <span class="error-message <?php echo ($init_step_error !== "") ? 'disp-b' : 'disp-n'; ?>"><?php echo $init_step_error; ?></span>
      </div>
      <input type="hidden" name="step" value="1" />
      <input class="btn btn-blue btn-submit" type="submit" name="submit" value="Volgende" />
      <!-- End of initial step -->
      <?php } ?>
    </form>
  </div>
</main>
<?php
  get_footer();

  // do not close php tags at the end of a file
