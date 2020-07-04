<?php 
	class MessageData {
		
		static $fileName = 'data/data.json';
		
		static public function addMessage($chatId, $message) {
			$allData = self::readAllMessages();
			$allData['data'][$chatId]['messages'][] = $message;
			
			//supprime le premier messae si il y en a plus de 5
			$numberMessages = count($allData['data'][$chatId]['messages']);
			if ($numberMessages > 5) {
				array_splice($allData['data'][$chatId]['messages'], 0, $numberMessages-5);
			}
			
			//update json data file
			file_put_contents(self::$fileName, json_encode($allData));
			
			return $allData;
		}
		
		static public function readAllMessages() {
			$content = json_decode(file_get_contents(self::$fileName), true);
			return $content;
		}
	}