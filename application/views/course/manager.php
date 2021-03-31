<?php if ($action === 'create'): ?>
    <h1>Create New Course</h1>
<?php else: ?>
    <h1>Edit Course<h1>
    <h5><?php echo $course['title']; ?></h5>
<?php endif; ?>
<?php echo zl_status(); ?>
<?php echo form_open(($action === 'create') ? site_url('course/create') : site_url('course/edit').'?id='.urlencode($id), 'onsubmit="return confirm(\'Are you sure?\')"'); ?>
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <?php if ($action === 'edit'): ?>
        <div class="my-3">Course ID: <code><?php echo $id; ?></code></div>
    <?php endif; ?>
    <div class="my-3">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="title" placeholder="Course title" value="<?php echo htmlspecialchars($course['title']); ?>">
        <div class="form-text">Enter up to 100 characters</div>
    </div>
    <div class="my-3">
        <label class="form-label">Password <?php if ($action === 'create'): ?><span class="text-danger">*</span><?php endif; ?></label>
        <input type="password" class="form-control" name="password" placeholder="Course password <?php echo ($action === 'edit') ? '(unchanged)' : ''; ?>">
        <div class="form-text">Enter 8-72 characters</div>
    </div>
    <div class="my-3">
        <label class="form-label">Password confirmation <?php if ($action === 'create'): ?><span class="text-danger">*</span><?php endif; ?></label>
        <input type="password" class="form-control" name="password_confirm" placeholder="Repeat course password">
    </div>
    <div class="my-3">
        <label class="form-label">Metadata</label>
        <input type="text" class="form-control" name="metadata" placeholder="Displayed at each item in the course list (optional)" value="<?php echo htmlspecialchars($course['metadata']); ?>">
        <div class="form-text">Markdown formatted. Enter up to 250 characters</div>
    </div>
    <div class="my-3">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description" placeholder="Describe your course (optional)" rows="8"><?php echo htmlspecialchars($course['description']); ?></textarea>
        <div class="form-text">Markdown formatted</div>
    </div>
    <div class="my-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="allow_leave" value="1" <?php echo !empty($course['allow_leave']) ? 'checked' : ''; ?>>
            <label class="form-check-label">Allow members to leave</label>
        </div>
        <div class="form-text">Whether members are allowed to unregister from this course</div>
    </div>
    <div class="my-3">
        <button class="btn btn-success" type="submit" name="submit" value="1">Submit <span class="fa fa-paper-plane ms-2"></span></button>
        <?php if ($action === 'edit'): ?>
            <a onclick="return confirm('Are you sure? This action can\'t be undone')" class="btn btn-danger ms-2" href="<?php echo site_url('course/delete').'?id='.urlencode($id); ?>">Delete <span class="fa fa-trash ms-2"></span></a>
        <?php endif; ?>
    </div>
</form>
