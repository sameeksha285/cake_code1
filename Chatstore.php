<?php

class ChatStore {
	private $id;
	private $chat_user;
	private $chat_message;
	private $cretedon;

	public function setId($id){$this->id=$id;}
	public function getId(){return $this->id;}
	public function setChat_user($chat_user){$this->chat_user=$chat_user;}
	public function getChat_user(){return $this->chat_user;}
	public function setMsg($chat_message){$this->chat_message=$chat_message;}
	public function getMsg(){return $this->chat_message;}
	public function setCreatedOn($createdon){$this->createdon=$createdon;}
	public function getCreatedOn(){return $this->createdon;}

	public function _construct()
	{
		   $con=mysqli_connect('localhost' ,'root' ,'','chat');
	}

	public function saveChat(){
		   $stmt = $this->$con->prepare("INSERT INTO chatdetail VALUES(null ,chat_user ,chat_message,createdon)");
		   $stmt->bindParam('chat_user', $this->chat_user);	
		   $stmt->bindParam('chat_message', $this->chat_message);	
		   $stmt->bindParam('createdon',$this->createdon);	
		   if($stmt->execute())
		   	{
		   		return true;
		   	}
		   	else{
		   		return false;
		   	}
	}
}
?>