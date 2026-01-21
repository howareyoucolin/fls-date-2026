<?php if( ! defined('ROOT_PATH') ) die( 'Curiosity kills cat!' );

class DB{
	
	private $connection = null;
	
	public function __construct($host, $database, $username, $password){
		
		$this->connection = @mysqli_connect( $host, $username, $password, $database );
		
		if( !$this->connection ){
			$error = mysqli_connect_error();
			throw new Exception( 'Error connecting to MySQL server: ' . $error . ' (Host: ' . $host . ', Database: ' . $database . ')' );
		}
		
		mysqli_set_charset( $this->connection, 'utf8' );
		
	}
	
	public function query($sql){
		
		$result = mysqli_query( $this->connection, $sql );
		
		if( !$result ){
			throw new Exception( 'MySQL query error: ' . mysqli_error( $this->connection ) . ' (Query: ' . substr( $sql, 0, 100 ) . '...)' );
		}
		
		return $result;
		
	}
	
	public function get_last_insert_id(){
		return $this->connection->insert_id;
	}
	
	public function get_results($sql){
		
		$results = array();
		$query_result = $this->query( $sql );
		
		while( $object = $query_result->fetch_object() ){
			$results[] = $object;
		}
		
		$query_result->free();
		return $results;
		
	}
	
	public function get_row($sql){
		
		$query_result = $this->query( $sql );
		$object = $query_result->fetch_object();
		$query_result->free();
		
		return $object ?: false;
		
	}
	
	public function get_var($sql){
		
		$query_result = $this->query( $sql );
		$row = $query_result->fetch_row();
		$query_result->free();
		
		return $row ? $row[0] : false;
		
	}
	
	public function get_count($sql){
		
		$query_result = $this->query( $sql );
		$count = $query_result->num_rows;
		$query_result->free();
		
		return $count;
		
	}
	
	public function prepare($sql, ...$args){
		
		if( is_null($sql) ){
			return false;
		}
		
		foreach( $args AS $i => $arg ){
			if( $arg === null ){
				throw new Exception( 'Undefined property in arguments!' );
			}
			$sql = str_replace( "%s$i", $this->connection->real_escape_string($arg), $sql );
		}
		
		return $sql;
		
	}
	
	public function get_connection(){
		return $this->connection;
	}
	
}