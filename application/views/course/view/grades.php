<?php if ($role === 'instructor'): ?>
    <div class="p-3">
        <div class="card bg-light">
            <div class="card-body">
                <form method="get">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($_GET['id']); ?>">
                    <input type="hidden" name="tab" value="grades">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="order" value="user_name" <?php echo $_GET['order'] === 'user_name' ? 'checked' : ''; ?>>
                        <label class="form-check-label">Sort by user name</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="order" value="grade" <?php echo $_GET['order'] === 'grade' ? 'checked' : ''; ?>>
                        <label class="form-check-label">Sort by grade</label>
                    </div>
                    <div class="mt-3"><button type="submit" class="btn btn-primary">Apply</button></div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
<div class="p-3">
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <?php if ($role === 'instructor'): ?>
                        <th scope="col">#</th>
                        <th scope="col">User ID</th>
                        <th scope="col">User Name</th>
                        <th scope="col">Grade Total</th>
                    <?php else: ?>
                        <th scope="col">#</th>
                        <th scope="col">Quiz</th>
                        <th scope="col">Grade</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grades as $i => $grade): ?>
                    <tr>
                        <?php if ($role === 'instructor'): ?>
                            <th scope="row"><?php echo ($i + 1); ?></th>
                            <td><?php echo $grade['user_id']; ?></td>
                            <td><?php echo $grade['name']; ?></td>
                            <td><?php echo $grade['score']; ?></td>
                        <?php else: ?>
                            <th scope="row"><?php echo ($i + 1); ?></th>
                            <td><a href="<?php echo site_url('quiz/view').'?id='.urlencode($grade['quiz_id']); ?>"><?php echo htmlspecialchars($grade['quiz_title']); ?></a></td>
                            <td><?php echo $grade['score']; ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <?php if ($role === 'participant'): ?>
                <tfoot>
                    <th scope="col" colspan="2">Grade Total:</th>
                    <td><?php echo $grade_total; ?></td>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
