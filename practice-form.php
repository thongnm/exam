<?php
global $wpdb;
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
$quiz_id = $_GET['id'];
$query = $wpdb->prepare( "
SELECT p.*, qq.quiz_id, qq.question_order AS `order`
FROM {$wpdb->posts} p 
INNER JOIN {$wpdb->prefix}learnpress_quiz_questions qq ON p.ID = qq.question_id
WHERE qq.quiz_id = %d
AND p.post_status = %s
ORDER BY question_order, quiz_question_id ASC
",$quiz_id, 'publish' );

$results = $wpdb->get_results( $query );


?>
<div>
<h1>Practice Form <?php  echo $id ?> </h1>
<ul>
<?php 
foreach ( $results as $k => $v ) {
  $post_title = $v->post_title;
  ?>
  <li><?php echo $post_title ?></li>
  <div style="padding-left:20px">
      <div class="radio">
      <?php 
       $question_id = $v->ID;
      $sql = $wpdb->prepare( "
           SELECT * FROM $wpdb->learnpress_question_answers WHERE question_id = %d
         ", $question_id );
       
       $question_answers = $wpdb->get_results( $sql );
      //  var_dump($question_id);
       $answer_options = array();
       foreach ( $question_answers as $k1 => $v1 ) {
        $v1 = (array) $v1;
        if ( $answer_data = LP_Helper::maybe_unserialize( $v1['answer_data'] ) ) {
					foreach ( $answer_data as $kk => $vv ) {
						$v1[ $kk ] = $vv;
					}
        }
        ?>
        <label><input type="radio" name="optradio<?php echo $v->ID?>" >  <?php echo $v1['text'] ?></label>
        <br>
        <?php
        
       }
       
      ?>
      </div>
    </ul>
  </div>
<?php
}
?>
</ul>
</div>

