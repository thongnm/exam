<?php
global $wpdb;
// Load styles 
wp_enqueue_style('bootstrap-styles');
wp_enqueue_style('exam-styles');

$login  = (isset($_GET['login']) ) ? $_GET['login'] : '';
if ( $login == "failed" ) {
  echo '<div class="alert alert-danger">Email hoặc mật khẩu không hợp lệ.</div>';
}
$args = array(
  'redirect' => get_bloginfo('url'), 
  'form_id' => 'loginform-custom',
  'label_username' => __( 'Email' ),
  'label_password' => __( 'Mật khẩu' ),
  'label_remember' => __( 'Remember Me' ),
  'label_log_in' => __( 'Đăng nhập' ),
  'remember' => false
);
wp_login_form( $args );
?>
<div>
  <a style="text-decoration: false" href="<?php echo (get_bloginfo('url'). '/'. EXAM_LOGIN_SLUG)?>/?action=<?php echo EXAM_ACTION_REGISTER?>"
  >Đăng ký tài khoản mới</a>
</div>