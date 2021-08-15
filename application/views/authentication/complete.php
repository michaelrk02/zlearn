<div class="container-lg my-5">
    <div class="card mx-auto shadow" style="max-width: 30rem">
        <div class="py-3 bg-success text-light">
            <h3 class="card-title text-center">One Step Remaining</h3>
        </div>
        <div class="card-body bg-light">
            <?php echo zl_status(); ?>
            <p>Complete your profile by providing your full name and your e-mail address</p>
            <?php echo form_open('authentication/complete', 'onsubmit="return confirm(\'Are you sure?\')"'); ?>
                <div class="my-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="fa fa-pen"></span></span>
                        <input type="text" class="form-control" name="name" placeholder="Full name" value="<?php echo zl_session_get('tmp_name'); ?>" <?php echo ZL_SSO_FIXED ? 'readonly' : ''; ?>>
                    </div>
                    <div class="form-text">Enter your full name. For example: John Doe</div>
                </div>
                <div class="my-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="fa fa-envelope"></span></span>
                        <input type="text" class="form-control" name="email" placeholder="E-mail address" value="<?php echo zl_session_get('tmp_email'); ?>" <?php echo ZL_SSO_FIXED ? 'readonly' : ''; ?>>
                    </div>
                    <div class="form-text">Enter your e-mail address. For example: john.doe@gmail.com</div>
                </div>
                <div class="my-3">
                    <button type="submit" class="btn btn-success" name="submit" value="1">Submit <span class="fa fa-check ms-2"></span></button>
                </div>
            </form>
        </div>
    </div>
</div>
