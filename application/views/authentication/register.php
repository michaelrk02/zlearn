<div class="container-lg my-5">
    <div class="card mx-auto shadow" style="max-width: 30rem">
        <div class="py-3 bg-primary text-light">
            <h3 class="card-title text-center">Register</h3>
        </div>
        <div class="card-body bg-light">
            <?php echo zl_status(); ?>
            <p>Fill the registration form below</p>
            <?php echo form_open('authentication/register'); ?>
                <div class="my-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="fa fa-user"></span></span>
                        <input type="text" class="form-control" name="user_id" placeholder="User ID" value="<?php echo set_value('user_id'); ?>">
                    </div>
                    <div class="form-text">Contains alpha-numeric characters, underscores, or dashes with maximum 50 characters long. For example: johndoe1337</div>
                </div>
                <div class="my-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="fa fa-lock"></span></span>
                        <input type="password" class="form-control" name="password" placeholder="Password">
                    </div>
                    <div class="form-text">Set a password with 8 characters minimum and 72 characters maximum</div>
                </div>
                <div class="my-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="fa fa-lock"></span></span>
                        <input type="password" class="form-control" name="password_confirm" placeholder="Password confirmation">
                    </div>
                    <div class="form-text">Repeat the password you have entered before</div>
                </div>
                <div class="my-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="fa fa-pen"></span></span>
                        <input type="text" class="form-control" name="name" placeholder="Full name" value="<?php echo set_value('name'); ?>">
                    </div>
                    <div class="form-text">Enter your full name. For example: John Doe</div>
                </div>
                <div class="my-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="fa fa-envelope"></span></span>
                        <input type="text" class="form-control" name="email" placeholder="E-mail address" value="<?php echo set_value('email'); ?>">
                    </div>
                    <div class="form-text">Enter your e-mail address. For example: john.doe@gmail.com</div>
                </div>
                <div class="my-1">Already have an account? <a href="<?php echo site_url('authentication/login'); ?>">Login</a></div>
                <div class="my-3">
                    <button type="submit" class="btn btn-success" name="submit" value="1">Register <span class="fa fa-paper-plane ms-2"></span></button>
                </div>
            </form>
        </div>
    </div>
</div>
