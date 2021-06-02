<?php
$choices = [NULL, 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
?>
<div id="token" class="d-none"><?php echo $token; ?></div>
<div id="get-response-url" class="d-none"><?php echo htmlspecialchars(site_url('quiz_attempt/get_response').'?quiz_id='.urlencode($id)); ?></div>
<div id="put-response-url" class="d-none"><?php echo htmlspecialchars(site_url('quiz_attempt/put_response').'?quiz_id='.urlencode($id)); ?></div>
<h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
<p><a target="_blank" href="<?php echo site_url('quiz/view').'?id='.urlencode($id); ?>">View quiz information</a></p>
<div class="mb-3">
    <iframe src="<?php echo site_url('plugins/pdf_viewer').'?src='.urlencode($pdf_url); ?>" width="90%" height="600"></iframe>
</div>
    <div id="timer" class="alert alert-info">
        Time remaining : <span id="time-remaining" class="fw-bold"></span>
    </div>
    <h3>Question #<span class="zl-question-no">#</span></h3>
    <div class="spinner-border mb-2" id="loading"></div>
<?php if (!empty($quiz['essay'])): ?>
    <h5>Write down your answer for question number <span class="zl-question-no">#</span> in the textbox below</h5>
    <div>
        <div>
            <textarea class="form-control" style="resize: vertical" rows="8" id="essay-answer" placeholder="Type your answer here and then click on 'Save' button when finished"></textarea>
            <div class="form-text">Type your answer on the textbox above and then click on 'Save' button to update your answer on the server when finished. You can reset the local answer and replacing with the answer stored on the server by pressing the 'Sync' button.</div>
        </div>
        <div class="mt-3 row">
            <div class="col-auto ms-auto">
                <button type="button" class="btn btn-outline-primary zl-btn-action ms-2" id="sync-answer">Sync <span class="fa fa-redo ms-2"></span></button>
                <button type="button" class="btn btn-primary zl-btn-action ms-2" id="save-answer">Save <span class="fa fa-save ms-2"></span></button>
            </div>
        </div>
    </div>
<?php else: ?>
    <p>Select one of the following options based on the question number <span class="zl-question-no">#</span> above</p>
    <div>
        <div class="row">
            <?php for ($i = 1; $i <= $quiz['mc_num_choices']; $i++): ?>
                <div class="col-6 col-md-3 d-grid my-2">
                    <button class="btn btn-outline-dark zl-choice-btn zl-btn-action" data-zl-choice-id="<?php echo $i; ?>"><?php echo $choices[$i]; ?></button>
                </div>
            <?php endfor; ?>
        </div>
        <div><button type="button" class="btn btn-link zl-btn-action" id="clear-choice">Clear</button></div>
    </div>
<?php endif; ?>
<div class="row my-3">
    <div class="col-6 col-sm-4 col-md-3 col-lg-2 me-auto d-grid">
        <button type="button" class="btn btn-secondary zl-nav-prev zl-btn-action"><span class="fa fa-angle-left me-2"></span> Previous</button>
    </div>
    <div class="col-6 col-sm-4 col-md-3 col-lg-2 ms-auto d-grid">
        <button type="button" class="btn btn-secondary zl-nav-next zl-btn-action">Next <span class="fa fa-angle-right ms-2"></span></button>
    </div>
</div>
<div class="card my-3">
    <div class="card-body bg-light">
        <h5>Quiz Navigation</h5>
        <div class="row">
            <?php for ($i = 1; $i <= $quiz['num_questions']; $i++): ?>
                <div class="col-6 col-md-3 my-2">
                    <div class="p-2 bg-white">
                        <div class="d-grid"><button class="btn btn-warning zl-btn-action zl-nav-btn" data-zl-question-no="<?php echo $i; ?>"><?php echo $i; ?></button></div>
                        <?php if (empty($quiz['essay'])): ?>
                            <div class="text-center zl-nav-answer" data-zl-question-no="<?php echo $i; ?>">-- N/A --</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>
<div class="my-3">
    <a class="btn btn-success" onclick="return confirm('Are you sure to go back? (if you are currently working on an essay quiz, make sure you have saved the current answer before proceeding)')" href="<?php echo site_url('quiz/view').'?id='.urlencode($id); ?>"><span class="fa fa-save me-2"></span> Save and Close</a>
</div>

<script>

var choices = ['-- N/A --', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

var token;
var tokenHeaders = {};
var getResponseUrl;
var putResponseUrl;

var numQuestions = <?php echo $quiz['num_questions']; ?>;
var currentQuestionNo;

var essay = <?php echo !empty($quiz['essay']) ? 'true' : 'false'; ?>;

var numChoices;

var timeRemaining;
var deadline;

function navigate(questionNo, force) {
    if (typeof(force) === 'boolean') {
        if (force) {
            force = true;
        }
    } else {
        force = false;
    }

    var proceed = true;
    if (essay && !force) {
        proceed = confirm('Are you sure to navigate to another question? Make sure you have saved your current answer first');
    }
    if (proceed) {
        if ((1 <= questionNo) && (questionNo <= numQuestions)) {
            $('.zl-question-no').text(questionNo);

            $('.zl-nav-btn')
                .removeClass(['btn-warning', 'btn-info'])
                .addClass('btn-warning');

            $('.zl-nav-btn[data-zl-question-no="' + questionNo + '"]')
                .removeClass('btn-warning')
                .addClass('btn-info');

            currentQuestionNo = questionNo;
        }
        refresh();
    }
}

function showLoading() {
    $('#loading').show();
    $('.zl-btn-action').attr('disabled', true);
}

function hideLoading() {
    $('#loading').hide();
    $('.zl-btn-action').attr('disabled', false);
}

function displayError(xhr, status, error) {
    alert('An error occured when performing the operation: ' + error);
}

function refresh() {
    showLoading();

    var data;
    if (essay) {
        data = JSON.stringify({
            question_no: currentQuestionNo
        });
    } else {
        data = JSON.stringify({});
    }

    $.ajax(getResponseUrl, {
        complete: hideLoading,
        contentType: 'application/json',
        data: data,
        dataType: 'json',
        error: displayError,
        headers: tokenHeaders,
        method: 'POST',
        success: function(response) {
            if (essay) {
                $('#essay-answer').val(response.data[0]);
            } else {
                $('.zl-nav-answer').each(function(index, element) {
                    var questionNo = $(element).attr('data-zl-question-no');

                    $(element).text(choices[response.data[questionNo.toString()][0]]);
                });

                $('.zl-choice-btn')
                    .removeClass(['btn-outline-dark', 'btn-dark'])
                    .addClass('btn-outline-dark');

                var choiceId = response.data[currentQuestionNo.toString()][0];
                if (choiceId > 0) {
                    $('.zl-choice-btn[data-zl-choice-id="' + choiceId + '"]')
                        .removeClass('btn-outline-dark')
                        .addClass('btn-dark');
                }
            }
        }
    });
}

<?php if (empty($quiz['essay'])): ?>
numChoices = <?php echo $quiz['mc_num_choices']; ?>;
<?php endif; ?>

function choose(choiceId) {
    if ((0 <= choiceId) && (choiceId <= numChoices)) {
        showLoading();
        $.ajax(putResponseUrl, {
            complete: refresh,
            contentType: 'application/json',
            data: JSON.stringify({
                question_no: currentQuestionNo,
                data: choiceId
            }),
            dataType: 'json',
            error: displayError,
            headers: tokenHeaders,
            method: 'POST'
        });
    }
}

function saveAnswer() {
    showLoading();
    $.ajax(putResponseUrl, {
        complete: function() {
            alert('Your answer has been successfully saved');
            refresh();
        },
        contentType: 'application/json',
        data: JSON.stringify({
            question_no: currentQuestionNo,
            data: $('#essay-answer').val()
        }),
        dataType: 'json',
        error: displayError,
        headers: tokenHeaders,
        method: 'POST'
    });
}

$(document).ready(function() {
    hideLoading();

    token = $('#token').text();
    tokenHeaders['X-ZLEARN-Attempt-Token'] = token;

    getResponseUrl = $('#get-response-url').text();
    putResponseUrl = $('#put-response-url').text();

    $('.zl-nav-btn').on('click', function(e) {
        navigate(parseInt($(e.target).attr('data-zl-question-no')));
    });

    $('.zl-nav-prev').on('click', function() {
        navigate(currentQuestionNo - 1);
    });

    $('.zl-nav-next').on('click', function() {
        navigate(currentQuestionNo + 1);
    });

    if (!essay) {
        $('#clear-choice').on('click', function() {
            choose(0);
        });

        $('.zl-choice-btn').on('click', function(e) {
            choose(parseInt($(e.target).attr('data-zl-choice-id')));
        });
    } else {
        $('#save-answer').on('click', function() {
            if (confirm('Save answer? This will overwrite your previous response on the server')) {
                saveAnswer();
            }
        });

        $('#sync-answer').on('click', function() {
            if (confirm('Sync answer? This will overwrite your local answer written in the textbox above')) {
                refresh();
            }
        });
    }

    navigate(1, true);

    var duration = <?php echo $quiz['duration'] * 60; ?>;
    $('#timer').hide();
    if (duration != 0) {
        var timestamp = <?php echo $timestamp; ?>;
        deadline = timestamp + duration;
        $('#timer').show();
        timeRemaining = $('#time-remaining');

        updateTimer();
        setInterval(updateTimer, 1000);
    }
});

function updateTimer() {
    var now = Math.floor(Date.now() / 1000);
    var remaining = Math.max(0, deadline - now);

    var hours = Math.floor(remaining / 3600);
    var minutes = Math.floor(remaining / 60) % 60;
    var seconds = remaining % 60;

    var text = hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');

    timeRemaining.text(text);
}

</script>

