<?php
	include 'MessageData.php';
	
	$openWeatherKey = "YOUR_OPEN_WEATHER_KEY";
	$telegramToken = "TOUR_TELEGRAM_TOKEN";

	$path = "https://api.telegram.org/bot$telegramToken";
	$update = json_decode(file_get_contents("php://input"), TRUE);
	
	$chatId = $update["message"]["chat"]["id"];
	$message = $update["message"]["text"];
	
	if (strpos($message, "/start") === 0) {
		$url = $path . '/sendmessage?';
		
		$data = array(
		  'chat_id'      => $chatId,
		  'text'    => "Bienvenue dans TestBot, que voulez-vous faire?",
		  'reply_markup'       => [
			'keyboard' => [["🌦️ Meteo", "❌ Quitter"]],
			'resize_keyboard' => true, 
			'one_time_keyboard' => true
		  ],
		);
		
		sendPost($url, $data);
	}
	else if ($message == "❌ Quitter") {
		$url = $path . '/sendPhoto?';
		
		$data = array(
		  'chat_id'      => $chatId,
		  'caption'    => "Merci de nous avoir visiter, nous esperons vous revoir biento! Au revoir 👋",
		  'photo' => "https://pbs.twimg.com/profile_images/939161800037355520/lvGNqhFT_400x400.jpg",
		  'reply_markup'       => [
			'inline_keyboard' => [[["text" => "Follow me", "url" => "http://github.com/lyabs243"]]],
		  ],
		);
		
		sendPost($url, $data);
	}
	else if ($message == "🌦️ Meteo" || $message == "/weather") {
		$url = $path . '/sendmessage?';
		
		$data = array(
		  'chat_id'      => $chatId,
		  'text'    => "Saisizzez le nom de la ville ou vous voulez voir la meteo, je me ferai un plaisir de vous informer😊",
		);
		
		sendPost($url, $data);
	}
	else {
		//get the previous message
		$chats = MessageData::readAllMessages();
		$messages = $chats['data'][$chatId]['messages'];
		$lastMessage = end($messages);
		
		if (isset($lastMessage)) {
			if ($lastMessage == "🌦️ Meteo" || $lastMessage == "/weather") {
				$location = $message;
				$weather = json_decode(file_get_contents("http://api.openweathermap.org/data/2.5/weather?q=".$location."&units=metric&appid=".$openWeatherKey), TRUE);
				
				$global = $weather["weather"][0]["main"];
				$temp =  $weather["main"]["temp"];
				$tempMin = $weather["main"]["temp_min"];
				$tempMax = $weather["main"]["temp_max"];
				
				$url = $path . '/sendmessage?';
				
				$responseMessage = "
					Here's the weather
					
					🌍 $location
					
					🌈 Temps      : $global
					🌤️ Temperature: $temp °C
					❄️ Temp Min   : $tempMin °C
					☀️ Temp Max   : $tempMax °C
				";
				
				$data = array(
				  'chat_id'      => $chatId,
				  'text'    => $responseMessage,
				);
				
				sendPost($url, $data);
			}
		}
	}
	
	//add message to json file
	MessageData::addMessage($chatId, $message);
	
	function sendPost($url, $data) {
		$options = array(
		  'http' => array(
			'method'  => 'POST',
			'content' => json_encode( $data ),
			'header'=>  "Content-Type: application/json\r\n" .
						"Accept: application/json\r\n"
			)
		);

		$context  = stream_context_create( $options );
		$result = file_get_contents( $url, false, $context );
		$response = json_decode( $result );
		return $response;
	}