<?php

$actions = [];
$actions[] = ['Enroll', 'course/enroll'];

if ($allow_course_management) {
    $actions[] = ['Create', 'course/create'];
}

foreach ($actions as $key => $value) {
    $actions[$key] = '<a class="mx-1" href="'.site_url($value[1]).'">'.$value[0].'</a>';
}

$actions = implode(' ', $actions);

?>
<h1>Courses</h1>
<p><?php echo $actions; ?></p>
<?php echo zl_status(); ?>
<div class="card bg-light my-3">
    <div class="card-body">
        <form method="get">
            <div class="row">
                <div class="col-12 col-lg-5 my-1">
                    <div class="input-group">
                        <span class="input-group-text">Filter</span>
                        <input type="text" class="form-control" name="filter" placeholder="Course title" value="<?php echo $filter; ?>">
                    </div>
                </div>
                <div class="col-6 col-lg-3 my-1">
                    <div class="input-group">
                        <span class="input-group-text">Page</span>
                        <select class="form-select" name="page">
                            <?php for ($i = 1; $i <= $max_page; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($page == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="col-6 col-lg-3 my-1">
                    <div class="input-group">
                        <span class="input-group-text">Display</span>
                        <input type="number" class="form-control" name="display" placeholder="Items per page" value="<?php echo $display; ?>">
                    </div>
                </div>
                <div class="col-12 col-lg-1 my-1">
                    <button type="submit" class="btn btn-success" style="width: 100%">Go</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php foreach ($courses as &$course): ?>
    <div class="card my-3">
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
            <h6 class="card-subtitle mb-2 text-muted"><?php echo !empty($course['instructor']) ? 'Instructor' : 'Participant'; ?></h6>
            <p class="card-text course-metadata"><?php echo htmlspecialchars($course['metadata']); ?></p>
            <a class="card-link btn btn-warning" href="<?php echo site_url('course/view').'?id='.urlencode($course['course_id']); ?>">Open <span class="fa fa-folder-open ms-2"></span></a>
            <?php if ($allow_course_management && !empty($course['instructor'])): ?>
                <a class="card-link btn btn-secondary" href="<?php echo site_url('course/edit').'?id='.urlencode($course['course_id']); ?>">Edit <span class="fa fa-edit ms-2"></span></a>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
<script>
$(document).ready(function() {
    $('.course-metadata').html(function(index, metadata) {
        return marked(metadata);
    });
});
</script>
