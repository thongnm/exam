<?php
// Load styles 
wp_enqueue_style('bootstrap-styles');
wp_enqueue_style('exam-styles');

if(!isset($url)) {
  $url = EXAM_PRATICE_PAGE_SLUG;
}
?>
<div class="list-group">
    <?php
    while ( $quizzes->have_posts() ) {
      $quizzes->the_post();
      ?>
        <a href="<?php bloginfo('url')?>/<?php echo $url ?>/?id=<?php the_ID(); ?><?php echo isset($type)? '&type='. $type : '' ?>  " class="list-group-item">
          <?php the_title( '<div>', '</div>' ); ?>
        </a>
      <?php
    }
    ?>
</div>

