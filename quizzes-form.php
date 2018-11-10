<?php
// Load styles 
wp_enqueue_style('exam-styles');

// WP_Query arguments
$args = array (
	'post_type'              => array( LP_QUIZ_CPT ),
	'post_status'            => array( 'publish' ),
	'nopaging'               => true,
	'order'                  => 'ASC',
	'orderby'                => 'menu_order',
);
// The Query
$services = new WP_Query( $args );
?>
<div>
<ul>
    <?php
    while ( $services->have_posts() ) {
      $services->the_post();
      ?>
      <li id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <a href="<?php bloginfo('url')?>/practice/?id=<?php the_ID(); ?>"><?php the_title( '<h1 class="entry-title">', '</h1>' ); ?></a>
        </li>
      <?php
    }
    ?>
</div>

