<?php

include($_SERVER['DOCUMENT_ROOT']."/includes/header.php");

require_once('config/config.php');
require_once('translations/en.php');
require_once('libraries/PHPMailer.php');
require_once('classes/Registration.php');
$registration = new Registration();
require_once('classes/Login.php');
$login = new Login();
if ($login->isUserLoggedIn() == true) {
    header("Location: /index");
} else {
    
?>

<div class="box box-main">    
    <div style="max-width:500px; margin-left:auto; margin-right:auto;">
    <div class="panel panel-default" >
        <div class="panel-heading">
            <div class="panel-title">Resend Account Activation Email</div>
            <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="/auth/login">Sign In</a></div>
        </div>     

        <div style="padding-top:30px" class="panel-body" >
            
            <?php
            // show potential errors / feedback (from login object)
            if (isset($login)) {
                if ($login->errors) {
                    foreach ($login->errors as $error) {
                        ?>
                        <div class="alert alert-danger alert-dismissible" role="alert">
                          <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                          <?php echo $error; ?>
                        </div>
                        <?php
                    }
                }
                if ($login->messages) {
                    foreach ($login->messages as $message) {
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
            
            <form class="form-horizontal" role="form" method="post" action="resend" name="resendform">
                <div style="margin-bottom: 25px" class="input-group">
                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                    <input id="user_email" type="text" class="form-control" name="user_email" value="" placeholder="Email" required>                                        
                </div>
                
                <ul class="list-group">
                    <li class="list-group-item" style="padding:10px;">
                
                        <div class="form-group" style="margin-bottom:0;">
                            <div class="col-md-12" style="">
                                <img src="tools/showCaptcha" alt="captcha" />
                            </div>
                            <div class="col-md-12">
                                <input style="margin-top:10px;" type="text" id="captcha" name="captcha" class="form-control" placeholder="Please enter the above characters" required />
                            </div>
                        </div>
                        
                    </li>
                </ul>

                <div style="margin-top:15px" class="form-group">
                    <div class="col-sm-12 controls">
                        <input type="submit" name="resend" class="btn btn-success" value="Resend"/>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12 control">
                        <div style="border-top: 1px solid#888; padding-top:15px; font-size:85%" >
                            Don't have an account! <a href="/auth/register">Sign Up Here</a>
                        </div>
                    </div>
                </div>    
            </form>     
        </div>                     
    </div>  
    </div>

</div>

<?php  
}

include($_SERVER['DOCUMENT_ROOT']."/includes/footer.php");