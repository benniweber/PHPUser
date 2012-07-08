<?php
//echo('User');
require_once './config/config.php';
class User {
	/* Variables */
	protected $id = "";
	protected $name = "";
	protected $pass = "";
	protected $session = "";
	protected $mail = "";
	/*
	protected $ = "";
	protected $ = "";
	protected $ = "";
	*/
	
	/**
	 * Return value for most functions.
	 * Convention:
	 * Array, (if its not an array, something went totally wrong) 
	 * $ret['outcome']=1 means success
	 * $ret['outcome']=0 means failure
	 * $ret['message']=some kind of message, you may show to your users
	 */
	private $ret = array();
	
	/* Methods */
	/**
	 * Just the constructor
	 */
	public function __construct() {
	}
	
	/**
	 * Register a new user
	 * 
	 * @param $name - username
	 * @param $pass - password, make sure the user entered the right one (give him 2 input fields and compare the values)
	 * @param $mail - the user's email
	 * @return Array, (if its not an array, something went totally wrong) 
	 * $ret['outcome']=1 means user successfully registered
	 * $ret['outcome']=0 means user not registered, due to some error
	 * $ret['message']=some kind of message, you may show to your users
	 */
	public function register($name, $pass, $mail){
		
		$db = $this->connectDb();
		if (is_string($db)) {
			return $this->getErrorRet($db);
		}
		
    	//intital values?
    	if (	($this->name = trim($name)) == '' OR
            ($this->pass = trim($pass)) == '' OR
            ($this->mail = trim($mail)) == '' ) {
        		
			return $this->getErrorRet(EMPTY_FORM);
    	}
    	
    	//Check username length
    	if (!preg_match('~\A\S{3,30}\z~', $this->name)) {    		
			return $this->getErrorRet(BAD_NAME);
    	}
    	
    	//check pass length
    	if (strlen($this->pass) > 72 or strlen($this->pass) < 4 ) {    		
			return $this->getErrorRet(BAD_PASS);
    	}
    	
    	//username in use?
    	$sql = 'SELECT
    	id
    	FROM
    	user
    	WHERE
    	name = ?
    	LIMIT
    	1';
    	$stmt = $db->prepare($sql);
    	if (!$stmt) {
    		return $this->getErrorRet();
    	}
    	$stmt->bind_param('s', $this->name);
    	$stmt->execute();
    	$stmt->store_result();
    	if ($stmt->num_rows) {  		
			return $this->getErrorRet(NAME_TAKEN);
    	}
    	$stmt->close();
    	
    	//Mail in use?
    	$sql = 'SELECT
    	mail
    	FROM
    	user
    	WHERE
    	mail = ?
    	LIMIT
    	1';
    	$stmt = $db->prepare($sql);
    	if (!$stmt) {
    		return $db->error;
    	}
    	$stmt->bind_param('s', $this->mail);
    	$stmt->execute();
    	$stmt->store_result();
    	if ($stmt->num_rows) {    		
			return $this->getErrorRet(MAIL_TAKEN);
    	}
    	$stmt->close();
    	
    	//valid!
    	$sql = 'INSERT INTO
    	user(name, mail, reg)
    	VALUES
    	(?, ?, ?)';
    	$stmt = $db->prepare($sql);
    	if (!$stmt) { //Fehler beim Query präperieren
    		return $this->getErrorRet();
    	}
    	$time=time();
    	$stmt->bind_param('ssi', $this->name, $this->mail, $time);
    	if (!$stmt->execute()) { //Execute Error
    		return $this->getErrorRet();
    	}
    	
    	//add pass
    	$this->id = $stmt->insert_id; 
    	$sql = 'UPDATE
    	user
    	SET
    	pass = ?
    	WHERE
    	id = ?';
    	$stmt = $db->prepare($sql);
    	if (!$stmt) { //Error    		
    		return $this->getErrorRet();
    	}
    	//Calcutale Hash for pass
    	require_once './phpass/PasswordHash.php';
    	$t_hasher = new PasswordHash(8, FALSE);
    	$hash = $t_hasher->HashPassword($this->pass);
    	$stmt->bind_param('si', $hash, $this->id);
    	if (!$stmt->execute()) { 		
    		return $this->getErrorRet();
    	}
    	
    	
    	//Success
    	return $this->getSuccessRet(REG_SUCCESS);    
	}
	
	/**
	 * Login with username and password
	 * 
	 * @param $name - username
	 * @param $pass - password
	 */
	public function login($name,$pass){
		
	}
	
	/**
	 * Login with cookies, kind of a session
	 * 
	 * This cookies are expected:
	 * phpu_id - The user's id
	 * phpu_session - The user's actual session
	 * 
	 */
	public function checkSession(){
		
	}
	
	/**
	 * change the user, you should set some variables before.
	 * The user must be logged in properly!
	 */
	public function change() {
		
	}
	
	/* Internal help functions */
	private function connectDb(){
		$db = @new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);		
		
		if (mysqli_connect_errno()) { //Connection error?
			return NO_DB;
		}
		return $db;
	}
	
	private function getErrorRet($message){
		$this->ret = array();
		if ( strlen($message) < 1){
			$message = 'Unspecified Error.';
		}
		$this->ret['outcome'] = 0;
		$this->ret['message'] = $message;
		return $this->ret;
	}
	
	private function getSuccessRet($message){
		$this->ret = array();
		if ( strlen($message) < 1){
			$message = 'Unspecified Error.';
		}
		$this->ret['outcome'] = 1;
		$this->ret['message'] = $message ;
		return $this->ret;
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