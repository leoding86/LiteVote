var survey = window.survey = {
  questions: [],
  questionsTotal: 0,
  $questions: [],

  countQuestions: function() {

  },

  /* 检查选择类型的问题有效性 */
  checkSelectableQuestion: function(question_id, $recently_answer) {
    var question = this.questions[question_id];
    var maximum = question.maximum_items;
    var minimum = question.minimum_items;
    var checked_count = 0;
    var $question = this.$questions.filter('#' + question_id);
    var hasError = false;
    var _this = this;

    if (!$question) {
      hasError = true;
      return;
    }

    $question.find('.answer-select').each(function(i, $answer) {
      if ($answer.checked) {
        checked_count++;
      }
    });

    if (checked_count == 0) {
      hasError = true;
      return;
    }

    if (maximum > 0 && checked_count > maximum) {
      this.answerMaximumError($question);
      hasError = true;
    }

    if (minimum > 0 && checked_count < minimum) {
      this.answerMinimumError($question);
      hasError = true;
    }

    if (hasError) {
      $question.addClass('error-question');
    } else {
      $question.removeClass('error-question');
    }

    return !hasError;
  },

  checkAllQuestions: function() {
    var _this = this;
    var allRight = true;
    $.each(this.questions, function(question_id, question) {
      /* 暂时只有check类型问题 */
      if (!_this.checkSelectableQuestion(question_id)) {
        _this.$questions.filter('#' + question_id).addClass('error-question');
        allRight = false;
      }
    });

    return allRight;
  },

  answerMaximumError: function($question) {
    this.errorAnimation($question.find('.select-maximum-badge'));
  },

  answerMinimumError: function($question) {
    this.errorAnimation($question.find('.select-minimum-badge'));
  },

  errorAnimation: function($el) {
    $el.stop(true).animate({'top': '5px'}, 50)
                  .animate({'top': '-5px'}, 100)
                  .animate({'top': 0}, 50);
  },

  formAlert: function() {

  },

  init: function() {
    this.$questions = $('.question-content');
    var _this = this;
    this.$questions.find('.answer-select').on('change', function() {
      _this.checkSelectableQuestion($(this).parents('.question-content').attr('id'), $(this));
    });
  }
};