<?php

$navtabs = [];
$navtabs[] = ['Materials', 'materials'];
$navtabs[] = ['Quizzes', 'quizzes'];
$navtabs[] = ['Grades', 'grades'];
$navtabs[] = ['Members', 'members'];

?>

<div class="modal fade" id="course-information" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Course Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>

$(document).ready(function() {
    $('#course-description').html(function(index, description) {
        return marked(description);
    });
});

</script>

<div class="my-2"><a href="<?php echo site_url('course/listing'); ?>"><span class="fa fa-arrow-circle-left me-2"></span> Back to My Courses</a></div>
<h1><?php echo $course['title']; ?></h1>
<p>Status: <b><?php echo $role; ?></b></p>
<p class="course-metadata"><?php echo htmlspecialchars($course['metadata']); ?></p>
<p>
    <a href="#" class="mx-1" data-bs-toggle="modal" data-bs-target="#course-information">View course information</a>
    <?php if (($role === 'instructor') && $allow_course_management): ?>
        <a class="mx-1" href="<?php echo site_url('course/edit').'?id='.urlencode($id); ?>">Edit course</a>
    <?php endif; ?>
    <?php if (!(($role === 'instructor') && $allow_course_management) && !empty($course['allow_leave'])): ?>
        <a class="mx-1 text-danger" onclick="return confirm('Are you sure to leave this course? All of your quiz works and grades related to this course will be deleted also')" href="<?php echo site_url('course/leave').'?id='.urlencode($id); ?>">Leave course</a>
    <?php endif; ?>
</p>
<?php echo zl_status(); ?>
<ul class="nav nav-tabs">
    <?php foreach ($navtabs as $navtab): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo ($tab === $navtab[1]) ? 'active' : ''; ?>" href="?id=<?php echo urlencode($id); ?>&tab=<?php echo $navtab[1]; ?>"><?php echo $navtab[0]; ?></a>
        </li>
    <?php endforeach; ?>
</ul>
<script>
$(document).ready(function() {
    $('.course-metadata').html(function(index, metadata) {
        return marked(metadata);
    });
});
</script>
