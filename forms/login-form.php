<?php
global $wpdb;

$verified =  isset($_SESSION['exam_verified'])? $_SESSION['exam_verified']: '';
if($verified != ''){
  echo "<div class='alert alert-success'>". $verified ."</div>";
  unset($_SESSION['exam_verified']);
}

$exam_registered =  isset($_SESSION['exam_registered'])? $_SESSION['exam_registered']: '';
$not_verified =  isset($_SESSION['exam_email_not_verify'])? $_SESSION['exam_email_not_verify']: '';
$login  = (isset($_GET['login']) ) ? $_GET['login'] : '';

if($exam_registered != ''){
  echo "<div class='alert alert-success'>". $exam_registered ."</div>";
  unset($_SESSION['exam_registered']);
}

if($not_verified != ''){
  echo "<div class='alert alert-danger'>". $not_verified ."</div>";
  unset($_SESSION['exam_email_not_verify']);
}
else if ( $login == "failed" ) {
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