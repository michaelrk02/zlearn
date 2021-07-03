<h1>Quiz Grading</h1>
<p><b>Course:</b> <a href="<?php echo site_url('course/view').'?id='.urlencode($quiz['course_id']); ?>"><?php echo htmlspecialchars($course['title']); ?></a></p>
<p><b>Quiz:</b> <a href="<?php echo site_url('quiz/view').'?id='.urlencode($id); ?>"><?php echo htmlspecialchars($quiz['title']); ?></a> - <a href="<?php echo site_url('quiz/viewpdf').'?id='.urlencode($id); ?>" target="_blank">View PDF</a></p>
<p><b>Respondent:</b> <?php echo $user['name']; ?> <span class="text-muted">[<?php echo $user_id; ?>]</span></p>
<?php echo zl_status(); ?>
<hr>
<h3><?php echo $user['name']; ?>'s Answer</h3>
<p>Question number: <b><?php echo $question_no; ?></b></p>
<div class="mb-2 bg-light p-2"><pre><?php echo htmlspecialchars($response['data'][0]); ?></pre></div>
<div class="mb-2"><a class="btn btn-warning" href="<?php echo site_url('quiz/save_response').'?id='.urlencode($id).'&user_id='.urlencode($user_id).'&question_no='.$question_no; ?>" download>Save Response <span class="ms-2 fa fa-save"></span></a></div>
<div class="text-muted">Response ID: <span class="font-monospace"><?php echo md5($id.'/'.$user_id.'/'.$question_no); ?></span></div>
<hr>
<?php echo form_open(site_url('quiz/grade').'?id='.urlencode($id).'&user_id='.urlencode($user_id).'&question_no='.$question_no); ?>
    <div class="row">
        <div class="col-2 d-grid"><a class="btn btn-secondary" href="<?php echo site_url('quiz/grade').'?id='.urlencode($id).'&user_id='.urlencode($user_id).'&question_no='.($question_no - 1); ?>">Previous</a></div>
        <div class="col-8 col-lg-4 col-md-6 ms-auto me-auto">
            <div class="input-group">
                <span class="input-group-text">Points:</span>
                <input type="number" class="form-control" name="points" value="<?php echo $response['data'][1]; ?>">
                <button type="submit" class="btn btn-primary" name="submit" value="1">Grade <span class="ms-2 fa fa-marker"></span></button>
            </div>
        </div>
        <div class="col-2 d-grid"><a class="btn btn-secondary" href="<?php echo site_url('quiz/grade').'?id='.urlencode($id).'&user_id='.urlencode($user_id).'&question_no='.($question_no + 1); ?>">Next</a></div>
    </div>
</form>
<div class="row">
    <div class="col col-auto ms-auto"><a class="btn btn-success" href="<?php echo site_url('quiz/grades').'?id='.urlencode($id).'#UID-'.md5($user_id); ?>">Finish and calculate <span class="ms-2 fa fa-check"></span></a></div>
</div>
