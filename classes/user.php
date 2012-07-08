<?php
//echo('User');
require_once './config/config.php';
class User {
	/* Variables */
	protected $id = "";
	protected $name = "";
	protected $pass = "";
	protected $mail = "";
	/*
	protected $ = "";
	protected $ = "";
	protected $ = "";
	*/
	
	/* Methods */
	public function __construct() {
	}
	
	public function login($name,$pass){
		
	}
	
	public function persist() {
		
	}
	
	/* Getter/Setter */
	public function  getId (){
		return $this->id;
	}
	/*
	public function setId ($id){
		$this->id = $id;
	}
	*/
	public function  getName (){
		return $this->name;
	}
	public function setName ($name){
		$this->name = $id;
	}
	public function  getPass (){
		return $this->pass;
	}
	public function setPass ($pass){
		$this->pass = $pass;
	}
	public function  getMail (){
		return $this->mail;
	}
	public function setId ($mail){
		$this->mail = $mail;
	}
}
?>