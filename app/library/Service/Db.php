<?php
namespace Service;
use \Config;
use \Log;
use \PDO;

/**
 * Class and Function List:
 * Function list:
 * - __construct()
 * - init()
 * - bind()
 * - query()
 * - lastInsertId()
 * - row()
 * - single()
 * - close()
 * - __destruct()
 * - ExceptionLog()
 * Classes list:
 * - Db
 */
/**
 * 数据库操作，
 * PDO 预处理封装
 * @修改自		https://github.com/indieteq/PHP-MySQL-PDO-Database-Class
 */
class Db
{
	# @object, The PDO object
	private $pdo;
	# @object, PDO statement object
	private $sQuery;

	public function __construct($dsn, $username, $password)
	{
		$this->pdo = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
		# We can now log any exceptions on Fatal error.
		// $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		# Disable emulation of prepared statements, use REAL prepared statements instead.
		$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}

	/**
	 *	Every method which needs to execute a SQL query uses this method.
	 *
	 *	1. If not connected, connect to the database.
	 *	2. Prepare Query.
	 *	3. Parameterize Query.
	 *	4. Execute Query.
	 *	5. On exception : Write Exception into the log + SQL query.
	 *	6. Reset the Parameters.
	 */
	private function init($query, $parameters = '')
	{
		try
		{
			// Prepare query
			if (Config::get('isdebug'))
			{
				Log::write($query, 'SQL');
				\PC::DB($query, 'prepare');
			}
			if ($this->sQuery = $this->pdo->prepare($query))
			{
				# Add parameters to the parameter array
				$parameters = is_array($parameters) ? $this->bind($parameters) : array();
				#excute
				if (Config::get('isdebug'))
				{
					Log::write('SQL param:' . json_encode($parameters), 'SQL');
					\PC::DB($parameters, 'parameters');
				}
				return $this->sQuery->execute($parameters);
			}
		}
		catch (PDOException $e)
		{
			Log::write('数据库链接错误:' . $e->getMessage() . '执行：' . $query, 'ERROR');
		}
	}

	/**
	 *	Add more parameters to the parameter array
	 *	@param array $parray
	 */
	private function bind($parray)
	{
		if (empty($parray))
		{
			return array();
		}

		$parameters = array();
		foreach ($parray as $key => $value)
		{
			$parameters[':' . $key] = $value;
		}
		return $parameters;
	}

	/**
	 *   	If the SQL query  contains a SELECT or SHOW statement it returns an array containing all of the result set row
	 *	If the SQL statement is a DELETE, INSERT, or UPDATE statement it returns the number of affected rows
	 *
	 *  @param  string $query
	 *	@param  array  $params
	 *	@param  int    $fetchmode
	 *	@return mixed
	 */
	public function query($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
	{
		$query = trim($query);
		if (!$this->init($query, $params))
		{
			return false;
		}
		$result = $this->sQuery->fetchAll($fetchmode);
		$this->sQuery->closeCursor();
		return $result;
	}

	/**
	 * 执行sql语句
	 * @method execute
	 * @param  [type] $query     [description]
	 * @param  [type] $params    [description]
	 * @param  [type] $fetchmode [description]
	 * @return [int]            [影响条数]
	 */
	public function execute($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
	{
		$query = trim($query);
		if (!$this->init($query, $params))
		{
			return false;
		}
		$result = $this->sQuery->rowCount();
		$this->sQuery->closeCursor();
		return $result;
	}

	/**
	 *  Returns the last inserted id.
	 *  @return string
	 */
	public function lastInsertId()
	{
		return $this->pdo->lastInsertId();
	}

	/**
	 *	Returns an array which represents a row from the result set
	 *
	 *	@param  string $query
	 *	@param  array  $params
	 *  @param  int    $fetchmode
	 *	@return array
	 */
	public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
	{
		if ($this->init($query, $params))
		{
			$result = $this->sQuery->fetch($fetchmode);
			$this->sQuery->closeCursor();
			return $result;
		}
	}

	/**
	 *	Returns the value of one single field/column
	 *
	 *	@param  string $query
	 *	@param  array  $params
	 *	@return string
	 */
	public function single($query, $params = null)
	{
		if ($this->init($query, $params))
		{
			$result = $this->sQuery->fetchColumn();
			$this->sQuery->closeCursor();
			return $result;
		}
	}

	/*
	 *   You can use this little method if you want to close the PDO connection
	 */
	public function close()
	{
		# Set the PDO object to null to close the connection
		# http://www.php.net/manual/en/pdo.connections.php
		$this->pdo = null;
	}

	public function __destruct()
	{
		$this->close();
	}
}
?>