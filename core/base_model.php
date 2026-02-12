<?php

class BaseModel {
	
	protected $db;
	protected $id;
	protected $fields = array();
	
	/**
	 * Creates new model instance
	 * param $db: database instance
	 * param $id: record's id, null id usually means new record
	 * param $fields: eager load given fields, use '*' for all fields 
	 */
	public function __construct($db,$id = null,$fields = array()){
		$this->db = $db;
		$this->id = $id;
		if(!empty($fields) && isset($this->id))$this->getFields($fields);
	}	
	
	/**
	 * Get current record's id
	 */
	public function getId(){
		return $this->id;	
	}

	private static function validateIdentifier($identifier){
		if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
			throw new InvalidArgumentException('Invalid SQL identifier: '.$identifier);
		}
		return $identifier;
	}

	private static function quoteIdentifier($identifier){
		return '`'.self::validateIdentifier($identifier).'`';
	}
	
	/**
	 * Retrieves given field from memory or database
	 */
	protected function getField($field){
		if(array_key_exists($field,$this->fields)){
			return $this->fields[$field];
		}
		else if(isset($this->id)){
			$fieldSql = self::quoteIdentifier($field);
			$tableSql = self::quoteIdentifier(static::tableName);
			$query = 'SELECT '.$fieldSql.' FROM '.$tableSql.' WHERE `id` = :id';
			$res = $this->db->query($query, array(':id' => (int) $this->id));
			if(isset($res[0][$field])){
				$this->setField($field, $res[0][$field]);
				return $this->fields[$field];
			}else return false;
		}else return false;
	}
	
	/**
	 * Retrieve multiple fields from database
	 * Use '*' for all fields
	 */
	protected function getFields($fields = array()){
		$select = self::buildSelect($fields);
		$tableSql = self::quoteIdentifier(static::tableName);
		$query = 'SELECT '.$select.' FROM '.$tableSql.' WHERE `id` = :id';
		$res = $this->db->query($query, array(':id' => (int) $this->id));
		if(isset($res[0])){
			$ret = array();
			foreach($res[0] as $field => $value){
				$this->setField($field, $value);	
				$ret[$field] = $value;
			}
			return $ret;
		}else return false;
	}
	
	/**
	 * Sets given field
	 */
	protected function setField($field,$value){
		$this->fields[$field] = $value;
	}
	
	/**
	 * Sets given fields
	 */
	protected function setFields($data){
		foreach ($data as $field => $value){
			$this->setField($field,$value);
		}
	}
	
	/**
	 * Finds one record using given criteria
	 * param $fields: Eager load given fields
	 * param $conditions: array of WHERE conditions
	 * param $order: array of ORDER BY criteria
	 */
	public static function findFirst($db,$fields = array(),$conditions = array(),$order = array()){
		$ret = self::find($db,$fields,$conditions,$order,array(0,1));
		if(!empty($ret))return $ret[0];
		else return false;
	}
	
	/**
	 * Finds records using given criteria
	 * param $fields: Eager load given fields
	 * param $conditions: array of WHERE conditions
	 * param $order: array of ORDER BY criteria
	 * param $limit: array in form array(a,b)
	 */
	public static function find($db,$fields = array(),$conditions = array(),$order = array(),$limit = null){
		$params = array();
		$where = self::buildWhere($conditions, $params);
		$sort = self::buildOrderBy($order);
		$limitSql = self::buildLimit($limit);
		$select = self::buildSelect($fields);
		$tableSql = self::quoteIdentifier(static::tableName);
		$query = 'SELECT '.$select.' FROM '.$tableSql.' WHERE '.$where.' '.$sort.' '.$limitSql;
		$res = $db->query($query, $params);
		$ret = array();
		$className = static::className;
		foreach($res as $result){
			$newClass = new $className($db);
			foreach ($result as $field => $value){
				if($field != 'id')$newClass->setField($field, $value);
				else $newClass->setId($value);
			}
			foreach($conditions as $field => $value)if(!is_array($value)){
				if($field != 'id')$newClass->setField($field, $value);
				else $newClass->setId($value);
			}
			$ret[] = $newClass;
		}
		return $ret;
	}

	/**
	 * Builds WHERE string based on given conditions
	 * Can handle basic equality or IN using array as condition value
	 */
	private static function buildWhere($conditions, &$params){
		$where = '1';
		$counter = 0;
		foreach($conditions as $key => $condition){
			$fieldSql = self::quoteIdentifier($key);
			if(is_array($condition)){
				if (empty($condition)) {
					$where .= ' AND 0 = 1';
					continue;
				}
				$placeholders = array();
				foreach($condition as $value){
					$placeholder = ':w_'.$key.'_'.$counter++;
					$placeholders[] = $placeholder;
					$params[$placeholder] = $value;
				}
				$where .= ' AND '.$fieldSql.' IN ('.implode(', ', $placeholders).')';
			}else{
				$placeholder = ':w_'.$key.'_'.$counter++;
				$where .= ' AND '.$fieldSql.' = '.$placeholder;
				$params[$placeholder] = $condition;
			}
		}	
		return $where;
	}
	
	/**
	 * Builds ORDER BY string based on given array
	 */
	private static function buildOrderBy($order){
		$parts = array();
		foreach($order as $key => $dir){
			$fieldSql = self::quoteIdentifier($key);
			$direction = strtoupper((string) $dir) === 'DESC' ? 'DESC' : 'ASC';
			$parts[] = $fieldSql.' '.$direction;
		}
		return !empty($parts) ? 'ORDER BY '.implode(', ', $parts) : '';
	}
	
	/**
	 * Builds LIMIT string based on given array
	 */
	private static function buildLimit($limit){
		if (!isset($limit)) {
			return '';
		}
		$start = max(0, (int) $limit[0]);
		$count = max(0, (int) $limit[1]);
		return 'LIMIT '.$start.', '.$count;
	}
	
	/**
	 * Builds SELECT string based on given array
	 */
	private static function buildSelect($fields){
		if($fields === '*') return '*';
		if(empty($fields)) return '`id`';

		$quoted = array();
		$hasId = false;
		foreach($fields as $field){
			if ($field === 'id') $hasId = true;
			$quoted[] = self::quoteIdentifier($field);
		}
		if(!$hasId) array_unshift($quoted, '`id`');
		return implode(', ', $quoted);
	}
	
	/**
	 * Builds UPDATE data string from given data
	 */
	private static function buildUpdateData($data, &$params){
		$datas = array();
		$counter = 0;
		foreach($data as $field => $value){
			$fieldSql = self::quoteIdentifier($field);
			$placeholder = ':u_'.$field.'_'.$counter++;
			$datas[] = $fieldSql.' = '.$placeholder;
			$params[$placeholder] = $value;
		}
		return implode(', ', $datas);
	}
	
	/**
	 * Builds INSERT data strings from given data
	 */
	private static function buildInsertData($data, &$params){
		$columns = array();
		$values = array();
		$counter = 0;
		foreach($data as $field => $value){
			$columns[] = self::quoteIdentifier($field);
			$placeholder = ':i_'.$field.'_'.$counter++;
			$values[] = $placeholder;
			$params[$placeholder] = $value;
		}
		return array(
			'columns' => implode(', ', $columns),
			'values' => implode(', ', $values)
		);
	}
	
	/**
	 * Get list of instances using custom query
	 */
	public static function sql($db,$q){
		throw new BadMethodCallException('Unsafe raw SQL execution is disabled. Use parameterized model methods instead.');
	}
	
	/**
	 * Set id for current record
	 */
	public function setId($id){
		$this->id = $id;
	}
	
	/**
	 * Delete current record from database
	 */
	public function delete(){
		if(isset($this->id)) {
			$tableSql = self::quoteIdentifier(static::tableName);
			$query = 'DELETE FROM '.$tableSql.' WHERE `id` = :id';
			$this->db->query($query, array(':id' => (int) $this->id));
		}
	}
	
	/**
	 * Update current record with given data
	 * Also fields set with setField will be updated
	 */
	public function update($data = array()){
		if(isset($this->id)) {
			$this->setFields($data);
			$params = array();
			$dataString = self::buildUpdateData($this->fields, $params);
			$params[':id'] = (int) $this->id;
			$tableSql = self::quoteIdentifier(static::tableName);
			$query = 'UPDATE '.$tableSql.' SET '.$dataString.' WHERE `id` = :id';
			$this->db->query($query, $params);
		}
	}
	
	/**
	 * Insert current record with given data
	 * Also fields set with setField will be inserted
	 */
	public function insert($data = array()){
		$this->setFields($data);
		$params = array();
		$insertStrings = self::buildInsertData($this->fields, $params);
		$tableSql = self::quoteIdentifier(static::tableName);
		$query = 'INSERT INTO '.$tableSql.' ('.$insertStrings['columns'].', `created_at`) VALUES ('.$insertStrings['values'].', NOW())';
		$this->db->query($query, $params);
		$this->id = $this->db->lastInsertId();
	}
	
}
