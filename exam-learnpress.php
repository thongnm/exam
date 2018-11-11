<?php
/**
 * Class ExamData
 */
defined( 'ABSPATH' ) || exit();

class ExamLernPress {
  
  public static function register_quizz_category() {
    register_taxonomy( 'quizz_category', array( 'lp_quiz' ),
      array(
        'label'             => __( 'Quizz Categories', 'learnpress' ),
        'labels'            => array(
          'name'          => __( 'Quizz Categories', 'learnpress' ),
          'menu_name'     => __( 'Category', 'learnpress' ),
          'singular_name' => __( 'Category', 'learnpress' ),
          'add_new_item'  => __( 'Add New Quizz Category', 'learnpress' ),
          'all_items'     => __( 'All Categories', 'learnpress' )
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

}