<?php
/**
 * Class ExamData
 */
defined( 'ABSPATH' ) || exit();

class ExamData {
  
  public static function get_quizz_questions($quiz_id) {
    global $wpdb;
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
    $query = $wpdb->prepare( "
      SELECT p.*, qq.quiz_id, qq.question_order AS `order`,pp.post_title as quizz_title
      FROM {$wpdb->posts} p 
      INNER JOIN {$wpdb->prefix}learnpress_quiz_questions qq ON p.ID = qq.question_id
      INNER JOIN {$wpdb->posts} pp  ON pp.ID = qq.quiz_id
      WHERE qq.quiz_id = %d
      AND p.post_status = %s
      ORDER BY rand()
      ",$quiz_id, 'publish' );

    $results = $wpdb->get_results( $query );
    return $results;
  }
  public static function get_question_answers($question_id) {
    global $wpdb;
    $sql = $wpdb->prepare( "
            SELECT * FROM $wpdb->learnpress_question_answers 
            WHERE question_id = %d 
            ORDER BY rand()
          ", $question_id );
        
    $question_answers = $wpdb->get_results( $sql );
    return  $question_answers;
  }

  public static function get_all_answers($question_ids) {
    global $wpdb;
    $format = array_fill( 0, sizeof( $question_ids ), '%d' );
    $sql = $wpdb->prepare( "
            SELECT * FROM $wpdb->learnpress_question_answers 
            WHERE question_id IN (" . join( ',', $format ) . ")
            ORDER BY rand()
          ", $question_ids );
        
    $question_answers = $wpdb->get_results( $sql );
    return  $question_answers;
  }
  public static function get_list_quizzes() {
    // WP_Query arguments
    $args = array (
      'post_type'              => array( LP_QUIZ_CPT ),
      'post_status'            => array( 'publish' ),
      'nopaging'               => true,
      'order'                  => 'ASC',
      'orderby'                => 'menu_order',
    );
    // The Query
    $quizzes = new WP_Query( $args );
    return $quizzes;
  }

}