<?php
/**
 * Class ExamData
 */
defined( 'ABSPATH' ) || exit();

class ExamHandler {
  
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
      if ($test_type == EXAM_TYPE_RENEW && $score >= EXAM_MIN_SCORE_TO_PASS_RENEW) {
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

  public static function register_user() {
    if ( !isset($_REQUEST) ) return;
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $error = '';
    if ($password != $password2)
    {
      $error = 'Mật khẩu không khớp.';  
    }
    else {
      $user_id = username_exists( $email );
      if ( !$user_id and email_exists($email) == false ) {
        $user_id = wp_create_user( $email, $password, $email );
        // lock user
        ExamHandler::lock_user($user_id);

      } else {
        $error = 'Email đã tồn tại.';
      }
    }
    if($error != '') {
      $_SESSION['exam_register_error'] = $error;
      $url = $_SERVER['HTTP_REFERER'];
      wp_redirect($url);
      exit();
    }
    // Redirect to login
    $url = get_bloginfo('url') . '/'. EXAM_LOGIN_SLUG;
    wp_redirect($url);
  }
  public static function lock_user( $user_id ){
		$user = get_user_by('id', $user_id);
    $hash = MD5($user->data->user_email. rand(0, 1000));

    add_user_meta( $user_id, 'verify-lock', $hash );
    
    // Send email
    ExamHandler::send_email($user, $hash);

  }

  public static function send_email( $user, $lock ){
		if( ! $user || ! $user instanceof WP_User )
			return;
		if( ! $lock || empty( $lock ) )
			return;

		$user_email = $user->data->user_email;

    $plugin_dir_path = plugin_dir_path( __FILE__ );
    $template = $plugin_dir_path . 'tpl/verify.php';
    $link = add_query_arg( [
      'user_id' => $user->ID, 
      'verify_email' => $lock], 
      get_bloginfo('url'). '/login');
    
    $email = (new WP_Mail)
		    ->to( $user_email )
		    ->subject( 'Verify your email address' )
		    ->template( $template, [
		        'name' => $user->data->display_name,
            'link' => $link
		    ])
		    ->send();
  }
  
  public static function change_password() {
    if ( !isset($_REQUEST) ) return;
    $old_password = $_POST['old_password'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $error = '';
    $message = '';
    $user = wp_get_current_user();
    $is_valid = wp_check_password( $old_password, $user->user_pass, $user->data->ID );
    if(!$is_valid)
    {
      $error = 'Mật khẩu cũ không đúng';  
    }
    else if ($password != $password2)
    {
      $error = 'Mật khẩu không khớp.';  
    }
    else {
      $udata['ID'] = $user->data->ID;
      $udata['user_pass'] = $password;
      $uid = wp_update_user( $udata );
      if($uid) 
      {
        $message = "Cập nhật mật khẩu thành công";
      } else {
        $error = "Cập nhật mật khẩu không thành công";
      }
    }
    $_SESSION['exam_change_password_error'] = $error;
    $_SESSION['exam_change_password_message'] = $message;
    $url = $_SERVER['HTTP_REFERER'];
    wp_redirect($url);
    exit();
  }

}