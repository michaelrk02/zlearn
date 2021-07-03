<div class="p-3">
    <div class="mb-3">
        <?php if ($role === 'instructor'): ?>
            <a class="mx-1" href="<?php echo site_url('quiz/create').'?course_id='.urlencode($id); ?>">Create quiz</a>
        <?php endif; ?>
    </div>
    <div class="card my-3 bg-light">
        <div class="card-body">
            <form method="get">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <input type="hidden" name="tab" value="quizzes">
                <div class="row">
                    <div class="col-12 col-lg-5 my-1">
                        <div class="input-group">
                            <span class="input-group-text">Filter</span>
                            <input type="text" class="form-control" name="filter" placeholder="Quiz title" value="<?php echo htmlspecialchars($filter); ?>">
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
    <?php foreach ($quizzes as $quiz): ?>
        <div class="card my-3">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($quiz['title']); ?> <?php if (!empty($quiz['locked'])): ?><span class="badge bg-secondary">LOCKED</span><?php endif; ?></h5>
                <div class="card-text py-2">
                    <div class="my-1">Type: <b><?php echo !empty($quiz['essay']) ? 'essay' : 'multiple choice'; ?></b></div>
                    <div class="mu-1">Duration: <b><?php echo $quiz['duration'] == 0 ? 'unlimited' : $quiz['duration'].' minutes'; ?></b></div>
                    <div class="my-1">Number of questions: <b><?php echo $quiz['num_questions']; ?></b></div>
                </div>
                <a class="card-link btn btn-info" href="<?php echo site_url('quiz/view').'?id='.urlencode($quiz['quiz_id']); ?>">View <span class="fa fa-eye ms-2"></span></a>
                <?php if ($role === 'instructor'): ?>
                    <a class="card-link btn btn-secondary" href="<?php echo site_url('quiz/edit').'?id='.urlencode($quiz['quiz_id']); ?>">Edit <span class="fa fa-edit ms-2"></span></a>
                    <a class="card-link btn btn-secondary" href="<?php echo site_url('quiz/configure').'?id='.urlencode($quiz['quiz_id']); ?>">Configure <span class="fa fa-cog ms-2"></span></a>
                <?php endif; ?>
                <?php if (($role === 'instructor') || !empty($quiz['show_leaderboard'])): ?>
                    <a class="card-link btn btn-secondary" href="<?php echo site_url('quiz/grades').'?id='.urlencode($quiz['quiz_id']); ?>">Grades</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
