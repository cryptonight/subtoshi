<?php

//Please excuse any spelling errors in the comments.  Thank you.
//start the session so we can access session variables
session_start();
//I keep all my comonly used functions in this file
require_once('_functions.php');
require_once("config.php");
/*
*Note that because payment ids in cryptonote are kind of like bitcoin addresses, I may use the terms in place of each other to keep code simple.
*For example, I store payment ids in the "address" database
*/

$method = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['method']);
if(isset($_GET['coin'])){
    $coin = preg_replace("/[^a-zA-Z0-9]+/", "", strtolower($_GET['coin']));
}

$allCoins = array("btc");
$cryptonote = array();
for($i=0;$i<count($coins);$i++){
    array_push($allCoins,preg_replace("/[^a-zA-Z0-9]+/", "", strtolower($coins[$i]["ticker"])));
    if($coins[$i]["type"] === "cryptonote"){
        array_push($cryptonote,preg_replace("/[^a-zA-Z0-9]+/", "", strtolower($coins[$i]["ticker"])));
    }
}

$result = "Error";

if(isset($_GET['coin']) && !in_array($coin, $allCoins)){
    echo json_encode(array("result" => "Error. Invalid coin: ".$coin));
    exit("No coin");
}

$doesntneedlogin = array("getDailyStats","getMarketHistory","getMarketOrders","getBalanceAll");

if(!isset($_GET['user_id']) && !in_array($method, $doesntneedlogin)){
    echo json_encode(array("result" => "Error. Please login."));
    exit("Not logged in");
}

if($_GET['user_id'] === "66" || $_GET['user_id'] === "114"){
    echo json_encode(array("result" => "Error. Account has been locked.  Please contact support for further information."));
}

if(isset($_GET['type'])){
$type = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['type']);
}
if(isset($_GET['price'])){
$price = $_GET['price'];
}
if(isset($_GET['size'])){
$size = $_GET['size'];
}

$api_key = urlencode("***REMOVED***");

switch ($method) {
    case "getDeposits":
        $result = getDeposits($coin);
        break;
    case "getUserOrders":
        $result = getOrders($coin,$type);
        break;
    case "getPaymentIds":
        $result = getPaymentIds($coin);
        break;
    case "getUserTransactions":
        $result = getTransactions($coin,$type);
        break;
    case "getBalance":
        $result = getBalance($coin);
        break;
    case "getBitcoinAddresses":
        $result = getBitcoinAddresses();
        break;
    case "getMarketHistory":
        $result = getMarketHistory($coin);
        break;
    case "getMarketOrders":
        $result = getMarketOrders($coin,$type);
        break;
    case "getDailyStats":
        $result = getDailyStats($coin);
        break;
    case "getTransactionHistory":
        $result = getTransactionHistory();
        break;
    case "getDepositHistory":
        $result = getDepositHistory();
        break;
    case "getWithdrawalHistory":
        $result = getWithdrawalHistory();
        break;
    case "getAddresses":
        $result = getAddresses($coin);
        break;
    case "getBalanceAll":
        $result = getBalanceAll($coin);
        break;
    case "getAllDash":
        $result = getAllDash();
        break;
}

if(isset($coin)){
    echo json_encode(array("ticker"=>$coin,"result" => $result));
}else{
    echo json_encode(array("result" => $result));
}
exit("done");

function getBitcoinAddresses(){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from addresses where user_id = :id AND coin = :coin");
    $statement->execute(array(':id' => $_GET['user_id'], ':coin' => 'btc'));
    $addresses = array();
    while( $row = $statement->fetch()) {
        array_push($addresses, $row['address']);
    }
    return $addresses;
}

function coinCurl($url_p){
    $url = "http://***REMOVED***/".$url_p;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    curl_close($ch);
    return json_decode($data,true);
}

function getAddresses($coin){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from addresses where user_id = :id AND coin = :coin");
    $statement->execute(array(':id' => $_GET['user_id'], ':coin' => $coin));
    $addresses = array();
    while( $row = $statement->fetch()) {
        array_push($addresses, $row['address']);
    }
    return $addresses;
}

function getDeposits($coin){
    //Let's get their payment ids
    $cointype = getCoinType($coin);
    if($cointype == "cryptonote"){
        $payment_ids = getPaymentIds($coin);
    }else{
        $payment_ids = getAddresses($coin);
    }
    $deposits = array();
    
    global $api_key;
    
    if($coin === "btc"){
        
        $payment_ids = getBitcoinAddresses();
        
        $url = "https://blockchain.info/latestblock?api_code=".$api_key;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        $blockheight = json_decode($data,true)["height"];
        logBlockHeight($blockheight,$coin);
        
        for($i=0;$i<count($payment_ids);$i++){
            $url = "https://blockchain.info/address/".$payment_ids[$i]."?format=json&api_code=".$api_key;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($data,true)["txs"];
            for($j=0;$j<count($data);$j++){
                $tx_result = file_get_contents("https://blockchain.info/q/txresult/".$data[$j]["hash"]."/".$payment_ids[$i]."?api_code=".$api_key);
                error_log("TX RESULT: " . $tx_result);
                if(bccomp($tx_result,"0") > 0){
                    if(!isset($data[$j]['block_height'])){
                        array_push($deposits, array("confirms" => "0", "amount" => $tx_result));
                    }else{
                        $coin = "btc";
                        $block_height = $data[$j]['block_height'];
                        $amount = $tx_result;
                        $tx_hash = $data[$j]['hash'];
                        $address = $payment_ids[$i];
                        $time_stamp = $data[$j]['time'];
                        logDeposit($coin,$block_height,$amount,$tx_hash,$address,$time_stamp);
                    }
                }
            }
        }
    }else if($cointype == "cryptonote"){
    
        //Loop over them getting the deposits to each
        for($i=0;$i<count($payment_ids);$i++){
            //This is the other server that is hosting the wallets
            $url = "http://***REMOVED***/getDeposits.php?coin=".$coin."&payment_id=".$payment_ids[$i];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($data,true);
            $blockheight = $data['block_height'];
            logBlockHeight($blockheight,$coin);
            $result = $data['result'];
            for($j=0;$j<count($result);$j++){
            	//Check to see if the deposit has been logged
            	logDeposit($coin,$result[$j]['block_height'],$result[$j]['amount'],$result[$j]['tx_hash'],$payment_ids[$i],$result[$j]['time_stamp']);
            }
        }
    
    }else{
        //Follow general things for like mintcoin
    }
    
    $blockheight = getBlockHeight($coin);
    
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from deposits where user_id = :id AND coin = :coin");
    $statement->execute(array(':id' => $_GET['user_id'], ':coin' => $coin));
    $addresses = array();
    while( $row = $statement->fetch()) {
        $confirms = bcadd(bcsub($blockheight,$row['block_height']),"1");
    	$amount = $row['amount'];
    	array_push($deposits,array("confirms" => $confirms, "amount" => $amount));
    }
    
    return $deposits;
    
}


function getCoinType($coin){
    global $coins;
    for($i=0;$i<count($coins);$i++){
        if($coins[$i]["ticker"] == $coin){
            return $coins[$i]["type"];
        }
    }
    return false;
}

function getDepositHistory(){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from deposits where user_id = :id order by time_stamp");
    $statement->execute(array(':id' => $_GET['user_id']));
    $result = array();
    while( $row = $statement->fetch()) {
        $blockheight = getBlockHeight($row['coin']);
        $confirms = bcadd(bcsub($blockheight,$row['block_height']),"1");
    	array_push($result,array("confirms" => $confirms, "amount" => $row['amount'], "coin" => $row['coin'], "time_stamp" => gmdate("Y-m-d H:i:s", $row['time_stamp'])));
    }
    return $result;
}

function getBlockHeight($coin){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select block_height from blockheights where coin = :coin");
    $statement->execute(array(':coin' => $coin));
    return $statement->fetchColumn();
}

function logBlockHeight($bh,$coin){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from blockheights where coin = :coin");
    $statement->execute(array(':coin' => $coin));
    $rows = $statement->rowCount();
    if($rows === 0){
        $dns = 'mysql:host=localhost;dbname=exchange';
        $user = '***REMOVED***';
        $pass = '***REMOVED***';
        $db = new PDO($dns, $user, $pass);
        $stmt = $db->prepare('INSERT INTO blockheights (coin, block_height, creation_time) VALUES (:coin, :block_height, now())');
        $stmt->execute(array(':coin' => $coin, ':block_height' => $bh));
    }else{
        $dns = 'mysql:host=localhost;dbname=exchange';
        $user = '***REMOVED***';
        $pass = '***REMOVED***';
        $db = new PDO($dns, $user, $pass);
        $stmt = $db->prepare('UPDATE blockheights SET block_height=:block_height WHERE coin=:coin AND block_height < :block_height');
        $stmt->execute(array(':block_height' => $bh, ':coin' => $coin));
        $stmt = $db->prepare('UPDATE blockheights SET update_time=now() WHERE coin=:coin AND block_height = :block_height');
        $stmt->execute(array(':block_height' => $bh, ':coin' => $coin));
    }
}

function logDeposit($coin,$block_height,$amount,$tx_hash,$payment_id,$time_stamp){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from deposits where tx_hash = :tx_hash AND coin = :coin");
    $statement->execute(array(':tx_hash' => $tx_hash, ':coin' => $coin));
    $rows = $statement->rowCount();
    if($rows === 0){
        $dns = 'mysql:host=localhost;dbname=exchange';
        $user = '***REMOVED***';
        $pass = '***REMOVED***';
        $db = new PDO($dns, $user, $pass);
        
        $stmt = $db->prepare('INSERT INTO deposits (coin, block_height, amount, tx_hash, payment_id, time_stamp, creation_time, user_id) VALUES (:coin, :block_height, :amount, :tx_hash, :payment_id, :time_stamp, now(), :user_id)');
        $stmt->execute(array(':coin' => $coin, ':block_height' => $block_height,':amount' => $amount, ':tx_hash' => $tx_hash, ':payment_id' => $payment_id, ':time_stamp' => $time_stamp, ':user_id' => $_GET['user_id']));
    }
}

function getOrders($coin, $type){
    $orders = array();
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    //ducknote buy and sell orders
    if($type === "both"){
        $statement = $db->prepare("select * from orders where placed_by = :placed_by AND coin = :coin");
        $statement->execute(array(':placed_by' => $_GET['user_id'], ':coin' => $coin));
    }else{
        $statement = $db->prepare("select * from orders where type = :type AND placed_by = :placed_by AND coin = :coin");
        $statement->execute(array(':type' => $type, ':placed_by' => $_GET['user_id'], ':coin' => $coin));
    }
    while( $row = $statement->fetch() ) {
    	array_push($orders, array("price" => $row['price'], "amount" => $row['size'], "filled" => $row['filled'], "canceled" => $row['canceled'], "creation_time" => $row['creation_time'], "update_time" => $row['update_time'], "id" => $row['id']));
    }
    return $orders;
}

function getDailyStats($coin){
    $transactions = array();
    
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from transactions where coin = :coin and creation_time > DATE(NOW()) and creation_time < NOW() order by creation_time");
    $statement->execute(array(':coin' => $coin));
    while( $row = $statement->fetch() ) {
    	array_push($transactions, $row['price']);
    }
    
    if(count($transactions === 0)){
        $statement = $db->prepare("select * from transactions where coin = :coin order by creation_time desc limit 1");
        $statement->execute(array(':coin' => $coin));
        while( $row = $statement->fetch() ) {
        	array_push($transactions, $row['price']);
        }
    }
    
    $stats = array();
    $stats["open"] = $transactions[0];
    $stats["last"] = $transactions[count($transactions)-1];
    $stats["high"] = max($transactions);
    $stats["low"] = min($transactions);
    
    return $stats;
}

function getMarketOrders($coin, $type){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    
    $statement = $db->prepare("select * from orders where NOT (size = filled) AND (canceled is null OR canceled = 0) AND type = :type AND coin = :coin");
    $statement->execute(array(":type" => $type, ":coin" => $coin));
    
    $orders = array();
    
    while( $row = $statement->fetch() ) {
    	$price = $row['price'];
    	$size = bcsub($row['size'],$row['filled'],8);
    	if(isset($orders[$price])){
    		$orders[$price] = bcadd($orders[$price],$size,8);
    	}else{
    		$orders[$price] = bcadd($orders[$price],$size,8);
    	}
    }
    
    $result = array();
    foreach ($orders as $key => $value) {
        array_push($result,array("price" => $key, "amount" => $value));
    }
    
    usort($result, 'order_by_price');
    
    return $result;
}

function order_by_price($a, $b) {
    return $b['price'] > $a['price'] ? 1 : -1;
}

function getPaymentIds($coin){
    
    global $cryptonote;
    
    if(!in_array($coin, $cryptonote)){
        return "error";
    }
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from addresses where user_id = :id AND coin = :coin");
    $statement->execute(array(':id' => $_GET['user_id'], ':coin' => $coin));
    $payment_ids = array();
    while( $row = $statement->fetch()) {
        array_push($payment_ids, $row['address']);
    }
    return $payment_ids;
}


function getTransactions($coin,$type){
    $transactions = array();
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    if($type === "buy"){
        $statement = $db->prepare("select * from transactions where buyer_id = :id AND coin = :coin");
    }else{
        $statement = $db->prepare("select * from transactions where seller_id = :id AND coin = :coin");
    }
    $statement->execute(array(':id' => $_GET['user_id'], ':coin' => $coin));
    while( $row = $statement->fetch() ) {
    	array_push($transactions, array("price" => $row['price'], "amount" => $row['size'], "creation_time" => $row['creation_time']));
    }
    return $transactions;
}

function getTransactionHistory(){
    $transactions = array();
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from transactions where buyer_id = :id or seller_id = :id");
    $statement->execute(array(':id' => $_GET['user_id']));
    while( $row = $statement->fetch() ) {
    	$r = array("coin" => $row['coin'], "price" => $row['price'], "amount" => $row['size'], "creation_time" => $row['creation_time']);
    	if($row["buyer_id"] === $_GET['user_id']){
    	    $r["type"] = "buy";
    	}else{
    	    $r["type"] = "sell";
    	}
    	array_push($transactions, $r);
    }
    return $transactions;
}

function getMarketHistory($coin){
    $transactions = array();
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from transactions where coin = :coin");
    $statement->execute(array(':coin' => $coin));
    while( $row = $statement->fetch() ) {
    	array_push($transactions, array("price" => $row['price'], "amount" => $row['size'], "creation_time" => $row['creation_time'], "type" => $row['type']));
    }
    return $transactions;
}

function getBalance($coin){
    
    if($coin === "btc"){
        return getBalanceBTC();
    }
    
    //Process:
    /*   Get deposits sent to address (getDeposits.php)
    *    Set a new balance variable to the sum of the deposits
    *    Create 2 new variables called in and out
    *    Loop over all the transactions, if it is a sell, increase out by the size of the transaction
    *                                    if it is a buy, increase in by the size of the transaction
    *    Take a 0.5% fee by increasing the out variable by 0.5% of the in variable -> $out = bcadd($out, bcmul($in, "0.005"));
    *    Loop over their active orders and put those coins on hold
    *    Loop over all the withdrawals, and subtract from the balance
    *    Return balance
    */
    $deposits = getDeposits($coin);
    
    $active = "0";
    $pending = "0";
    $hold = "0";
    for($i=0;$i<count($deposits);$i++){
        if(bccomp($deposits[$i]["confirms"], "6") >= 0){
            $active = bcadd($active,$deposits[$i]["amount"]);
        }else{
            $pending = bcadd($pending,$deposits[$i]["amount"]);
        }
    }
    $in = "0";
    $out = "0";
    $transactions = getTransactions($coin,"buy");
    for($i=0;$i<count($transactions);$i++){
        $in = bcadd($in, $transactions[$i]["amount"]);
    }
    $transactions = getTransactions($coin,"sell");
    for($i=0;$i<count($transactions);$i++){
        $out = bcadd($out, $transactions[$i]["amount"]);
    }
    $fee = bcmul($in, "0.005",3);
    $out = bcadd($out, $fee,3);
    $orders = getOrders($coin,"sell");
    for($i=0;$i<count($orders);$i++){
        if($orders[$i]['canceled'] !== '1'){
          $amt = bcsub($orders[$i]['amount'],$orders[$i]['filled']);
          $hold = bcadd($hold,$amt);
          $out = bcadd($out, $amt,3);
        }
    }
    
    $active = bcadd($active, $in);
    
    $totalWithdrawals = getWithdrawalsSum($coin);
    $out = bcadd($out,$totalWithdrawals,3);
    
    $active = bcsub($active, $out,3);
    
    return array("active" => $active, "pending" => $pending, "hold" => $hold);
}

function getAllDash(){
    $total = "0";
    for($i=1;$i<130;$i++){
        $_GET['user_id'] = $i;
        $amount = getBalance("dsh");
        $total = bcadd($total,$amount['active'],3);
        $total = bcadd($total,$amount['hold'],3);
        usleep(500000);
    }
    return $total;
}


//Get all user data
function getBalanceAll($coin){
    if($coin === "btc"){
        return ""; //getBalanceBTC();
    }
    
    //Process:
    /*   Get deposits sent to address (getDeposits.php)
    *    Set a new balance variable to the sum of the deposits
    *    Create 2 new variables called in and out
    *    Loop over all the transactions, if it is a sell, increase out by the size of the transaction
    *                                    if it is a buy, increase in by the size of the transaction
    *    Take a 0.5% fee by increasing the out variable by 0.5% of the in variable -> $out = bcadd($out, bcmul($in, "0.005"));
    *    Loop over their active orders and put those coins on hold
    *    Loop over all the withdrawals, and subtract from the balance
    *    Return balance
    */
    $deposits = getDepositsAll($coin);
    $totalDeposit = "0";
    $totalPending = "0";
    for($i=0;$i<count($deposits);$i++){
        if(bccomp($deposits[$i]["confirms"], "6") >= 0){
            $totalDeposit = bcadd($active,$deposits[$i]["amount"]);
        }else{
            $totalPending = bcadd($pending,$deposits[$i]["amount"]);
        }
    }
    
    $totalWithdrawals = getWithdrawalsSumAll($coin);
    
    return array("Total in" => $totalDeposit, "Total out" => $totalWithdrawals);
}

function getDepositsAll($coin){
    //Let's get their payment ids
    $deposits = array();
    
    $blockheight = getBlockHeight($coin);
    
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from deposits where coin = :coin");
    $statement->execute(array(':coin' => $coin));
    $addresses = array();
    while( $row = $statement->fetch()) {
        $confirms = bcadd(bcsub($blockheight,$row['block_height']),"1");
    	$amount = $row['amount'];
    	array_push($deposits,array("confirms" => $confirms, "amount" => $amount));
    }
    
    return $deposits;
    
}

function getWithdrawalsSumAll($coin){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    $stmt = $db->prepare("select * from withdrawals where coin=:coin and (verified = :pending or verified = :complete)");
    $stmt->execute(array(':coin' => $coin, ':pending' => '0', ':complete' => '1'));
    
    $total = "0";
    
    while( $row = $stmt->fetch() ){
        $total = bcadd($total,xpnd($row['size']));
        $total = bcadd($total,xpnd($row['fee']));
    }
    
    return $total;
}

//
//End get all user daya
//
//

function getBalanceBTC(){
    $deposits = getDeposits("btc");
    
    $active = "0";
    $pending = "0";
    $hold = "0";
    for($i=0;$i<count($deposits);$i++){
        if(bccomp($deposits[$i]["confirms"], "6") >= 0){
            $active = bcadd($active,bcmul($deposits[$i]["amount"],"100000000000"));
        }else{
            $pending = bcadd($pending,$deposits[$i]["amount"]);
        }
    }
    $in = "0";
    $out = "0";
    
    //Loop over every coin buy and subtract BTC, and loop over every coin sell and add btc
    $coins = array("xdn","dsh");
    for($i=0;$i<count($coins);$i++){
        $transactions = getTransactions($coins[$i],"sell");
        for($j=0;$j<count($transactions);$j++){
            $in = bcadd($in, bcmul($transactions[$j]["amount"],$transactions[$j]["price"]));
        }
        $transactions = getTransactions($coins[$i],"buy");
        for($j=0;$j<count($transactions);$j++){
            $out = bcadd($out, bcmul($transactions[$j]["amount"],$transactions[$j]["price"]));
        }
    }
    
    $fee = bcmul($in, "0.005",3);
    $out = bcadd($out, $fee,3);
    
    for($i=0;$i<count($coins);$i++){
        $orders = getOrders($coins[$i],"buy");
        for($j=0;$j<count($orders);$j++){
            if($orders[$j]['canceled'] !== '1'){
              $amt = bcsub($orders[$j]['amount'],$orders[$j]['filled']);
              $hold = bcadd($hold,bcmul($amt,$orders[$j]['price']));
              $out = bcadd($out,bcmul($amt,$orders[$j]['price']),3);
            }
        }
    }
    
    $active = bcadd($active, $in);
    
    $totalWithdrawals = getWithdrawalsSum("btc");
    $totalWithdrawals = bcmul($totalWithdrawals,"100000000000");
    $out = bcadd($out,$totalWithdrawals,3);
    
    $active = bcsub($active, $out,3);
    
    return array("active" => $active, "pending" => $pending, "hold" => $hold);
    
}

function getFee($coin){
    
    if($coin === "btc"){
        return "20000";
    }
    
    global $coins;
    
    for($i=0;$i<count($coins);$i++){
        if($coins[$i]["ticker"] === $coin){
            return bcmul($coins[$i]["fee"],"100000000");
        }
    }
}

//for use with calculating balances
function getWithdrawalsSum($coin){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    $stmt = $db->prepare("select * from withdrawals where coin=:coin and placed_by = :placed_by and (verified = :pending or verified = :complete)");
    $stmt->execute(array(':coin' => $coin, ':placed_by'=>$_GET['user_id'], ':pending' => '0', ':complete' => '1'));
    
    $total = "0";
    
    while( $row = $stmt->fetch() ){
        $total = bcadd($total,xpnd($row['size']));
        $total = bcadd($total,xpnd($row['fee']));
    }
    
    return $total;
}

function getWithdrawalHistory(){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '***REMOVED***';
    $pass = '***REMOVED***';
    $db = new PDO($dns, $user, $pass);
    $stmt = $db->prepare("select * from withdrawals where placed_by = :placed_by");
    $stmt->execute(array(':placed_by'=>$_GET['user_id']));
    
    $withdrawals = array();
    
    while( $row = $stmt->fetch() ){
        array_push($withdrawals,array("creation_time" => $row['creation_time'], "verification_time" => $row['verification_time'], "amount" => $row['size'], "verified" => $row['verified'], "address" => $row['address'], "coin" => $row['coin'], "payment_id" => $row['payment_id'], "tx_hash" => $row['tx_hash'], "hash" => $row['verify_hash']));
    }
    
    return $withdrawals;
}