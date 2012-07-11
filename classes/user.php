<?php
require_once './config/config.php';
require_once './phpass/PasswordHash.php';
class User {
	/* Variables */
	protected $id = 0;
	protected $name = "";
	protected $pass = "";
	protected $session = "";
	protected $mail = "";
	protected $reg = 0;
	protected $lastAct = 0;
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
	 * $ret['id']=userid, if successful, 0 otherwhise
	 */
	private $ret = array();
	private $db;
	
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
	 * $ret['id']=userid, if successful, 0 otherwhise
	 */
	public function register($name, $pass, $mail){
		//DB Connection
		$this->db = $this->connectDb();
		if (is_string($this->db)) {
			return $this->getErrorRet($this->db);
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
    	phpu_user
    	WHERE
    	name = ?
    	LIMIT
    	1';
    	$stmt = $this->db->prepare($sql);
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
    	phpu_user
    	WHERE
    	mail = ?
    	LIMIT
    	1';
    	$stmt = $this->db->prepare($sql);
    	if (!$stmt) {
    		return $this->getErrorRet();
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
    	phpu_user(name, mail, reg)
    	VALUES
    	(?, ?, ?)';
    	$stmt = $this->db->prepare($sql);
    	if (!$stmt) { //Fehler beim Query präperieren
    		return $this->getErrorRet();
    	}
    	$this->reg=time();
    	$stmt->bind_param('ssi', $this->name, $this->mail, $this->reg);
    	if (!$stmt->execute()) { //Execute Error
    		return $this->getErrorRet();
    	}
    	
    	//add pass
    	$this->id = $stmt->insert_id; 
    	$sql = 'UPDATE
    	phpu_user
    	SET
    	pass = ?
    	WHERE
    	id = ?';
    	$stmt = $this->db->prepare($sql);
    	if (!$stmt) { //Error    		
    		return $this->getErrorRet();
    	}
    	//Calculate Hash for pass
    	$t_hasher = new PasswordHash(8, FALSE);
    	$hash = $t_hasher->HashPassword($this->pass.$this->id);
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
	 * @return Array, (if its not an array, something went totally wrong) 
	 * $ret['outcome']=1 means user successfully registered
	 * $ret['outcome']=0 means user not registered, due to some error
	 * $ret['message']=some kind of message, you may show to your users
	 * $ret['id']=userid, if successful, 0 otherwhise
	 */
	public function login($name,$pass){
		//DB Connection
		$this->db = $this->connectDb();
		if (is_string($this->db)) {
			return $this->getErrorRet($this->db);
		}
		//input not initial		
		if (('' == $this->name = trim($name)) OR
				('' == $this->pass = trim($pass))) {
			return $this->getErrorRet(EMPTY_FORM);
		}
		
		//Check name
		$sql = 'SELECT
		id, mail, reg
		FROM
		phpu_user
		WHERE
		name = ?';
		$stmt = $this->db->prepare($sql);
		if (!$stmt) { //Prepare Error
			return $this->getErrorRet();
		}
		$stmt->bind_param('s', $this->name);
		if (!$stmt->execute()) { //Execute Error
			return $this->getErrorRet();
		}
		$stmt->bind_result($this->id, $this->mail, $this->reg); //bind id, mail, reg
		if (!$stmt->fetch()) {
			return $this->getErrorRet(INVALID_LOGIN);
		}
		$stmt->close();
		//Check password
		$sql = 'SELECT
		pass
		FROM
		phpu_user
		WHERE
		id = ?';
		$stmt = $this->db->prepare($sql);
		if (!$stmt) { //Prepare Error
			return $this->getErrorRet();
		}				
		//Bind Id 
		$stmt->bind_param('i', $this->id);
		if (!$stmt->execute()) { //Execute+Check Error			
			return $this->getErrorRet();
		}
		$stmt->bind_result($dbHash);		
		if (!$stmt->fetch()) {			
			return $this->getErrorRet();
		}
		$stmt->close();
		
		//Compare Hash from DB with Hashed Pass
		$t_hasher = new PasswordHash(8, FALSE);
		$check = $t_hasher->CheckPassword($this->pass.$this->id, $dbHash);
		if ($check) {
			//Set Session			
			$sql = 'UPDATE
			phpu_user
			SET
			session = ? ,
			lastact = ?
			WHERE
			id = ?';
			$stmt = $this->db->prepare($sql);
			if (!$stmt) { //Fehler beim Query präperieren
				return $this->getErrorRet();
			}
			$time=time();
			$this->session = $this->createString(30);
			$stmt->bind_param('sii', $this->session, $time, $this->id);
			if (!$stmt->execute()) { //Execute Error
				return $this->getErrorRet();
			}
			if (SET_COOKIES){
				//Set Cookie         
				setcookie('phpu_id', $this->id);
				setcookie('phpu_session', $this->session);
			
				$_COOKIE['phpu_id'] = $this->id; // fake-cookie
				$_COOKIE['phpu_session'] = $this->session; // fake-cookie
			}			
    		return $this->getSuccessRet(LOGIN_SUCCESS); 
		} else {
			return $this->getErrorRet(INVALID_LOGIN);
		}
		
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
		//DB Connection
		$this->db = $this->connectDb();
		if (is_string($this->db)) {
			return $this->getErrorRet($this->db);
		}
    	if (!isset($_COOKIE['phpu_id'], $_COOKIE['phpu_session'])) {
			return $this->getErrorRet(EMPTY_SESSION);
    	}    
		//input not initial		
		if (('' == $this->id = trim($_COOKIE['phpu_id'])) OR
				('' == $this->session = trim( $_COOKIE['phpu_session']))) {
			return $this->getErrorRet(EMPTY_SESSION);
		}
		
		if (!$this->session){
			return $this->getErrorRet(INVALID_SESSION);
		} 
		
		//Check session
		$sql = 'SELECT
		lastact
		FROM
		phpu_user
		WHERE
		id = ? AND
		session = ?';
		$stmt = $this->db->prepare($sql);
		if (!$stmt) { //Prepare Error
			return $this->getErrorRet();
		}
		$stmt->bind_param('is', $this->id, $this->session);
		if (!$stmt->execute()) { //Execute Error
			return $this->getErrorRet();
		}
		$lastact = "";
		$stmt->bind_result($lastact); //bind id, lastact
		if (!$stmt->fetch()) {
			return $this->getErrorRet(INVALID_SESSION);
		}
		$stmt->close();
				
		
		$time=time();
		if (abs($time-$lastact)<=SESSION_VALID_TIME){
			//Update session			
			$sql = 'UPDATE
			phpu_user
			SET
			lastact = ?
			WHERE
			id = ?';
			$stmt = $this->db->prepare($sql);
			if (!$stmt) { //Error
				return $this->getErrorRet();
			}			
			$stmt->bind_param('ii', $time, $this->id);
			if (!$stmt->execute()) {
				return $this->getErrorRet();
			}
			$stmt->close();
						
			return $this->getSuccessRet(SESSION_SUCCESS);
			
		} else {
			return $this->getErrorRet(INVALID_SESSION);
		}
	}
	
	/**
	 * Log the User out!
	 * The user must be logged in properly!
	 */
	public function logOut(){
		//id and pass schould be right, end that Users session
		//DB Connection
		$this->db = $this->connectDb();
		if (is_string($this->db)) {
			return $this->getErrorRet($this->db);
		}	
		//Update session to 0
		$sql = 'UPDATE
			phpu_user
			SET
			session = ?
			WHERE
			id = ? AND
			pass = ?';
			$stmt = $this->db->prepare($sql);
			if (!$stmt) { //Error
				return $this->getErrorRet();
			}			
			$stmt->bind_param('iis', $time, $this->id, $this->pass);
			if (!$stmt->execute()) {
				return $this->getErrorRet();
			}
			$stmt->close();
		
		return $this->getSuccessRet(LOGOUT_SUCCESS);
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
	
	private function closeDb(){	
		if (is_object($this->db)) {			
			$thread_id = $this->db->thread_id;
			$this->db->kill($thread_id);
			$this->db->close();
		}
	}
	
	private function createString($laenge) {   
		$zeichen = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz.,-_:;#*+!$%&/()=?";   
		$out = "";
		mt_srand( (double) microtime() * 1000000); 
		for ($i=0;$i<$laenge;$i++){ 
			$out = $out.$zeichen[mt_rand(0,(strlen($zeichen)-1))];       
  		}         
		return $out;   
	}
	
	private function getErrorRet($message = 'Unspecified Error.'){
		$this->id = 0;
		$this->name = "";
		$this->pass = "";
		$this->session = "";
		$this->mail = "";
		$this->reg = "";
		$this->lastAct = "";
		$this->ret = array();
		$this->ret['outcome'] = 0;
		$this->ret['message'] = $message;
		$this->ret['id'] = 0 ;
		
		$this->closeDb();
		return $this->ret;
	}
	
	private function getSuccessRet($message = 'Success.'){
		//User information from DB
		$this->readUserFromDb();
		$this->ret = array();
		$this->ret['outcome'] = 1;
		$this->ret['message'] = $message ;
		$this->ret['id'] = $this->id ;
		
		$this->closeDb();
		return $this->ret;
	}
	
	private function readUserFromDb() {
		//User information
		$sql = 'SELECT
		name, pass, mail, reg, session, lastact
		FROM
		phpu_user
		WHERE
		id = ? ';
		$stmt = $this->db->prepare($sql);
		if (!$stmt) { //Prepare Error
			return false;//$this->getErrorRet();
		}
		$stmt->bind_param('i', $this->id);
		if (!$stmt->execute()) { //Execute Error
			return false;//return $this->getErrorRet();
		}
		$stmt->bind_result($this->name, $this->pass, $this->mail, $this->reg, $this->session, $this->lastAct); //bind name, pass, mail, reg
		if (!$stmt->fetch()) {
			return false;//return $this->getErrorRet(INVALID_SESSION);
		}
		$stmt->close();
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
	public function  getSession (){
		return $this->session;
	}
	public function  getMail (){
		return $this->mail;
	}
	public function setMail ($mail){
		$this->mail = $mail;
	}
	public function  getReg (){
		return $this->reg;
	}
	public function  getLastAct (){
		return $this->lastAct;
	}
}
?>