<?php
/**
 * Class ExamData
 */
defined( 'ABSPATH' ) || exit();

class ExamData {
  
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
 
  public static function finish_test_ajax_request() {
    // The $_REQUEST contains all the data sent via ajax
    if ( isset($_REQUEST) ) {
      $ui_id = $_POST['ui_id'];
      $test_type = $_POST['test_type'];

      $user_answers = json_decode(stripslashes($_POST['user_answers']));
      // Get test data array(question => answer)
      $meta_data = ExamLearnPress::get_user_items_meta($ui_id, META_KEY_EXAM_TEST_QUESTIONS);
      $test_data = maybe_unserialize($meta_data->meta_value);
      
      // Get question law meta
      $meta_data_law = ExamLearnPress::get_user_items_meta($ui_id, META_KEY_EXAM_TEST_QUESTIONS_LAW);
      $questions_law = maybe_unserialize($meta_data_law->meta_value);
      $law_correct = 0;
      $correct_count = 0;
      $incorrect_count = 0;
      $unanswer_count = 0;
      $test_answered = array();
      foreach ($test_data as $question_id => $answer_id) {
        $is_answered = False;
        foreach ($user_answers as $a) {
          if($question_id == $a->question_id) {
            $is_answered = True;
            $test_answered[$question_id] = $a->answer_id;
            if($answer_id == $a->answer_id) {
              $correct_count++;
              if(in_array($question_id, $questions_law)) {
                $law_correct++;
              }
            } else {
              $incorrect_count++;
            }
          }
        }
        if(!$is_answered) {
          $unanswer_count++;
        }
      }
      $score = $correct_count * SCORE_PER_QUESTION;
      // Check is pass
      $is_passed = False;
      if ($type == EXAM_TYPE_RENEW && $score >= EXAM_MIN_SCORE_TO_PASS_RENEW) {
        $is_passed = True;
      }
      else if ($law_correct >= EXAM_MIN_LAW_TO_PASS
        && $score >= EXAM_MIN_SCORE_TO_PASS) {
        $is_passed = True;
      }
      // Save test data
      ExamLearnPress::add_user_items_meta($ui_id, $test_answered, META_KEY_EXAM_TEST_ANSWERS);

      // Return values
      $obj = new stdClass;
      $obj->law_correct = $law_correct;
      $obj->correct_count = $correct_count;
      $obj->incorrect_count = $incorrect_count;
      $obj->unanswer_count = $unanswer_count;
      $obj->score = $score;
      $obj->is_passed = $is_passed;
      $obj->end_time = current_time( 'mysql' );
      
      // Save test data
      ExamLearnPress::add_user_items_meta($ui_id, $obj, META_KEY_EXAM_TEST_RESULT);

      
      $obj->questions_with_answers = $test_data;


      wp_send_json($obj);

    }
   
    // Always die in functions echoing ajax content
    die();
  }

}