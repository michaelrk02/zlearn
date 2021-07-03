<?php if ($action === 'create'): ?>
    <h1>Create New Quiz</h1>
<?php else: ?>
    <h1>Edit Quiz<h1>
    <h5><?php echo $quiz['title']; ?></h5>
<?php endif; ?>
<?php echo zl_status(); ?>
<?php echo form_open_multipart(($action === 'create') ? site_url('quiz/create').'?course_id='.urlencode($quiz['course_id']) : site_url('quiz/edit').'?id='.urlencode($id), 'onsubmit="return confirm(\'Are you sure?\')"'); ?>
    <div class="my-3">Course: <b><?php echo htmlspecialchars($course['title']); ?></b> <span class="text-muted font-monospace">[<?php echo $quiz['course_id']; ?>]</span></div>
    <?php if ($action === 'edit'): ?>
        <div class="my-3">Quiz ID: <code><?php echo $id; ?></code></div>
    <?php endif; ?>
    <div class="my-3">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="title" placeholder="Quiz title" value="<?php echo htmlspecialchars($quiz['title']); ?>">
        <div class="form-text">Enter up to 100 characters</div>
    </div>
    <div class="my-3">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description" placeholder="Quiz description (optional)" rows="4"><?php echo htmlspecialchars($quiz['description']); ?></textarea>
        <div class="form-text">Enter up to 1000 characters. Markdown formatted</div>
    </div>
    <div class="my-3">
        <label class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
        <input type="number" class="form-control" name="duration" placeholder="Quiz duration in minutes" value="<?php echo htmlspecialchars($quiz['duration']); ?>">
        <div class="form-text">Enter 0 for limitless duration</div>
    </div>
    <div class="my-3">
        <label class="form-label">Number of questions <span class="text-danger">*</span></label>
        <input type="number" class="form-control" name="num_questions" placeholder="Number of quiz questions" value="<?php echo htmlspecialchars($quiz['num_questions']); ?>">
        <div class="form-text">WARNING: changing this value will reset user responses</div>
    </div>
    <div class="my-3">
        <label class="form-label">Questions PDF <span class="text-danger">*</span></label>
        <div class="alert alert-info">Status: <?php if (!empty($quiz['questions_hash'])): ?><b>UPLOADED</b> <span class="text-muted font-monospace">[hash: <?php echo $quiz['questions_hash']; ?>]</span> <a class="mx-1" target="_blank" href="<?php echo site_url('quiz/viewpdf').'?id='.urlencode($id); ?>">View</a><?php else: ?><b>NOT UPLOADED</b><?php endif; ?></div>
        <input type="file" class="form-control" name="questions_pdf">
        <div class="form-text">Upload questions file with PDF format</div>
    </div>
    <div class="my-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="essay" value="1" <?php echo !empty($quiz['essay']) ? 'checked' : ''; ?>>
            <label class="form-check-label">Essay</label>
        </div>
        <div class="form-text">Whether the quiz type is essay or multiple choice. WARNING: changing this value will reset user responses</div>
    </div>
    <div class="my-3">
        <label class="form-label">Number of multiple choices <span class="text-danger">*</span></label>
        <select class="form-select" name="mc_num_choices">
            <?php for ($i = 2; $i <= 10; $i++): ?>
                <option value="<?php echo $i; ?>" <?php echo ($quiz['mc_num_choices'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
            <?php endfor; ?>
        </select>
        <div class="form-text">Ignore (or set to default value of 2) if the quiz type is essay. WARNING: changing this value will reset user responses</div>
    </div>
    <div class="my-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="show_grades" value="1" <?php echo !empty($quiz['show_grades']) ? 'checked' : ''; ?>>
            <label class="form-check-label">Show grades</label>
        </div>
        <div class="form-text">Allow participants to see their own grades</div>
    </div>
    <div class="my-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="show_leaderboard" value="1" <?php echo !empty($quiz['show_leaderboard']) ? 'checked' : ''; ?>>
            <label class="form-check-label">Show leaderboard</label>
        </div>
        <div class="form-text">Whether to enable leaderboard feature (display rank and other participants' grades)</div>
    </div>
        <div class="my-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="locked" value="1" <?php echo !empty($quiz['locked']) ? 'checked' : ''; ?>>
                <label class="form-check-label">Locked</label>
            </div>
            <div class="form-text">Whether the quiz is locked for attempts</div>
        </div>
    <div class="my-3">
        <button class="btn btn-success" type="submit" name="submit" value="1">Submit <span class="fa fa-paper-plane ms-2"></span></button>
        <?php if ($action === 'edit'): ?>
            <a onclick="return confirm('Are you sure? This action can\'t be undone')" class="btn btn-danger ms-2" href="<?php echo site_url('quiz/delete').'?id='.urlencode($id); ?>">Delete <span class="fa fa-trash ms-2"></span></a>
        <?php endif; ?>
    </div>
</form>
