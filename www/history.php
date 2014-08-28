<?php include("includes/header.php"); ?>

<div class="box box-main">

<div class="page-header" style="border-bottom:1px solid #ddd;">
  <h4>Transaction History</h4>
</div>

<table class="table table-bordered">
  <thead>
    <tr><th>Market</th><th>Type</th><th>Date (UTC)</th><th>Price</th><th>Amount</th><th>0.5% Fee</th><th>Total</th></tr>
  </thead>
  <tbody id="history">
  </tbody>
  </table>

</div>

<script>

$.post( "api/api", { method: "getTransactionHistory" }, function( data ) {
    var rows = "";
    for(var i=0;i<data.result.length;i++){
        var price = math.eval(data.result[i].price+"/1000");
        var amount = math.eval(data.result[i].amount+"/100000000");
        var total = math.eval(price+"*"+amount+"/100000000");
        var fee = "";
        var fee_coin = "";
        if(data.result[i].type == "sell"){
            fee = math.eval(total+"*0.005");
            fee_coin = "BTC";
            total = math.eval(total+"*0.995");
        }else{
            fee = math.eval(amount+"*0.005");
            fee_coin = data.result[i].coin.toUpperCase();
            total = math.eval(amount+"*0.995");
        }
        rows = "<tr><td>BTC/"+data.result[i].coin.toUpperCase()+"</td><td>"+data.result[i].type+"</td><td>"+data.result[i].creation_time+"</td><td>"+price+" Sat</td><td>"+amount+" "+data.result[i].coin.toUpperCase()+"</td><td>"+format8(fee)+" "+fee_coin+"</td><td>"+format8(total)+" "+fee_coin+"</td></tr>"+rows;
    }
    $("#history").html(rows);
}, "json");

</script>

<?php include("includes/footer.php"); ?>