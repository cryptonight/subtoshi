<html>
<head><title></title></head>
<body>

<?php

session_start();

if(isset($_GET['logout'])){
    session_destroy();
    session_start();
}

if(isset($_POST['username']) && isset($_POST['password'])){
    $username = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['username']);
    $password = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['password']);
    if($username === "pwstegman" && hash("sha256",$password) === "***REMOVED***"){
        $_SESSION['admin_logged_in'] = "1";
    }
}

if(!isset($_SESSION['admin_logged_in'])){
?>

Please login to continue
<form action="index" method="post">
<input type="text" name="username">
<input type="password" name="password">
<input type="submit">
</form>

<?php
}else{
?>

<h1>Admin dash</h1>

<h3>Stats</h3>

<p><?php echo getStats(); ?></p>

<a href="index?logout">Logout</a>

<?php
}

function getStats(){
    $stats = file_get_contents("http://107.23.128.4/stats.php");
    $stats = json_decode($stats,true);
    $r = "";
    foreach ($stats as $key => $value) {
        $r = $r . "<p><b>".strtoupper($key)."</b></p>";
        $r = $r . "<p> - Cold: ".bcdiv($value['cold'],"100000000",8)."</p>";
        $r = $r . "<p> - Hot: ". bcdiv($value['hot'],"100000000",8) . "</p>";
        $r = $r . "<p> - Total: ". bcdiv($value['total'],"100000000",8) . "</p>";
    }
    return $r;
}

?>
</body>
</html>