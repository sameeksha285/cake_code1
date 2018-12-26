<?php
       echo $_GET;
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
		
        $result = $conn->query("SELECT name FROM users WHERE id = '".$chat_user_id."' ");
        echo json_encode( $result);
        if ($result->num_rows > 0) {
		    
		    while($row = $result->fetch_assoc()) {
           		return $data['name'] = $row["chat-user"];

		 		
		  	}

		} else {
		    echo "0 results";
		}
		
		$conn->close();	

?>
	