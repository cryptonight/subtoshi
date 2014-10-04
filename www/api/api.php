<?php

//Only allow connections from this server
define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
if(!IS_AJAX) {die('Restricted access');}
$pos = strpos($_SERVER['HTTP_REFERER'],getenv('HTTP_HOST'));
if($pos===false)
  die('Restricted access');

//Please excuse any spelling errors in the comments.  Thank you.
session_start();
require_once("_functions.php");
require_once("api_functions.php");
require_once("../config/config.php");
require_once("../auth/config/config.php");
require_once("../auth/libraries/PHPMailer.php");

//check all post variables
foreach ($_POST as $key => $value) {
    check($value);
}

$method = $_POST['method'];

if(validPOST("coin"))
    $coin = $_POST['coin'];

if(validPOST("type"))
    $type = $_POST['type'];

if(validPOST("price"))
    $price = $_POST['price'];

if(validPOST("size"))
    $size = $_POST['size'];

//Make sure they're logged in if required
$doesntneedlogin = array("getDailyStats","getMarketHistory","getMarketOrders");
assert(isset($_SESSION['user_id']) || in_array($method, $doesntneedlogin));

$allCoins = array("btc");
$cryptonote = array();
for($i=0;$i<count($coins);$i++){
    array_push($allCoins,strtolower($coins[$i]["ticker"]));
    if($coins[$i]["type"] === "cryptonote"){
        array_push($cryptonote,strtolower($coins[$i]["ticker"]));
    }
}

assert(!validPOST("coin") || in_array($coin, $allCoins));

$result = "Error";

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
        $result = confirmWithdrawal($_POST['hash']);
        break;
    case "requestWithdrawal":
        $result = requestWithdrawal($coin,$_POST['address'],$_POST['amount'],$_POST['payment_id']);
        break;
    case "getWithdrawalHistory":
        $result = getWithdrawalHistory();
        break;
    case "cancelWithdrawal":
        $result = cancelWithdrawal($_POST['hash']);
        break;
    case "cancelOrder":
        $result = cancelOrder($_POST['id']);
        break;
    case "generateAddress":
        $result = generateAddress($coin);
        break;
    case "getAddresses":
        $result = getAddresses($coin);
        break;
}

$json_result = array("result" => $result);
if(validPOST('coin'))
    $json_result["ticker"] = $coin;
echo json_encode($json_result);
exit();

function check($str){
    assert(preg_replace("/[^a-zA-Z0-9.]+/", "", $str) === $str);
}

function validPOST($str){
    return (isset($_POST[$str]) && !empty($_POST[$str]));
}


