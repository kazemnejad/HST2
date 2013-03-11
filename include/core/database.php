<<<<<<< HEAD
<?php
require_once 'include/config.php';
require_once 'include/core/utilFunctions.php';

error_reporting(E_ALL);
ini_set("display_errors", "On");
class db_engine{
	//variable
	private $host;
	private $db_name;
	private $user;
	private $pass;
	//Function
	public function __construct(){
		global $_config;
		$this->host = $_config['db']['host'];
		$this->db_name = $_config['db']['name'];
		$this->user = $_config['db']['user'];
		$this->pass = $_config['db']['pass'];
		
		#connect & select database
		$this->connect();
		mysql_select_db($this->db_name);
	}
	private function connect(){
		mysql_connect($this->host,$this->user,$this->pass) or die("Unable to connect database");
	}
	public function setDBname($dbn){
		$this->db_name = $dbn;
	}
	public function query($query, $dieOnError = true) {
		mysql_escape_string($query);
		$result = mysql_query($query);
		if ( $result === false){
			if ($dieOnError)
				die ($query." : ". mysql_error());
			return false;
		}
		if (getConfig('main', 'writeQueries') == 1) echo_nobuffer('QUERY:--> ' . $query . '<br>');
		return $result;
	}

	public function getLastId($tableName){
		$this->connect();
		$result = $this->query("SELECT * FROM $tableName ORDER BY id DESC LIMIT 1");
		$id = 0;
		if(mysql_num_rows($result) > 0)
		$id = mysql_result($result, 0, 0);
		return ++$id;
	}
}
?>
=======
<?php
	require_once "../config.php";
	class db_engine{
		//variable
		private $host;
		private $db_name;
		private $user;
		private $pass;
		//Function
		public function __construct(){
			$host = $_config['db']['host'];
			$db_name = $_config['db']['name'];
			$user = $_config['db']['user'];
			$pass = $_config['db']['pass'];
		}
		private function connect(){
			mysql_connect($host,$user,$pass) or die("Unable to connect database");
		}
		public function query($query){
			this->connect();
			mysql_escape_string($query);
			if(mysql_query($query)){
				return false;
			}
		}
	}
?>
>>>>>>> origin/master
