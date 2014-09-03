math.config({
  number: 'bignumber',
  precision: 64
});

function confirmBuy(){

	var rate = $("#buyorder_price").val();
	var amount = $("#buyorder_size").val();

	var rateD = (rate.indexOf(".") == -1 || rate.length-rate.indexOf(".")-1 <= 3);
	var sizeD = (amount.indexOf(".") == -1 || amount.length-amount.indexOf(".")-1 <= 8);
	
	var total = xpnd(math.eval(rate+"/100000000*"+amount));

	if(isGood(rate) && isGood(amount) && rateD && sizeD && !isPlacedSell(rate) && enoughBuy(total)){
		var fee = xpnd(math.eval(amount+"*0.005")+"");
		bootbox.confirm("<p><b>Please confirm your buy order</b></p><p><b>Bid </b>"+rate+" satoshi<br/><b>Size </b>"+amount+" "+getUrlVars()['coin'].toUpperCase()+"<br/><b>0.5% Fee </b>"+fee+" "+getUrlVars()['coin'].toUpperCase()+"<br/><b>Total </b>"+total+" BTC</p>", function(result) {
			if(result){
				$("#buyorder_price").val("");
				$("#buyorder_size").val("");
				$("#buyorder_total").val("");
				$.post( "api/api",{ method: "addBuyOrder", coin: getUrlVars()['coin'], price: rate, size: amount}, function( data ) {
				  	if(data.result.toLowerCase() != "success"){
				  		var msg = data.result;
				  		var thealert = '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><strong><i class="fa fa-exclamation-triangle fa-fw" style="font-size:1.1em;"></i></strong> '+msg+'</div>';
				  		$("#alerts").append(thealert);
				  	}
				  	loadData();
				}, "json" );
			}
		});
	}else if(!isGood(rate) || !isGood(amount)){
		bootbox.alert("<p><b>Unable to add buy order</b></p><p>Incorrect number format</p>");
	}else if(!rateD && !sizeD){
		bootbox.alert("<p><b>Unable to add buy order</b></p><p>Maximum 3 decimal places for the bid<br/>Maximum 8 decimal places for the size</p>");
	}else if(!rateD){
		bootbox.alert("<p><b>Unable to add buy order</b></p><p>Maximum 3 decimal places for the bid</p>");
	}else if(!sizeD){
		bootbox.alert("<p><b>Unable to add buy order</b></p><p>Maximum 8 decimal places for the size</p>");
	}else if(isPlacedSell(rate)){
		bootbox.alert("<p><b>Unable to add buy order</b></p><p>Please cancel your sell order for "+rate+" satoshi before adding a buy order at "+rate+" satoshi</p>");
	}else if(!enoughBuy(total)){
		bootbox.alert("<p><b>Unable to add buy order</b></p><p>You do not have enough funds to place this order</p>");
	}else{
		bootbox.alert("<p><b>Unable to add buy order</b></p><p>Please enter valid numbers</p>");
	}
}


function confirmSell(){

	var rate = $("#sellorder_price").val();
	var amount = $("#sellorder_size").val();

	var rateD = (rate.indexOf(".") == -1 || rate.length-rate.indexOf(".")-1 <= 3);
	var sizeD = (amount.indexOf(".") == -1 || amount.length-amount.indexOf(".")-1 <= 8);

	if(isGood(rate) && isGood(amount) && rateD && sizeD && !isPlacedBuy(rate) && enoughSell(amount)){
		var total = xpnd(math.eval(rate+"/100000000*"+amount));
		var fee = xpnd(math.eval(total+"*0.005"));
		total = format8(total);
		fee = format8(fee);
		bootbox.confirm("<p><b>Please confirm your sell order</b></p><p><b>Ask </b>"+rate+" satoshi<br/><b>Size </b>"+amount+" "+getUrlVars()['coin'].toUpperCase()+"<br/><b>0.5% Fee </b>"+fee+" BTC<br/><b>Total </b>"+total+" BTC</p>", function(result) {
			if(result){
				$("#sellorder_price").val("");
				$("#sellorder_size").val("");
				$("#sellorder_total").val("");
				$.post( "api/api",{ method: "addSellOrder", coin: getUrlVars()['coin'], price: rate, size: amount}, function( data ) {
					if(data.result.toLowerCase() != "success"){
				  		var msg = data.result;
				  		var thealert = '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><strong><i class="fa fa-exclamation-triangle fa-fw" style="font-size:1.1em;"></i></strong> '+msg+'</div>';
				  		$("#alerts").append(thealert);
				  	}
					loadData();
				}, "json" );
			}
		});
	}else if(!isGood(rate) || !isGood(amount)){
		bootbox.alert("<p><b>Unable to add sell order</b></p><p>Incorrect number format</p>");
	}else if(!rateD && !sizeD){
		bootbox.alert("<p><b>Unable to add sell order</b></p><p>Maximum 3 decimal places for the ask<br/>Maximum 8 decimal places for the size</p>");
	}else if(!rateD){
		bootbox.alert("<p><b>Unable to add sell order</b></p><p>Maximum 3 decimal places for the ask</p>");
	}else if(!sizeD){
		bootbox.alert("<p><b>Unable to add sell order</b></p><p>Maximum 8 decimal places for the size</p>");
	}else if(isPlacedBuy(rate)){
		bootbox.alert("<p><b>Unable to add sell order</b></p><p>Please cancel your buy order for "+rate+" satoshi before adding a sell order at "+rate+" satoshi</p>");
	}else if(!enoughSell(amount)){
		bootbox.alert("<p><b>Unable to add sell order</b></p><p>You do not have enough funds to place this order</p>");
	}else{
		bootbox.alert("<p><b>Unable to add sell order</b></p><p>Please enter valid numbers</p>");
	}
}

function calcbuytotal(){

	var bid = $("#buyorder_price").val();
	var size = $("#buyorder_size").val();
    
	if(isGood(bid) && isGood(size)){
		var total = math.eval(bid+"/100000000*"+size);
		$("#buyorder_total").val(format8input(total));
		var fee = math.eval(size+"*0.005");
		$("#buyorder_fee").html(format8(fee));
	}else{
		$("#buyorder_total").val("");
		$("#buyorder_fee").text("0.00000000");
	}

}

function calcselltotal(){
	var ask = $("#sellorder_price").val();
	var size = $("#sellorder_size").val();

	if(isGood(ask) && isGood(size)){
		var total = math.eval(ask+"/100000000*"+size);
		$("#sellorder_total").val(format8input(total));
		var fee = math.eval(total+"*0.005");
		$("#sellorder_fee").html(format8(fee));
	}else{
		$("#sellorder_total").val("");
		$("#sellorder_fee").text("0.00000000");
	}
}

function calcsellamount(){
	var ask = $("#sellorder_price").val();
	var total = $("#sellorder_total").val();

	if(isGood(ask) && isGood(total)){
		var size = xpnd(math.round(math.eval(total+"/"+ask+"*100000000"),8));
		$("#sellorder_size").val(size);
		var fee = xpnd(math.eval(total+"*0.005"));
		$("#sellorder_fee").html(format8(fee));
	}else{
		$("#sellorder_size").val("");
		$("#sellorder_fee").text("0.00000000");
	}
}

function calcbuyamount(){
	var bid = $("#buyorder_price").val();
	var total = $("#buyorder_total").val();

	if(isGood(bid) && isGood(total)){
		var size = xpnd(math.round(math.eval(total+"/"+bid+"*100000000"),8));
		$("#buyorder_size").val(size);
		var fee = math.eval(total+"*0.005");
		$("#buyorder_fee").html(format8(fee));
	}else{
		$("#buyorder_size").val("");
		$("#buyorder_fee").text("0.00000000");
	}
}

function isGood(num){
	return num.match(/^\d+(\.\d+)?$/);
}

function isPlacedBuy(a){
    return active_buys.indexOf(a) != -1;
}

function isPlacedSell(a){
    return active_sells.indexOf(a) != -1;
}

function enoughBuy(total){
	return parseInt(math.compare(math.bignumber(balance_btc),math.bignumber(total))+"") >= 0;
}

function enoughSell(amount){
	return parseInt(math.compare(math.bignumber(balance_coin),math.bignumber(amount))+"") >= 0;
}