<h1>Enroll a Course</h1>
<?php echo zl_status(); ?>
<?php echo form_open('course/enroll', 'onsubmit="return confirm(\'Are you sure?\')"'); ?>
    <div class="my-3">
        <label class="form-label">Course ID</label>
        <input type="text" class="form-control" name="course_id" placeholder="Enter course ID" value="<?php echo htmlspecialchars(set_value('course_id')); ?>">
        <div class="form-text">Enter the course ID provided by your instructor</div>
    </div>
    <div class="my-3">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" name="password" placeholder="Enter course password">
        <div class="form-text">Enter the course password provided by your instructor</div>
    </div>
    <div class="my-3">
        <button type="submit" class="btn btn-success" name="submit" value="1">Enroll <span class="fa fa-sign-in-alt ms-2"></span></button>
    </div>
</form>
