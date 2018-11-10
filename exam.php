<?php
/*
Plugin Name: ExamPractice
Plugin URI: 
Description: ExamPractice
Author: Thong Ngo
Version: 1.0.0
Author URI: 
*/

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

class Exam {
  protected static $instance = NULL;

	public static function get_instance() {
		NULL === self::$instance and self::$instance = new self;
		return self::$instance;
	}
  function __construct() {
    // hook init event to handle plugin initialization:
    add_action('init', array($this, 'init'));
    // Register styles
    wp_register_style( 'exam-styles', plugins_url('exam-styles.css', __FILE__) );

  }

  /**
   * Init the plugin configuration
   *
   * @return void
   */
  function init() {
    
    // Add shortcode
    add_shortcode('exam-quizzes',  array( $this, 'exam_quizzes_form'));
    add_shortcode('exam-practice',  array( $this, 'exam_practice_form'));
    add_shortcode('exam-test',  array( $this, 'exam_test_form'));
    
  }
  function exam_quizzes_form(){
    include 'quizzes-form.php';
  }
  function exam_practice_form(){
    include 'practice-form.php';
  }
  function exam_test_form(){
    include 'test-form.php';
  }

}

Exam::get_instance();

// add_filter( 'query_vars', 'exam_query_vars' );

// function exam_rewrites_init(){
//   add_rewrite_rule(
//       'practice/([0-9]+)/?$',
//       'index.php?pagename=practice&id=$matches[1]',
//       'top' );
//   add_rewrite_rule(
//       'exam/([0-9]+)/?$',
//       'index.php?pagename=exam&id=$matches[1]',
//       'top' );
// }
// function exam_query_vars( $query_vars ){
//     $query_vars[] = 'id';
//     return $query_vars;
// }
