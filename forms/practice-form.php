<?php
global $wpdb;

$quiz_id = $_GET['id'];

$questions = ExamData::get_quizz_questions($quiz_id);
foreach ( $questions as $k => $v ) {
  $ids[] = $v->ID;
}
$answers = ExamData::get_all_answers($ids);

$quizz_title = get_post($quiz_id)->post_title;
?>
<div>
  <h1>Luyện thi chứng chỉ hành nghề xây dựng: <?php  echo $quizz_title ?></h1>
  <div>
  <?php 
  $index = 1;
  foreach ( $questions as $k => $v ) {
    $post_title = $v->post_title;
    $question_id = $v->ID;
    $correct = 0;
    ?>
    <div>
      <div>
        <span class="exam_question_index"> Câu <?php echo $index++ ?>:</span>
        <?php echo $post_title ?>
       </div>
      <?php 
      $answer_index_text = ['a', 'b', 'c', 'd', 'e', 'f'];
      $answer_index = 0;
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
      <div class="exam-answers-container">
        <div class="radio">
          <label onclick="examShowAnswer('<?php echo $question_id?>')" id="<?php echo $option_id?>" ><input type="radio" name="optradio_<?php echo $v->ID?>" >  
            <?php echo  $answer_index_text[$answer_index++]. '.  ' . $v1['text'] ?>
          </label>
          <br>
        </div>
      </div>
      <?php } ?>
    </div>
  <?php } ?>
</div>
</div>

