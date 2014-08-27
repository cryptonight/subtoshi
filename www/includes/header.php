<?php
require_once($_SERVER['DOCUMENT_ROOT']."/config/config.php");
require_once($_SERVER['DOCUMENT_ROOT']."/api/_functions.php");
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Exchange</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="/bootstrap-3.2.0/css/bootstrap.css">
        <!-- Optional theme -->
        <link rel="stylesheet" href="/bootstrap-3.2.0/css/bootstrap-theme.css">
        <!-- Font Awesome -->
        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
        <!--custom css -->
        <link rel="stylesheet" href="/css/custom.css">
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <!-- bootbox -->
        <script src="/bootstrap-3.2.0/js/bootbox.min.js"></script>
        <!-- Precise math -->
        <script src="/js/math.min.js"></script>
        <script src="/js/BigInt.js"></script>
    </head>
    <body>
        <div class="wrapper">
        <div class="content">
        <div class="navbar navbar-inverse navbar-static-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="/">Subtoshi</a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bar-chart-o"></i> Markets</a>
                          <ul class="dropdown-menu" role="menu">
                            <?php
                            for($i=0;$i<count($coins);$i++){
                                echo '<li><a href="/market?coin='.$coins[$i]["ticker"].'">'.strtoupper($coins[$i]["ticker"]).' - '.$coins[$i]["name"].'</a></li>';
                            }
                            ?>
                          </ul>
                        </li>
                        <?php session_start(); if(isset($_SESSION['user_id']) && !isset($_GET['logout'])){ ?>
                        <li><a href="/balances"><i class="fa fa-btc"></i> Balances</a></li>
                        <li><a href="/auth/settings"><i class="fa fa-gears"></i> Settings</a></li>
                        <li><a href="/history"><i class="fa fa-book"></i> Transaction History</a></li>
                        <?php } ?>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <?php session_start(); if(isset($_SESSION['user_id']) && !isset($_GET['logout'])){ ?>
                        <p class="navbar-text">Welcome, <?php echo $_SESSION['user_name']; ?></p>
                        <li id="logout"><a href="/auth/login?logout"><i class="fa fa-sign-out"></i> Logout</a></li>
                        <?php }else{ ?>
                        <li id="signup"><a href="/auth/register"><i class="fa fa-users"></i> Sign up</a></li>
                        <li id="login"><a href="/auth/login"><i class="fa fa-sign-in"></i> Login</a></li>
                        <?php } ?>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </div>
        
        <div class="container">