<?php include("includes/header.php"); ?>

<div class="box box-main">

<div class="page-header" style="border-bottom:1px solid #ddd;">
  <h4>Balances</h4>
</div>

<div class="row">
  <div class="col-md-4">
    <p><b>[BTC] Bitcoin <span id="BTC-active"></span></b></p>
    <p><b>On orders: <span id="BTC-hold"></span></b></p>
    <p><b>Pending deposit: <span id="BTC-pending"></span></b></p>
  </div>
  <div class="col-md-8">
    <form role="form">
      <div class="row">
        <div class="col-md-2" style="padding-top:0.2em; text-align:right;">
          Amount:
        </div>
        <div class="col-md-3" style="padding-left:0;">
          <input type="text" class="form-control" style="height:2em;" placeholder="Amount" id="btc-withdrawal-amount" onkeydown="setTotal('btc','0.0002');" onkeyup="setTotal('btc','0.0002');">
        </div>
        <div class="col-md-4" style="padding-top:0.2em;">
          <span>Transaction fee: 0.0002 BTC
        </div>
        <div class="col-md-3" style="padding-top:0.2em;">
          Total: <span id="btc-withdrawal-total"></span>
        </div>
      </div>
      <div class="row" style="padding-top:5px;">
        <div class="col-md-2" style="padding-top:0.2em; text-align:right;">
          Address:
        </div>
        <div class="col-md-9" style="padding-left:0;">
          <input type="text" class="form-control" style="height:2em;" placeholder="Address" id="btc-withdrawal-address">
        </div>
      </div>
      <div class="row">
        <div class="col-md-2">
        </div>
        <div class="col-md-10" style="padding-top:5px; padding-left:0;">
          <input type="button" class="btn btn-default btn-sm" value="Withdraw" id="btc-withdrawal-button" onclick="requestWithdrawal('btc');">
        </div>
      </div>
    </form>
  </div>
</div>
<div class="row" style="padding-top:7px;">
  <div class="col-md-12">
    <p><b>Deposit Address: </b><span id="BTC-depositaddress"</p>
  </div>
</div>

<hr style="border-top:1px solid #ddd;">

<?php
for($i=0;$i<count($coins);$i++){
?>
<div class="row">
  <div class="col-md-4">
    <p><b>[<?php echo strtoupper($coins[$i]["ticker"]); ?>] <?php echo $coins[$i]["name"]; ?> <span id="<?php echo $coins[$i]["ticker"]; ?>-active"></span></b></p>
    <p><b>On orders: <span id="<?php echo $coins[$i]["ticker"]; ?>-hold"></span></b></p>
    <p><b>Pending deposit: <span id="<?php echo $coins[$i]["ticker"]; ?>-pending"></span></b></p>
  </div>
  <div class="col-md-8">
    <form role="form">
      <div class="row">
        <div class="col-md-2" style="padding-top:0.2em; text-align:right;">
          Amount:
        </div>
        <div class="col-md-3" style="padding-left:0;">
          <input type="text" class="form-control" style="height:2em;" placeholder="Amount" id="<?php echo $coins[$i]["ticker"]; ?>-withdrawal-amount" onkeydown="setTotal('<?php echo $coins[$i]["ticker"]; ?>','<?php echo $coins[$i]["fee"]; ?>');" onkeyup="setTotal('<?php echo $coins[$i]["ticker"]; ?>','<?php echo $coins[$i]["fee"]; ?>');">
        </div>
        <div class="col-md-4" style="padding-top:0.2em;">
          <span>Transaction fee: <?php echo strtoupper($coins[$i]["fee"]); ?> <?php echo strtoupper($coins[$i]["ticker"]); ?>
        </div>
        <div class="col-md-3" style="padding-top:0.2em;">
          Total: <span id="<?php echo $coins[$i]["ticker"]; ?>-withdrawal-total"></span>
        </div>
      </div>
      <div class="row" style="padding-top:5px;">
        <div class="col-md-2" style="padding-top:0.2em; text-align:right;">
          Address:
        </div>
        <div class="col-md-9" style="padding-left:0;">
          <input type="text" class="form-control" style="height:2em;" placeholder="Address" id="<?php echo $coins[$i]["ticker"]; ?>-withdrawal-address">
        </div>
      </div>
      <div class="row" style="padding-top:5px;">
        <div class="col-md-2" style="padding-top:0.2em; text-align:right;">
          Payment Id:
        </div>
        <div class="col-md-9" style="padding-left:0;">
          <input type="text" class="form-control" style="height:2em;" placeholder="Payment Id (optional)" id="<?php echo $coins[$i]["ticker"]; ?>-withdrawal-payment-id">
        </div>
      </div>
      <div class="row">
        <div class="col-md-2">
        </div>
        <div class="col-md-10" style="padding-top:5px; padding-left:0;">
          <input type="button" class="btn btn-default btn-sm" value="Withdraw" id="<?php echo $coins[$i]["ticker"]; ?>-withdrawal-button" onclick="requestWithdrawal('<?php echo $coins[$i]["ticker"]; ?>');">
        </div>
      </div>
    </form>
  </div>
</div>
<div class="row" style="padding-top:7px;">
  <div class="col-md-12">
    <p><b>Deposit Address: </b><?php echo $coins[$i]["depositAddress"]; ?></p>
    <p><b>Payment id: </b><span id="<?php echo $coins[$i]["ticker"]; ?>-paymentid"></span></p>
  </div>
</div>

<hr style="border-top:1px solid #ddd;">

<?php
}
?>

<div class="page-header" style="border-bottom:1px solid #ddd;">
  <h4>Deposit History</h4>
</div>

<div style="width:100%; overflow:auto;">
<table class="table table-bordered">
  <thead>
    <tr><th>Currency</th><th>Amount</th><th>Date (UTC)</th><th>Status</th></tr>
  </thead>
  <tbody id="depositHistory">
  </tbody>
  </table>
  </div>
  
<hr>

<div class="page-header" style="border-bottom:1px solid #ddd;">
  <h4>Withdrawal History</h4>
</div>

<div style="width:100%; overflow:auto;">
<table class="table table-bordered">
  <thead>
    <tr><th>Currency</th><th>Amount</th><th>Requested on (UTC)</th><th>Verified on (UTC)</th><th>Status</th></tr>
  </thead>
  <tbody id="withdrawalHistory">
  </tbody>
  </table>
  </div>

</div>

<script>
math.config({
  number: 'bignumber',
  precision: 64
});
</script>

<script>

window.onload = function(){
	updateData();	
}

function updateData(){
  loadBalances();
  loadDepositHistory();
	loadWithdrawalHistory();
}

function requestWithdrawal(ticker){

  var adrs = $("#"+ticker+"-withdrawal-address").val();
  var amt = $("#"+ticker+"-withdrawal-amount").val();
  var pid = $("#"+ticker+"-withdrawal-payment-id").val();
  
  $("#"+ticker+"-withdrawal-address").val("");
  $("#"+ticker+"-withdrawal-amount").val("");
  $("#"+ticker+"-withdrawal-payment-id").val("");

  $('#'+ticker+"-withdrawal-button").prop('disabled', true);

  $.post( "api/api", { method: "requestWithdrawal", coin: ticker, address: adrs, amount: amt, payment_id: pid }, function( data ) {
    
    $('#'+ticker+"-withdrawal-button").prop('disabled', false);
    
    bootbox.alert(data.result);
    
    updateData();
    
  }, "json");
}

function loadBalances(){
  
  //Bitcoin
  $.post( "api/api", { method: "getBalance", coin: "btc" }, function( data ) {
      $("#BTC-active").html(format8(math.eval(data.result.active+"/10000000000000000000")+""));
      $("#BTC-hold").html(format8(math.eval(data.result.hold+"/10000000000000000000")+""));
      $("#BTC-pending").html(format8(math.eval(data.result.pending+"/100000000")+""));
    }, "json");
    $.post( "api/api", { method: "getBitcoinAddresses"}, function( data ) {
      if(data.result.length == 0){
        $("#BTC-depositaddress").html("<input type='button' value='Generate Deposit Address' class='btn btn-default btn-xs' style='margin-left:5px;' onclick='generateBitcoinDepositAddress()' id='btc-generate-btn'>");
      }else{
        $("#BTC-depositaddress").html(data.result[data.result.length-1]);
      }
    }, "json");
  
  //Other coins
  
  var coins = <?php echo json_encode($coins); ?>;
  
  for(var i=0;i<coins.length;i++){
    $.post( "api/api", { method: "getBalance", coin: coins[i]["ticker"] }, function( data ) {
      $("#"+data.ticker+"-active").html(format8(math.eval(data.result.active+"/100000000")+""));
      $("#"+data.ticker+"-hold").html(format8(math.eval(data.result.hold+"/100000000")+""));
      $("#"+data.ticker+"-pending").html(format8(math.eval(data.result.pending+"/100000000")+""));
      console.log(data.result.ticker + " " + data.result.active);
    }, "json");
    $.post( "api/api", { method: "getPaymentIds", coin: coins[i]["ticker"] }, function( data ) {
      if(data.result.length == 0){
        $("#"+data.ticker+"-paymentid").html("<input type='button' value='Generate Payment id' class='btn btn-default btn-xs' style='margin-left:5px;' onclick='generatePaymentId(this)' id='"+data.ticker+"-generate-btn'>");
      }else{
        $("#"+data.ticker+"-paymentid").html(data.result[data.result.length-1]);
      }
    }, "json");
  }
}

function loadDepositHistory(){
  $.post( "api/api", { method: "getDepositHistory"}, function( data ) {
      var rows = "";
      for(var i=0;i<data.result.length;i++){
        var amount = format8(math.eval(data.result[i].amount+"/100000000"));
        var status = "";
        if(data.result[i].confirms >= 6){
          status = "Complete";
        }else{
          status = data.result[i].confirms+"/6 Confirmations";
        }
        rows += "<tr><td>"+data.result[i].coin.toUpperCase()+"</td><td>"+amount+" "+data.result[i].coin.toUpperCase()+"</td><td>"+data.result[i].time_stamp+"</td><td>"+status+"</td></tr>";
      }
      $("#depositHistory").html(rows);
    }, "json");
}

function loadWithdrawalHistory(){
  $.post( "api/api", { method: "getWithdrawalHistory"}, function( data ) {
      var rows = "";
      for(var i=0;i<data.result.length;i++){
        var amount = format8(math.eval(data.result[i].amount+"/100000000"));
        var status = "";
        var verification_time = data.result[i].verification_time;
        var theFunction = "";
        var action = "";
        if(data.result[i].verified == "1"){
          status = "Complete";
          theFunction = 'viewDetails("'+data.result[i].address+'","'+data.result[i].payment_id+'","'+data.result[i].tx_hash+'");return false;';
          action = "<input type='button' value='details' class='btn btn-xs btn-primary' onclick='"+theFunction+"' style='float:right;'>";
        }else if(data.result[i].verified == "0"){
          theFunction = 'cancelWithdrawal("'+data.result[i].hash+'");return false;';
          status = "Verification email sent";
          action = "<input type='button' value='cancel' class='btn btn-xs btn-danger' onclick='"+theFunction+"' style='float:right;'>";
          verification_time = "";
        }else if(data.result[i].verified == "4"){
          status = "Withdrawal canceled";
          verification_time = "";
          action = "<input type='button' value='details' class='btn btn-xs btn-default' disabled='disabled' style='float:right;'>";
        }else{
          status = "Withdrawal failed";
          action = "<input type='button' value='details' class='btn btn-xs btn-default' disabled='disabled' style='float:right;'>";
        }
        var row = "<tr><td>"+data.result[i].coin.toUpperCase()+"</td><td>"+amount+" "+data.result[i].coin.toUpperCase()+"</td><td>"+data.result[i].creation_time+"</td><td>"+verification_time+"</td><td>"+status+action+"</td></tr>";
        rows = row + rows;
      }
      $("#withdrawalHistory").html(rows);
    }, "json");
}

function generateBitcoinDepositAddress(elm){
  $.post( "api/api", { method: "generateBitcoinAddress" }, function( data ) {
    $("#BTC-depositaddress").html(data.result);
  }, "json");
}

function generatePaymentId(elm){
  var thecoin = elm.id.split("-")[0];
  $.post( "api/api", { method: "generatePaymentId", coin: thecoin }, function( data ) {
    $("#"+data.ticker+"-paymentid").html(data.result);
  }, "json");
}

function setTotal(ticker,fee){
  var total = math.eval($("#"+ticker+"-withdrawal-amount").val()+"-"+fee);
  console.log($("#"+ticker+"-withdrawal-amount").val()+"-"+fee);
  $("#"+ticker+"-withdrawal-total").html(total+" "+ticker.toUpperCase());
}

function cancelWithdrawal(w_hash){
bootbox.dialog({
  message: "<p>Are you sure you want to cancel this withdrawal?</p>",
  buttons: {
    no: {
      label: "No",
      className: "btn-default",
    },
    yes: {
      label: "Yes",
      className: "btn-primary",
      callback: function() {
        $.post( "/api/api",{ method: "cancelWithdrawal", hash: w_hash}, function( data ) {
          updateData();
          console.log(data);
        }, "json" );
      }
    }
  }
});
}

function viewDetails(address,payment_id,tx_hash){
  bootbox.alert("<div style='width:100%; overflow:auto;'><p><b>Transaction Details</b></p><p><b>Sent to: </b>"+address+"</p><p><b>Payment Id: </b>"+payment_id+"</p><p><b>Tx_hash: </b>"+tx_hash+"</p></div>");
}

</script>

<?php include("includes/footer.php"); ?>