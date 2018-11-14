<?php
global $wpdb;
// Load styles 
wp_enqueue_style('bootstrap-styles');
wp_enqueue_style('exam-styles');
$error =  isset($_SESSION['exam_change_password_error'])? $_SESSION['exam_change_password_error']: '';
$message =  isset($_SESSION['exam_change_password_message'])? $_SESSION['exam_change_password_message']: '';
if($error != ''){
  echo "<div class='alert alert-danger'>". $error ."</div>";
  unset($_SESSION['exam_change_password_error']);
}
if($message != ''){
  echo "<div class='alert alert-success'>". $message ."</div>";
  unset($_SESSION['exam_change_password_message']);
}
?>

<form action="<?php echo admin_url( 'admin-post.php') ?>" method="POST">
  <input type="hidden" name="action" value="exam_change_password">
  <div>
    <label for="old_password"><b>Mật khẩu cũ</b></label>
    <input type="password" placeholder="" name="old_password" required>

    <label for="psw"><b>Mật khẩu</b></label>
    <input type="password" placeholder="" name="password" required>

    <label for="psw-repeat"><b>Nhập lại mật khẩu</b></label>
    <input type="password" placeholder="" name="password2" required>
    <hr>
    <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
  </div>
</form>