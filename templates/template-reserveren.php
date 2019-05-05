<?php get_header(); ?>
<?php
  /*
    REMOVE THIS COMMENT

    content for generic page goes here (layout can be used accross multiple pages):
    -> e.g.
      - About us
      - Biography of an author
      - ...

    creating a custom page:
    -> https://developer.wordpress.org/themes/template-files-section/page-template-files/#creating-custom-page-templates-for-global-use
   */
?>
<main>
  <h1>Reserveren</h1>
  <?php
    $homepage_news = new WP_Query([
      "post_type" => "machine",
      "posts_per_page" => -1 // control number of posts with this -> -1 is all posts
    ]);

  while($homepage_news->have_posts()) {
    $homepage_news->the_post();
?>
  <h2><?php the_title(); ?></h2>
<?php
  }
?>
</main>
<?php
  get_footer();

  // do not close php tags at the end of a file
