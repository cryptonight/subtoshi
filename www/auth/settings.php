<?php

include($_SERVER['DOCUMENT_ROOT']."/includes/header.php");

require_once('config/config.php');
require_once('translations/en.php');
require_once('libraries/PHPMailer.php');
require_once('classes/Login.php');
$login = new Login();
if ($login->isUserLoggedIn() == false) {
    header("Location: /index");
} else {
    
?>

<div class="box box-main">    
    <div style="max-width:500px; margin-left:auto; margin-right:auto;">
        <div class="panel panel-default" >
        <div class="panel-heading">
            <div class="panel-title">Settings</div>
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

<!-- edit form for username / this form uses HTML5 attributes, like "required" and type="email" -->
<form method="post" action="settings" name="user_edit_form_name" class="form-horizontal" role="form">
    <div style="margin-bottom: 25px" class="input-group">
        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
        <input id="user_name" type="text" name="user_name" pattern="[a-zA-Z0-9]{2,64}" class="form-control" placeholder="Change username (<?php echo WORDING_CURRENTLY; ?>: <?php echo $_SESSION['user_name']; ?>)" value="" required />
    </div>
    <div style="margin-top:15px" class="form-group">
        <div class="col-sm-12 controls">
            <input type="submit" class="btn btn-success" name="user_edit_submit_name" value="<?php echo WORDING_CHANGE_USERNAME; ?>" />
        </div>
    </div>
</form><hr/>



<!-- edit form for user's password / this form uses the HTML5 attribute "required" -->
<form method="post" action="settings" name="user_edit_form_password" class="form-horizontal" role="form">
    <div style="margin-bottom: 25px" class="input-group">
        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
        <input id="user_password_old" type="password" name="user_password_old" autocomplete="off" class="form-control" value="" placeholder="Old password"  />
    </div>
    
    <div style="margin-bottom: 25px" class="input-group">
        <span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
        <input id="user_password_new" type="password" name="user_password_new" autocomplete="off" class="form-control" value="" placeholder="New password"/>
    </div>
    
    <div style="margin-bottom: 25px" class="input-group">
        <span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
        <input id="user_password_repeat" type="password" name="user_password_repeat" autocomplete="off" class="form-control" value="" placeholder="Repeat new password"/>
    </div>
    
    <div style="margin-top:15px" class="form-group">
        <div class="col-sm-12 controls">
            <input type="submit" name="user_edit_submit_password" class="btn btn-success"value="<?php echo WORDING_CHANGE_PASSWORD; ?>" />
        </div>
    </div>
</form><hr/>

</div>
</div>
</div>
</div>

<?php  
}

include($_SERVER['DOCUMENT_ROOT']."/includes/footer.php");