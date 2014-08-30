<?php include("includes/header.php"); ?>


<div class="box box-main">

<div class="page-header">
  <h3>BTC/<?php echo strtoupper($_GET['coin']); ?> - <?php echo getname($_GET['coin']); ?> <small>Last: <span id="last"></span> - High: <span id="high"></span> - Low: <span id="low"></span></small></h3>
</div>

<div class="row">
<div class="col-md-12">
<div class="row">
<div class="col-md-12" id="alerts">
  <?php if(!isset($_SESSION['user_id'])){ ?>
  <div class="alert alert-danger" role="alert">
  <strong><i class="fa fa-exclamation-triangle fa-fw" style="font-size:1.1em;"></i></strong> Please <a href="/auth/login" class="alert-link">login</a> to place a buy or sell order.
  </div>
  <?php } ?>
</div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Buy <?php echo strtoupper($_GET['coin']); ?><span class="panel-title-right">You have <span id="balance-btc">0</span> BTC</span></h3>
          </div>
          <div class="panel-body">
            <form class="form-horizontal" role="form">
                <div class="form-group">
                  <label for="buyorder_price" class="col-sm-2 control-label price" style="padding-right:0; text-align:left;">Price</label>
                  <div class="col-sm-10">
                    <div class="input-group" style="padding-right:5px; padding-left:5px; width:100%;">
                    <input type="text" class="form-control" id="buyorder_price" placeholder="Price" onkeyup="calcbuytotal();">
                    <span class="input-group-addon" style="width:5em;">satoshi</span>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label for="buyorder_size" class="col-sm-2 control-label amountb" style="padding-right:0; text-align:left;">Amount</label>
                  <div class="col-sm-10">
                  <div class="input-group" style="padding-right:5px; padding-left:5px; width:100%;">
                    <input type="text" class="form-control" id="buyorder_size" placeholder="Amount" onkeyup="calcbuytotal();">
                    <span class="input-group-addon" style="width:5em;"><?php echo strtoupper($_GET['coin']); ?></span>
                  </div>
                  </div>
                </div>
              </form>
          </div>
          <ul class="list-group">
          <li class="list-group-item">
          <form class="form-horizontal" role="form">
          <div class="form-group">
                  <label for="buyorder_total" class="col-sm-2 control-label" style="padding-right:0; text-align:left;">Total</label>
                  <div class="col-sm-10">
                  <div class="input-group" style="padding-right:5px; padding-left:5px; width:100%;">
                    <input type="text" class="form-control" id="buyorder_total" placeholder="Amount" onkeyup="calcbuyamount();">
                    <span class="input-group-addon" style="width:5em;">BTC</span>
                  </div>
                  </div>
                </div>
          <div class="form-group">
                  <div class="col-sm-12">
                    <span style="float:right; padding-right:5px;">0.5% fee: <span id="buyorder_fee">0.00000000</span> <?php echo strtoupper($_GET['coin']); ?></span>
                  </div>
                </div>
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <input type="button" class="btn btn-default" id="buy_btn" style="float:right; margin-right:5px;" value="Buy <?php echo strtoupper($_GET['coin']); ?>" onclick="confirmBuy();">
            </div>
          </div>
          </form>
          </li>
          </ul>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Sell <?php echo strtoupper($_GET['coin']); ?><span class="panel-title-right">You have <span id="balance-coin">0</span> <?php echo strtoupper($_GET['coin']); ?></span></h3>
          </div>
          <div class="panel-body">
              <form class="form-horizontal" role="form">
                <div class="form-group">
                  <label for="sellorder_price" class="col-sm-2 control-label price" style="padding-right:0; text-align:left;">Price</label>
                  <div class="col-sm-10">
                    <div class="input-group" style="padding-right:5px; padding-left:5px; width:100%;">
                    <input type="text" class="form-control" id="sellorder_price" placeholder="Price" onkeyup="calcselltotal();">
                    <span class="input-group-addon" style="width:5em;">satoshi</span>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label for="sellorder_size" class="col-sm-2 control-label amounts" style="padding-right:0; text-align:left;">Amount</label>
                  <div class="col-sm-10">
                  <div class="input-group" style="padding-right:5px; padding-left:5px; width:100%;">
                    <input type="text" class="form-control" id="sellorder_size" placeholder="Amount" onkeyup="calcselltotal();">
                    <span class="input-group-addon" style="width:5em;"><?php echo strtoupper($_GET['coin']); ?></span>
                  </div>
                  </div>
                </div>
              </form>
          </div>
          <ul class="list-group">
          <li class="list-group-item">
          <form class="form-horizontal" role="form">
          <div class="form-group">
                  <label for="sellorder_total" class="col-sm-2 control-label" style="padding-right:0; text-align:left;">Total</label>
                  <div class="col-sm-10">
                  <div class="input-group" style="padding-right:5px; padding-left:5px; width:100%;">
                    <input type="text" class="form-control" id="sellorder_total" placeholder="Amount" onkeyup="calcsellamount();">
                    <span class="input-group-addon" style="width:5em;">BTC</span>
                  </div>
                  </div>
                </div>
          <div class="form-group">
                  <div class="col-sm-12">
                    <span style="float:right; padding-right:5px;">0.5% fee: <span id="sellorder_fee">0.00000000</span> BTC</span>
                  </div>
                </div>
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <input type="button" class="btn btn-default" id="sell_btn" style="float:right; margin-right:5px;" value="Sell <?php echo strtoupper($_GET['coin']); ?>" onclick="confirmSell();">
            </div>
          </div>
          </form>
          </li>
          </ul>
        </div>
    </div>
    </div>
    
    <div class="row">
    <div class="col-md-6">
        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Buy Orders<span class="panel-title-right">Total: <span id="buyvolume"></span> BTC</span></h3>
          </div>
          <div class="panel-body" style="max-height:300px; overflow:auto; padding:10px;">
          
            <table class="table table-condensed">
            <thead>
            <tr><th>Price (satoshi) <i class="fa fa-question-circle fa-fw help price"></i></th><th><?php echo strtoupper($_GET['coin']); ?> <i class="fa fa-question-circle fa-fw help coinb"></i></th><th>BTC <i class="fa fa-question-circle fa-fw help btc"></i></th></tr>
            </thead>
            <tbody id="buyorders">
            </tbody>
            </table>
          
          </div>
    </div>
    </div>
    <div class="col-md-6">
    <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Sell Orders<span class="panel-title-right">Total: <span id="sellvolume"></span> <?php echo strtoupper($_GET['coin']); ?></span></h3>
          </div>
          <div class="panel-body" style="max-height:300px; overflow:auto; padding:10px;">
          
            <table class="table table-condensed">
            <thead>
            <tr><th>Price (satoshi) <i class="fa fa-question-circle fa-fw help price"></i></th><th><?php echo strtoupper($_GET['coin']); ?> <i class="fa fa-question-circle fa-fw help coins"></i></th><th>BTC <i class="fa fa-question-circle fa-fw help btc"></i></th></tr>
            </thead>
            <tbody id="sellorders">
            </tbody>
            </table>
          
          </div>
    </div>
    </div>
    </div>
    <hr>
    <div class="row">
    <div class="col-md-12">
      <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Your Active Sell Orders</h3>
          </div>
          <div class="panel-body" style="max-height:300px; overflow:auto; padding:10px;">
            <table class="table table-condensed">
            <thead>
            <tr><th>Date (UTC)</th><th><span class="price">Price (satoshi)</span></th><th><span class="coins"><?php echo strtoupper($_GET['coin']); ?></span></th><th><span class="btc">BTC</span></th><th>Filled (<?php echo strtoupper($_GET['coin']); ?>)</th><th>Filled (BTC)</th><th> </th></tr>
            </thead>
            <tbody id="active_sell_orders">
            </tbody>
            </table>
          
          </div>
      </div>
    </div>
    </div>
    <hr>
    <div class="row">
    <div class="col-md-12">
      <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Your Active Buy Orders</h3>
          </div>
          <div class="panel-body" style="max-height:300px; overflow:auto; padding:10px;">
            <table class="table table-condensed">
            <thead>
            <tr><th>Date (UTC)</th><th><span class="price">Price (satoshi)</span></th><th><span class="coinb"><?php echo strtoupper($_GET['coin']); ?></span></th><th><span class="btc">BTC</span></th><th>Filled (<?php echo strtoupper($_GET['coin']); ?>)</th><th>Filled (BTC)</th><th> </th></tr>
            </thead>
            <tbody id="active_buy_orders">
            </tbody>
            </table>
          
          </div>
      </div>
    </div>
    </div>
    <hr>
    <div class="row">
    <div class="col-md-12">
      <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-history fa-fw"></i> Market History</h3>
          </div>
          <div class="panel-body" style="max-height:300px; overflow:auto; padding:10px;">
            <table class="table table-condensed">
            <thead>
            <tr><th>Date (UTC)</th><th><span class="price">Price (satoshi)</span></th><th><span class="coin"><?php echo strtoupper($_GET['coin']); ?></span></th><th><span class="btc">BTC</span></th></tr>
            </thead>
            <tbody id="markethistory">
            </tbody>
            </table>
          
          </div>
      </div>
    </div>
    </div>
    
</div>
</div>

</div>

<script src="/js/market.js"></script>

<script>
window.onload = function(){
		loadData();
		addTooltips();
		<?php if(!isset($_SESSION['user_id'])){ ?>
		disableForms();
		<?php } ?>
}

var active_sells = [];
var active_buys = [];
var balance_coin = "0";
var balance_btc = "0";

function loadData(){
  updateData();
	setTimeout(loadData, 30000);
}

function updateData(){
  loadActiveOrders();
	loadMarketOrders();
	loadMarketHistory();
	loadMarketStats();
	loadBalances();
}


function loadBalances(){
  $.post( "api/api", { method: "getBalance", coin: getUrlVars()['coin'] }, function( data ) {
    var balance = math.eval(data.result.active+"/100000000");
    balance = format8(balance);
    $("#balance-coin").html(balance);
    balance_coin = math.eval(data.result.active+"/100000000")+"";
  }, "json");
  $.post( "api/api", { method: "getBalance", coin: "btc" }, function( data ) {
    var balance = math.eval(data.result.active+"/10000000000000000000");
    balance = format8(balance);
    $("#balance-btc").html(balance);
    balance_btc = math.eval(data.result.active+"/10000000000000000000")+"";
  }, "json");
}

function loadMarketStats(){
  $.post( "api/api", { method: "getDailyStats", coin: getUrlVars()['coin'] }, function( data ) {
      if(!data.result.open){
          $("#open").html("N/A");
          $("#high").html("N/A");
          $("#low").html("N/A");
          $("#last").html("N/A");
      }else{
          $("#open").html(xpnd(math.eval(data.result.open+"/1000")));
          $("#high").html(xpnd(math.eval(data.result.high+"/1000")));
          $("#low").html(xpnd(math.eval(data.result.low+"/1000")));
          $("#last").html(xpnd(math.eval(data.result.last+"/1000")));
      }
  }, "json");
}

function disableForms(){
  $("#buyorder_price").prop('disabled', true);
  $("#buyorder_size").prop('disabled', true);
  $("#buyorder_total").prop('disabled', true);
  $("#buy_btn").prop('disabled', true);
  $("#sellorder_price").prop('disabled', true);
  $("#sellorder_size").prop('disabled', true);
  $("#sellorder_total").prop('disabled', true);
  $("#sell_btn").prop('disabled', true);
}

function loadMarketOrders(){
  $.post( "api/api",{ method: "getMarketOrders", coin: getUrlVars()['coin'], type: "sell"}, function( data ) {
	  var rows = "";
	  var volume = "0";
	  for(var i=0;i<data.result.length;i++){
	    var price = xpnd(math.eval(data.result[i].price+"/1000"));
	    var amount = format8(math.eval(data.result[i].amount+"/100000000"));
	    var total = format8(math.eval(price+"*"+amount+"/100000000"));
      var row = "<tr><td>"+price+"</td><td>"+amount+"</td><td>"+total+"</td></tr>";
      rows = row+rows;
      volume = xpnd(math.eval(volume+"+"+amount));
    }
    $("#sellorders").html(rows);
    $("#sellvolume").html(format8(volume));
    $("#sellvolumestats").html(format8(volume));
	}, "json" );
	$.post( "api/api",{ method: "getMarketOrders", coin: getUrlVars()['coin'], type: "buy"}, function( data ) {
	  var rows = "";
	  var volume = "0";
	  for(var i=0;i<data.result.length;i++){
	    var price = xpnd(math.eval(data.result[i].price+"/1000"));
	    var amount = format8(math.eval(data.result[i].amount+"/100000000"));
	    var total = format8(math.eval(price+"*"+amount+"/100000000"));
      rows += "<tr><td>"+price+"</td><td>"+amount+"</td><td>"+total+"</td></tr>";
      var toadd = xpnd(math.eval(price+"*"+amount+"/100000000"));
      volume = xpnd(math.eval(volume+"+"+toadd));
    }
    $("#buyorders").html(rows);
    $("#buyvolume").html(format8(volume));
    $("#buyvolumestats").html(format8(volume));
	}, "json" );
}

function loadActiveOrders(){
  $.post( "api/api",{ method: "getUserOrders", coin: getUrlVars()['coin'], type: "buy"}, function( data ) {
	  var rows = "";
	  for(var i=0;i<data.result.length;i++){
	    if(data.result[i]['amount'] != data.result[i]['filled'] && data.result[i].canceled != 1){
  	    var price = xpnd(math.eval(data.result[i]['price']+"/1000"));
  	    var amount = format8(math.eval(data.result[i]['amount']+"/100000000"));
  	    var filled = format8(math.eval(data.result[i]['filled']+"/100000000"));
  	    var total = format8(math.round(math.eval(price+"*"+amount+"/100000000"),8));
  	    var totalfilled = format8(math.round(math.eval(price+"*"+filled+"/100000000"),8));
  	    var theFunction = 'cancelOrder("'+data.result[i].id+'");return false;';
        var action = "<input type='button' value='cancel' class='btn btn-xs btn-danger' onclick='"+theFunction+"'>";
  	    rows += "<tr><td>"+data.result[i]['creation_time']+"</td><td>"+price+"</td><td>"+amount+"</td><td>"+total+"</td><td>"+filled+"</td><td>"+totalfilled+"</td><td>"+action+"</td></tr>"
        active_buys.push(price);
	    }
    }
	  $("#active_buy_orders").html(rows);
	}, "json" );
	$.post( "api/api",{ method: "getUserOrders", coin: getUrlVars()['coin'], type: "sell"}, function( data ) {
	  var rows = "";
	  for(var i=0;i<data.result.length;i++){
	    if(data.result[i]['amount'] != data.result[i]['filled'] && data.result[i].canceled != 1){
  	    var price = xpnd(math.eval(data.result[i]['price']+"/1000"));
  	    var amount = format8(math.eval(data.result[i]['amount']+"/100000000"));
  	    var filled = format8(math.eval(data.result[i]['filled']+"/100000000"));
  	    var total = format8(math.round(math.eval(price+"*"+amount+"/100000000"),8));
  	    var totalfilled = format8(math.round(math.eval(price+"*"+filled+"/100000000"),8));
  	    var theFunction = 'cancelOrder("'+data.result[i].id+'");return false;';
        var action = "<input type='button' value='cancel' class='btn btn-xs btn-danger' onclick='"+theFunction+"'>";
  	    rows += "<tr><td>"+data.result[i]['creation_time']+"</td><td>"+price+"</td><td>"+amount+"</td><td>"+total+"</td><td>"+filled+"</td><td>"+totalfilled+"</td><td>"+action+"</td></tr>"
  	    active_sells.push(price);
	    }
	  }
	  $("#active_sell_orders").html(rows);
	}, "json" );
}

function loadMarketHistory(){
  $.post( "api/api",{ method: "getMarketHistory", coin: getUrlVars()['coin']}, function( data ) {
	  var rows = "";
	  for(var i=0;i<data.result.length;i++){
	    var date = data.result[i]["creation_time"];
	    var price = xpnd(math.eval(data.result[i]["price"]+"/1000"));
	    var amount = format8(math.eval(data.result[i]["amount"]+"/100000000"));
	    var total = format8(math.eval(price+"*"+amount+"/100000000"));
	    rows += "<tr><td>"+date+"</td><td>"+price+"</td><td>"+amount+"</td><td>"+total+"</td></tr>";
	  }
	  $("#markethistory").html(rows);
	}, "json" );
}

function cancelOrder(order_id){
bootbox.dialog({
  message: "<p>Are you sure you want to cancel this order?</p>",
  buttons: {
    no: {
      label: "No",
      className: "btn-default",
    },
    yes: {
      label: "Yes",
      className: "btn-primary",
      callback: function() {
        $.post( "/api/api",{ method: "cancelOrder", id: order_id}, function( data ) {
          updateData();
        }, "json" );
      }
    }
  }
});
}

//Tooltips
function addTooltips(){
  $( ".price" ).each(function( index ) {
    addTooltip(this,"The price per coin in satoshi");
  });
  $( ".amount" ).each(function( index ) {
    addTooltip(this,"The amount you want to exchange");
  });
  $( ".amountb" ).each(function( index ) {
    addTooltip(this,"The amount you want to buy");
  });
  $( ".amounts" ).each(function( index ) {
    addTooltip(this,"The amount you want to sell");
  });
  $( ".coin" ).each(function( index ) {
    addTooltip(this,"The amount of <?php echo getname($_GET['coin']); ?> exchanged");
  });
  $( ".coinb" ).each(function( index ) {
    addTooltip(this,"The amount of <?php echo getname($_GET['coin']); ?> being bought");
  });
  $( ".coins" ).each(function( index ) {
    addTooltip(this,"The amount of <?php echo getname($_GET['coin']); ?> being sold");
  });
  $( ".btc" ).each(function( index ) {
    addTooltip(this,"The total in BTC");
  });
  $('[data-toggle="tooltip"]').tooltip({'placement': 'top'});
}

function addTooltip(elm,text){
  $(elm).attr("data-toggle","tooltip");
  $(elm).attr("data-placement","top");
  $(elm).attr("title",text);
}

</script>


<?php include("includes/footer.php"); ?>