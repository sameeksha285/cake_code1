<?php

		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: *");
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
		$data = array();
		$data_send = array();
        $result = $conn->query("SELECT * FROM messages");

        if ($result->num_rows > 0) {
		    
		    while($row = $result->fetch_assoc()) {
           		$data['name'] = $row["chat-user"];
		 		$data['message'] = $row["chat-message"];
		 		$data['attach'] = $row["chat-attach"];
		 		array_push($data_send, $data);
		  	}

		} else {
		    echo "0 results";
		}
		echo json_encode($data_send);
		$conn->close();	

?>
	