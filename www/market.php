<?php include("includes/header.php"); ?>


<div class="box box-main">

<div class="page-header">
  <h3>BTC/<?php echo strtoupper($_GET['coin']); ?> - <?php echo getname($_GET['coin']); ?> <small>Last trade: <span id="lasttrade"></span></small></h3>
</div>

<div class="row">
<div class="col-md-9">
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
            <h3 class="panel-title">Buy <?php echo strtoupper($_GET['coin']); ?><span style="float:right;">You have <span id="balance-btc"></span> BTC</span></h3>
          </div>
          <div class="panel-body">
            <form class="form-horizontal" role="form">
                <div class="form-group">
                  <label for="buyorder_price" class="col-sm-2 control-label" style="padding-right:0; text-align:left;">Price</label>
                  <div class="col-sm-10">
                    <div class="input-group" style="padding-right:5px; padding-left:5px; width:100%;">
                    <input type="text" class="form-control" id="buyorder_price" placeholder="Price" onkeyup="calcbuytotal();">
                    <span class="input-group-addon" style="width:4em;">Sat</span>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label for="buyorder_size" class="col-sm-2 control-label" style="padding-right:0; text-align:left;">Amount</label>
                  <div class="col-sm-10">
                  <div class="input-group" style="padding-right:5px; padding-left:5px; width:100%;">
                    <input type="text" class="form-control" id="buyorder_size" placeholder="Amount" onkeyup="calcbuytotal();">
                    <span class="input-group-addon" style="width:4em;"><?php echo strtoupper($_GET['coin']); ?></span>
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
                    <span class="input-group-addon" style="width:4em;">BTC</span>
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
            <h3 class="panel-title">Sell <?php echo strtoupper($_GET['coin']); ?><span style="float:right;">You have <span id="balance-coin"></span> <?php echo strtoupper($_GET['coin']); ?></span></h3>
          </div>
          <div class="panel-body">
              <form class="form-horizontal" role="form">
                <div class="form-group">
                  <label for="sellorder_price" class="col-sm-2 control-label" style="padding-right:0; text-align:left;">Price</label>
                  <div class="col-sm-10">
                    <div class="input-group" style="padding-right:5px; padding-left:5px; width:100%;">
                    <input type="text" class="form-control" id="sellorder_price" placeholder="Price" onkeyup="calcselltotal();">
                    <span class="input-group-addon" style="width:4em;">Sat</span>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label for="sellorder_size" class="col-sm-2 control-label" style="padding-right:0; text-align:left;">Amount</label>
                  <div class="col-sm-10">
                  <div class="input-group" style="padding-right:5px; padding-left:5px; width:100%;">
                    <input type="text" class="form-control" id="sellorder_size" placeholder="Amount" onkeyup="calcselltotal();">
                    <span class="input-group-addon" style="width:4em;"><?php echo strtoupper($_GET['coin']); ?></span>
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
                    <span class="input-group-addon" style="width:4em;">BTC</span>
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
            <h3 class="panel-title">Sell Orders<span style="float:right;">Total: <span id="sellvolume"></span> <?php echo strtoupper($_GET['coin']); ?></span></h3>
          </div>
          <div class="panel-body" style="max-height:300px; overflow:auto; padding:10px;">
          
            <table class="table table-condensed">
            <thead>
            <tr><th>Price</th><th><?php echo strtoupper($_GET['coin']); ?></th><th>BTC</th></tr>
            </thead>
            <tbody id="sellorders">
            </tbody>
            </table>
          
          </div>
    </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Buy Orders<span style="float:right;">Total: <span id="buyvolume"></span> BTC</span></h3>
          </div>
          <div class="panel-body" style="max-height:300px; overflow:auto; padding:10px;">
          
            <table class="table table-condensed">
            <thead>
            <tr><th>Price</th><th><?php echo strtoupper($_GET['coin']); ?></th><th>BTC</th></tr>
            </thead>
            <tbody id="buyorders">
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
            <tr><th>Date (UTC)</th><th>Price</th><th><?php echo strtoupper($_GET['coin']); ?></th><th>BTC</th><th>Filled (<?php echo strtoupper($_GET['coin']); ?>)</th><th>Filled (BTC)</th><th> </th></tr>
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
            <tr><th>Date (UTC)</th><th>Price</th><th><?php echo strtoupper($_GET['coin']); ?></th><th>BTC</th><th>Filled (<?php echo strtoupper($_GET['coin']); ?>)</th><th>Filled (BTC)</th><th> </th></tr>
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
            <tr><th>Date (UTC)</th><th>Price</th><th><?php echo strtoupper($_GET['coin']); ?></th><th>BTC</th></tr>
            </thead>
            <tbody id="markethistory">
            </tbody>
            </table>
          
          </div>
      </div>
    </div>
    </div>
    
</div>
<div class="col-md-3" style="border-left:1px solid #DDD;">
<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title"><i class="fa fa-bar-chart-o fa-fw"></i> Market Stats</h3>
  </div>
  <div class="panel-body">
    <div class="row">
    <div class="col-md-6">
    <b>Open: <span id="<?php echo $_GET['coin']; ?>-open"></span></b>
    </div>
    <div class="col-md-6">
    <b>High: <span id="<?php echo $_GET['coin']; ?>-high"></span></b>
    </div>
    </div>
    <div class="row">
    <div class="col-md-6">
    <b>Low: <span id="<?php echo $_GET['coin']; ?>-low"></span></b>
    </div>
    <div class="col-md-6">
    <b>Last: <span id="<?php echo $_GET['coin']; ?>-last"></span></b>
    </div>
    </div>
  </div>
  <ul class="list-group">
    <li class="list-group-item">
    <p><b>Total sells (<?php echo strtoupper($_GET['coin']); ?>): <span id="sellvolumestats"></span></b>
    <p><b>Total buys (BTC): <span id="buyvolumestats"></span></b></p>
    </li>
    <li class="list-group-item">
    <p><b><?php echo getname($_GET['coin']); ?>'s <a href="<?php echo gettalk($_GET['coin']); ?>" target="_newtab"><i class="fa fa-external-link"></i> Bitcointalk</a></b></p>
    </li>
    </ul>
</div>

</div>
</div>

</div>

<script src="/js/market.js"></script>

<script>
window.onload = function(){
		loadData();
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
          $("#"+data.ticker+"-open").html("N/A");
          $("#"+data.ticker+"-high").html("N/A");
          $("#"+data.ticker+"-low").html("N/A");
          $("#"+data.ticker+"-last").html("N/A");
          $("#lasttrade").html("N/A");
      }else{
          $("#"+data.ticker+"-open").html(xpnd(math.eval(data.result.open+"/1000")));
          $("#"+data.ticker+"-high").html(xpnd(math.eval(data.result.high+"/1000")));
          $("#"+data.ticker+"-low").html(xpnd(math.eval(data.result.low+"/1000")));
          $("#"+data.ticker+"-last").html(xpnd(math.eval(data.result.last+"/1000")));
          $("#lasttrade").html(xpnd(math.eval(data.result.last+"/1000")) + " Satoshi");
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
	  $.each(data.result, function( index, value ) {
	    var price = xpnd(math.eval(index+"/1000"));
	    var amount = xpnd(math.eval(value+"/100000000"));
	    var total = format8(math.eval(price+"*"+amount+"/100000000"));
      rows += "<tr><td>"+price+"</td><td>"+amount+"</td><td>"+total+"</td></tr>";
      volume = xpnd(math.eval(volume+"+"+amount));
      active_sells.push(price);
    });
    $("#sellorders").html(rows);
    $("#sellvolume").html(format8(volume));
    $("#sellvolumestats").html(format8(volume));
	}, "json" );
	$.post( "api/api",{ method: "getMarketOrders", coin: getUrlVars()['coin'], type: "buy"}, function( data ) {
	  var rows = "";
	  var volume = "0";
	  $.each(data.result, function( index, value ) {
	    var price = xpnd(math.eval(index+"/1000"));
	    var amount = xpnd(math.eval(value+"/100000000"));
	    var total = format8(math.eval(price+"*"+amount+"/100000000"));
      rows += "<tr><td>"+price+"</td><td>"+amount+"</td><td>"+total+"</td></tr>";
      var toadd = xpnd(math.eval(price+"*"+amount+"/100000000"));
      volume = xpnd(math.eval(volume+"+"+toadd));
      active_buys.push(price);
    });
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
  	    var amount = xpnd(math.eval(data.result[i]['amount']+"/100000000"));
  	    var filled = xpnd(math.eval(data.result[i]['filled']+"/100000000"));
  	    var total = xpnd(math.round(math.eval(price+"*"+amount+"/100000000"),8));
  	    var totalfilled = xpnd(math.round(math.eval(price+"*"+filled+"/100000000"),8));
  	    var theFunction = 'cancelOrder("'+data.result[i].id+'");return false;';
        var action = "<input type='button' value='cancel' class='btn btn-xs btn-danger' onclick='"+theFunction+"'>";
  	    rows += "<tr><td>"+data.result[i]['creation_time']+"</td><td>"+price+"</td><td>"+amount+"</td><td>"+total+"</td><td>"+filled+"</td><td>"+totalfilled+"</td><td>"+action+"</td></tr>"
      }
    }
	  $("#active_buy_orders").html(rows);
	}, "json" );
	$.post( "api/api",{ method: "getUserOrders", coin: getUrlVars()['coin'], type: "sell"}, function( data ) {
	  var rows = "";
	  for(var i=0;i<data.result.length;i++){
	    if(data.result[i]['amount'] != data.result[i]['filled'] && data.result[i].canceled != 1){
  	    var price = xpnd(math.eval(data.result[i]['price']+"/1000"));
  	    var amount = xpnd(math.eval(data.result[i]['amount']+"/100000000"));
  	    var filled = xpnd(math.eval(data.result[i]['filled']+"/100000000"));
  	    var total = xpnd(math.round(math.eval(price+"*"+amount+"/100000000"),8));
  	    var totalfilled = xpnd(math.round(math.eval(price+"*"+filled+"/100000000"),8));
  	    var theFunction = 'cancelOrder("'+data.result[i].id+'");return false;';
        var action = "<input type='button' value='cancel' class='btn btn-xs btn-danger' onclick='"+theFunction+"'>";
  	    rows += "<tr><td>"+data.result[i]['creation_time']+"</td><td>"+price+"</td><td>"+amount+"</td><td>"+total+"</td><td>"+filled+"</td><td>"+totalfilled+"</td><td>"+action+"</td></tr>"
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
	    var amount = xpnd(math.eval(data.result[i]["amount"]+"/100000000"));
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

</script>


<?php include("includes/footer.php"); ?>