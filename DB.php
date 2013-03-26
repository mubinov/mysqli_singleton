<?php

/**
 * Предоставляет интерфейс к базе данных
 */
class DB {
	private $engine = null,
			$transaction = false;
	private static $instance = false;
	private static $countQueries = 0;

	public $result;

	/**
	 * Возвращает объект класса DB
	 * @return DB
	 */
	public static function getInstance () {
		if (self::$instance === false) {
			self::$instance = new DB;
		}
		return self::$instance;
	}
	
	private function __construct () {}

	public function __destruct () {}

	/**
	 * Загружает движок
	 */
	public function loadEngine () {	
		if($this->engine != null) return false;

		/* MariaDB 5.3 hotfix */
        error_reporting(0);

        if (!$this->engine = new mysqli (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME)) {
            throw new Exception ('Server Connection Failed');
        }

        /* MariaDB 5.3 hotfix */
		error_reporting(E_ALL);
		
		$this->engine->query("/*!40101 SET NAMES 'utf8' */");
	}

	/**
	 * Выполнить запрос к базе данных
	 * @param string $sQuery строка с запросом
	 * @param integer $insertId ссылка на переменную, которой будет присвоен идентификатор вставленной записи
	 * @return resource
	 */
	public function query ($sQuery, &$insertId = 0, $delayed = false) {
        self::$countQueries++;
		
		if(!$this->result = $this->engine->query ($sQuery, $insertId)){
			if ($this->transaction) {
				$this->engine->query ("ROLLBACK");
				$this->transaction = false;
			}
			throw new Exception ('MySQL error: ' . $this->engine->error . "\nQuery:" . $sQuery);
		}	
		
		$insertId = $this->engine->insert_id;
		return $this->result;
	}
	
	/**
	 * Аналог функции mysql_fetch_row
	 * @return array
	 */
	public function fetch_row () {
		return $this->result->fetch_row();
	}
	
	/**
	 * Аналог функции mysql_fetch_array
	 * @return array
	 */
	public function fetch_array () {
		return $this->result->fetch_assoc();
	}
	
	/**
	 * Аналог функции mysql_num_rows
	 * @return integer
	 */
	public function num_rows () {
		return $this->result->num_rows;
	}
	
	/**
	 * Аналог функции mysql_data_seek
	 * @param integer $position
	 * @return void
	 */
	public function reset_pointer ($position = 0) {
		return $this->result->data_seek ($position);
	}
	
	/**
	 * Начинает транзакцию
	 * @return void
	 */
	public function startTransaction () {
		$this->engine->query ("SET AUTOCOMMIT=0");
		$this->engine->query ("START TRANSACTION");
		$this->transaction = true;
	}
	
	/**
	 * Завершает транзакию
	 * @return void
	 */
	public function stopTransaction () {		
		if ($this->engine->transaction) {
			$this->engine->query ("COMMIT");
			$this->transaction = false;
		}
	}
	
	/**
	 * Возвращает количество выполненных запросов к БД
	 * @return integer
	 */
	public static function getCountQueries () {
		return self::$countQueries;
	}

	/**
	 * Очистка запроса
	 * @param string $string - запрос для очистки
	 * @return string
	 */
	public function escape_string ($string) {
		return $this->engine->escape_string ($string);
	}
}