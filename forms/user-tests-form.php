<?php
include "require_logged_in.php";

global $wpdb;
// Load styles 
wp_enqueue_style('bootstrap-styles');
wp_enqueue_style('exam-styles');
// make user user logged in
if(!is_user_logged_in()) {
  return;
}

$user_tests = ExamData::get_user_tests(get_current_user_id());
?>
<div id="exam_user_tests-container">
<table class="table table-striped">
    <thead>
      <tr>
        <th>Thời gian kết thúc</th>
        <th>Bài thi</th>
        <th>Số câu đúng</th>
        <th>Điểm thi</th>
      </tr>
    </thead>
    <tbody>
<?php  
foreach ( $user_tests as $k => $v ) {
$meta_data = maybe_unserialize($v->meta_value);
?>
      <tr>
        <td><?php echo date_format(date_create($meta_data->end_time),"d/m/Y H:i:s");?></td>
        <td><?php echo $v->post_title?></td>
        <td><?php echo $meta_data->correct_count?></td>
        <td><?php echo $meta_data->score?></td>
      </tr>
<?php
}
?>
</tbody>
</table>
</div>

