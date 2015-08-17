<?php
/**
 * Class and Function List:
 * Function list:
 * - __construct()
 * - find()
 * - select()
 * - save()
 * - update()
 * - add()
 * - insert()
 * - delete()
 * - query()
 * - field()
 * - where()
 * - orWhere()
 * - order()
 * - limit()
 * - count()
 * - __call()
 * - __set()
 * - __get()
 * - __unset()
 * - set()
 * - get()
 * - clear()
 * - parseField()
 * - parseData()
 * - buildSelectSql()
 * - buidlFromSql()
 * - buildWhereSql()
 * - buildTailSql()
 * Classes list:
 * - Model
 */
class Model
{
	/**
	 * 数据库表名
	 * @var string
	 */
	protected $table;
	/**
	 * 主键
	 * @var string
	 */
	protected $pk = 'id';

	protected $fields   = array(); //查询字段
	protected $data     = array(); //数据
	protected $where    = '';      //查询条件
	protected $param    = array(); //查询参数
	protected $distinct = false;   //是否去重
	protected $order    = '';      //排序字段
	protected $limit    = null;

	private static $_db = null; //数据库连接

	public function __construct($table, $pk = 'id')
	{
		$this->table = $table;
		$this->pk    = $pk;
		if (self::$_db == null)
		{
			$config    = Yaf_registry::get('config')['database'];
			$dsn       = $config['driver'] . ':host=' . $config['host'] . ';dbname=' . $config['database'];
			self::$_db = new Db($dsn, $config['username'], $config['password']);
		}
	}

	/**
	 * 单条查询
	 * @method find
	 * @param  [mixed] $id [description]
	 * @return [array]     [结果数组]
	 * @author NewFuture
	 * @example
	 * 	find(1);//查找主键为1的结果
	 * 	find(['name'=>'ha'])//查找name为ha的结果
	 */
	public function find($id = null, $value = null)
	{
		if (null !== $value)
		{
			$this->data[$id] = $value;
		}
		elseif (null != $id)
		{
			if (is_array($id))
			{
				$this->data = array_merge($this->data, $id);
			}
			else
			{
				$this->data[$this->pk] = $id;
			}
		}
		$this->limit = 1;
		$result      = $this->select();

		return $this->data = isset($result[0]) ? $result[0] : $result;
	}

	/**
	 * 批量查询
	 * @method select
	 * @param  array  $data [查询数据条件]
	 * @return [type]       [description]
	 * @author NewFuture
	 */
	public function select($data = array())
	{
		$field_string = null;
		if (is_array($data))
		{
			//数组条件
			$this->data = array_merge($this->data, $data);
		}
		elseif (is_string($data))
		{
			//select筛选字段
			$field_string = $data;
		}

		$sql = $this->buildSelectSql($field_string);
		$sql .= $this->buidlFromSql();
		$sql .= $this->buildWhereSql();
		$sql .= $this->buildTailSql();

		return $this->query($sql, $this->param);
	}

	/**
	 * 保存数据
	 * @method save
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 * @author NewFuture
	 */
	public function save($data = array())
	{
		if (is_numeric($data))
		{
			$this->where($this->pk, '=', $data);
			$data       = $this->data;
			$this->data = array();
		}
		return $this->update($data);
	}

	/**
	 * 更新数据
	 * @method update
	 * @param  array  $data [要更新的数据]
	 * @return [type]       [description]
	 * @author NewFuture
	 */
	public function update($data = array())
	{
		//字段过滤
		$fields = $this->fields;
		if (!empty($fields))
		{
			$fields = array_flip($fields);
			$data   = array_intersect_key($data, $field);
		}

		if (!empty($data))
		{
			$this->param   = array_merge($this->param, $data);
			$update_string = '';
			foreach (array_keys($data) as $key)
			{
				$update_string .= '`' . $key . '` = :' . $key . ',';
			}
			$sql = 'UPDATE `' . $this->table . '`';
			$sql .= ' SET ' . trim($update_string, ',');
			$sql .= $this->buildWhereSql();

			return $this->query($sql, $this->param);
		}
	}

	/**
	 * 新增数据
	 * 合并现有的data属性
	 * @method add
	 * @param  array $data [description]
	 * @author NewFuture
	 */
	public function add($data = array())
	{
		if (is_array($data))
		{
			$this->data = array_merge($this->data, $data);
		}
		$data = $this->data;
		return $this->insert($data);
	}

	/**
	 * 插入数据库
	 * @method insert
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 * @author NewFuture
	 */
	public function insert($data = array())
	{
		//字段过滤
		$fields = $this->fields;
		if (!empty($fields))
		{
			$fields = array_flip($fields);
			$data   = array_intersect_key($data, $field);
		}

		if (!empty($data))
		{

			$fields = array_keys($data);
			$sql    = 'INSERT INTO `' . $this->table . '` ';
			$sql .= ' (' . implode(',', $fields) . ') VALUES ( :' . implode(',:', $fields) . ')';
			$this->query($sql, $data);
			return self::$_db->lastInsertId();
		}
	}

	/**
	 * 删除数据
	 * @method delete
	 * @param  string $id [description]
	 * @return [type]     [description]
	 * @author NewFuture
	 */
	public function delete($id = '')
	{
		if (null != $id)
		{
			if (is_array($id))
			{
				$this->data = array_merge($this->data, $id);
			}
			else
			{
				$this->data[$this->pk] = $id;
			}
		}
		$sql = 'DELETE';
		$sql .= $this->buidlFromSql();
		$where = $this->buildWhereSql();
		if (!$where)
		{
			return false;
		}
		else
		{
			$sql .= $where;
			return $this->query($sql, $this->param);
		}
	}

	/**
	 * 直接查询
	 * @method query
	 * @param  [type] $sql  [description]
	 * @param  [type] $bind [description]
	 * @return [type]       [description]
	 * @author NewFuture
	 */
	public function query($sql, $bind = null)
	{
		$result = self::$_db->query($sql, $bind);
		$this->clear();
		return $result;
	}

	/**
	 * 字段过滤
	 * @method field
	 * @param  [type] $key   [description]
	 * @param  [type] $alias [description]
	 * @return [type]        [description]
	 * @author NewFuture
	 */
	public function field($field, $alias = null)
	{
		if ($alias && $field)
		{
			$this->fields[$field] = $alias;
		}
		else
		{
			$this->fields[$field] = $field;
		}
		return $this;
	}

	public function where($key, $exp = '', $value = '', $conidition = 'AND')
	{
		if ($conidition !== 'OR')
		{
			$conidition = 'AND';
		}

		if ($exp && is_string($key))
		{
			//表达式如x>1
			$name = $key . '_' . bin2hex($exp);
			$this->where .= ' AND (`' . $key . '` ' . $exp . ' :' . $name . ')';
			$this->param[$name] = $value;
		}
		elseif (is_array($key))
		{
			//数组形式
			$str = '';
			foreach ($key as $k => $v)
			{
				$name = $k . '_w_eq';
				$str .= '（`' . $k . '` = :' . $name . ') AND';
				$this->param[$name] = $v;
			}
			$this->where = substr($str, 0, -4);
		}
		else
		{
			//直接sql条件
			$this->where .= '(' . $key . ')';
		}
		return $this;
	}

	/**
	 * OR 条件
	 * @method orWhere
	 * @param  [type]  $key   [description]
	 * @param  string  $exp   [description]
	 * @param  string  $value [description]
	 * @return [type]         [description]
	 * @author NewFuture
	 */
	public function orWhere($key, $exp = '', $value = '')
	{
		return $this->where($key, $exp, $value, 'OR');
	}

	/**
	 * 排序条件
	 * @method order
	 * @param  [type]  $fields      [description]
	 * @param  boolean $desc [是否降序]
	 * @return [type]               [description]
	 * @author NewFuture
	 */
	public function order($fields, $desc = false)
	{
		if ($desc === true || strtoupper($desc) == 'DESC')
		{
			$order = ' `' . $fields . '` DESC ';
		}
		else
		{
			$order = ' ` ' . $fields . '`';
		}
		$this->order .= $this->order ? (',' . $order) : ($order);
		return $this;
	}

	/**
	 * 限制位置和数量
	 * @method limit
	 * @param  integer $n      [description]
	 * @param  integer $offset [description]
	 * @return [type]          [description]
	 * @author NewFuture
	 */
	public function limit($n = 20, $offset = 0)
	{
		if ($offset > 0)
		{
			$this->limit = intval($offset) . ',' . intval($n);
		}
		else
		{
			$this->limit = intval($n);
		}
		return $this;
	}

	/**
	 * 统计
	 * @method count
	 * @param  [type] $field [description]
	 * @return [type]        [description]
	 * @author NewFuture
	 */
	public function count($field = null)
	{
		$exp = $field ? 'count(`' . $field . '`)' : 'count(*)';
		$sql = $this->buildSelectSql($exp);
		$sql .= $this->buidlFromSql();
		$this->clear();
		return self::$_db->single($sql);
	}

	public function __call($op, $args)
	{
		$op = strtoupper($op);
		if (in_array($op, ['MAX', 'MIN', 'AVG', 'SUM']) && isset($args[0]))
		{
			//数学计算
			$sql = $this->buildSelectSql($op . '(`' . $args[0] . '`)');
			$sql .= $this->buidlFromSql();
			$this->clear();
			return self::$_db->single($sql);
		}
	}

	/**
	 * 设置字段值
	 * @method __set
	 * @param  [type]  $name  [description]
	 * @param  [type]  $value [description]
	 * @access private
	 * @author NewFuture
	 */
	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}

	/**
	 * 获取字段值
	 * @method __get
	 * @param  [type]  $name [description]
	 * @return [type]        [description]
	 * @access private
	 * @author NewFuture
	 */
	public function __get($name)
	{
		return isset($this->data[$name]) ? $this->data[$name] : null;
	}

	public function __unset($name)
	{
		unset($this->data[$name]);
	}

	/**
	 * 设置数据
	 * @method set
	 * @param  [type] $data  [description]
	 * @param  [type] $value [description]
	 * @author NewFuture
	 */
	public function set($data, $value = null)
	{
		if (is_array($data))
		{
			$this->data = array_merge($this->data, $data);
		}
		else
		{
			$this->data[$data] = $value;
		}
		return $this;
	}

	/**
	 * 读取数据
	 * 如果有直接读取，无数据库读取
	 * @method get
	 * @param  [type] $name [字段名称]
	 * @param  [type] $auto_db [是否自动尝试从数据库获取]
	 * @return [type]       [description]
	 * @author NewFuture
	 */
	public function get($name = null, $auto_db = true)
	{
		if ($name)
		{
			if (isset($this->data[$name]))
			{
				return $this->data[$name];
			}
			elseif ($auto_db)
			{
				//数据库读取
				$sql = $this->buildSelectSql(' `' . $name . '`');
				$sql .= $this->buidlFromSql();
				$sql .= $this->buildWhereSql();
				$sql .= 'LIMIT 1';
				$data  = $this->data;
				$value = self::$_db->single($sql, $this->param);
				$this->clear();
				if ($value !== null)
				{
					$data[$name] = $value;
				}
				$this->data = $data;
				return $value;
			}
		}
		else
		{
			return empty($this->data) && $auto_db ? $this->find() : $this->data;
		}
	}

	/**
	 * 清空
	 * @method clear
	 * @return [type] [description]
	 * @author NewFuture
	 */
	public function clear()
	{
		$this->fields   = array(); //查询字段
		$this->data     = array(); //数据
		$this->where    = '';      //查询条件
		$this->param    = array(); //查询参数
		$this->distinct = false;   //排序方式
		$this->order    = '';      //排序字段
		$this->limit    = null;
		return $this;
	}

	/**
	 * field分析
	 * @access private
	 * @param mixed $fields
	 * @return string
	 * @author THINKPHP
	 */
	private function parseField()
	{
		$fields = $this->fields;
		if (is_string($fields) && '' !== $fields)
		{
			$fields = explode(',', $fields);
		}

		if (empty($fields))
		{
			$str = '*';
		}
		else
		{
			$str = '';
			// 完善数组方式传字段名的支持
			// 支持 'fieldname'=>'alias' 这样的字段别名定义
			foreach ($fields as $key => $field)
			{
				$str .= is_numeric($key) ? ('`' . $field . '`,') : ('`' . $key . '` AS `' . $field . '`,');
			}
		}
		//TODO 如果是查询全部字段，并且是join的方式，那么就把要查的表加个别名，以免字段被覆盖
		return trim($str, ',');
	}

	/**
	 * 数据解析和拼接
	 * @method parseData
	 * @param  string    $pos [description]
	 * @return [type]         [description]
	 * @author NewFuture
	 */
	private function parseData($pos = ',')
	{
		$fieldsvals = array();
		foreach (array_keys($this->data) as $column)
		{
			$fieldsvals[] = '`' . $column . '` = :' . $column;
		}
		$this->param = array_merge($this->param, $this->data);
		return implode($pos, $fieldsvals);
	}

	/**
	 * 构建select子句
	 * @method buildSelectSql
	 * @return [type]         [description]
	 * @author NewFuture
	 */
	private function buildSelectSql($exp = null)
	{
		$sql = $this->distinct ? 'SELECT DISTINCT ' : 'SELECT ';
		$sql .= $exp ?: $this->parseField();
		return $sql;
	}

	/**
	 * 构建From子句
	 * @method buidlFromSql
	 * @return [type]       [description]
	 * @author NewFuture
	 */
	private function buidlFromSql()
	{
		return ' FROM (`' . $this->table . '`)';
	}

	/**
	 * 构建where子句
	 * @method buildWhereSql
	 * @return [string]        [''或者WHERE（xxx）]
	 * @author NewFuture
	 */
	private function buildWhereSql()
	{
		$datastr = $where = $this->parseData(')AND(');
		$where   = null;
		if ($datastr)
		{
			$where = '(' . $datastr . ')' . $this->where;
		}
		elseif ($this->where)
		{
			//去掉第一个AND或者OR
			$where = strstr($this->where, '(');
		}
		return $where ? ' WHERE(' . $where . ')' : '';
	}

	/**
	 * 构建尾部子句
	 * limt order等
	 * @method buildTailSql
	 * @return [type]       [description]
	 * @author NewFuture
	 */
	private function buildTailSql()
	{
		$tail = '';
		if ($this->order)
		{
			$tail .= ' ORDER BY ' . $this->order;
		}
		if ($this->limit)
		{
			$tail .= ' LIMIT ' . $this->limit;
		}
		return $tail;
	}
}
?>