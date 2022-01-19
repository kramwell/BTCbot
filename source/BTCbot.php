<?php
$botToken = "000000000:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAA";
$url = "https://api.telegram.org/bot".$botToken;

$update = file_get_contents("php://input");
$update = json_decode($update, TRUE);

	#LOGGING OF SENT TO TELEGRAM
	#$singleVarToShow = "";
    checkJSON($singleVarToShow,$update);
		
##############################################################

$isCPUtalkBotId = $update['callback_query']['message']['from']['id'];

if ($isCPUtalkBotId == "000000000"){ #this is my bot replying.
	$snip = $update['callback_query']['data'];
	$message = $update['callback_query']['data'];
	$chat_id = $update['callback_query']['message']['chat']['id'];
	$message_id = $update['callback_query']['message']['message_id'];
}else{
	$chat_id = $update['message']['chat']['id'];
	$snip = $update['message']['text'];
	$message = $update['message']['text'];
	$message_id = $update['message']['message_id'];
	$new_member_id = $update['message']['new_chat_member']['id'];
}


##############################################################	
# DISPLAY START MESSAGE
##############################################################	
if ($snip == '/start') {
		
		
		$reply = "<b>Hello!</b> I am the BTC bot- please see options below.";

		$keyboard = array(
		"inline_keyboard" => array(
		array(
			array(
				"text" => "Get Current Price.",
				"callback_data" => "/price"
			)
		)
		)); 		
		
		
		$postfields = array(
			'chat_id' => "$chat_id",
			'text' => "$reply",
			'message_id' => "$message_id",
			'reply_markup' => json_encode($keyboard)
		);
		
	}

##############################################################	
# DISPLAY PRICE
##############################################################	
if ($snip == '/price') {

	#get price here
	$amount = 1;

	#$update = file_get_contents("https://api.crypto-bridge.org/api/v1/ticker");
	$update = executeGETQuery('https://api.crypto-bridge.org/api/v1/ticker');
	$json_array = json_decode($update, TRUE);

	#print_r($json_array);

	$updateBTCUSD = executeGETQuery("https://api.coinmarketcap.com/v2/ticker/?convert=BTC&limit=1");
	$json_arrayBTC = json_decode($updateBTCUSD, TRUE);

	#print_r($json_arrayBTC['data']['1']['quotes']['USD']['price']);

	foreach($json_array as $json){
		
if (empty($json['id'])) {
	$reply = "Damn crypto-bridge.. :(";
}else{
		
		if ($json['id'] == 'BTCC_BTC'){
			
			$BTCCusd = $json['ask'] * $json_arrayBTC['data']['1']['quotes']['USD']['price'];
			
			$totalUSD = $BTCCusd * $amount;
			
			$reply = '1 BTCC: ' . $json['ask'] . " BTC\nCurrent Price: " .round($BTCCusd, 2). " USD";
			
		}
	}
}
		
		#$reply = "price is x.";	
		
		$postfields = array(
			'chat_id' => "$chat_id",
			'text' => "$reply",
			'message_id' => "$message_id"
		);
		
}
	
	
	
##############################################################
#REPLACE OR DISPLAY
##############################################################
if ($isCPUtalkBotId == "000000000"){ #this is my bot replying.	
	executeQuery($postfields, $url."/editMessageText?disable_notification=TRUE&parse_mode=HTML&");	
}else{
	executeQuery($postfields, $url."/sendMessage?disable_notification=TRUE&parse_mode=HTML&");
}	
	
	
##############################################################
#EXECUTE QUERY - SEND TO CURL
##############################################################

function executeQuery($postfields, $urlToSend){

	if (!$curld = curl_init()) {
	exit;
	}

	curl_setopt($curld, CURLOPT_POST, true);
	curl_setopt($curld, CURLOPT_POSTFIELDS, $postfields);
	curl_setopt($curld, CURLOPT_URL,$urlToSend);
	#curl_setopt($curld, CURLOPT_RETURNTRANSFER, true); #seemed to speed things up?

	$output = curl_exec($curld);

	curl_close ($curld);

	#if ($delete){
		exit;
	#}	
}

function deleteMessage($postfields, $urlToSend){

	if (!$curld = curl_init()) {
	exit;
	}

	curl_setopt($curld, CURLOPT_POST, true);
	curl_setopt($curld, CURLOPT_POSTFIELDS, $postfields);
	curl_setopt($curld, CURLOPT_URL,$urlToSend);
	#curl_setopt($curld, CURLOPT_RETURNTRANSFER, true); #seemed to speed things up?

	$output = curl_exec($curld);

	curl_close ($curld);
}

function executeGETQuery($urlToSend){

// Get cURL resource
$curl = curl_init();
// Set some options - we are passing in a useragent too here
curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $urlToSend,
    #CURLOPT_USERAGENT => 'Codular Sample cURL Request'
));
// Send the request & save response to $resp
$resp = curl_exec($curl);
// Close request to clear up some resources
curl_close($curl);

return $resp;

}

##############################################################
#output all results if dumpResult() is called.
##############################################################
function checkJSON($singleVarToShow,$update){

	$myFile = "log.txt";
	$updateArray = print_r($update,TRUE);
	$fh = fopen($myFile, 'a') or die("can't open file");
	fwrite($fh,"ID ".$isCPUtalkBotId ."\n\n");
	
	fwrite($fh, $updateArray."\n\n");
	fclose($fh);
}

?>