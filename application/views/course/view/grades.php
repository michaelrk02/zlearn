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
