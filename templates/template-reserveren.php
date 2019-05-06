<?php

  get_header();
?>
<main>
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
  <div class="page-reserveren-content">
    <form id="reservation-form" action="<?php the_permalink(); ?>" method="post">
    </form>
  </div>
</main>
<?php
  get_footer();

  // do not close php tags at the end of a file
