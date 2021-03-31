<div class="p-3">
    <div class="mb-3">
        <?php if ($role === 'instructor'): ?>
            <a class="mx-1" href="<?php echo site_url('material/add').'?course_id='.urlencode($id); ?>">Add material</a>
        <?php endif; ?>
    </div>
    <div class="card my-3 bg-light">
        <div class="card-body">
            <form method="get">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="tab" value="materials">
                <div class="row">
                    <div class="col-12 col-lg-6 my-1">
                        <div class="input-group">
                            <span class="input-group-text">Filter</span>
                            <input type="text" class="form-control" name="filter" placeholder="Material title" value="<?php echo $filter; ?>">
                        </div>
                    </div>
                    <div class="col-12 col-lg-6 my-1">
                        <div class="input-group">
                            <span class="input-group-text">Order</span>
                            <select class="form-select" name="order">
                                <option value="timestamp" <?php echo $order === 'timestamp' ? 'selected' : ''; ?>>Timestamp</option>
                                <option value="title" <?php echo $order === 'title' ? 'selected' : ''; ?>>Title</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6 col-lg-5 my-1">
                        <div class="input-group">
                            <span class="input-group-text">Page</span>
                            <select class="form-select" name="page">
                                <?php for ($i = 1; $i <= $max_page; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($page == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-6 col-lg-5 my-1">
                        <div class="input-group">
                            <span class="input-group-text">Display</span>
                            <input type="number" class="form-control" name="display" placeholder="Items per page" value="<?php echo $display; ?>">
                        </div>
                    </div>
                    <div class="col-12 col-lg-2 my-1">
                        <button type="submit" class="btn btn-success" style="width: 100%">Go</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php foreach ($materials as $material): ?>
        <div class="card my-3">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($material['title']); ?></h5>
                <h6 class="card-subtitle mb-2 text-muted zl-material-timestamp"><?php echo $material['timestamp']; ?></h6>
                <p class="card-text"><?php echo htmlspecialchars($material['subtitle']); ?></p>
                <a class="card-link btn btn-info" href="<?php echo site_url('material/view').'?id='.urlencode($material['material_id']); ?>">View <span class="fa fa-eye ms-2"></span></a>
                <?php if ($role === 'instructor'): ?>
                    <a class="card-link btn btn-secondary" href="<?php echo site_url('material/edit').'?id='.urlencode($material['material_id']); ?>">Edit <span class="fa fa-edit ms-2"></span></a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
$(document).ready(function() {
    $('.zl-material-timestamp').text(function(index, timestamp) {
        return 'Posted on ' + (new Date(parseInt(timestamp) * 1000)).toString();
    });
});
</script>
