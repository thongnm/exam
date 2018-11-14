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
    
    add_filter( 'query_vars', array( $this, 'add_query_vars_filter') );

    // ExamLearnPress::register_quizz_category();
    ExamLearnPress::custom_quiz_general_meta_box();

  }
  
  function exam_change_password() {
    ob_start();
    include 'change-password-form.php';
    return ob_get_clean();
  }
  function exam_user_tests() {
    ob_start();
    include 'user-tests-form.php';
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
    // Register styles
    wp_register_style('exam-styles', plugins_url('exam-styles.css', __FILE__) );
    wp_enqueue_script('exam-scripts', plugins_url('exam-scripts.js', __FILE__), array(), false, $in_footer = true);
    
    // Bootstrap
    wp_register_style('bootstrap-styles', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' );
    wp_enqueue_script('bootstrap.min', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array(), false, $in_footer = true);
   
  }
  
  function my_front_end_login_fail( $username ) {
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
    
    ob_start();
    if ($action == EXAM_ACTION_REGISTER) {
      include 'register-form.php';
    } else {
      include 'login-form.php';
    }
    return ob_get_clean();
  }

  function exam_quizzes_all(){
    $quizzes = ExamData::get_list_quizzes();
    ob_start();
    include 'quizzes-form.php';
    return ob_get_clean();
  }
  function exam_quizzes_law(){
    $quizzes = ExamData::get_list_quizzes_law();
    ob_start();
    include 'quizzes-form.php';
    return ob_get_clean();
  }
  function exam_quizzes_specific(){
    $quizzes = ExamData::get_list_tests();
    ob_start();
    include 'quizzes-form.php';
    return ob_get_clean();
  }
  function exam_tests_form(){
    $quizzes = ExamData::get_list_tests();
    $url = EXAM_TEST_PAGE_SLUG;
    ob_start();
    include 'quizzes-form.php';
    return ob_get_clean();
  }
  function exam_tests_form_renew(){
    $quizzes = ExamData::get_list_tests();
    $url = EXAM_TEST_PAGE_SLUG;
    $type = EXAM_TYPE_RENEW;
    ob_start();
    include 'quizzes-form.php';
    return ob_get_clean();
  }

  function exam_practice_form(){
    ob_start();
    include 'practice-form.php';
    return ob_get_clean();
  }
  function exam_test_form(){
    ob_start();
    include 'test-form.php';
    return ob_get_clean();
  }

}


Exam::get_instance();

