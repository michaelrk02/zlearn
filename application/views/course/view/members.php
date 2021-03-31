<div class="p-3">
    <?php if ($role === 'instructor'): ?>
        <div class="card my-3">
            <div class="card-body">
                <p>To add members, share these information below</p>
                <div><b>Course ID:</b> <code><?php echo $id; ?></code></div>
                <div><b>Course password:</b> <i>course password that has been set</i></div>
            </div>
        </div>
    <?php endif; ?>
    <h5>Instructors</h5>
    <ol>
        <?php foreach ($members as $member): ?>
            <?php if (!empty($member['instructor'])): ?>
                <li>
                    <?php echo $member['name']; ?> <?php if (zl_session_get('user_id') === $member['user_id']): ?>(<b>YOU</b>)<?php endif; ?>
                    <?php if ($role === 'instructor'): ?>
                        <span class="text-muted font-monospace">[<?php echo $member['user_id']; ?>]</span>
                    <?php endif; ?>
                    <?php if (($role === 'instructor') && ($member['user_id'] !== zl_session_get('user_id')) && $allow_course_management): ?>
                        <a class="mx-1" onclick="return confirm('Are you sure?')" href="<?php echo site_url('course/set_role').'?id='.urlencode($id).'&user_id='.urlencode($member['user_id']).'&role=participant'; ?>">Make participant</a>
                        <a class="mx-1 text-danger" onclick="return confirm('Are you sure?')" href="<?php echo site_url('course/remove_member').'?id='.urlencode($id).'&user_id='.urlencode($member['user_id']); ?>">Remove</a>
                    <?php endif; ?>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
    <h5>Participants</h5>
    <ol>
        <?php foreach ($members as $member): ?>
            <?php if (empty($member['instructor'])): ?>
                <li>
                    <?php echo $member['name']; ?> <?php if (zl_session_get('user_id') === $member['user_id']): ?>(<b>YOU</b>)<?php endif; ?>
                    <?php if ($role === 'instructor'): ?>
                        <span class="text-muted font-monospace">[<?php echo $member['user_id']; ?>]</span>
                    <?php endif; ?>
                    <?php if (($role === 'instructor') && ($member['user_id'] !== zl_session_get('user_id')) && $allow_course_management): ?>
                        <a class="mx-1" onclick="return confirm('Are you sure?')" href="<?php echo site_url('course/set_role').'?id='.urlencode($id).'&user_id='.urlencode($member['user_id']).'&role=instructor'; ?>">Make instructor</a>
                        <a class="mx-1 text-danger" onclick="return confirm('Are you sure?')" href="<?php echo site_url('course/remove_member').'?id='.urlencode($id).'&user_id='.urlencode($member['user_id']); ?>">Remove</a>
                    <?php endif; ?>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</div>
