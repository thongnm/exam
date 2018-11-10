<?php
global $wpdb;
// Load styles 
wp_enqueue_style('exam-styles');

$quiz_id = $_GET['id'];

$questions = ExamData::get_quizz_questions($quiz_id);
foreach ( $questions as $k => $v ) {
  $ids[] = $v->ID;
}
$answers = ExamData::get_all_answers($ids);

$quizz_title = $questions[0]->quizz_title;
?>
<div>
  <h1><?php  echo $quizz_title ?></h1>
  <ul>
  <?php 
  foreach ( $questions as $k => $v ) {
    $post_title = $v->post_title;
    $question_id = $v->ID;
    $correct = 0;
    ?>
    <li>
      <div><?php echo $post_title ?></div>
      <?php 
      foreach ( $answers as $k1 => $v1 ) {
        if ($v1->question_id != $question_id) continue;
        $v1 = (array) $v1;
        if ( $answer_data = LP_Helper::maybe_unserialize( $v1['answer_data'] ) ) {
          foreach ( $answer_data as $kk => $vv ) {
            $v1[ $kk ] = $vv;
          }
        }

        if($v1['is_true'] == 'yes') {
        ?>
          <input type="hidden" id="q_<?php echo $question_id ?>" value="<?php echo $v1['question_answer_id'] ?>">
        <?php
        }
        $option_id  = 'opt_'. $question_id . '_' . $v1['question_answer_id']
        ?>
      <div style="padding-left:20px">
        <div class="radio">
        <label onclick="examShowAnswer('<?php echo $question_id?>')" id="<?php echo $option_id?>" ><input type="radio" name="optradio_<?php echo $v->ID?>" >  <?php echo $v1['text'] ?></label>
        <br>
        </div>
      </div>
    </li>
    <?php } ?>
  <?php } ?>
</ul>
</div>

