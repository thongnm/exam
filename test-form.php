<?php
global $wpdb;
// Load styles 
wp_enqueue_style('bootstrap-styles');
wp_enqueue_style('exam-styles');

$quiz_id = $_GET['id'];

$questions = ExamData::get_questions_for_test($quiz_id);

$ui_id = ExamLearnPress::add_user_items($quiz_id, $questions);

foreach ( $questions as $k => $v ) {
  $ids[] = $v->ID;
}
$answers = ExamData::get_all_answers($ids);

$quizz_title = get_post($quiz_id)->post_title;

$start_time = current_time( 'mysql' );

$duration = ExamLearnPress::get_duration($quiz_id);

?>
<div>
  <div class="exam-question-header">
    <div class="exam-quizz-title">Thi thử: <?php  echo $quizz_title ?></div>
    <div class="exam-quizz-timer">Thời gian: <span id="exam_timer"></span></div>
  </div>
  <div class="exam-question-content">
    <div class="col-md-4">
      <?php
      $index = 0; 
      foreach ( $questions as $k => $v ) {
      ?>
        <button id="exam_question_btn_<?php echo $index?>" onclick="showQuestion(<?php echo  $index?> )" type="button" class="btn btn-default exam-btn-question">
          Câu <?php echo ++$index ?>
        </button>
      <?php
      }
      ?>
    </div>
    <div class="col-md-8">
      <div class="exam-question-container">
        <ul>
        <?php 
        $index = 1;
        foreach ( $questions as $k => $v ) {
          $post_title = $v->post_title;
          $question_id = $v->ID;
          $correct = 0;
          ?>
          <li id="exam_question_<?php echo $question_id?>" class="exam-question" >
            <div><span class="exam-question-index">Câu <?php echo $index++ ?>:</span> <?php echo $post_title ?></div>
            <div class="exam-answers-container">
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
                $test_data[$question_id] = $v1['question_answer_id'];
              }
              $option_id  = 'opt_'. $question_id . '_' . $v1['question_answer_id']
              ?>
              <div class="radio">
                <label onclick="userAnswer(<?php echo $question_id?>, <?php echo $v1['question_answer_id']?>)" id="<?php echo $option_id?>" ><input type="radio" name="optradio_<?php echo $v->ID?>" >  <?php echo $v1['text'] ?></label>
                <br>
              </div>
          
          <?php } ?>
          </div>
          </li>
        <?php } ?>
        </ul>
      </div>
      <div class="exam-btn-prevnext-container">
        <button onclick="showPrevNext(-1)" type="button" class="btn btn-default exam-btn-prev">
          <<
        </button>
        <button onclick="showPrevNext(1)" type="button" class="btn btn-default exam-btn-next">
          >>
        </button>
      </div>
      <div class="exam-btn-finish-container">
        <button type="button" id="exam_finish_btn" class="btn btn-primary exam-btn-finish">
          Kết thúc
        </button>
      </div>
    </div>
  </div>
  <div class="exam-question-footer">
  </div>
  <!-- Modal -->
  <div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" id="mi-modal">
    <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <div class="modal-title" id="myModalLabel">Xác nhận</div>
      </div>
      <div class="modal-body">
        <div>Bạn muốn kết thúc bài thi?</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="modal-btn-si">Đồng ý</button>
      </div>
    </div>
    </div>
  </div>
  <!-- End Modal -->
</div>

<script>
var exam_question_ids = '<?php echo implode(",", $ids )?>';
var exam_current_index = 0;
var exam_list_ids = exam_question_ids.split(',');
var user_answers = [];
function showTimer() {
    var total = <?php echo $duration ?>;
    var timer_id = 'exam_timer';
    // Update the count down every 1 second
    var x = setInterval(function() {
        var minutes = Math.floor(total/60);
        var seconds = total--% 60;
        // Output the result in an element with id="demo"
        document.getElementById(timer_id).innerHTML = minutes.toString().padStart(2,0) + ":" + seconds.toString().padStart(2,0);
        // If the count down is over, write some text 
        if (minutes == 0 && seconds == 0) {
            clearInterval(x);
            document.getElementById(timer_id).innerHTML = "EXPIRED";
        }
    }, 1000);
}
function userAnswer(question_id, answer_id) {
  let isExisted = false;
  user_answers.map(item => {
    if(item.question_id === question_id) {
      isExisted = true;
      return {question_id, answer_id};
    }
    return item;
  });
  if(!isExisted) user_answers.push({question_id, answer_id});

  var $ = jQuery;
  $('#exam_question_btn_'+ exam_current_index).addClass("exam_question_btn_answered");
  
}
function showPrevNext(index) {
  if ((index < 0 && exam_current_index === 0)
    || (index > 0 && exam_current_index === exam_list_ids.length-1))  return;
  showQuestion(exam_current_index + index)
}
function init($) {
  $("#exam_finish_btn").on("click", function(){
    $("#mi-modal").modal('show');
  });
  $("#modal-btn-si").on("click", function(){
    submitTest();
    $("#mi-modal").modal('hide');
  });
}

function submitTest() {
  console.log('user_answers', user_answers);
}

function showQuestion(index) {
  // show/hide question
  var $ = jQuery;
  $('#exam_question_'+ exam_list_ids[exam_current_index]).removeClass("exam_current_question");
  $('#exam_question_'+ exam_list_ids[index]).addClass("exam_current_question");
  // set current question button
  $('#exam_question_btn_'+ exam_current_index).removeClass("exam_current_question_btn");
  $('#exam_question_btn_'+ index).addClass("exam_current_question_btn");
 
  exam_current_index = index;
}

(function ($) {
  init($);
  showTimer();
  showQuestion(exam_current_index)
})(jQuery);

</script>

<?php
// Save user item meta data
ExamLearnPress::add_user_items_meta($ui_id, $test_data);
?>

