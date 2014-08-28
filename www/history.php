<?php include("includes/header.php"); ?>

<div class="box box-main">

<div class="page-header" style="border-bottom:1px solid #ddd;">
  <h4>Transaction History</h4>
</div>

<div style="width:100%; overflow:auto;">
<table class="table table-bordered">
  <thead>
    <tr><th>Market</th><th>Type</th><th>Date (UTC)</th><th>Price</th><th>Amount</th><th>0.5% Fee</th><th>Total</th></tr>
  </thead>
  <tbody id="history">
  </tbody>
  </table>
  </div>

</div>

<script>

math.config({
  number: 'bignumber', // Default type of number: 'number' (default) or 'bignumber'
  precision: 64        // Number of significant digits for BigNumbers
});

$.post( "api/api", { method: "getTransactionHistory" }, function( data ) {
    var rows = "";
    for(var i=0;i<data.result.length;i++){
        var price = math.divide(math.bignumber(data.result[i].price),math.bignumber("1000"))+"";
        var amount = math.divide(math.bignumber(data.result[i].amount),math.bignumber("100000000"))+"";
        var total = math.divide(math.bignumber(math.multiply(math.bignumber(price),math.bignumber(amount))+""),math.bignumber("100000000"))+"";
        var fee = "";
        var fee_coin = "";
        if(data.result[i].type == "sell"){
            fee = math.multiply(math.bignumber(total),math.bignumber("0.005"))+"";
            fee_coin = "BTC";
            total = math.multiply(math.bignumber(total),math.bignumber("0.995"))+"";
        }else{
            fee = math.multiply(math.bignumber(amount),math.bignumber("0.005"))+"";
            fee_coin = data.result[i].coin.toUpperCase();
            total = math.multiply(math.bignumber(amount),math.bignumber("0.995"))+"";
        }
        rows = "<tr><td>BTC/"+data.result[i].coin.toUpperCase()+"</td><td>"+data.result[i].type+"</td><td>"+data.result[i].creation_time+"</td><td>"+price+" Sat</td><td>"+amount+" "+data.result[i].coin.toUpperCase()+"</td><td>"+format8(fee)+" "+fee_coin+"</td><td>"+format8(total)+" "+fee_coin+"</td></tr>"+rows;
    }
    $("#history").html(rows);
}, "json");

</script>

<?php include("includes/footer.php"); ?>