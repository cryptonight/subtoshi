<?php

//Please excuse any spelling errors in the comments.  Thank you.

//start the session so we can access session variables
session_start();

//I keep all my commonly used functions in this file
require_once('_functions.php');

//Get all the data we need to fill the order
$id = $_SESSION['user_id'];
$coin = $_POST['coin'];
$price = $_POST['price'];
$size = $_POST['size'];
$location = $_POST['location'];

//TODO: verify location and coin are valid.

//Make sure the price and size data is valid
if(!is_numeric($price) || !is_numeric($size)){
	header("Location: ".$location);
	exit();
}

//This function converts scientific notation to a regular number as a string
$price = xpnd($price);
$size = xpnd($size);

//Always to math with integers.  So, becuase the price has 3 decimals, and the size can have up to 8 decimals, we multiply by 10^3 and 10^8 respectively
$price = bcmul($price, "1000");
$size = bcmul($size, "100000000");

//Make sure the price and size are greater than 0
if(bccomp($price, "0") <= 0 || bccomp($size, "0") <= 0){
	header("Location: ".$location);
	exit();
}

//Now make sure they have enough funs to place the order
//Let's get our current balance for the given coin
$data = file_get_contents("getBalance.php?coin=".$coin);
$data = json_decode($data, true);
$data = $data['balance'];
//Make sure the balance is a number
if(!is_numeric($data)){
    header("Location: ".$location);
	exit();
}
//Now make sure that there are enough funds to place the order
$orderTotal = bcmul($price, $size);
$orderTotal = convertto11($neededbalancebitcoinlarge); //The convert to 11 function just divides the order by 10^11
if(bccomp($orderTotal, $data) > 0){ //if the order is larger than what we have
	header("Location: ".$location);
	exit();
}

//Now we look for sell orders that match this buy order, and fill those orders
$dns = 'mysql:host=localhost;dbname=orders';
$user = '***REMOVED***';
$pass = '***REMOVED***';

$db = new PDO($dns, $user, $pass);

$statement = $db->prepare("select * from :coin where price = :price AND type = sell AND (canceled is null OR canceled = 0) AND NOT (size = filled)");
$statement->execute(array(':coin' => $coin, ':price' => $price));

//How much of the buy order we are placing has been filled by sell orders
$filled = "0";

//Let's get sql ready to update the sell orders
$dns = 'mysql:host=localhost;dbname=orders';
$user = '***REMOVED***';
$pass = '***REMOVED***';
$db = new PDO($dns, $user, $pass);

//Also, let's get another sql instance ready to log transactions
//Yes, isn't my naming scheme complicated
$dns2 = 'mysql:host=localhost;dbname=transactions';
$user2 = '***REMOVED***';
$pass2 = '***REMOVED***';
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
			$stmt = $db->prepare('UPDATE :coin SET filled=:filled WHERE id=:id');
			$stmt->execute(array(':coin' => $coin, ':filled' => $rowsize, ':id' => $rowid));
			$stmt = $db->prepare('UPDATE :coin SET update_time=now() WHERE id=:id');
			$stmt->execute(array(':coin' => $coin, ':id' => $rowid));
			//Now add this transaction to the transaction log
			//The transaction log is used to determine market history, and logs are also a good thing to keep
			$stmt = $db2->prepare('INSERT INTO :coin (buyer_id, seller_id, price, size, creation_time) VALUES (:buyer_id, :seller_id, :price, :size, now())');
			$stmt->execute(array(':coin' => $coin, ':buyer_id' => $id, ':seller_id' => $rowplacedby, ':price' => $price, ':size' => $toadd));
		}else{ //If the sell order is larger than the buy order we are placing
			//Fill the buy order completely
			$filled = $size;
			//Update the sell order by filling it partially
			$stmt = $db->prepare('UPDATE :coin SET filled=:filled WHERE id=:id');
			$stmt->execute(array(':coin' => $coin, ':filled' => bcadd($rowfilled,$size), ':id' => $rowid));
			$stmt = $db->prepare('UPDATE :coin SET update_time=now() WHERE id=:id');
			$stmt->execute(array(':coin' => $coin, ':id' => $rowid));
			//We log the transaction for the same reason we logged it before
			$stmt = $db2->prepare('INSERT INTO :coin (buyer_id, seller_id, price, size, creation_time) VALUES (:buyer_id, :seller_id, :price, :size, now())');
			$stmt->execute(array(':coin' => $coin, ':buyer_id' => $id, ':seller_id' => $rowplacedby, ':price' => $price, ':size' => $size));
		}
	}
}

//And finally, add the buy order to the database
$dns = 'mysql:host=localhost;dbname=orders';
$user = '***REMOVED***';
$pass = '***REMOVED***';
$db = new PDO($dns, $user, $pass);

$stmt = $db->prepare('INSERT INTO :coin (type, placed_by, price, size, filled, creation_time) VALUES (:type, :placed_by, :price, :size, :filled, now())');
$stmt->execute(array(':coin' => $coin, ':type' => "buy", ':placed_by' => $id,':price' => $price, ':size' => $size, ':filled' => $filled));

header("Location: ".$location);