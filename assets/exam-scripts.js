function examShowAnswer(question_id) {
  const correct = jQuery('#q_' + question_id).val();
  jQuery('#opt_'+ question_id + '_' + correct ).addClass("exam_correct_answer");
}
