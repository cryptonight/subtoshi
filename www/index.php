<?php include("includes/header.php"); ?>

<div class="box box-main">

<div class="page-header">
  <h2>Welcome to Subtoshi <small>We take you further (than 8 decimal places)</small></h2>
</div>

<div class="row" style="text-align:center;">
<?php if(!isset($_SESSION['user_id'])){ ?>
<h3>Get started in seconds</h3>
<a href="/auth/register" class="btn btn-primary"><i class="fa fa-users"></i> Sign up</a>
<a href="/auth/login" class="btn btn-success"><i class="fa fa-sign-in"></i> Login</a>
<?php }else{ ?>
<h3>Welcome, <?php echo $_SESSION['user_name']; ?></h3>
<?php } ?>
</div>

<div class="row" style="margin-top:25px;">
<h3 style="text-align:center;"><i class="fa fa-signal"></i> Today's Market Data (satoshi)</h3>
<table class="table table-hover" style="border-top: 1px solid #DDD; max-width:540px; margin-left:auto; margin-right:auto;">
<thead>
<tr>
<th>Market</th>
<th>Currency</th>
<th>Open</th>
<th>High</th>
<th>Low</th>
<th>Last Trade</th>
</tr>
</thead>
<tbody>
<?php
for($i=0;$i<count($coins);$i++){
    echo "<tr><td><a href='market?coin=".$coins[$i]['ticker']."'>BTC/".strtoupper($coins[$i]['ticker'])."</a></td><td>".$coins[$i]['name']."</td><td id='".$coins[$i]['ticker']."-open'></td><td id='".$coins[$i]['ticker']."-high'></td><td id='".$coins[$i]['ticker']."-low'></td><td id='".$coins[$i]['ticker']."-last'></td></tr>";
}
?>
</tbody>
</table>
</div>

<hr>

<div class="row" style="text-align:center;">
<div class="col-md-4">
<h1><i class="fa fa-globe"></i></h1>
<h3>USA Based</h3>
<p>We are based in the United States, and follow all the laws and regulations that apply to currency exchanges.</p>
</div>
<div class="col-md-4">
<h1><i class="fa fa-rocket"></i></h1>
<h3>11 Decimal Places</h3>
<p>Unlike other exchanges, we use a Satoshi as our base unit, and allow 3 decimals past a satoshi in price!</p>
</div>
<div class="col-md-4">
<h1><i class="fa fa-shield"></i></h1>
<h3>Secure</h3>
<p>A majority of the funds held at the exchange are stored in cold wallets.</p>
</div>
</div>

</div>

<script>

var coins = <?php echo json_encode($coins); ?>;
  
for(var i=0;i<coins.length;i++){
    var coin = coins[i]["ticker"];
    $.post( "api/api", { method: "getDailyStats", coin: coins[i]["ticker"] }, function( data ) {
        if(!data.result.open){
            $("#"+data.ticker+"-open").html("N/A");
            $("#"+data.ticker+"-high").html("N/A");
            $("#"+data.ticker+"-low").html("N/A");
            $("#"+data.ticker+"-last").html("N/A");
        }else{
            $("#"+data.ticker+"-open").html(xpnd(math.eval(data.result.open+"/1000")));
            $("#"+data.ticker+"-high").html(xpnd(math.eval(data.result.high+"/1000")));
            $("#"+data.ticker+"-low").html(xpnd(math.eval(data.result.low+"/1000")));
            $("#"+data.ticker+"-last").html(xpnd(math.eval(data.result.last+"/1000")));
        }
    }, "json");
}

</script>

<?php include("includes/footer.php"); ?>