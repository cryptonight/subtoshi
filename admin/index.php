<html>
<head>

<title>Admin dash</title>

<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
<style>
	html, body{
		background-color:#DDD;
		font-family: 'Open Sans', sans-serif;
	}
	.container {
		background-color:#FFF;
		text-align:center;
		width:300px;
		overflow:auto;
		padding:20px;
		border:1px solid #BBB;
		border-radius:5px;
		margin-left:auto;
		margin-right:auto;
		box-shadow: 0 8px 6px -6px black;
	}
</style>

<link rel="icon" type="image/png" href="speedometer.png">

</head>
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
    if($username === "pwstegman" && hash("sha256",$password) === "**REMOVED**"){
        $_SESSION['admin_logged_in'] = "1";
    }
}

if(!isset($_SESSION['admin_logged_in'])){
?>

<div class="container">

Please login to continue
<form action="index" method="post">
<input type="text" name="username">
<input type="password" name="password">
<input type="submit">
</form>

</div>

<?php
}else{
?>

<div class="container">

<h1>Admin dash</h1>
<hr/>

<?php

if(isset($_GET['available'])){
    echo "<a href='index'>Total balance</a>";
}else{
    echo "<a href='index?available'>Available balance</a>";
}

?>

<p><?php

echo getStats(isset($_GET['available']));

?></p>

<p><b>BTC</b></p>
<table align='center' style='width:250px; border:1px solid gray;'>
<?php
    $url = "https://blockchain.info/merchant/".urlencode('**REMOVED**')."/balance?password=".urlencode('**REMOVED**')."&api_code=".urlencode("**REMOVED**");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    curl_close($ch);
    $cold = bcdiv(json_decode($data,true)['balance'],'100000000',8);
    echo "<tr><td>Cold</td><td style='text-align:right;'>".$cold."</td></tr>";
    $url = "https://blockchain.info/merchant/".urlencode('**REMOVED**')."/balance?password=".urlencode('**REMOVED**')."&api_code=".urlencode("**REMOVED**");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    curl_close($ch);
    $hot = bcdiv(json_decode($data,true)['balance'],'100000000',8);
    echo "<tr><td>Hot</td><td style='text-align:right;'>".$hot."</td></tr>";
    $total = bcadd($hot,$cold,8);
    echo "<tr><td>Total</td><td style='text-align:right;'>".$total."</td></tr>";
    $refill = bcsub(bcdiv($total,"2",8),$hot,8);
    echo "<tr><td>Refill amount</td><td style='text-align:right;'>".$refill."</td></tr>";
    $hotp = bcmul(bcdiv($hot,$total,8),'100',2);
    $color = "green";
    $morecss = "";
    if(bccomp($hotp,"25") <= 0){
        $color = "red";
        $morecss = "font-weight:bold;";
    }
    echo "<tr><td>Hot %</td><td style='text-align:right; color:".$color.";".$morecss."'>".$hotp." %</td></tr>";
    
    
?>
</table>

<a href="index?logout">Logout</a>

</div>

<?php
}

function getStats($unlocked){
    $more = "";
    if($unlocked === true){
        $more = "?unlocked";
    }
    $stats = coinCurl("stats.php".$more);
    $r = "";
    foreach ($stats as $key => $value) {
        $r = $r . "<p><b>".strtoupper($key)."</b></p>";
        
        $total = bcdiv($value['total'],"100000000",8);
        $cold = bcdiv($value['cold'],"100000000",8);
        $hot = bcdiv($value['hot'],"100000000",8);
        $refill = bcdiv(bcsub(bcdiv($total,"2",8),$hot,8),"2",8);
        $hotp = bcmul(bcdiv($hot,$total,8),'100',2);
        $color = "green";
        $morecss = "";
        if(bccomp($hotp,"25") <= 0){
            $color = "red";
            $morecss = "font-weight:bold;";
        }
        
        $r = $r . "<table align='center' style='width:250px; border:1px solid gray;'>";
        $r = $r . "<tr><td>Cold</td><td style='text-align:right;'>".number_format($cold)."</td></tr>";
        $r = $r . "<tr><td>Hot</td><td style='text-align:right;'>".number_format($hot)."</td></tr>";
        $r = $r . "<tr><td>Total</td><td style='text-align:right;'>".number_format($total)."</td></tr>";
        $r = $r . "<tr><td>Refill amount</td><td style='text-align:right;'>".number_format($refill)."</td></tr>";
        $r = $r . "<tr><td>Hot %</td><td style='text-align:right; color:".$color.";".$morecss."'>".$hotp." %</td></tr>";
        $r = $r . "</table>";
        
    }
    return $r;
}

function coinCurl($url_p){
    $url = "http://**REMOVED**/".$url_p;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    curl_close($ch);
    return json_decode($data,true);
}

?>
</body>
</html>
