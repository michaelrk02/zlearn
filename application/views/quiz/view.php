<div class="my-3"><a href="<?php echo site_url('course/view').'?id='.urlencode($quiz['course_id']).'&tab=quizzes'; ?>"><span class="fa fa-arrow-circle-left me-2"></span> Back to quizzes on "<?php echo htmlspecialchars($course['title']); ?>"</a></div>
<?php echo zl_status(); ?>
<h1><?php echo htmlspecialchars($quiz['title']); ?> <?php if (!empty($quiz['locked'])): ?><span class="badge bg-secondary"><span class="fa fa-lock me-2"></span> LOCKED</span><?php endif; ?></h1>
<p>Quiz on <a href="<?php echo site_url('course/view').'?id='.urlencode($quiz['course_id']); ?>"><?php echo $course['title']; ?></a></p>

<div class="my-3">
    <?php if ($role === 'instructor'): ?>
        <a class="mx-1" href="<?php echo site_url('quiz/edit').'?id='.urlencode($id); ?>">Edit quiz</a>
        <a class="mx-1" href="<?php echo site_url('quiz/configure').'?id='.urlencode($id); ?>">Configure quiz</a>
    <?php endif; ?>
    <?php if (($role === 'instructor') || !empty($quiz['show_leaderboard'])): ?>
        <a class="mx-1" href="<?php echo site_url('quiz/grades').'?id='.urlencode($id); ?>">Grades / leaderboard</a>
    <?php endif; ?>
</div>
<h3>Description</h3>
<p id="description"><?php echo htmlspecialchars($quiz['description']); ?></p>
<h3>Technical Specifications</h3>
<ul>
    <li>Duration: <b><?php echo $quiz['duration'] == 0 ? 'unlimited' : $quiz['duration'].' minutes' ?></b></li>
    <li>Number of questions: <b><?php echo $quiz['num_questions']; ?></b></li>
    <li>Type: <b><?php echo !empty($quiz['essay']) ? 'essay' : 'multiple choice'; ?></b></li>
    <li>Show individual grades: <b><?php echo !empty($quiz['show_grades']) ? 'yes' : 'no'; ?></b></li>
    <li>Show leaderboard: <b><?php echo !empty($quiz['show_leaderboard']) ? 'yes' : 'no'; ?></b></li>
</ul>
<h3>Grading</h3>
<ul>
    <?php if (!empty($quiz['essay'])): ?>
        <li>Your response will be manually graded by our instructors</li>
    <?php else: ?>
        <li>Score if correct: <b><?php echo $quiz['mc_score_correct']; ?></b></li>
        <li>Score if incorrect: <b><?php echo $quiz['mc_score_incorrect']; ?></b></li>
        <li>Score if empty: <b><?php echo $quiz['mc_score_empty']; ?></b></li>
        <li>Minimum grade: <b><?php echo min($quiz['mc_score_correct'], $quiz['mc_score_incorrect'], $quiz['mc_score_empty']) * $quiz['num_questions']; ?></b></li>
        <li>Maximum grade: <b><?php echo max($quiz['mc_score_correct'], $quiz['mc_score_incorrect'], $quiz['mc_score_empty']) * $quiz['num_questions']; ?></b></li>
    <?php endif; ?>
</ul>
<?php if (($role === 'participant') && empty($quiz['locked']) && isset($attempt)): ?>
    <h3>Last Attempt</h3>
    <div class="my-1">Started on <span id="timestamp"><?php echo $attempt['timestamp']; ?></span></div>
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

    var timestamp = $('#timestamp');
    if (timestamp.length != 0) {
        timestamp.text(function(index, timestamp) {
            return (new Date(parseInt(timestamp) * 1000)).toString();
        });
    }
});
</script>
