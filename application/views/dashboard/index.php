<div><?php echo zl_status(); ?></div>
<div class="my-2 alert alert-info">
    <h3>Profile</h3>
    <hr>
    <p>User ID: <b><?php echo zl_session_get('user_id'); ?></b></p>
    <p>User Name: <b><?php echo htmlspecialchars($user['name']); ?></b></p>
    <p>E-mail Address: <b><?php echo htmlspecialchars($user['email']); ?></b></p>
</div>
<div class="mt-4">
    <h3>My Courses</h3>
    <div class="row">
        <?php foreach ($courses as $course): ?>
            <div class="col col-12 col-md-4 my-2">
                <div class="card" style="height: 100%">
                    <div class="bg-light text-center">
                        <div class="fa fa-book display-4 text-muted" style="margin-top: 4rem; margin-bottom: 4rem"></div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                        <h6 class="card-subtitle text-muted"><?php echo !empty($course['instructor']) ? 'Instructor' : 'Participant'; ?></h6>
                        <p class="mt-2 course-metadata"><?php echo htmlspecialchars($course['metadata']); ?></p>
                        <div><a class="btn btn-primary" href="<?php echo site_url('course/view').'?id='.urlencode($course['course_id']); ?>">Go to course</a></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="my-2"><a href="<?php echo site_url('course/listing'); ?>">See all</a></div>
</div>
<script>
$(document).ready(function() {
    $('.course-metadata').html(function(index, metadata) {
        return marked(metadata);
    });
});
</script>
