<?php

include($_SERVER['DOCUMENT_ROOT']."/includes/header.php");

require_once('config/config.php');
require_once('translations/en.php');
require_once('libraries/PHPMailer.php');
require_once('classes/Registration.php');
$registration = new Registration();
//if (!$registration->registration_successful && !$registration->verification_successful) { ?>

<div class="box box-main">    
    <div style="max-width:500px; margin-left:auto; margin-right:auto;">
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="panel-title">Sign Up</div>
        <div style="float:right; font-size: 85%; position: relative; top:-10px"><a id="signinlink" href="/auth/login">Sign In</a></div>
    </div>  
    <div class="panel-body" >
    
        <?php

        // show potential errors / feedback (from registration object)
        if (isset($registration)) {
            if ($registration->errors) {
                foreach ($registration->errors as $error) {
                    ?>
                    <div class="alert alert-danger alert-dismissible" role="alert">
                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                      <?php echo $error; ?>
                    </div>
                    <?php
                }
            }
            if ($registration->messages) {
                foreach ($registration->messages as $message) {
                    ?>
                    <div class="alert alert-info alert-dismissible" role="alert">
                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                      <?php echo $message; ?>
                    </div>
                    <?php
                }
            }
        }
        ?>
    
        <form id="registerform" class="form-horizontal" role="form" method="post" action="register" name="registerform">
            
            <div id="signupalert" style="display:none" class="alert alert-danger">
                <p>Error:</p>
                <span></span>
            </div>
              
            <div class="form-group">
                <label for="user_name" class="col-md-3 control-label">Username</label>
                <div class="col-md-9">
                    <input id="user_name" type="text" pattern="[a-zA-Z0-9]{2,64}" name="user_name" class="form-control" placeholder="Username" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="user_email" class="col-md-3 control-label">Email</label>
                <div class="col-md-9">
                    <input id="user_email" type="email" name="user_email" class="form-control" placeholder="Email" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="user_password_new" class="col-md-3 control-label">Password</label>
                <div class="col-md-9">
                    <input id="user_password_new" type="password" name="user_password_new" pattern=".{6,}" class="form-control" placeholder="Password (min. 6 characters)" required autocomplete="off">
                </div>
            </div>
            
            <div class="form-group">
                <label for="user_password_repeat" class="col-md-3 control-label">Password repeat</label>
                <div class="col-md-9">
                    <input id="user_password_repeat" type="password" name="user_password_repeat" pattern=".{6,}" class="form-control" placeholder="Password repeat" required autocomplete="off">
                </div>
            </div>
        
            <div class="form-group">
                <label for="captcha" class="col-md-3 control-label">Captcha</label>
                <div class="col-md-9" style="margin-top:-25px;">
                    <img src="tools/showCaptcha" alt="captcha" />
                </div>
                <div class="col-md-3"></div>
                <div class="col-md-9">
                    <input style="margin-top:15px;" type="text" id="captcha" name="captcha" class="form-control" placeholder="Please enter the above characters" required />
                </div>
            </div>
            
            <div class="form-group">
                <div class="col-md-3">
                </div>
                <div class="col-md-9">
                    <div class="input-group">
                        <div class="checkbox">
                            <label>
                                <input id="terms" name="terms" type="checkbox" required> I agree to the <a href="/terms" target="_blank">Terms and Conditions</a>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <!-- Button -->                                        
                <div class="col-md-offset-3 col-md-9">
                    <input type="submit" name="register" value="sign up" class="btn btn-primary">
                </div>
            </div>
        </form>
     </div>
</div>

</div>
</div>

<?php //} ?>

<?php
include($_SERVER['DOCUMENT_ROOT']."/includes/footer.php");