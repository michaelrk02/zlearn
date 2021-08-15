<h3>Account Settings</h3>
<?php echo zl_status(); ?>
<?php echo form_open('settings', 'onsubmit="return confirm(\'Are you sure?\')"'); ?>
    <div class="my-3">
        <label class="form-label">User ID</label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars(zl_session_get('user_id')); ?>" disabled>
    </div>
    <div class="my-3">
        <label class="form-label">Full Name <span class="text-danger">*</span></label>
        <input <?php echo !$allow ? 'readonly' : ''; ?> type="text" class="form-control" name="name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($user['name']); ?>">
    </div>
    <div class="my-3">
        <label class="form-label">E-mail Address <span class="text-danger">*</span></label>
        <input <?php echo !$allow ? 'readonly' : ''; ?> type="text" class="form-control" name="email" placeholder="Enter your e-mail address" value="<?php echo htmlspecialchars($user['email']); ?>">
    </div>
    <div class="my-3">
        <label class="form-label">Password</label>
        <input <?php echo !$allow ? 'readonly' : ''; ?> type="password" class="form-control" name="password" placeholder="(unchanged)">
    </div>
    <div class="my-3">
        <label class="form-label">Password Confirmation</label>
        <input <?php echo !$allow ? 'readonly' : ''; ?> type="password" class="form-control" name="password_confirm" placeholder="Retype password (if changed)">
    </div>
    <div class="my-3">
        <button <?php echo !$allow ? 'disabled' : ''; ?> type="submit" class="btn btn-success" name="submit" value="1"><span class="fa fa-sync me-2"></span> Update</button>
    </div>
</form>

