<?php
include "require_logged_in.php";

global $wpdb;
global $current_user; 
wp_get_current_user();

if(!isset($_GET['id'])) {
  return;
}
$quiz_id = $_GET['id'];
$type = isset($_GET['type']) ? $_GET['type'] : '';

$genral_law_quiz = ExamData::get_general_law_quiz();
$base_quiz_id = get_post_meta($quiz_id,'_exam_base_quizz', true);

$general_law_title = get_post($genral_law_quiz)->post_title;   
$specific_law_title = get_post($base_quiz_id)->post_title;   

if(!$quiz_id) return;

if ($type == EXAM_TYPE_RENEW) {
  $result = ExamData::get_questions_for_test_renew($quiz_id);
} else {
  $result = ExamData::get_questions_for_test($quiz_id);
}
$questions_law = $result->questions_law;

$general_law_questions = $result->general_law_questions;
$specific_law_questions = $result->specific_law_questions;

$questions_specific = $result->questions_specific;

foreach ( $questions_law as $k => $v ) {
  $question_law_ids[] = $v->ID;
}

foreach ( $general_law_questions as $k => $v ) {
  $general_law_questions_ids[] = $v->ID;
}
foreach ( $specific_law_questions as $k => $v ) {
  $specific_law_questions_ids[] = $v->ID;
}

$ui_id = ExamLearnPress::add_user_items($quiz_id);
ExamLearnPress::add_user_items_meta($ui_id, $question_law_ids, META_KEY_EXAM_TEST_QUESTIONS_LAW);

ExamLearnPress::add_user_items_meta($ui_id, $general_law_questions_ids, META_KEY_EXAM_TEST_QUESTIONS_LAW_GENERAL);
ExamLearnPress::add_user_items_meta($ui_id, $specific_law_questions_ids, META_KEY_EXAM_TEST_QUESTIONS_LAW_SPECIFIC);

$questions = array_merge($questions_law, $questions_specific);
shuffle($questions);

foreach ( $questions as $k => $v ) {
  $ids[] = $v->ID;
}
$answers = ExamData::get_all_answers($ids);

$quizz_title = get_post($quiz_id)->post_title;

$quizz_title = ExamData::get_quiz_title_for_test($quizz_title);

$start_time = current_time( 'mysql' );

$duration = EXAM_TEST_DURATION;
if ($type == EXAM_TYPE_RENEW) {
  $duration = EXAM_TEST_DURATION_RENEW;
} 

?>
<div id="exam_test_page">
  <div class="exam-quizz-title">Trắc nghiệm <?php  echo $quizz_title ?></div>
  <style>
  #exam_result_container div {
    margin-top: 3px;
  }
  </style>
  <div id="exam_result_container" style="display:none" >
    <div style="border-width:2px;border-style:solid;">
      <div class="row">
        <div class="col-xs-6 text-center">
            <span style="font-weight:bold">BỘ XÂY DỰNG</span>
        </div>
        <div class="col-xs-6 text-center">
          <span style="font-weight:bold;color:#2196f3">HỆ THỐNG SÁT HẠCH TRỰC TUYẾN</span>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-6 text-center">
            <span style="font-weight:bold">CỤC QUẢN LÝ HOẠT ĐỘNG XÂY DỰNG</span>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12 text-center">
            <span style="color:red;font-size:12pt;font-weight:bold">
            PHIẾU KẾT QUẢ SÁT HẠCH
            </span>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-3"></div>
        <div class="col-xs-6 text-center">
            <hr/>
        </div>
        <div class="col-xs-3"></div>
      </div>
      <div class="row">
        <div class="col-xs-2"></div>
        <div class="col-xs-8 text-center">
          <div style="font-weight:bold">
          BÀI SÁT HẠCH: <span id="test_title"><?php  echo $quizz_title ?></span>
          </div>
            
        </div>
        <div class="col-xs-2"></div>
      </div>
      <div class="row">
        <div class="col-xs-2"></div>
        <div class="col-xs-4">
          Tài khoản: <span id="test_account" style="font-weight:bold"><?php echo $current_user->user_login?></span>
        </div>
        <div class="col-xs-4">
          CMND: <span id="test_id" style="font-weight:bold"></span>
        </div>
        <div class="col-xs-2"></div>
      </div>
      <div class="row">
        <div class="col-xs-2"></div>
        <div class="col-xs-4">
          Họ và tên: <span id="test_name" style="font-weight:bold"></span>
        </div>
        <div class="col-xs-4">
          Ngày sinh: <span id="test_dob" style="font-weight:bold"></span>
        </div>
        <div class="col-xs-2"></div>
      </div>
      <div class="row">
        <div class="col-xs-2"></div>
        <div class="col-xs-4">
          Địa chỉ thường trú: <span id="test_address" style="font-weight:bold"></span>
        </div>
        <div class="col-xs-4">
        </div>
        <div class="col-xs-2"></div>
      </div>
      <div class="row">
        <div class="col-xs-2"></div>
        <div class="col-xs-4">
          Đơn vị: <span id="test_company" style="font-weight:bold"></span>
        </div>
        <div class="col-xs-4">
        </div>
        <div class="col-xs-2"></div>
      </div>
      <div class="row">
        <div class="col-xs-2"></div>
        <div class="col-xs-4">
          Ngày thi: <span id="test_date" style="font-weight:bold"><?php echo date('d/m/Y') ?></span>
        </div>
        <div class="col-xs-4">
        </div>
        <div class="col-xs-2"></div>
      </div>
      <div class="row">
        <div class="col-xs-1"></div>
        <div class="col-xs-10" style="border-width:3px;border-style:double;">
          <span>
            Điểm thi: <span id="test_score" style="font-weight:bold">100</span>
          </span>
          <span style="float:right" >
            Kết quả: <span id="test_result" style="font-weight:bold">Dat</span>
          </span>
        </div>
        <div class="col-xs-1"></div>
      </div>
      <div class="row">
        <div class="col-xs-1"></div>
        <div class="col-xs-10">
        <hr/>
        </div>
        <div class="col-xs-1"></div>
      </div>
      <div class="row">
        <div class="col-xs-1"></div>
        <div class="col-xs-5">
          <div style="font-weight:bold">Trong đó:</div>
          <div id="general_law_title"><?php echo $general_law_title?></div>
          <div id="specific_law_title"><?php echo $specific_law_title?></div>
          <div id="quiz_title"><?php echo $quizz_title?></div>
        </div>
        <div class="col-xs-5 text-right">
          <div style="font-weight:bold">Kết quả</div>
          <div id="general_law_count"></div>
          <div id="specific_law_count"></div>
          <div id="quiz_count"></div>
        </div>
        <div class="col-xs-1"></div>
      </div>
      <div class="row">
        <div class="col-xs-1"></div>
        <div class="col-xs-5 text-center" style="font-weight:bold">
          CÁN BỘ SÁT HẠCH
        </div>
        <div class="col-xs-5 text-center" style="font-weight:bold">
          THÍ SINH
        </div>
        <div class="col-xs-1"></div>
      </div>
      <div class="row">
        <div class="col-xs-12">
          <br>
          <br>
        </div>
      </div>
    </div>
    <div>
      <button id="exam_review_result" onclick="showReview()" type="button" class="btn btn-primary">
        Xem chi tiết
      </button>  
    </div>
  </div>

<div id="exam_test_container">
  <div class="exam-question-header">
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
            <div>
                <span class="exam_question_index">Câu <?php echo $index++ ?>:</span>
                <span class="exam_question_title"> <?php echo $post_title ?></div></span>
            <div class="exam-answers-container">
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
                $test_data[$question_id] = $v1['question_answer_id'];
              }
              $option_id  = 'opt_'. $question_id . '_' . $v1['question_answer_id']
              ?>
              <div class="radio">
                <label onclick="userAnswer(<?php echo $question_id?>, <?php echo $v1['question_answer_id']?>)" id="<?php echo $option_id?>" ><input type="radio" name="optradio_<?php echo $v->ID?>" >  
                  <?php echo  $answer_index_text[$answer_index++]. '.  ' . $v1['text'] ?>
                </label>
                <br>
              </div>
          
          <?php } ?>
          </div>
          </li>
        <?php } ?>
        </ul>
      </div>
      <div class="exam-btn-prevnext-container">
        <button onclick="showPrevNext(-1)" type="button" class="btn btn-primary exam-btn-prev">
          << Câu trước
        </button>
        <button onclick="showPrevNext(1)" type="button" class="btn btn-primary exam-btn-next">
          Câu sau >>
        </button>
      </div>
      <div class="exam-btn-finish-container">
        <button type="button" id="exam_finish_btn" class="btn btn-danger exam-btn-finish">
          Kết thúc bài thi
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
        <button type="button" class="btn btn-primary" id="modal-btn-cancel">Thoát</button>
        <button type="button" class="btn btn-danger" id="modal-btn-si">Đồng ý</button>
      </div>
    </div>
    </div>
  </div>
  <!-- End Modal -->
</div>
</div>
<script>
var exam_question_ids = '<?php echo implode(",", $ids )?>';
var exam_current_index = 0;
var exam_list_ids = exam_question_ids.split(',');
var user_answers = [];
var exam_ajax_url = '<?php echo admin_url( 'admin-ajax.php') ?>';
var $ = jQuery;

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
            document.getElementById(timer_id).innerHTML = "Hết giờ";
            submitTest();
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

  $('#exam_question_btn_'+ exam_current_index).addClass("exam_question_btn_answered");
  
}
function showPrevNext(index) {
  if ((index < 0 && exam_current_index === 0)
    || (index > 0 && exam_current_index === exam_list_ids.length-1))  return;
  showQuestion(exam_current_index + index)
}
function init() {
  $("#exam_finish_btn").on("click", function(){
    $("#mi-modal").modal('show');
  });
  $("#modal-btn-si").on("click", function(){
    submitTest();
    $("#mi-modal").modal('hide');
  });
  $("#modal-btn-cancel").on("click", function(){
    $("#mi-modal").modal('hide');
  });
  $('#exam_result_container').hide();
}

function submitTest() {
  $.ajax({
        url: exam_ajax_url, 
        method: 'post',
        data: {
            'action': 'finish_test_ajax_request',
            'user_answers' : JSON.stringify(user_answers),
            'ui_id': <?php echo $ui_id?>,
            'test_type': '<?php echo $type?>'
        },
        success:function(data) {
            showTestResult(data);
        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
  }); 
  
}
function showTestResult(data) {
  console.log('data', data);
  $('#exam_test_container').hide();
  $('#exam_result_container').show();
  $('#test_result').html((data.is_passed)? 'Đạt': 'Không đạt');
  $('#test_score').html(data.score);
  if(data.test_type === 'renew') {
    $('#general_law_count').html(`(${data.law_correct_general}/5)`);
    $('#specific_law_count').html(`(${data.law_correct_specific}/5)`);
    
    $('#quiz_title').hide();
    $('#quiz_count').hide();

  } else {
    $('#general_law_count').html(`(${data.law_correct_general}/2)`);
    $('#specific_law_count').html(`(${data.law_correct_specific}/3)`);
    $('#quiz_count').html(`(${data.correct_count - data.law_correct}/25)`);
  }

  window.questions_with_answers = data.questions_with_answers;
}
function showReview() {
  $('#exam_test_container').show();
  // hide timer
  $('.exam-question-header').hide();
  // hide finish button
  $('.exam-btn-finish-container').hide();
  // set correct answer
  $.each(window.questions_with_answers, function(question_id, answer_id) {
    $(`#opt_${question_id}_${answer_id}`).addClass('exam_correct_answer');
  });

}
function showQuestion(index) {
  // show/hide question
  
  $('#exam_question_'+ exam_list_ids[exam_current_index]).removeClass("exam_current_question");
  $('#exam_question_'+ exam_list_ids[index]).addClass("exam_current_question");
  // set current question button
  $('#exam_question_btn_'+ exam_current_index).removeClass("exam_current_question_btn");
  $('#exam_question_btn_'+ index).addClass("exam_current_question_btn");
 
  exam_current_index = index;
}

(function ($) {
  init();
  showTimer();
  showQuestion(exam_current_index);
})(jQuery);

</script>

<?php
// Save user item meta data
ExamLearnPress::add_user_items_meta($ui_id, $test_data, META_KEY_EXAM_TEST_QUESTIONS);
?>

