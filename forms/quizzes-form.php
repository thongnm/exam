<?php

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
          <div><?php echo isset($custom_title)? ExamData::get_custom_quiz_title(get_the_title()) : get_the_title() ?></div>
        </a>
      <?php
    }
    ?>
</div>

