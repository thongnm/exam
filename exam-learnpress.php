<?php
/**
 * Class ExamLernPress
 */
defined( 'ABSPATH' ) || exit();

class ExamLernPress {
  
  public static function register_quizz_category() {
    register_taxonomy( 'quizz_category', array( 'lp_quiz' ),
      array(
        'label'             => 'Quizz Categories',
        'labels'            => array(
          'name'          => 'Quizz Categories',
          'menu_name'     => 'Category',
          'singular_name' => 'Category',
          'add_new_item'  => 'Add New Quizz Category',
          'all_items'     => 'All Categories'
        ),
        'query_var'         => true,
        'public'            => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_menu'      => 'learn_press',
        'show_admin_column' => true,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'rewrite'           => array(
          'slug'         => 'quizz-category',
          'hierarchical' => true,
          'with_front'   => false
        ),
      )
    );
  }
  public static function custom_quiz_general_meta_box(){
    add_filter('learn_press_quiz_general_meta_box','ExamLernPress::learn_press_quiz_general_meta_box');
    add_filter('learn_press_question_meta_box_args','ExamLernPress::learn_press_question_meta_box_args');
  }
  /**
   * Customize Quiz General Setting box
   *
   * @param [type] $meta_box
   * @return void
   */
  public static function learn_press_quiz_general_meta_box($meta_box){
    $meta_box = array(
      'title'      => 'General Settings',
      'post_types' => LP_QUIZ_CPT,
      'context'    => 'normal',
      'priority'   => 'high',
      'fields'     => array(
        array(
          'name' => 'Pháp luật chung',
          'desc' => 'Chọn nếu là bài ôn tập Pháp luật chung.',
          'id'   => '_exam_is_general_law',
          'type' => 'yes_no',
          'std'  => 'no'
        ),
        array(
          'name' => 'Pháp luật chuyên ngành',
          'desc' => 'Chọn nếu là bài ôn tập Pháp luật chuyên ngành.',
          'id'   => '_exam_is_specific_law',
          'type' => 'yes_no',
          'std'  => 'no'
        ),
        array(
          'name'         => 'Pháp luật chuyên ngành',
          'desc'         => 'Đề thi chuyên môn sẽ lấy một số câu hỏi từ "Pháp luật chung" và "Pháp luật chuyên ngành" này.',
          'id'           => '_exam_base_quizz',
          'type'         => 'post',
          'post_type'    => 'lp_quiz'
        ),
        array(
          'name'         => 'Thời gian thi',
          'desc'         => 'Thời gian thi.',
          'id'           => '_exam_duration',
          'type'         => 'duration',
          'default_time' => 'minute',
          'min'          => 0,
          'std'          => 30,
        )
      )
    );
    return $meta_box;
  }
  /**
   * Hide question setting box
   *
   * @param [type] $meta_box
   * @return void
   */
  public static function learn_press_question_meta_box_args($meta_box) {
    $meta_box = array(
      'id'     => 'question_settings',
      'title'  => __( 'Settings', 'learnpress' ),
      'fields' => array()
    );
    return $meta_box;
  }
}