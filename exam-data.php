<?php
/**
 * Class ExamData
 */
defined( 'ABSPATH' ) || exit();

class ExamData {
  
  public static function get_quizz_questions_practice($quiz_id) {
    global $wpdb;
    $query = $wpdb->prepare( "
      SELECT p.*, qq.quiz_id, qq.question_order AS `order`
      FROM {$wpdb->posts} p 
      INNER JOIN {$wpdb->prefix}learnpress_quiz_questions qq ON p.ID = qq.question_id
      WHERE qq.quiz_id = %d
      AND p.post_status = %s
      ",$quiz_id, 'publish' );

    $results = $wpdb->get_results( $query );
    return $results;
  }

  public static function get_quizz_questions($quiz_id, $limit = 1000) {
    global $wpdb;
    $query = $wpdb->prepare( "
      SELECT p.*, qq.quiz_id, qq.question_order AS `order`
      FROM {$wpdb->posts} p 
      INNER JOIN {$wpdb->prefix}learnpress_quiz_questions qq ON p.ID = qq.question_id
      WHERE qq.quiz_id = %d
      AND p.post_status = %s
      ORDER BY rand()
      LIMIT %d
      ",$quiz_id, 'publish', $limit );

    $results = $wpdb->get_results( $query );
    return $results;
  }
  
  public static function get_question_answers($question_id) {
    global $wpdb;
    $sql = $wpdb->prepare( "
            SELECT * FROM $wpdb->learnpress_question_answers 
            WHERE question_id = %d 
            ORDER BY answer_order
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
            ORDER BY answer_order
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
  public static function get_list_quizzes_law() {
    // WP_Query arguments
    $args = array (
      'post_type'              => array( LP_QUIZ_CPT ),
      'post_status'            => array( 'publish' ),
      'nopaging'               => true,
      'order'                  => 'ASC',
      'orderby'                => 'menu_order',
      'meta_query' => array(
        'relation' => 'OR',
        array(
            'key' => '_exam_is_general_law',
            'value' => 'yes',
            'compare' => '==',
        ),
        array(
          'key' => '_exam_is_specific_law',
          'value' => 'yes',
          'compare' => '==',
        )
      )

    );
    // The Query
    $quizzes = new WP_Query( $args );
    return $quizzes;
  }

  public static function get_list_tests() {
    // WP_Query arguments
    $args = array (
      'post_type'              => array( LP_QUIZ_CPT ),
      'post_status'            => array( 'publish' ),
      'nopaging'               => true,
      'order'                  => 'ASC',
      'orderby'                => 'menu_order',
      'meta_query' => array(
        'relation' => 'AND',
        array(
            'key' => '_exam_is_general_law',
            'value' => 'yes',
            'compare' => '!=',
        ),
        array(
          'key' => '_exam_is_specific_law',
          'value' => 'yes',
          'compare' => '!=',
        )
      )

    );
    // The Query
    $quizzes = new WP_Query( $args );
    return $quizzes;
  }
  public static function get_general_law_quiz() {
    // WP_Query arguments
    $args = array (
      'post_type'              => array( LP_QUIZ_CPT ),
      'post_status'            => array( 'publish' ),
      'nopaging'               => true,
      'meta_query' => array(
        array(
            'key' => '_exam_is_general_law',
            'value' => 'yes',
            'compare' => '=',
        )
    )
    );
    // The Query
    $results = get_posts( $args );
    return $results[0];
  }
  public static function get_questions_for_test($quiz_id) {
    // Get general law questions
    $genral_law_quiz = ExamData::get_general_law_quiz();
    $general_law_questions = ExamData::get_quizz_questions($genral_law_quiz->ID, 2);
    // Get specific law questions
    $base_quiz_id = get_post_meta($quiz_id,'_exam_base_quizz', true);
    $specific_law_questions = ExamData::get_quizz_questions($base_quiz_id, 3);
    
    // Get remaining questions
    $questions = ExamData::get_quizz_questions($quiz_id, 25);
    
    $questions_law = array_merge($general_law_questions, $specific_law_questions);
    
    $obj = new stdClass;
    $obj->questions_specific = $questions;
    $obj->questions_law = $questions_law ;
    return $obj;
  }
  public static function get_questions_for_test_renew($quiz_id) {
    // Get general law questions
    $genral_law_quiz = ExamData::get_general_law_quiz();
    $general_law_questions = ExamData::get_quizz_questions($genral_law_quiz->ID, 5);
    // Get specific law questions
    $base_quiz_id = get_post_meta($quiz_id,'_exam_base_quizz', true);
    $specific_law_questions = ExamData::get_quizz_questions($base_quiz_id, 5);
    
    $questions_law = array_merge($general_law_questions, $specific_law_questions);
    
    $obj = new stdClass;
    $obj->questions_specific = array();
    $obj->questions_law = $questions_law ;
    return $obj;
  }
  public static function get_user_tests($user_id) {
    global $wpdb;
    $sql = $wpdb->prepare( "
            SELECT p.post_title, ui.start_time, uim.meta_value 
            FROM $wpdb->learnpress_user_items ui
            INNER JOIN $wpdb->posts p on ui.item_id = p.ID
            INNER JOIN $wpdb->learnpress_user_itemmeta uim ON ui.user_item_id = learnpress_user_item_id AND meta_key = '_exam_test_result'
            WHERE user_id = %d 
            ORDER BY start_time desc
          ", $user_id );
        
    return  $wpdb->get_results( $sql );
  }
  public static function can_take_exam($user_id) {
    // Get quota
    $quota = get_user_meta($user_id, EXAM_KEY_QUOTA, True);    
    if (intval($quota) > 0) {
      update_user_meta($user_id, EXAM_KEY_QUOTA, intval($quota) - 1);
      return true;
    }
    return false;
  }
}