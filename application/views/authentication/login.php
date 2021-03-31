<div class="container-lg my-5">
    <div class="card mx-auto shadow" style="max-width: 30rem">
        <div class="py-3 bg-primary text-light">
            <h3 class="card-title text-center">Login</h3>
        </div>
        <div class="card-body bg-light">
            <?php echo zl_status(); ?>
            <p>Login using your credentials below</p>
            <?php echo form_open('authentication/login'); ?>
                <div class="input-group my-3">
                    <span class="input-group-text"><span class="fa fa-user"></span></span>
                    <input type="text" class="form-control" name="user_id" placeholder="User ID" value="<?php echo set_value('user_id'); ?>">
                </div>
                <div class="input-group my-3">
                    <span class="input-group-text"><span class="fa fa-lock"></span></span>
                    <input type="password" class="form-control" name="password" placeholder="Password">
                </div>
                <div class="my-1">Don't have an account? <a href="<?php echo site_url('authentication/register'); ?>">Register</a></div>
                <div class="my-1">Forgot user ID or password? <a href="<?php echo site_url('authentication/help'); ?>">Click here</a></div>
                <div class="my-3">
                    <button type="submit" class="btn btn-success" name="submit" value="1">Login <span class="fa fa-sign-in-alt ms-2"></span></button>
                </div>
                <?php if (ZL_SSO_ENABLE): ?>
                    <p class="text-center">- OR -</p>
                    <div class="d-grid">
                        <a class="btn btn-danger btn-lg" href="<?php echo $sso_url; ?>">Login with <?php echo ZL_SSO_NAME; ?> <span class="fa fa-key ms-2"></span></a>
                    </div>
                    <div class="form-text">If you have a(n) <a target="_blank" href="<?php echo ZL_SSO_HOME; ?>"><?php echo ZL_SSO_NAME; ?></a> account, login using this method instead</div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
