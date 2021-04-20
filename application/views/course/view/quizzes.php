<div class="p-3">
    <div class="mb-3">
        <?php if ($role === 'instructor'): ?>
            <a class="mx-1" href="<?php echo site_url('quiz/create').'?course_id='.urlencode($id); ?>">Create quiz</a>
        <?php endif; ?>
    </div>
</div>
