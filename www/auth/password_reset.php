<?php

include($_SERVER['DOCUMENT_ROOT']."/includes/header.php");

?>
<div class="box box-main">    
    <div style="max-width:500px; margin-left:auto; margin-right:auto;">
<?php
require_once('config/config.php');
require_once('translations/en.php');
require_once('libraries/PHPMailer.php');
require_once('classes/Login.php');
$login = new Login();
if ($login->passwordResetWasSuccessful() == true && $login->passwordResetLinkIsValid() != true) {
    header("Location: /index");
} 

if ($login->passwordResetLinkIsValid() == true) {
?>

<form method="post" action="password_reset" name="new_password_form">
    <input type='hidden' name='user_name' value='<?php echo $_GET['user_name']; ?>' />
    <input type='hidden' name='user_password_reset_hash' value='<?php echo $_GET['verification_code']; ?>' />

    <label for="user_password_new"><?php echo WORDING_NEW_PASSWORD; ?></label>
    <input id="user_password_new" type="password" name="user_password_new" pattern=".{6,}" required autocomplete="off" />

    <label for="user_password_repeat"><?php echo WORDING_NEW_PASSWORD_REPEAT; ?></label>
    <input id="user_password_repeat" type="password" name="user_password_repeat" pattern=".{6,}" required autocomplete="off" />
    <input type="submit" name="submit_new_password" value="<?php echo WORDING_SUBMIT_NEW_PASSWORD; ?>" />
</form>

<!-- no data from a password-reset-mail has been provided, so we simply show the request-a-password-reset form -->
<?php } else { ?>

<!--
<form method="post" action="password_reset" name="password_reset_form">
    <label for="user_email"><?php echo WORDING_REQUEST_PASSWORD_RESET; ?></label>
    <input id="user_email" type="text" name="user_email" required />
    <input type="submit" name="request_password_reset" value="<?php echo WORDING_RESET_PASSWORD; ?>" />
</form>
-->

<div class="panel panel-default" >
        <div class="panel-heading">
            <div class="panel-title">Forgot Password</div>
            <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="/auth/login">Back to login</a></div>
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
            
            <form class="form-horizontal" role="form" method="post" action="password_reset" name="password_reset_form">
                <div style="margin-bottom: 25px" class="input-group">
                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                    <input id="user_email" type="text" name="user_email" class="form-control" value="" placeholder="email or username" required>                                        
                </div>


                <div style="margin-top:15px" class="form-group">
                    <div class="col-sm-12 controls">
                        <input type="submit" name="request_password_reset" class="btn btn-success" value="Request password reset"/>
                    </div>
                </div>
            </form>     
        </div>                     
    </div>  



<?php } ?>

</div>
</div>

<?php
include($_SERVER['DOCUMENT_ROOT']."/includes/footer.php");