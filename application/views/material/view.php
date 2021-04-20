<div class="my-2"><a href="<?php echo site_url('course/view').'?id='.urlencode($material['course_id']).'&tab=materials'; ?>"><span class="fa fa-arrow-circle-left"></span> Back to materials on "<?php echo htmlspecialchars($course['title']); ?>"</a></div>
<h1><?php echo $material['title']; ?></h1>
<h5 class="mb-3"><?php echo $material['subtitle']; ?></h5>
<?php echo zl_status(); ?>
<div class="mb-2"><b>Course:</b> <a href="<?php echo site_url('course/view').'?id='.urlencode($material['course_id']); ?>"><?php echo $course['title']; ?></a></div>
<div class="mb-2"><b>Timestamp:</b> <span id="timestamp"><?php echo $material['timestamp']; ?></span></div>
<div class="mb-2">
    <?php if ($role === 'instructor'): ?>
        <a class="mx-1" href="<?php echo site_url('material/edit').'?id='.urlencode($id); ?>">Edit material</a>
    <?php endif; ?>
</div>
<hr>
<p id="contents"><?php echo $material['contents']; ?></p>

<script>

$(document).ready(function() {
    $('#timestamp').text(function(index, timestamp) {
        return (new Date(parseInt(timestamp) * 1000)).toString();
    });

    var contents = $('#contents');
    contents.html(marked(contents.text()));
});

</script>
