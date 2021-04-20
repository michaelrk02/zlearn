<?php echo zl_status(); ?>
<h1><?php echo htmlspecialchars($quiz['title']); ?> <?php if (!empty($quiz['locked'])): ?><span class="badge bg-secondary"><span class="fa fa-lock me-2"></span> LOCKED</span><?php endif; ?></h1>
<p>Quiz on <a href="<?php echo site_url('course/view').'?id='.urlencode($quiz['course_id']); ?>"><?php echo $course['title']; ?></a></p>
<h3>Description</h3>
<p id="description"><?php echo htmlspecialchars($quiz['description']); ?></p>
<h3>Technical Specifications</h3>
<ul>
    <li>Number of questions: <b><?php echo $quiz['num_questions']; ?></b></li>
    <li>Type: <b><?php echo !empty($quiz['essay']) ? 'essay' : 'multiple choice'; ?></b></li>
    <?php if (empty($quiz['essay'])): ?>
        <li>Correct score: <b><?php echo $quiz['mc_score_correct']; ?></b></li>
        <li>Incorrect score: <b><?php echo $quiz['mc_score_incorrect']; ?></b></li>
        <li>Empty score: <b><?php echo $quiz['mc_score_empty']; ?></b></li>
        <li>Maximum score: <b><?php echo max($quiz['mc_score_correct'], $quiz['mc_score_incorrect'], $quiz['mc_score_empty']) * $quiz['num_questions']; ?></b></li>
        <li>Minimum score: <b><?php echo min($quiz['mc_score_correct'], $quiz['mc_score_incorrect'], $quiz['mc_score_empty']) * $quiz['num_questions']; ?></b></li>
    <?php endif; ?>
    <li>Show grades: <b><?php echo !empty($quiz['show_grades']) ? 'yes' : 'no'; ?></b></li>
    <li>Show leaderboard: <b><?php echo !empty($quiz['show_leaderboard']) ? 'yes' : 'no'; ?></b></li>
</ul>
<?php if (($role === 'participant') && empty($quiz['locked']) && isset($attempt)): ?>
    <p>Current attempt: <b><?php echo $attempt['answered']; ?></b> out of <?php echo $quiz['num_questions']; ?> questions have been answered</p>
<?php endif; ?>
<div class="my-3">
    <?php if (($role === 'participant') && empty($quiz['locked'])): ?>
        <a onclick="return confirm('Are you sure?')" class="btn btn-success" href="<?php echo site_url('quiz/attempt').'?id='.urlencode($id); ?>"><?php echo !isset($attempt) ? 'Start' : 'Continue'; ?> attempt <span class="fa fa-flag-checkered ms-2"></span></a>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    var description = $('#description');
    description.html(marked(description.text()));
});
</script>
