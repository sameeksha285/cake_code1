<?php

class ChatHandler {
	
	function send($message,$chat_user_id=null, $chat_attach=null,$chat_message=null) {
	
		global $clientSocketArray;
		$messageLength = strlen($message);
		if(isset($chat_user_id) && !empty($chat_user_id)){
	    $get_name = "SELECT name FROM users WHERE id = '".$chat_user_id."' ";
		$name = @$this->selectFrmDb($get_name);
		$query = "INSERT INTO `messages`(`user_id`, `chat-user`, `chat-attach`,`chat-message`, `company_id`) 
		VALUES ('".$chat_user_id."','".$name."','".$chat_attach."', '".$chat_message."','1')";
		@$this->addToDb($query);
		}
		$chat_attach_ext = explode('.', $chat_attach)['1'];
		if($chat_attach_ext == 'zip'){
		$attach = $name . ":<a target='_blank' href='//localhost/simple-php-chat-using-websocket/".$chat_attach."' > ".$chat_attach."</a>";
		$attachArray = array('message'=>$attach,'message_type'=>'chat-box-html');
		$chatattach = $this->seal(json_encode($attachArray));
		
		}else{
		$attach = $name . ":<a target='_blank' href='//localhost/simple-php-chat-using-websocket/".$chat_attach."' > <img style='height: 100px; width:100px;' src=//localhost/simple-php-chat-using-websocket/" . $chat_attach . "></a>";
		$attachArray = array('message'=>$attach,'message_type'=>'chat-box-html');
		$chatattach = $this->seal(json_encode($attachArray));
			
		}

		$attachLength = strlen($chatattach);
		foreach($clientSocketArray as $clientSocket)
		{
			@socket_write($clientSocket,$message,$messageLength);
			if(($chat_message) && ($chat_attach))
			{
				@socket_write($clientSocket,$chatattach,$attachLength);
			}
			
		}	
		
	}

	function addToDb($query){
		$servername = "localhost";
		$username = "root";
		$password = "";
		$dbname = "orangescrum";
		// Create connection
		$conn = mysqli_connect($servername, $username, $password, $dbname);
		// Check connection
		if (!$conn) {
		    die("Connection failed: " . mysqli_connect_error()); 
		}

		if (mysqli_query($conn, $query)) {
		    return true;
		} else {
		    return false;
		}

		mysqli_close($conn);
	}

	function selectFrmDb($sql){
		$servername = "localhost";
		$username = "root";
		$password = "";
		$dbname = "orangescrum";
		// Create connection
		$conn = mysqli_connect($servername, $username, $password, $dbname);
		// Check connection
		if (!$conn) {
		    die("Connection failed: " . mysqli_connect_error());
		}
		
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
		    // output data of each row
		  while($row = $result->fetch_assoc()) {
		  	
		    return  $row["name"];
		  	
		  }
		} else {
		    echo "0 results";
		}
		$conn->close();
       
	}

	function unseal($socketData) {
		$length = ord($socketData[1]) & 127;
		if($length == 126) {
			$masks = substr($socketData, 4, 4);
			$data = substr($socketData, 8);
		}
		elseif($length == 127) {
			$masks = substr($socketData, 10, 4);
			$data = substr($socketData, 14);
		}
		else {
			$masks = substr($socketData, 2, 4);
			$data = substr($socketData, 6);
		}
		$socketData = "";
		for ($i = 0; $i < strlen($data); ++$i) {
			$socketData .= $data[$i] ^ $masks[$i%4];
		}
		return $socketData;
	}

	function seal($socketData) {
		$b1 = 0x80 | (0x1 & 0x0f);
		$length = strlen($socketData);
		
		if($length <= 125)
			$header = pack('CC', $b1, $length);
		elseif($length > 125 && $length < 65536)
			$header = pack('CCn', $b1, 126, $length);
		elseif($length >= 65536)
			$header = pack('CCNN', $b1, 127, $length);
		return $header.$socketData;
	}

	function doHandshake($received_header,$client_socket_resource, $host_name, $port) {
		$headers = array();
		$lines = preg_split("/\r\n/", $received_header);
		foreach($lines as $line)
		{
			$line = chop($line);
			if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
			{
				$headers[$matches[1]] = $matches[2];
			}
		}

		$secKey = $headers['Sec-WebSocket-Key'];
		$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
		$buffer  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
		"Upgrade: websocket\r\n" .
		"Connection: Upgrade\r\n" .
		"WebSocket-Origin: $host_name\r\n" .
		"WebSocket-Location: ws://$host_name:$port/demo/shout.php\r\n".
		"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
		socket_write($client_socket_resource,$buffer,strlen($buffer));
	}
	
	function newConnectionACK($client_ip_address) {
		$message = 'New client ' . $client_ip_address.' joined';
		$messageArray = array('message'=>$message,'message_type'=>'chat-connection-ack');
		$ACK = $this->seal(json_encode($messageArray));
		return $ACK;
	}
	
	function connectionDisconnectACK($client_ip_address) {
		$message = 'Client ' . $client_ip_address.' disconnected';
		$messageArray = array('message'=>$message,'message_type'=>'chat-connection-ack');
		$ACK = $this->seal(json_encode($messageArray));
		return $ACK;
	}
	
	function createChatBoxMessage($chat_user_id,$chat_attach,$chat_box_message) {
		//echo $chat_attach;
		$get_name = "SELECT name FROM users WHERE id = '".$chat_user_id."' ";
		$name = @$this->selectFrmDb($get_name);

	if(($chat_box_message!== "") && (!$chat_attach)) {
	    $message = $name . ": <div class='chat-box-message'>" . $chat_box_message . "</div>";		
		echo "hello";
		$messageArray = array('message'=>$message,'message_type'=>'chat-box-html');
		$chatMessage = $this->seal(json_encode($messageArray));
		return $chatMessage;
	}	
	else if(($chat_attach !== "") && ($chat_box_message === "")){
		echo "gd bye";
		$chat_attach_ext = explode('.', $chat_attach)['1'];
		if($chat_attach_ext == 'zip'){
			$message = $name . ":<a target='_blank' href='//localhost/simple-php-chat-using-websocket/".$chat_attach."' > ".$chat_attach."</a>";
			$messageArray = array('message'=>$message,'message_type'=>'chat-box-html');
			$chatMessage = $this->seal(json_encode($messageArray));
		
		}else{
			$message = $name . ":<a target='_blank' href='//localhost/simple-php-chat-using-websocket/".$chat_attach."' > <img style='height: 100px; width:100px;' src=//localhost/simple-php-chat-using-websocket/" . $chat_attach . "></a>";
		$messageArray = array('message'=>$message,'message_type'=>'chat-box-html');
		$chatMessage = $this->seal(json_encode($messageArray));
			
		}
	    return $chatMessage;
	}
	else if(($chat_attach !== "") && ($chat_box_message !== "")){
		echo "hey welcome";
		$message = $name . ": <div class='chat-box-message'>" . $chat_box_message . "</div>";
		$messageArray = array('message'=>$message,'message_type'=>'chat-box-html');
		$chatMessage = $this->seal(json_encode($messageArray));
		return $chatMessage;
		

	}
	
	}
}
?>