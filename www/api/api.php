<?php

define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
if(!IS_AJAX) {die('Restricted access');}
$pos = strpos($_SERVER['HTTP_REFERER'],getenv('HTTP_HOST'));
if($pos===false)
  die('Restricted access');

//Please excuse any spelling errors in the comments.  Thank you.
//start the session so we can access session variables
session_start();
//I keep all my comonly used functions in this file
require_once('_functions.php');
require_once("../config/config.php");
require_once("../auth/config/config.php");
require_once("../auth/libraries/PHPMailer.php");
/*
*Note that because payment ids in cryptonote are kind of like bitcoin addresses, I may use the terms in place of each other to keep code simple.
*For example, I store payment ids in the "address" database
*/

$method = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['method']);
if(isset($_POST['coin'])){
    $coin = preg_replace("/[^a-zA-Z0-9]+/", "", strtolower($_POST['coin']));
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

if(isset($_POST['coin']) && !in_array($coin, $allCoins)){
    echo json_encode(array("result" => "Error. Invalid coin: ".$coin));
    exit();
}

$doesntneedlogin = array("getDailyStats","getMarketHistory","getMarketOrders");

if(!isset($_SESSION['user_id']) && !in_array($method, $doesntneedlogin)){
    echo json_encode(array("result" => "Error. Please login."));
    exit();
}

if(isset($_POST['type'])){
$type = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['type']);
}
if(isset($_POST['price'])){
$price = $_POST['price'];
}
if(isset($_POST['size'])){
$size = $_POST['size'];
}

$api_key = urlencode("**REMOVED**");

switch ($method) {
    case "generatePaymentId":
        $result = generatePaymentId($coin);
        break;
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
    case "addBuyOrder":
        $result = addBuyOrder($coin,$price,$size);
        break;
    case "addSellOrder":
        $result = addSellOrder($coin,$price,$size);
        break;
    case "getBitcoinAddresses":
        $result = getBitcoinAddresses();
        break;
    case "generateBitcoinAddress":
        $result = generateBitcoinAddress();
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
    case "confirmWithdrawal":
        $hash = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['hash']);
        $result = confirmWithdrawal($hash);
        break;
    case "requestWithdrawal":
        $address = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['address']);
        $amount = $_POST['amount'];
        $payment_id = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['payment_id']);
        $result = requestWithdrawal($coin,$address,$amount,$payment_id);
        break;
    case "getWithdrawalHistory":
        $result = getWithdrawalHistory();
        break;
    case "cancelWithdrawal":
        $result = cancelWithdrawal(preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['hash']));
        break;
    case "cancelOrder":
        $result = cancelOrder(preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['id']));
        break;
    case "generateAddress":
        $result = generateAddress($coin);
        break;
    case "getAddresses":
        $result = getAddresses($coin);
        break;
}

if(isset($coin)){
    echo json_encode(array("ticker"=>$coin,"result" => $result));
}else{
    echo json_encode(array("result" => $result));
}
exit();

function generatePaymentId($coin){
    
    global $cryptonote;
    
    if(!in_array($coin, $cryptonote)){
        return "error";
    }
    
    $payment_ids = getPaymentIds($coin);
    if(count($payment_ids) > 0){
        echo json_encode(array("payment_id" => "error. already generated payment_id."));
        exit();
    }
    $bytes = openssl_random_pseudo_bytes(32, $cstrong);
    $hex = bin2hex($bytes);
    $new_payment_id = "" . $hex;
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $stmt = $db->prepare('INSERT INTO addresses (user_id, address, creation_time, coin) VALUES (:user_id, :payment_id, now(), :coin)');
    $stmt->execute(array(':user_id' => $_SESSION['user_id'],':payment_id' => $new_payment_id, ':coin' => $coin));
    return $new_payment_id;
}

function getBitcoinAddresses(){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from addresses where user_id = :id AND coin = :coin");
    $statement->execute(array(':id' => $_SESSION['user_id'], ':coin' => 'btc'));
    $addresses = array();
    while( $row = $statement->fetch()) {
        array_push($addresses, $row['address']);
    }
    return $addresses;
}

function generateBitcoinAddress(){
    $adds = getBitcoinAddresses();
    if(count($adds) > 0){
        return "error. already generated bitcoin address.";
    }
    $url = "http://**REMOVED**/generateBitcoinAddress.php?user_id=".$_SESSION['user_id'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($data,true)['address'];
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $stmt = $db->prepare('INSERT INTO addresses (user_id, address, creation_time, coin) VALUES (:user_id, :address, now(), :coin)');
    $stmt->execute(array(':user_id' => $_SESSION['user_id'],':address' => $data, ':coin' => "btc"));
    return $data;
}

function generateAddress($coin){
    if(count(getAddresses($coin)) > 0){
        return "error. already generated address.";
    }
    $data = coinCurl("generateAddress.php?user_id=".$_SESSION['user_id']."&coin=".$coin);
    $address = $data["address"];
    if(!empty($address)){
        insertAddress($coin,$address);
        return $address;
    }
    return "";
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

function insertAddress($coin,$address){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $stmt = $db->prepare('INSERT INTO addresses (user_id, address, creation_time, coin) VALUES (:user_id, :address, now(), :coin)');
    $stmt->execute(array(':user_id' => $_SESSION['user_id'],':address' => $address, ':coin' => $coin));
    return $data;
}

function getAddresses($coin){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from addresses where user_id = :id AND coin = :coin");
    $statement->execute(array(':id' => $_SESSION['user_id'], ':coin' => $coin));
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
    }else if($cointype == "cryptonote" || $cointype=="sha256"){
    
        //Loop over them getting the deposits to each
        for($i=0;$i<count($payment_ids);$i++){
            //This is the other server that is hosting the wallets
            $url = "http://**REMOVED**/getDeposits.php?coin=".$coin."&payment_id=".$payment_ids[$i];
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
    
    }
    
    $blockheight = getBlockHeight($coin);
    
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from deposits where user_id = :id AND coin = :coin");
    $statement->execute(array(':id' => $_SESSION['user_id'], ':coin' => $coin));
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
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from deposits where user_id = :id order by time_stamp");
    $statement->execute(array(':id' => $_SESSION['user_id']));
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
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select block_height from blockheights where coin = :coin");
    $statement->execute(array(':coin' => $coin));
    return $statement->fetchColumn();
}

function logBlockHeight($bh,$coin){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from blockheights where coin = :coin");
    $statement->execute(array(':coin' => $coin));
    $rows = $statement->rowCount();
    if($rows === 0){
        $dns = 'mysql:host=localhost;dbname=exchange';
        $user = '**REMOVED**';
        $pass = '**REMOVED**';
        $db = new PDO($dns, $user, $pass);
        $stmt = $db->prepare('INSERT INTO blockheights (coin, block_height, creation_time) VALUES (:coin, :block_height, now())');
        $stmt->execute(array(':coin' => $coin, ':block_height' => $bh));
    }else{
        $dns = 'mysql:host=localhost;dbname=exchange';
        $user = '**REMOVED**';
        $pass = '**REMOVED**';
        $db = new PDO($dns, $user, $pass);
        $stmt = $db->prepare('UPDATE blockheights SET block_height=:block_height WHERE coin=:coin AND block_height < :block_height');
        $stmt->execute(array(':block_height' => $bh, ':coin' => $coin));
        $stmt = $db->prepare('UPDATE blockheights SET update_time=now() WHERE coin=:coin AND block_height = :block_height');
        $stmt->execute(array(':block_height' => $bh, ':coin' => $coin));
    }
}

function logDeposit($coin,$block_height,$amount,$tx_hash,$payment_id,$time_stamp){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from deposits where tx_hash = :tx_hash AND coin = :coin");
    $statement->execute(array(':tx_hash' => $tx_hash, ':coin' => $coin));
    $rows = $statement->rowCount();
    if($rows === 0){
        $dns = 'mysql:host=localhost;dbname=exchange';
        $user = '**REMOVED**';
        $pass = '**REMOVED**';
        $db = new PDO($dns, $user, $pass);
        
        $stmt = $db->prepare('INSERT INTO deposits (coin, block_height, amount, tx_hash, payment_id, time_stamp, creation_time, user_id) VALUES (:coin, :block_height, :amount, :tx_hash, :payment_id, :time_stamp, now(), :user_id)');
        $stmt->execute(array(':coin' => $coin, ':block_height' => $block_height,':amount' => $amount, ':tx_hash' => $tx_hash, ':payment_id' => $payment_id, ':time_stamp' => $time_stamp, ':user_id' => $_SESSION['user_id']));
    }
}

function getOrders($coin, $type){
    $orders = array();
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    //ducknote buy and sell orders
    if($type === "both"){
        $statement = $db->prepare("select * from orders where placed_by = :placed_by AND coin = :coin");
        $statement->execute(array(':placed_by' => $_SESSION['user_id'], ':coin' => $coin));
    }else{
        $statement = $db->prepare("select * from orders where type = :type AND placed_by = :placed_by AND coin = :coin");
        $statement->execute(array(':type' => $type, ':placed_by' => $_SESSION['user_id'], ':coin' => $coin));
    }
    while( $row = $statement->fetch() ) {
    	array_push($orders, array("price" => $row['price'], "amount" => $row['size'], "filled" => $row['filled'], "canceled" => $row['canceled'], "creation_time" => $row['creation_time'], "update_time" => $row['update_time'], "id" => $row['id']));
    }
    return $orders;
}

function getDailyStats($coin){
    $transactions = array();
    
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from transactions where coin = :coin and creation_time > DATE(NOW()) and creation_time < NOW() and (hide is null or hide != :hide) order by creation_time");
    $statement->execute(array(':coin' => $coin, ':hide' => "1"));
    while( $row = $statement->fetch() ) {
    	array_push($transactions, $row['price']);
    }
    
    if(count($transactions === 0)){
        $statement = $db->prepare("select * from transactions where coin = :coin and (hide is null or hide != :hide) order by creation_time desc limit 1");
        $statement->execute(array(':coin' => $coin, ':hide' => "1"));
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
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
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
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from addresses where user_id = :id AND coin = :coin");
    $statement->execute(array(':id' => $_SESSION['user_id'], ':coin' => $coin));
    $payment_ids = array();
    while( $row = $statement->fetch()) {
        array_push($payment_ids, $row['address']);
    }
    return $payment_ids;
}


function getTransactions($coin,$type){
    $transactions = array();
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    if($type === "buy"){
        $statement = $db->prepare("select * from transactions where buyer_id = :id AND coin = :coin");
    }else{
        $statement = $db->prepare("select * from transactions where seller_id = :id AND coin = :coin");
    }
    $statement->execute(array(':id' => $_SESSION['user_id'], ':coin' => $coin));
    while( $row = $statement->fetch() ) {
    	array_push($transactions, array("price" => $row['price'], "amount" => $row['size'], "creation_time" => $row['creation_time']));
    }
    return $transactions;
}

function getTransactionHistory(){
    $transactions = array();
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $statement = $db->prepare("select * from transactions where buyer_id = :id or seller_id = :id");
    $statement->execute(array(':id' => $_SESSION['user_id']));
    while( $row = $statement->fetch() ) {
    	$r = array("coin" => $row['coin'], "price" => $row['price'], "amount" => $row['size'], "creation_time" => $row['creation_time']);
    	if($row["buyer_id"] === $_SESSION['user_id']){
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
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
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
    $coins = array("xdn","dsh","mint");
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

function addBuyOrder($coin, $price, $size){
    
    //Make sure the price and size data is valid
    if(!is_numeric($price) || !is_numeric($size)){
    	return "error";
    }
    
    //This function converts scientific notation to a regular number as a string
    $price = xpnd($price);
    $sizebefore = $size;
    $sizeafter = xpnd($size);
    
    //Always do math with integers.  So, becuase the price has 3 decimals, and the size can have up to 8 decimals, we multiply by 10^3 and 10^8 respectively
    $price = bcmul($price, "1000");
    $size = bcmul($sizeafter, "100000000");
    
    //Make sure the price and size are greater than 0
    if(bccomp($price, "0") <= 0 || bccomp($size, "0") <= 0){
    	return "error";
    }
    
    //Now make sure they have enough funds to place the order
    //Let's get our current balance for the given coin
    $data = getBalance("btc")['active'] . "";
    //Now make sure that there are enough funds to place the order
    $orderTotal = bcmul($price, $size);
    if(bccomp($orderTotal, $data) > 0){ //if the order is larger than what we have
    	return "error. not enough funds.";
    }
    
    //Now we look for sell orders that match this buy order, and fill those orders
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    
    $db = new PDO($dns, $user, $pass);
    
    $statement = $db->prepare("select * from orders where price = :price AND type = :type AND coin = :coin AND (canceled is null OR canceled = 0) AND NOT (size = filled)");
    $statement->execute(array(':price' => $price, ':coin' => $coin, ':type' => "sell"));
    
    //How much of the buy order we are placing has been filled by sell orders
    $filled = "0";
    
    //Let's get sql ready to update the sell orders
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    
    //Also, let's get another sql instance ready to log transactions
    //Yes, isn't my naming scheme complicated
    $dns2 = 'mysql:host=localhost;dbname=exchange';
    $user2 = '**REMOVED**';
    $pass2 = '**REMOVED**';
    $db2 = new PDO($dns2, $user2, $pass2);
    
    //Go through all the sell orders
    while( $row = $statement->fetch()) {
    	if(bccomp($filled, $size) >= 0){ //If we have completely filled the buy order, then we can stop looking at sell orders
    		break;
    	}else{//Yes this else in unnessesary, becuase we have a break, but the else makes me happy, so I'm keeping it
    	    //Get the data we need from the mysql table row
    		$rowid = $row['id'];
    		$rowprice = $row['price'];
    		$rowsize = $row['size'];
    		$rowfilled = $row['filled'];
    		$rowplacedby = $row['placed_by'];
    		
    		//If the sell order is smaller than the buy order, then fill the sell order, and partially fill the buy order we are placing
    		//We subtract rowfilled from rowsize to get how much of the sell order is still up for sale
    		if(bccomp(bcsub($rowsize, $rowfilled), $size) <= 0){
    			//Calculate the amount of the sell order that is still for sale
    			$toadd = bcsub($rowsize, $rowfilled);
    			//Fill the buy order partially
    			$filled = bcadd($filled, $toadd);
    			//Now fill the sell order completely
    			
    			if(bccomp($toadd,"0") <= 0 || bccomp($price,"0") <= 0 || bccomp($filled,"0") <= 0){
    			    return "Error adding order.";
    			}
    			
    			$stmt = $db->prepare('UPDATE orders SET filled=:filled WHERE id=:id');
    			$stmt->execute(array(':filled' => $rowsize, ':id' => $rowid));
    			$stmt = $db->prepare('UPDATE orders SET update_time=now() WHERE id=:id');
    			$stmt->execute(array(':id' => $rowid));
    			//Now add this transaction to the transaction log
    			//The transaction log is used to determine market history, and logs are also a good thing to keep
    			
    			$fill = "part";
    			if(bccomp(bcsub($rowsize, $rowfilled), $size) == 0){
    			    $fill = "full";
    			}
    			
    			
    			$stmt = $db2->prepare('INSERT INTO transactions (buyer_id, seller_id, price, size, creation_time, coin, type, fill) VALUES (:buyer_id, :seller_id, :price, :size, now(), :coin, :type, :fill)');
    			$stmt->execute(array(':buyer_id' => $_SESSION['user_id'], ':seller_id' => $rowplacedby, ':price' => $price, ':size' => $toadd, ':coin' => $coin, ':type' => "buy", ':fill' => $fill));
    		}else{ //If the sell order is larger than the buy order we are placing
    			//Fill the buy order completely
    			$filled = $size;
    			
    			if(bccomp($size,"0") <= 0 || bccomp($price,"0") <= 0 || bccomp($filled,"0") <= 0){
    			    return "Error adding order.";
    			}
    			
    			//Update the sell order by filling it partially
    			$stmt = $db->prepare('UPDATE orders SET filled=:filled WHERE id=:id');
    			$stmt->execute(array(':filled' => bcadd($rowfilled,$size), ':id' => $rowid));
    			$stmt = $db->prepare('UPDATE orders SET update_time=now() WHERE id=:id');
    			$stmt->execute(array(':id' => $rowid));
    			//We log the transaction for the same reason we logged it before
    			$stmt = $db2->prepare('INSERT INTO transactions (buyer_id, seller_id, price, size, creation_time, coin, type, fill) VALUES (:buyer_id, :seller_id, :price, :size, now(), :coin, :type, :fill)');
    			$stmt->execute(array(':buyer_id' => $_SESSION['user_id'], ':seller_id' => $rowplacedby, ':price' => $price, ':size' => $size, ':coin' => $coin, ':type' => "buy", ':fill' => "full"));
    		}
    	}
    }
    
    //And finally, add the buy order to the database
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    
    $stmt = $db->prepare('INSERT INTO orders (type, placed_by, price, size, filled, creation_time, coin) VALUES (:type, :placed_by, :price, :size, :filled, now(), :coin)');
    $stmt->execute(array(':type' => "buy", ':placed_by' => $_SESSION['user_id'],':price' => $price, ':size' => $size, ':filled' => $filled, ':coin' => $coin));
    
    return "success";
}

function addSellOrder($coin, $price, $size){
    
    //Make sure the price and size data is valid
    if(!is_numeric($price) || !is_numeric($size)){
    	return "error. not numeric";
    }
    
    //This function converts scientific notation to a regular number as a string
    $price = xpnd($price);
    $size = xpnd($size);
    
    //Always to math with integers.  So, becuase the price has 3 decimals, and the size can have up to 8 decimals, we multiply by 10^3 and 10^8 respectively
    $price = bcmul($price, "1000");
    $size = bcmul($size, "100000000");
    
    //Make sure the price and size are greater than 0
    if(bccomp($price, "0") <= 0 || bccomp($size, "0") <= 0){
    	return "error. invalid values";
    }
    
    //Now make sure they have enough funds to place the order
    //Let's get our current balance for the given coin
    $data = getBalance($coin)['active'];
    //Make sure the balance is a number
    if(!is_numeric($data)){
        return "error. not numeric";
    }
    //Now make sure that there are enough funds to place the order
    if(bccomp($size, $data) > 0){ //if the amount they are selling is greater than the amount they have
    	return "error. not enough funds";
    }
    
    //Now we look for buy orders that match this sell order, and fill those orders
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    
    $db = new PDO($dns, $user, $pass);
    
    $statement = $db->prepare("select * from orders where price = :price AND type = :type AND coin = :coin AND (canceled is null OR canceled = 0) AND NOT (size = filled)");
    $statement->execute(array(':price' => $price, ':coin' => $coin, ':type' => "buy"));
    
    //How much of the sell order we are placing has been filled by buy orders
    $filled = "0";
    
    //Let's get sql ready to update the buy orders
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    
    //Also, let's get another sql instance ready to log transactions
    //Yes, isn't my naming scheme complicated
    $dns2 = 'mysql:host=localhost;dbname=exchange';
    $user2 = '**REMOVED**';
    $pass2 = '**REMOVED**';
    $db2 = new PDO($dns2, $user2, $pass2);
    
    //Go through all the buy orders
    while( $row = $statement->fetch()) {
    	if(bccomp($filled, $size) >= 0){ //If we have completely filled the sell order, then we can stop looking at buy orders
    		break;
    	}else{//Yes this else in unnessesary, becuase we have a break, but the else makes me happy, so I'm keeping it
    	    //Get the data we need from the mysql table row
    		$rowid = $row['id'];
    		$rowprice = $row['price'];
    		$rowsize = $row['size'];
    		$rowfilled = $row['filled'];
    		$rowplacedby = $row['placed_by'];
    		
    		//If the buy order is smaller than the sell order, then fill the buy order, and partially fill the sell order we are placing
    		//We subtract rowfilled from rowsize to get how much of the buy order is still active
    		if(bccomp(bcsub($rowsize, $rowfilled), $size) <= 0){
    			//Calculate the amount of the buy order that is still active
    			$toadd = bcsub($rowsize, $rowfilled);
    			//Fill the sell order partially
    			$filled = bcadd($filled, $toadd);
    			
    			if(bccomp($toadd,"0") <= 0 || bccomp($price,"0") <= 0 || bccomp($filled,"0") <= 0){
    			    return "Error adding order.";
    			}
    			
    			//Now fill the buy order completely
    			$stmt = $db->prepare('UPDATE orders SET filled=:filled WHERE id=:id');
    			$stmt->execute(array(':filled' => $rowsize, ':id' => $rowid));
    			$stmt = $db->prepare('UPDATE orders SET update_time=now() WHERE id=:id');
    			$stmt->execute(array(':id' => $rowid));
    			//Now add this transaction to the transaction log
    			//The transaction log is used to determine market history, and logs are also a good thing to keep
    			
    			$fill = "part";
    			if(bccomp(bcsub($rowsize, $rowfilled), $size) == 0){
    			    $fill = "full";
    			}
    			
    			$stmt = $db2->prepare('INSERT INTO transactions (buyer_id, seller_id, price, size, creation_time, coin, type, fill) VALUES (:buyer_id, :seller_id, :price, :size, now(), :coin, :type, :fill)');
    			$stmt->execute(array(':buyer_id' => $rowplacedby, ':seller_id' => $_SESSION['user_id'], ':price' => $price, ':size' => $toadd, ':coin' => $coin, ':type' => "sell", ':fill' => $fill));
    		}else{ //If the buy order is larger than the sell order we are placing
    			//Fill the sell order completely
    			$filled = $size;
    			
    			if(bccomp($size,"0") <= 0 || bccomp($price,"0") <= 0 || bccomp($filled,"0") <= 0){
    			    return "Error adding order.";
    			}
    			
    			//Update the buy order by filling it partially
    			$stmt = $db->prepare('UPDATE orders SET filled=:filled WHERE id=:id');
    			$stmt->execute(array(':filled' => bcadd($rowfilled,$size), ':id' => $rowid));
    			$stmt = $db->prepare('UPDATE orders SET update_time=now() WHERE id=:id');
    			$stmt->execute(array(':id' => $rowid));
    			//We log the transaction for the same reason we logged it before
    			$stmt = $db2->prepare('INSERT INTO transactions (buyer_id, seller_id, price, size, creation_time, coin, type, fill) VALUES (:buyer_id, :seller_id, :price, :size, now(), :coin, :type, :fill)');
    			$stmt->execute(array(':buyer_id' => $rowplacedby, ':seller_id' => $_SESSION['user_id'], ':price' => $price, ':size' => $size, ':coin' => $coin, ':type' => "sell", ':fill' => "full"));
    		}
    	}
    }
    
    //And finally, add the sell order to the database
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    
    $stmt = $db->prepare('INSERT INTO orders (type, placed_by, price, size, filled, canceled, creation_time, coin) VALUES (:type, :placed_by, :price, :size, :filled, :canceled, now(), :coin)');
    $stmt->execute(array(':type' => "sell", ':placed_by' => $_SESSION['user_id'],':price' => $price, ':size' => $size, ':filled' => $filled, ":canceled" => "0", ':coin' => $coin));
    
    return "success";
}

function requestWithdrawal($coin,$address,$amount2,$payment_id){
    $coin = preg_replace("/[^a-zA-Z0-9]+/", "", $coin);
    $address = preg_replace("/[^a-zA-Z0-9]+/", "", $address);
    $payment_id = preg_replace("/[^a-zA-Z0-9]+/", "", $payment_id);
    
    $amount = bcmul($amount2,"100000000");
    
    $balance = getBalance($coin)["active"];
    
    if($coin === "btc"){
        $balance = bcdiv($balance,"100000000000");
    }
    
    if(bccomp($balance,$amount) < 0){
        return "Error requesting withdrawal.  You do not have enough funds.";
    }
    
    //check for weird transactions
    $transactions = getTransactionHistory();
    for($i=0;$i<count($transactions);$i++){
        if(bccomp($transactions[$i]["amount"],"0") < 0){
            return "Error requesting withdrawal.  Invalid transactions. Please contact support to resolve this issue."; 
        }
    }
    
    $amount = bcsub($amount,getFee($coin));
    
    if(bccomp(xpnd($amount),"0") <= 0){
        return "Error requesting withdrawal.  Total cannot be negative.";
    }
    
    //insert withdrawal into database, and send confirm email
    $bytes = openssl_random_pseudo_bytes(32, $cstrong);
    $hex = bin2hex($bytes);
    $hash_val = "" . $hex;
    $hash = hash('sha256',$hash_val);
    
    if(sendWithrawEmail($coin,$amount,$address,$payment_id,$hash) === true){
    
        $dns = 'mysql:host=localhost;dbname=exchange';
        $user = '**REMOVED**';
        $pass = '**REMOVED**';
        $db = new PDO($dns, $user, $pass);
        $stmt = $db->prepare('INSERT INTO withdrawals (placed_by,size,verify_hash,verified,creation_time,address,coin,payment_id,fee) VALUES (:placed_by,:size,:verify_hash,:verified,now(),:address,:coin,:payment_id,:fee)');
        $stmt->execute(array("placed_by" => $_SESSION['user_id'],":size" => $amount,":verify_hash" => $hash,":verified" => "0",":address" => $address,":coin" => $coin,":payment_id" => $payment_id,":fee" => getFee($coin)));
        
        return "A withdrawal confirmation email has been sent to your email address.";
        
    }else{
        return "Error requesting withdrawal.  Unable to send confirmation email.";
    }
    
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
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $stmt = $db->prepare("select * from withdrawals where coin=:coin and placed_by = :placed_by and (verified = :pending or verified = :complete)");
    $stmt->execute(array(':coin' => $coin, ':placed_by'=>$_SESSION['user_id'], ':pending' => '0', ':complete' => '1'));
    
    $total = "0";
    
    while( $row = $stmt->fetch() ){
        $total = bcadd($total,xpnd($row['size']));
        $total = bcadd($total,xpnd($row['fee']));
    }
    
    return $total;
}

function getWithdrawalHistory(){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $stmt = $db->prepare("select * from withdrawals where placed_by = :placed_by");
    $stmt->execute(array(':placed_by'=>$_SESSION['user_id']));
    
    $withdrawals = array();
    
    while( $row = $stmt->fetch() ){
        array_push($withdrawals,array("creation_time" => $row['creation_time'], "verification_time" => $row['verification_time'], "amount" => $row['size'], "verified" => $row['verified'], "address" => $row['address'], "coin" => $row['coin'], "payment_id" => $row['payment_id'], "tx_hash" => $row['tx_hash'], "hash" => $row['verify_hash']));
    }
    
    return $withdrawals;
}

function confirmWithdrawal($hash){
    
    //lookup withdrawal and verify it exists, then verify it and send withdrawal:
    
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $stmt = $db->prepare("select * from withdrawals where verify_hash=:verify_hash and verified = :verified");
    $stmt->execute(array(':verify_hash' => $hash, ':verified'=>'0'));
    if($stmt->rowCount() !== 1){
        return "Withdrawal does not exist or has already been verified";
    }
    $row = $stmt->fetch();
    
    $coin = $row['coin'];
    $address = $row['address'];
    $amount = $row['size'];
    $payment_id = $row['payment_id'];
    
    $url = "http://**REMOVED**/withdraw.php?coin=".$coin."&address=".$address."&amount=".$amount."&payment_id=".$payment_id;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $ch_data = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($ch_data,true);
    $result = $data['result'];
    
    //if it failed to send, mark withdrawal failed and mark its verified value to 3
    //else, mark verified to 1
    
    if(isset($result['tx_hash'])){
    
        $dns = 'mysql:host=localhost;dbname=exchange';
        $user = '**REMOVED**';
        $pass = '**REMOVED**';
        $db = new PDO($dns, $user, $pass);
        $stmt = $db->prepare('UPDATE withdrawals SET verified=:verified WHERE verify_hash=:verify_hash');
        $stmt->execute(array(':verified' => '1',':verify_hash' => $hash));
        $stmt = $db->prepare('UPDATE withdrawals SET tx_hash=:tx_hash WHERE verify_hash=:verify_hash');
        $stmt->execute(array(':tx_hash' => $result['tx_hash'],':verify_hash' => $hash));
        $stmt = $db->prepare('UPDATE withdrawals SET verification_time=now() WHERE verify_hash=:verify_hash');
        $stmt->execute(array(':verify_hash' => $hash));
        
        return "Withdrawal confirmed and sent successfully.";
    
    }else{
        
        $dns = 'mysql:host=localhost;dbname=exchange';
        $user = '**REMOVED**';
        $pass = '**REMOVED**';
        $db = new PDO($dns, $user, $pass);
        $stmt = $db->prepare('UPDATE withdrawals SET verified=:verified WHERE verify_hash=:verify_hash');
        $stmt->execute(array(':verified' => '3',':verify_hash' => $hash));
        $stmt = $db->prepare('UPDATE withdrawals SET verification_time=now() WHERE verify_hash=:verify_hash');
        $stmt->execute(array(':verify_hash' => $hash));
    }
    
    return "There was an error sending the withdrawal.  This is most likely due to an invalid address or payment id.  If you are sure your address and/or payment id is correct, please retry requesting the withdrawal again later.  If the problem persists, please contact support at support@subtoshi.com";
    
}

function sendWithrawEmail($coin,$amount,$address,$payment_id,$hash){
	$mail = new PHPMailer;
    // please look into the config/config.php for much more info on how to use this!
    // use SMTP or use mail()
    // Set mailer to use SMTP
    $mail->IsSMTP();
    //useful for debugging, shows full SMTP errors
    //$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
    // Enable SMTP authentication
    $mail->SMTPAuth = EMAIL_SMTP_AUTH;
    // Enable encryption, usually SSL/TLS
    if (defined(EMAIL_SMTP_ENCRYPTION)) {
        $mail->SMTPSecure = EMAIL_SMTP_ENCRYPTION;
    }
    // Specify host server
    $mail->Host = EMAIL_SMTP_HOST;
    $mail->Username = EMAIL_SMTP_USERNAME;
    $mail->Password = EMAIL_SMTP_PASSWORD;
    $mail->Port = EMAIL_SMTP_PORT;

    $mail->From = EMAIL_VERIFICATION_FROM;
    $mail->FromName = EMAIL_VERIFICATION_FROM_NAME;
    $mail->AddAddress($_SESSION['user_email']);
    $mail->Subject = "Confirm withrawal request";

    $link = 'https://subtoshi.com/confirm_withdrawal.php?verification_code='.urlencode($hash);

    // the link to your register.php, please set this value in config/email_verification.php
    
    $body = "<h2>Subtoshi.com</h2>
    <h3>".strtoupper($coin)." withdrawal confirmation</h3>
    <p>Please confirm the following withdrawal</p>
    <p><b>Amount: </b> ".bcdiv($amount,'100000000',8)." ".strtoupper($coin)."</p>
    <p><b>Address: </b> ".$address."</p>
    ";
    
    if(!empty($payment_id)){
        $body = $body . "<p><b>Payment id: </b> ".$payment_id."</p>";
    }
    
    $body = $body . "<p><a href='".$link."'>Click here to confirm the withdrawal</a></p>";
    
    $body = $body . "<p>If you did not request this withdrawal, please contact support immediately at support@subtoshi.com</p>";
    
    $mail->Body = $body;

    $mail->IsHTML(true);

    if(!$mail->Send()) {
        return false;
    } else {
        return true;
    }
}

function cancelWithdrawal($hash){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $stmt = $db->prepare('UPDATE withdrawals SET verified=:verified WHERE verify_hash=:verify_hash AND placed_by=:placed_by AND verified=:ver');
    $stmt->execute(array(':verified' => '4',':verify_hash' => $hash, ':placed_by' => $_SESSION['user_id'], ':ver' => '0'));
    $stmt = $db->prepare('UPDATE withdrawals SET verification_time=now() WHERE verify_hash=:verify_hash AND placed_by=:placed_by AND verified=:ver');
    $stmt->execute(array(':verify_hash' => $hash, ':placed_by' => $_SESSION['user_id'], ':ver' => '0'));
    return $stmt->rowCount() === 1;
}

function cancelOrder($id){
    $dns = 'mysql:host=localhost;dbname=exchange';
    $user = '**REMOVED**';
    $pass = '**REMOVED**';
    $db = new PDO($dns, $user, $pass);
    $stmt = $db->prepare('UPDATE orders SET canceled=:canceled WHERE id=:id AND placed_by = :placed_by');
	$stmt->execute(array(':canceled' => '1', ':id' => $id, ':placed_by' => $_SESSION['user_id']));
	$stmt = $db->prepare('UPDATE orders SET update_time=now() WHERE id=:id AND placed_by = :placed_by');
	$stmt->execute(array(':id' => $id, ':placed_by' => $_SESSION['user_id']));
	return $stmt->rowCount() === 1;
}
