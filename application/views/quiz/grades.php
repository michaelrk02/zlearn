<h1>Quiz Grades</h1>
<p><b>Course:</b> <a href="<?php echo site_url('course/view').'?id='.urlencode($quiz['course_id']); ?>"><?php echo htmlspecialchars($course['title']); ?></a></p>
<p><b>Quiz:</b> <a href="<?php echo site_url('quiz/view').'?id='.urlencode($id); ?>"><?php echo htmlspecialchars($quiz['title']); ?></a></p>
<div class="mb-2">
    <?php if ($role === 'instructor'): ?>
        <a class="mx-1" href="<?php echo site_url('quiz/grade_calculate').'?id='.urlencode($id); ?>">Calculate all grades</a>
    <?php endif; ?>
</div>
<?php echo zl_status(); ?>
<hr>
<ol>
    <?php foreach ($grades as $grade): ?>
        <li id="UID-<?php echo md5($grade['user_id']); ?>">
            <span><?php echo htmlspecialchars($grade['name']); ?></span>
            <span><b>(score: <?php echo $grade['score']; ?>)</b></span>
            <?php if ($user_id === $grade['user_id']): ?>
                <span><b>(YOU)</b></span>
            <?php endif; ?>
            <?php if ($role === 'instructor'): ?>
                <span class="text-muted font-monospace">[<?php echo htmlspecialchars($grade['user_id']); ?>]</span>
                <a class="mx-1" href="<?php echo site_url('quiz/grade').'?id='.urlencode($id).'&user_id='.urlencode($grade['user_id']).'&question_no=1'; ?>">Perform grading</a>
                <a class="mx-1" href="<?php echo site_url('quiz/grade_calculate').'?id='.urlencode($id).'&user_id='.urlencode($grade['user_id']); ?>">Calculate score</a>
                <span class="recently-graded">&raquo; <i>RECENTLY GRADED</i></span>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ol>

<script>
$('.recently-graded').hide();
if (location.hash.startsWith('#UID-')) {
    $(location.hash).find('.recently-graded').show();
}
</script>
