<?php 
$quota = get_user_meta( $user->ID , EXAM_KEY_QUOTA, True);
?>
<h3>Thông tin tài khoản</h3>
<table class="form-table">
  <tr>
    <th><label for="<?php echo EXAM_KEY_QUOTA?>">Số lần thi còn lại</label></th>
    <td>
      <input type="number"
          min="0"
          step="1"
          id="<?php echo EXAM_KEY_QUOTA?>"
          name="<?php echo EXAM_KEY_QUOTA?>"
          value="<?php echo esc_attr( $quota ); ?>"
          class="regular-text"
      />
    </td>
  </tr>
</table>