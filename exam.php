<?php
/*
Plugin Name: Exam
Plugin URI: 
Description: Exam
Author: Thong Ngo
Version: 1.0.0
Author URI: 
*/

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

require_once dirname( __FILE__ ) . '/exam-constants.php';

class Exam {
  protected static $instance = NULL;

	public static function get_instance() {
		NULL === self::$instance and self::$instance = new self;
		return self::$instance;
	}
  function __construct() {
    // hook init event to handle plugin initialization:
    add_action('init', array($this, 'init'));
    
    $this->includes();
  }
  /**
   * Includes needed files.
   */
  public function includes() {
    require_once('exam-data.php');
    require_once('exam-handler.php');
    require_once('exam-learnpress.php');
    require_once('vendors/WP_Mail.php');
  }
  /**
   * Init the plugin configuration
   *
   * @return void
   */
  function init() {
    if(!session_id()) {
      session_start();
    }
    add_action('wp_logout', array( $this, 'end_session'));
    add_action('show_admin_bar', array( $this, 'remove_admin_bar'));

    // Add shortcode
    add_shortcode('danh-sach-on-tap',  array( $this, 'exam_quizzes_all'));
    add_shortcode('danh-sach-on-tap-luat',  array( $this, 'exam_quizzes_law'));
    add_shortcode('danh-sach-on-tap-chuyen-nganh',  array( $this, 'exam_quizzes_specific'));
    
    add_shortcode('danh-sach-thi-cap-moi',  array( $this, 'exam_tests_form'));
    add_shortcode('danh-sach-thi-cap-lai',  array( $this, 'exam_tests_form_renew'));
    
    add_shortcode('on-tap',  array( $this, 'exam_practice_form'));
    add_shortcode('thi-thu',  array( $this, 'exam_test_form'));
    
    add_shortcode('dang-nhap',  array( $this, 'exam_login_form'));
    add_shortcode('lich-su-thi',  array( $this, 'exam_user_tests'));
    add_shortcode('doi-mat-khau',  array( $this, 'exam_change_password'));

    add_action( 'wp_login_failed', array( $this, 'my_front_end_login_fail' ));  // hook failed login

    add_action( 'wp_enqueue_scripts', array( $this, 'exam_enqueue_scripts' ));
    add_action( 'wp_ajax_finish_test_ajax_request', 'ExamHandler::finish_test_ajax_request' );
    
    // register handler
    add_action( 'admin_post_exam_register_user', 'ExamHandler::register_user' );
    add_action( 'admin_post_nopriv_exam_register_user', 'ExamHandler::register_user' );
    // Change password handler
    add_action( 'admin_post_exam_change_password', 'ExamHandler::change_password' );
    add_action('admin_enqueue_scripts', array( $this, 'admin_style')) ;

    add_filter( 'query_vars', array( $this, 'add_query_vars_filter') );

    add_filter( 'wp_mail_from_name', array( $this,'my_mail_from_name'));
    add_filter( 'authenticate',  array( $this,'check_active_user'), 100, 2 );
    
    // Custom user fields
    add_filter( 'manage_users_columns', array( $this,'add_user_columns') );
    add_filter( 'manage_users_custom_column', array( $this,'add_user_column_data'), 10, 3 );
    add_action( 'edit_user_profile', array( $this,'exam_show_extra_profile_fields' ));
    add_action( 'edit_user_profile_update', array( $this,'exam_update_profile_fields') );
    // After logged in
    add_action('wp_login', array( $this,'exam_after_login'), 10, 2);

    // ExamLearnPress::register_quizz_category();
    ExamLearnPress::custom_quiz_general_meta_box();

  }
  function exam_after_login( $user_login, $user ) {
    $user_id = $user->ID;
    $quota = get_user_meta( $user_id, EXAM_KEY_QUOTA, True);
    if(intval($quota) == 0) {
      // Add free quota
      $last_added = get_user_meta( $user_id, EXAM_KEY_QUOTA_FREE, True);
      $can_add = False;
      if($last_added == "") {
        $can_add = True;
      } else {
        $diff = date_diff(date_create(current_time('mysql')), date_create($last_added));
        if($diff->days >= 1) {
          $can_add = True;
        }
      } 

      if($can_add) {
        update_user_meta( $user_id, EXAM_KEY_QUOTA_FREE, current_time( 'mysql' ));
        update_user_meta( $user_id, EXAM_KEY_QUOTA, 1);
      }
    }
  }

  function exam_update_profile_fields( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
      return false;
    }
    if ( intval( $_POST[EXAM_KEY_QUOTA] ) >= 0 ) {
      update_user_meta( $user_id, EXAM_KEY_QUOTA, $_POST[EXAM_KEY_QUOTA]);
    }
  }
  function exam_show_extra_profile_fields( $user ) {
    include "forms/admin-user-info.php";
  }

  function add_user_columns($column) {
    $column[EXAM_KEY_QUOTA] = 'Số lần thi còn lại';
    return $column;
  }

  //add the data
  function add_user_column_data( $val, $column_name, $user_id ) {
    $user = get_userdata($user_id);

    switch ($column_name) {
        case EXAM_KEY_QUOTA :
            return $user->{EXAM_KEY_QUOTA};
            break;
        default:
    }
    return;
  }

  function admin_style() {
    wp_enqueue_style('admin-styles',plugins_url('assets/exam-admin-styles.css', __FILE__));
  }
  public function check_active_user( $user, $username ){
    if(!isset($user->ID)) return;
		$lock = get_user_meta( $user->ID, "verify-lock", true );

		if( $lock && ! empty( $lock ) ) {
      $_SESSION['exam_email_not_verify'] = "Tài khoản chưa được xác nhận.";
			return new WP_Error();
		}

		return $user;
	}
  function my_mail_from_name( $name ) {
      return EXAM_EMAIL_FROM_NAME;
  }
  function exam_change_password() {
    ob_start();
    include 'forms/change-password-form.php';
    return ob_get_clean();
  }
  function exam_user_tests() {
    ob_start();
    include 'forms/user-tests-form.php';
    return ob_get_clean();
  }
  function remove_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
      return false;
    }
    return true;
  }

  function end_session() {
    session_destroy ();
  }
  function add_query_vars_filter( $vars ) {
    $vars[] = "action";
    return $vars;
  }
  

  function exam_enqueue_scripts() {
    // Bootstrap
    wp_enqueue_style('bootstrap-styles', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' );
    wp_enqueue_script('bootstrap.min', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array(), false, $in_footer = true);
    
    // Register styles
    wp_enqueue_style('exam-styles', plugins_url('assets/exam-styles.css', __FILE__) );
    wp_enqueue_script('exam-scripts', plugins_url('assets/exam-scripts.js', __FILE__), array(), false, $in_footer = true);
   
  
  }
  
  function my_front_end_login_fail( $username ) {
    if(!isset($_SERVER['HTTP_REFERER'])) return;
    $referrer = $_SERVER['HTTP_REFERER'];  // where did the post submission come from?
    // if there's a valid referrer, and it's not the default log-in screen
    if ( !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') ) {
        $url = $referrer;
        if (!strpos($url, 'login=failed') !== false) {
          $url = $url . '?login=failed';
        }
        wp_redirect( $url );  // let's append some information (login=failed) to the URL for the theme to use
        
        exit;
    }
  }

  function exam_login_form(){
    $action  = (isset($_GET['action']) ) ? $_GET['action'] : '';
    
    // Verify email
    $verify_email  = (isset($_GET['verify_email']) ) ? $_GET['verify_email'] : '';
    $user_id  = (isset($_GET['user_id']) ) ? $_GET['user_id'] : '';
    if($verify_email != '' && $user_id != '') {
      $user_id = absint( $user_id );
      if( $verify_email === get_user_meta( $user_id, 'verify-lock', true ) ) {
        // Unlock user from loggin in
        $user = get_user_by('id', $user_id);
        delete_user_meta( $user_id, 'verify-lock' );
        $_SESSION['exam_verified'] = "Xác nhận Email thành công.";
        $url = get_bloginfo('url'). '/'. EXAM_LOGIN_SLUG;
        echo '<script type="text/javascript">window.location = "' . $url . '"</script>';
        exit();
      }
    }
    

    ob_start();
    if ($action == EXAM_ACTION_REGISTER) {
      include 'forms/register-form.php';
    }
    else if ($action == EXAM_ACTION_LOGOUT) {
      wp_logout();
      $url = get_bloginfo('url');
      echo '<script type="text/javascript">window.location = "' . $url . '"</script>';
      exit();
     
    }
    else {
      include 'forms/login-form.php';
    }
    return ob_get_clean();
  }

  function exam_quizzes_all(){
    $quizzes = ExamData::get_list_quizzes();
    ob_start();
    include 'forms/quizzes-form.php';
    return ob_get_clean();
  }
  function exam_quizzes_law(){
    $quizzes = ExamData::get_list_quizzes_law();
    ob_start();
    include 'forms/quizzes-form.php';
    return ob_get_clean();
  }
  function exam_quizzes_specific(){
    $quizzes = ExamData::get_list_tests();
    ob_start();
    include 'forms/quizzes-form.php';
    return ob_get_clean();
  }
  function exam_tests_form(){
    $quizzes = ExamData::get_list_tests();
    $url = EXAM_TEST_PAGE_SLUG;
    ob_start();
    include 'forms/quizzes-form.php';
    return ob_get_clean();
  }
  function exam_tests_form_renew(){
    $quizzes = ExamData::get_list_tests();
    $url = EXAM_TEST_PAGE_SLUG;
    $type = EXAM_TYPE_RENEW;
    ob_start();
    include 'forms/quizzes-form.php';
    return ob_get_clean();
  }

  function exam_practice_form(){
    ob_start();
    include 'forms/practice-form.php';
    return ob_get_clean();
  }
  function exam_test_form(){
    ob_start();
    if(is_user_logged_in() && !ExamData::can_take_exam(get_current_user_id()) ) {
      // redirect to exceeded quota page
      $url = get_bloginfo('url') . '/' . EXAM_PAGE_EXCEEDED_QUOTA;
      echo '<script type="text/javascript">window.location = "' . $url . '"</script>';
      exit();

    } else {
      include 'forms/test-form.php';
    }
    return ob_get_clean();
  }

}


Exam::get_instance();

