<?php
$choices = ['-- N/A --', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
?>
<h1>Configure Quiz</h1>
<h3><?php echo $quiz['title']; ?></h3>
<p>Course: <a href="<?php echo site_url('course/view').'?id='.urlencode($quiz['course_id']); ?>"><?php echo htmlspecialchars($course['title']); ?></a></p>
<?php echo zl_status(); ?>
<?php if (empty($quiz['essay'])): ?>
    <?php echo form_open(site_url('quiz/configure').'?id='.$id, 'onsubmit="return confirm(\'Are you sure?\')"'); ?>
        <div class="my-3">
            <label class="form-label">Correct score <span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="mc_score_correct" placeholder="Score if correct" value="<?php echo htmlspecialchars($quiz['mc_score_correct']); ?>">
        </div>
        <div class="my-3">
            <label class="form-label">Incorrect score <span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="mc_score_incorrect" placeholder="Score if incorrect" value="<?php echo htmlspecialchars($quiz['mc_score_incorrect']); ?>">
        </div>
        <div class="my-3">
            <label class="form-label">Empty score <span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="mc_score_empty" placeholder="Score if empty" value="<?php echo htmlspecialchars($quiz['mc_score_empty']); ?>">
        </div>
        <h5>Quiz answers</h5>
        <div class="row">
            <?php for ($i = 1; $i <= $quiz['num_questions']; $i++): ?>
                <div class="col-6 col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <label class="form-label">Answer number <?php echo $i; ?></label>
                            <select class="form-select" name="mc_answers[<?php echo $i; ?>]">
                                <?php for ($j = 0; $j <= $quiz['mc_num_choices']; $j++): ?>
                                    <option value="<?php echo $j; ?>" <?php echo ($quiz['mc_answers'][$i] == $j) ? 'selected' : ''; ?>><?php echo $choices[$j]; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
        <div class="my-3">
            <button type="submit" class="btn btn-success" name="submit" value="1">Apply <span class="fa fa-save ms-2"></span></button>
        </div>
    </form>
<?php else: ?>
    <p>Quiz with essay type doesn't need an extra configuration</p>
<?php endif; ?>
