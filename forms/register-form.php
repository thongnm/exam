<?php
global $wpdb;
$error =  isset($_SESSION['exam_register_error'])? $_SESSION['exam_register_error']: '';
if($error != ''){
  echo "<div class='alert alert-danger'>". $error ."</div>";
  unset($_SESSION['exam_register_error']);
}
?>

<form action="<?php echo admin_url( 'admin-post.php') ?>" method="POST">
  <input type="hidden" name="action" value="exam_register_user">
  <div>
    <label for="email"><b>Email</b></label>
    <input type="email" placeholder="" name="email" required>

    <label for="psw"><b>Mật khẩu</b></label>
    <input type="password" placeholder="" name="password" required>

    <label for="psw-repeat"><b>Nhập lại mật khẩu</b></label>
    <input type="password" placeholder="" name="password2" required>
    <hr>
    <button type="submit" class="btn btn-primary">Đăng ký</button>
  </div>
</form>