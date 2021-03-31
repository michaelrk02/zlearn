<?php if ($action === 'create'): ?>
    <h1>Create New Material</h1>
<?php else: ?>
    <h1>Edit Material<h1>
    <h5><?php echo $material['title']; ?></h5>
<?php endif; ?>
<?php echo zl_status(); ?>
<?php echo form_open(($action === 'add') ? site_url('material/add').'?course_id='.urlencode($material['course_id']) : site_url('material/edit').'?id='.urlencode($id), 'onsubmit="return confirm(\'Are you sure?\')"'); ?>
    <div class="my-3">Course: <b><?php echo htmlspecialchars($course['title']); ?></b> <span class="text-muted font-monospace">[<?php echo $material['course_id']; ?>]</span></div>
    <?php if ($action === 'edit'): ?>
        <div class="my-3">Material ID: <code><?php echo $id; ?></code></div>
    <?php endif; ?>
    <div class="my-3">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="title" placeholder="Material title" value="<?php echo htmlspecialchars($material['title']); ?>">
        <div class="form-text">Enter up to 100 characters</div>
    </div>
    <div class="my-3">
        <label class="form-label">Subtitle</label>
        <input type="text" class="form-control" name="subtitle" placeholder="Material subtitle (optional)" value="<?php echo htmlspecialchars($material['subtitle']); ?>">
        <div class="form-text">Enter up to 250 characters</div>
    </div>
    <div class="my-3">
        <label class="form-label">Contents <span class="text-danger">*</span></label>
        <textarea class="form-control" name="contents" placeholder="Material contents" rows="16"><?php echo htmlspecialchars($material['contents']); ?></textarea>
        <div class="form-text">Markdown formatted</div>
    </div>
    <div class="my-3">
        <button class="btn btn-success" type="submit" name="submit" value="1">Submit <span class="fa fa-paper-plane ms-2"></span></button>
        <?php if ($action === 'edit'): ?>
            <a onclick="return confirm('Are you sure? This action can\'t be undone')" class="btn btn-danger ms-2" href="<?php echo site_url('material/remove').'?id='.urlencode($id); ?>">Remove <span class="fa fa-trash ms-2"></span></a>
        <?php endif; ?>
    </div>
</form>
