<?php
// Load styles 
wp_enqueue_style('exam-styles');

$quizzes = ExamData::get_list_quizzes();
?>
<div>
<ul>
    <?php
    while ( $quizzes->have_posts() ) {
      $quizzes->the_post();
      ?>
      <li id="post-<?php the_ID(); ?>" >
        <a href="<?php bloginfo('url')?>/practice/?id=<?php the_ID(); ?>">
          <?php the_title( '<div>', '</div>' ); ?>
        </a>
      </li>
      <?php
    }
    ?>
</div>

