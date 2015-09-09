<?php
/**
 * CMyFrame SQL构造类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

class CBuilder
{
	/**
	 * SQL参数
	 */
	public $action = '' ,$whereValue = array(), $from = '' , $cols = '' ,$distinct = "", $join = '' , $joinTemp = '' , $on = '' , $where = '' , $val = '' , $groupBy = '' , $orderBy = '' , $limit = ' LIMIT 500 ' , $tablePre = '' ;
	
	/**
	 * 最终SQL语句
	 */
	private $_sql = '';
	
	/**
	 * 使用缓存
	 */
	private $_cache;
	
	/**
	 * 缓存时间
	 */
	private $_cacheTime = 3600;
	
	/**
	 * 强制使用主库
	 */
	public $isMaster;
	
	/**
	 * 数据配置名称
	 */
	public $configName;
	
	/**
	 * 预执行SQL
	 */
	public $prepare;
	
	
	/**
	 * 插入
	 */
	public function insert(){
		
		$this->action = 'INSERT INTO ';
		
		return $this;
	}
	
	/**
	 * 预执行
	 */
	public function prepare(){
		
		if(func_num_args() != 1){
			throw new CDbException('[查询错误]函数[CBuilder->prepare]参数参数传递错误');
		}
		
		$sql = func_get_arg(0);
		

		$this->prepare = $sql;
		return $this;
	}
	
	/**
	* 唯一
	*/
	public function distinct(){
		
		$v = func_get_arg(0);
		if($v) $this->distinct = 'DISTINCT('.$v.'),';
		else $this->distinct = 'DISTINCT';
		
		return $this;	
	}
	
	
	/**
	 * 直接执行SQL
	 */
	public function query($sql){
		
		$databaseObject = CDatabase::getDatabase($this->configName,true);
		$this->_sql = $sql;
		$rsReult = array();
		$btime = $etime = 0;
				
		try{
			//执行查询前钩子函数
			CHooks::callHooks(HOOKS_EXECUTE_BEFORE,$this);
				
			$btime = microtime(true);
			$rs = $databaseObject->query($this->_sql);
			if(!$rs){
				$errorData = $databaseObject->errorInfo();
						
				//发生SQL错误时 触发钩子
				$dbError = new CDBError();
				$dbError->setSQLErrorCode($errorData[0]);
				$dbError->setDriverErrorCode($errorData[1]);
				$dbError->setErrorMessage($errorData[2]);
				$dbError->setSql($this->_sql);
				CHooks::callHooks(HOOKS_EXECUTE_ERROR,$dbError);

				throw new PDOException('['.$errorData[1].'] '.$errorData[2].' with SQL ['.$this->_sql.']');
			}
			$rs->setFetchMode(PDO::FETCH_OBJ);
			$rsReult = $rs->fetchAll();
			$etime = microtime(true);
					
		}catch(PDOException $pdoException){
	
			throw new CDbException($pdoException->getMessage());
		}
				
		//查询结果对象
		$resultObject = new CResult();
		$resultObject->setSql($sql);
		$resultObject->setValue($rsReult);
		$resultObject->setCastTime(($etime > $btime) ? round($etime - $btime,6) : 0);
				
		//执行查询后钩子函数
		CHooks::callHooks(HOOKS_EXECUTE_END,$resultObject);
				
		//缓存结果
		if(!empty($this->_cache)){
			$cacheData = Cache::getInstance()->set($this->_cache,$resultObject,$this->_cacheTime);
		}
				
		return $resultObject;
	}
	
	/**
	 * 更新
	 */
	public function update(){
		
		$this->action = 'UPDATE';
		
		return $this;
	}
	
	/**
	 * 删除
	 */
	public function delete(){
		
		$this->action = 'DELETE';
		
		return $this;
	}
	
	/**
	 * 查询
	 */
	public function select(){
		
		$this->action = 'SELECT';
		
		$num = func_num_args();
		
		$cols = '';
		
		if( 0 == $num){
			$this->cols = ' * ';
			return $this;
		}else if(1 == $num && is_array(func_get_arg(0)) ){
			
			foreach(func_get_arg(0) as $key => $val){
				if(is_int($key)){
					$cols .= '`'.$val.'` ,';
				}else{
					if(strpos($key,'`')){
						$cols .= $key.' AS `'.$val.'` ,';
					}else{
						$cols .= '`'.$key.'` AS `'.$val.'` ,';
					}
				}
			}
		}else if(1 == $num && is_string(func_get_arg(0)) ){
			
			$cols = $this->_checkStrToSql(func_get_arg(0));
		}else if($num > 1){
			
			foreach(func_get_args() as $param){
				
				if(is_string($param)){
					$param = str_replace('`','',$param);
					$paramArr = explode(',',$param);
					foreach($paramArr as $val){
						if(strpos($val,'.')){
							$cols .= '`'.str_replace('.','`.`',$val).'` ,';
						}else{
							$cols .= '`'.$val.'` ,';
						}
					}
				}else if(is_array($param)){
					foreach($param as $thisKey => $thisVal){
						$cols .= ''.$thisKey.'  AS `'.$thisVal.'` ,';
					}
				}
			}
			$cols = str_replace('`*`','*',$cols);
		}
	
		$this->cols = substr($cols,0,-1);
		
		return $this;
	}
	
	/**
	 * 值解析
	 */
	public function value(){
		

		$num = func_num_args();
		
		if($num != 1 || !is_array(func_get_arg(0)) ){
			throw new CDbException('[查询错误]在函数中[CBuilder->value]传递参数错误');
		}
		
		$this->val = func_get_arg(0);	
		return $this;
	}
	
	/**
	 * from
	 */
	public function from(){
		
		$num = func_num_args();
		
		if(1 != $num){
			throw new CDbException('[查询错误]在函数中[CBuilder->from]传递参数错误');
		}
		
		$value = func_get_arg(0);
		
		$from = '';		
		
		if(is_array($value)){
			if(1 == count($value)){
				foreach($value as $key => $val){
					$from .= '`MyFrameTablePre_'.ltrim($key).'` AS `'.$val.'` ';
				}
			}else if(2 == count($value)){
				$from .= '`MyFrameTablePre_'.ltrim($value[0]).'` AS `'.$val[1].'` ';
			}else{
				throw new CDbException('[查询错误]在函数中[CBuilder->from]传递参数错误');
			}
		}else if(is_string($value)){	
			$from = '  ';
			$param = func_get_arg(0);
			$param = str_replace('`','',$param);
			$paramArr = explode(',',$param);
			foreach($paramArr as $val){
				if(strpos($val,'.')){
					$from .= '`MyFrameTablePre_'.str_replace('.','`.`',$val).'` ,';
				}else{
					$from .= 'MyFrameTablePre_'.ltrim($val).' ,';
				}
			}
			$from = str_replace('`*`','*',$from);
			
			$from = strtolower($from);
			if(stripos($val,'as')){
				$list = explode('as',$from);
				if(count($list) == 2){
					$from = 'MyFrameTablePre_'.ltrim($list[0]).' AS '.$list[1];
				}
			}
		}
		
		$this->from = substr($from,0,-1);
		return $this;
	}

	
	/**
	 * join
	 */
	public function join(){
		
		$num = func_num_args();
		
		$value = func_get_arg(0);
		$joinTemp = '';
		$joinDefaultType = 'LEFT';
		
		if($num > 1){
			$joinType = func_get_arg(1);
			$joinType = strtolower($joinType);
			if(in_array($joinType,array('left','right','inner'))){
				$joinDefaultType = 	$joinType;
			}
		}
		
		$joinDefaultType = strtoupper($joinDefaultType);
		
		$joinTemp .= ' '.$joinDefaultType.' JOIN ';
		
		$from = '';
		
		if(is_array($value)){
			if(1 == count($value)){
				foreach($value as $key => $val){
					$from .= '`'.$key.'` AS `'.$val.'` ';
				}
			}else if(2 == count($value)){
				$from .= '`'.$value[0].'` AS `'.$val[1].'` ';
			}else{
				throw new CDbException('[查询错误]在函数中[CBuilder->from]传递参数错误');
			}
		}else if(is_string($value)){
			$from = $this->_checkStrToSql(func_get_arg(0));
		}
		
		$joinTemp .= $from;

		$this->joinTemp = substr($joinTemp,0,-1);
	
		return $this;
	}
	
	/**
	 * on
	 */
	public function on(){
		
		$num = func_num_args();
		
		$where = $join = '';
		if($num == 3){
			$whereValue = func_get_arg(2);
			$whereValue = addslashes($whereValue);	
			$where .= substr($this->_checkStrToSql(func_get_arg(0)),0,-1).' '.func_get_arg(1).' '.substr($this->_checkStrToSql(func_get_arg(2)),0,-1);
		}else if($num == 1 && is_array(func_get_arg(0)) ){

			$paramArr = func_get_arg(0);
			$paramNum = count($paramArr);
			$nowNum = 0;
			foreach($paramArr as $key => $val){
				$val = addslashes($val);
				if(strpos($val,'<') || strpos($val,'>') || strpos($val,'=')){
					$where .= $key.$val;
				}else{
					$where .= substr($this->_checkStrToSql($key),0,-1).' = '.substr($this->_checkStrToSql($val),0,-1);
				}
				if($nowNum < $paramNum - 1){
					$where .= ' AND ';
				}
				$nowNum++;
			}
		}else{
			throw new CDbException('[查询错误]在函数中[CBuilder->on]传递参数错误');
		}
		
		if(empty($this->joinTemp)){
			throw new CDbException('[查询错误]函数[CBuilder->on]被设置前需先设置[CBuilder->join]函数');
		}
		
		
		$this->join .= $this->joinTemp.' ON '.$where;

		$this->joinTemp = '';
		
		return $this;
	}
	
	/**
	 * where
	 */
	public function where(){
		

		$num = func_num_args();
		
		$where = $join = '';
		if($num == 3){
			if(is_string(func_get_arg(2))){
				$whereValue = func_get_arg(2);
				$whereValue = addslashes($whereValue);
				$where .= substr($this->_checkStrToSql(func_get_arg(0)),0,-1).' '.func_get_arg(1).' ? ';
				$thisWhereValue = $whereValue;
				array_push($this->whereValue,$thisWhereValue);
			}else if(is_int(func_get_arg(2)) || is_float(func_get_arg(2)) ){
				$whereValue = func_get_arg(2);
				$where .= substr($this->_checkStrToSql(func_get_arg(0)),0,-1).' '.func_get_arg(1).' ? ';
				$thisWhereValue = $whereValue;
				array_push($this->whereValue,$thisWhereValue);
			}else if(is_array(func_get_arg(2))){
				$whereIn = func_get_arg(2);

				//移除重复
				$whereIn = array_unique($whereIn);
				foreach($whereIn as $key => $val){
					if(empty($val) && strlen($val) == 0){
						unset($whereIn[$key]);
					}
				}
				
				foreach($whereIn as $key => $val){
					$whereIn[$key] = addslashes($val);
					$whereInWait[] = ' ? ';
					array_push($this->whereValue,addslashes($val));
				}

				if(count($whereIn) > 0){
					if(count($whereIn) > 1){
						$inList = "(".implode(",",$whereInWait).")";
						$where .= substr($this->_checkStrToSql(func_get_arg(0)),0,-1).' '.func_get_arg(1).' '.$inList;
					}else if(count($whereIn) == 1 && isset($whereIn[0]) ){
						$where .= substr($this->_checkStrToSql(func_get_arg(0)),0,-1).' = ?';
					}	
				}
			}
		}else if($num == 1 && is_array(func_get_arg(0)) ){

			//以数组传递
			$paramArr = func_get_arg(0);
			
			if(empty($paramArr)){
				return $this;
			}
			
			$paramNum = count($paramArr);
			$nowNum = 0;
			foreach($paramArr as $key => $val){
				
				$val = addslashes($val);
				
				if(strpos($val,'<') || strpos($val,'>') || strpos($val,'=')){
					$where .= $key.' ? ';
					array_push($this->whereValue,addslashes($key));
				}else{
					$where .= $key.' = ? ';
					array_push($this->whereValue,$val);
				}
				if($nowNum < $paramNum - 1){
					$where .= ' AND ';
				}
				$nowNum++;
			}
		}else{
			throw new CDbException('[查询错误]在函数中[CBuilder->where]传递参数错误');
		}

		//where为空
		if(empty($where)){
			return $this;
		}
		
		if(empty($this->where)){
			$this->where .= ' WHERE '. $where;
		}else{
			$this->where .= ' AND '.$where;
		}
		
		return $this;
	}
	
	/**
	 * groupBy
	 */
	public function groupBy($val){
		$this->groupBy = ' GROUP BY '.substr($this->_checkStrToSql($val),0,-1);
		return $this;
	}
	
	/**
	 * orderBy
	 */
	public function orderBy($val,$sort = 'DESC'){
		$this->orderBy = ' ORDER BY '.substr($this->_checkStrToSql($val),0,-1).' '.$sort;
		return $this;
	}
	
	/**
	 * limit
	 */
	public function limit(){

		$num = func_num_args();
		
		if($num == 1){
			$this->limit = ' LIMIT '.func_get_arg(0);
		}else if($num == 2){
			$this->limit = ' LIMIT '.func_get_arg(0).','.func_get_arg(1);
		}
		
		return $this;
	}
	
	/**
	 * 执行
	 */
	public function execute(){
		
		//参数重载
		if(func_num_args() == 1 ){
			return $this->_executeParam1(func_get_arg(0));
		}
		
		switch(trim($this->action)){
			
			case 'SELECT':
				$databaseObject = CDatabase::getDatabase($this->configName,false);
				$sql = $this->_sql = $this->_createSelectSQL();
				$rsReult = array();
				$btime = $etime = 0;
				
				//检查缓存
				if(!empty($this->_cache)){				
					//尝试直接从缓存获取
					$cacheData = Cache::getInstance()->get($this->_cache);
					if(null != $cacheData){			
						//查询结果对象
						$resultObject = new CResult();
						$resultObject->setIsMaster(false);
						$resultObject->setSql($sql);
						$resultObject->setIsCache(true);
						$resultObject->setValue($cacheData->asArray());
						$resultObject->setCastTime(($etime > $btime) ? round($etime - $btime,6) : 0);	

						//执行查询后钩子函数
						CHooks::callHooks(HOOKS_EXECUTE_END,$resultObject);
						
						$this->_clearSelf();
						return $cacheData;
					}
				}
				
				try{
					//执行查询前钩子函数
					CHooks::callHooks(HOOKS_EXECUTE_BEFORE,$this);
				
					$btime = microtime(true);
					$rs = $databaseObject->prepare($this->_sql);

					if(!$rs){
						$errorData = $databaseObject->errorInfo();
						
						//发生SQL错误时 触发钩子
						$dbError = new CDBError();
						$dbError->setSQLErrorCode($errorData[0]);
						$dbError->setDriverErrorCode($errorData[1]);
						$dbError->setErrorMessage($errorData[2]);
						$dbError->setSql($this->_sql);
						CHooks::callHooks(HOOKS_EXECUTE_ERROR,$dbError);

						throw new PDOException('['.$errorData[1].'] '.$errorData[2].' with SQL ['.$this->_sql.']');
					}
					$rs->execute($this->whereValue);
					$rs->setFetchMode(PDO::FETCH_OBJ);
					$rsReult = $rs->fetchAll();
					$etime = microtime(true);
					
				}catch(PDOException $pdoException){
	
					throw new CDbException($pdoException->getMessage());
				}
				
				//查询结果对象
				$resultObject = new CResult();
				$resultObject->setIsMaster(false);
				$resultObject->setSql($sql);
				$resultObject->setWhereValue($this->whereValue);
				$resultObject->setValue($rsReult);
				
				$resultObject->setCastTime(($etime > $btime) ? round($etime - $btime,6) : 0);
				
				//执行查询后钩子函数
				CHooks::callHooks(HOOKS_EXECUTE_END,$resultObject);
				
				//缓存结果
				if(!empty($this->_cache)){
					$cacheData = Cache::getInstance()->set($this->_cache,$resultObject,$this->_cacheTime);
				}
				
				//清理
				$this->_clearSelf();
				return $resultObject;
				
				break;
				
				
			case 'INSERT INTO':
			    // 强制主库操作新增
			    $this->isMaster = true;
				$databaseObject = CDatabase::getDatabase($this->configName,$this->isMaster);
				$sql = $this->_sql = $this->_createInsertSQL();
				//执行查询前钩子函数
				CHooks::callHooks(HOOKS_EXECUTE_BEFORE,$this);
		
				$insertStatus = $databaseObject->exec($this->_sql);
				if($insertStatus > 0){
					
					//执行操作
					$execObject = new CExec();
					$execObject->setSql($sql);
					$execObject->setRow($insertStatus);
					$execObject->setStatus(true);
					$execObject->setLastInsertId($databaseObject->lastInsertId());
					
					//执行查询后钩子函数
					CHooks::callHooks(HOOKS_EXECUTE_END,$execObject);
					
					//清理
					$this->_clearSelf();
					return $execObject;
				}else{
					//发生错误
					$errorData = $databaseObject->errorInfo();
					
					//发生SQL错误时 触发钩子
					$dbError = new CDBError();
					$dbError->setSQLErrorCode($errorData[0]);
					$dbError->setDriverErrorCode($errorData[1]);
					$dbError->setErrorMessage($errorData[2]);
					$dbError->setSql($this->_sql);
					CHooks::callHooks(HOOKS_EXECUTE_ERROR,$dbError);
				
					throw new CDbException('['.$errorData[1].'] '.$errorData[2].' with SQL ['.$this->_sql.']');
				}
				//清理
				$this->_clearSelf();
				break;
				
			case 'UPDATE':
			    // 强制主库操作更新
			    $this->isMaster = true;
				$databaseObject = CDatabase::getDatabase($this->configName,$this->isMaster);
				$sql = $this->_sql = $this->_createUpdateSQL();
				
				//执行查询前钩子函数
				CHooks::callHooks(HOOKS_EXECUTE_BEFORE,$this);
				
				$rs = $databaseObject->prepare($this->_sql);
				$updateStatus = $rs->execute($this->whereValue);
				if(false === $updateStatus){
			
					$errorData = $databaseObject->errorInfo();
					
					//发生SQL错误时 触发钩子
					$dbError = new CDBError();
					$dbError->setSQLErrorCode($errorData[0]);
					$dbError->setDriverErrorCode($errorData[1]);
					$dbError->setErrorMessage($errorData[2]);
					$dbError->setSql($this->_sql);
					CHooks::callHooks(HOOKS_EXECUTE_ERROR,$dbError);
					
					throw new CDbException('['.$errorData[1].'] '.$errorData[2].' with SQL ['.$this->_sql.']');
					
				}else{
					
					$execObject = new CExec();
					$execObject->setSql($sql);
					$execObject->setRow($updateStatus);
					$execObject->setWhereValue($this->whereValue);
					$execObject->setStatus(true);
					
					//执行查询后钩子函数
					CHooks::callHooks(HOOKS_EXECUTE_END,$execObject);
					
					$this->_clearSelf();
					return $execObject;
				}

				$this->_clearSelf();
				break;
				
			case 'DELETE':
			    // 强制主库操作删除
			    $this->isMaster = true;
				$databaseObject = CDatabase::getDatabase($this->configName,$this->isMaster);
				$sql = $this->_sql = $this->_createDeleteSQL();
				
				//执行查询前钩子函数
				CHooks::callHooks(HOOKS_EXECUTE_BEFORE,$this);
				
//echo '<pre>';print_r($this->whereValue);exit;
				
				$rs = $databaseObject->prepare($this->_sql);
				$deleteStatus = $rs->execute($this->whereValue);
				if(false === $deleteStatus){

					$errorData = $databaseObject->errorInfo();
					
					//发生SQL错误时 触发钩子
					$dbError = new CDBError();
					$dbError->setSQLErrorCode($errorData[0]);
					$dbError->setDriverErrorCode($errorData[1]);
					$dbError->setErrorMessage($errorData[2]);
					$dbError->setSql($this->_sql);
					CHooks::callHooks(HOOKS_EXECUTE_ERROR,$dbError);
					
					throw new CDbException('['.$errorData[1].'] '.$errorData[2].' with SQL ['.$this->_sql.']');
					
				}else{

					$execObject = new CExec();
					$execObject->setSql($sql);
					$execObject->setWhereValue($this->whereValue);
					$execObject->setRow($deleteStatus);
					$execObject->setStatus(true);
					
					//执行查询后钩子函数
					CHooks::callHooks(HOOKS_EXECUTE_END,$execObject);
					
					$this->_clearSelf();
					return $execObject;
				}

				$this->_clearSelf();
				break;
				
			default:
				
				if(!empty($this->prepare)){
					
				}
				
				throw new CDbException('[查询错误]未指定操作方法');
				break;
		}
		
		$this->_clearSelf();
	}
	
	/**
	 * 返回执行的SQL
	 */
	public function getSql(){
		return $this->_sql;
	}
	
	/**
	 * 从缓存读取
	 */
	public function cache($key,$time = 3600){
		
		//记录缓存消息
		$this->_cache = $key;
		$this->_cacheTime = $time;
		
		return $this;
	}
	
	/**
	 * execute 参数重载
	 */
	private function _executeParam1($where){
		
		if(empty($this->prepare)){
			throw new CDbException('[查询错误]在函数中[CBuilder->prepare]传递参数错误');
		}
		
		try{
			$btime = microtime(true);
			$databaseObject = CDatabase::getDatabase($this->configName);
			
			$this->_sql = $this->prepare;
			CHooks::callHooks(HOOKS_EXECUTE_BEFORE,$this);
			
			$prepareObject = $databaseObject->prepare($this->_sql);
			$prepareObject->execute($where);
			$rsReult = $prepareObject->fetchAll();
			$etime = microtime(true);
			
		}catch(PDOException $pdoException){
			throw new CDbException($pdoException->getMessage());
		}
		
		$resultObject = new CResult();
		$resultObject->setSql($this->prepare);
		$resultObject->setValue($rsReult);
		$resultObject->setCastTime(($etime > $btime) ? round($etime - $btime,6) : 0);

		CHooks::callHooks(HOOKS_EXECUTE_END,$resultObject);
		
		//缓存结果
		if(!empty($this->_cache)){
			$cacheData = Cache::getInstance()->set($this->_cache,$resultObject,$this->_cacheTime);
		}
		
		$this->_clearSelf();
		return $resultObject;
	}
	
	/**
	 * 创建SQL
	 */
	private function _createSelectSQL(){
		
		//判断是否缺少必须值
		if(empty($this->from)){
			throw new CDbException('[查询错误]请通过[CBuilder->from]设置表名');
		}

		$sql = '';
		$sql = trim($this->action).' '.$this->distinct.' '.trim($this->cols).' FROM '.trim($this->from).' '.trim($this->join).' '.trim($this->where).' '.trim($this->groupBy).' '.trim($this->orderBy).' '.trim($this->limit);
		$sql = $this->_replaceTablePre($sql);
		return $sql;
	}
	
	/**
	 * 创建SQL
	 */
	private function _createDeleteSQL(){
		
		//判断是否缺少必须值
		if(empty($this->from)){
			throw new CDbException('[查询错误]请通过[CBuilder->from]设置表名');
		}
		
		$sql = '';
		$sql = trim($this->action).' FROM '.trim($this->from).' '.trim($this->where);
		$sql = $this->_replaceTablePre($sql);
		return $sql;
	}
	
	/**
	 * 创建SQL
	 */
	private function _createUpdateSQL(){
		
		//判断是否缺少必须值
		if(empty($this->from)){
			throw new CDbException('[查询错误]请通过[CBuilder->from]设置表名');
		}
		
		$sql = $updateStr = '';
		$value = $this->val;
		if(empty($value)){
			throw new CDbException('[查询错误]在函数中[CBuilder->value]传递参数错误');
		}
		
		foreach($value as $key => $val){
	
			if(false != strpos($key,'+') && (is_int($val) ) ){	

				//处理自增
				$updateStr .= '`'.trim(str_replace('+','',$key)).'` = `'.trim(str_replace('+','',$key)).'` + '.$val.' ,';
			}else if(false != strpos($key,'-') && (is_int($val) ) ){	

				//处理自增
				$updateStr .= '`'.trim(str_replace('-','',$key)).'` = `'.trim(str_replace('-','',$key)).'` - '.$val.' ,';
			}else{
				$updateStr .= '`'.$key.'` = \''.$val.'\' ,';
			}
		}
		
		$updateStr = substr($updateStr,0,-1);
		$sql = trim($this->action).' '.trim($this->from).' SET '.trim($updateStr).' '.trim($this->where);
		$sql = $this->_replaceTablePre($sql);
		return $sql;
	}

	/**
	 * 创建SQL
	 */
	private function _createInsertSQL(){
		
		//判断是否缺少必须值
		if(empty($this->from)){
			throw new CDbException('[查询错误]请通过[CBuilder->from]设置表名');
		}
		
		$sql = $keyStr = $valStr = '';
		$value = $this->val;

		if(empty($value)){
			throw new CDbException('[查询错误]在函数中[CBuilder->value]传递参数错误');
		}
	
		foreach($value as $key => $val){
			if( strlen($val) <= 0){
				continue;
			}

			$keyStr .= '`'.$key.'`,';
			if(is_int($val) || is_float($val) ){
				$valStr .= $val.',';
			}else if(is_string($val)){
				$valStr .= '\''.$val.'\',';
			}else{
				throw new CDbException('[查询错误]在函数中[CBuilder->value]传递参数错误,参数类型智能是(int,float,string)');
			}
		}
		
		$keyStr = substr($keyStr,0,-1);
		$valStr = substr($valStr,0,-1);
	
		$sql = trim($this->action).' '.trim($this->from).'('.$keyStr.') VALUES('.$valStr.')';
		$sql = $this->_replaceTablePre($sql);
		return $sql;
	}
	
	/**
	 * 替换表前缀
	 */
	private function _replaceTablePre($sql){

		if(!isset(CDatabase::$configData[intval($this->isMaster).$this->configName])){
			$sql = str_ireplace('MyFrameTablePre_','',$sql);
			return $sql;
		}
		$dbConfig = CDatabase::$configData[intval($this->isMaster).$this->configName];
		$tablePre = isset($dbConfig['tablePrefix']) ? $dbConfig['tablePrefix'] : '';
		$sql = str_ireplace('MyFrameTablePre_',$tablePre,$sql);
		return $sql;
	}
	
	/**
	 * 清理自身
	 */
	private function _clearSelf(){
			$this->action 
		= $this->from 
		= $this->cols 
		= $this->join 
		= $this->joinTemp 
		= $this->on 
		= $this->where 
		= $this->val 
		= $this->groupBy 
		= $this->orderBy 
		= $this->limit
		= $this->prepare
		= $this->_sql
		= $this->_cache
		= $this->distinct
		= $this->_cacheTime
		= '';
		
		$this->whereValue = array();
	}
	
	/**
	 * 检查由字符串转成SQL
	 */
	private function _checkStrToSql($param){
		$from = '';
		$param = func_get_arg(0);
		$param = str_replace('`','',$param);
		$paramArr = explode(',',$param);
		foreach($paramArr as $val){
			if(strpos($val,'.') && !is_float($val) && false ){
				$from .= '`'.str_replace('.','`.`',$val).'` ,';
			}else{
				$from .= $val.' ,';
			}
		}
		$from = str_replace('`*`','*',$from);
		return $from;
	}
	
	/**
	 * 开启一个事物
	 */
	public function beginTransaction(){

		//使用主库
		$databaseObject = CDatabase::getDatabase($this->configName,true);
		return $databaseObject->beginTransaction();
	}
	
	/**
	 * 提交一个事物
	 */
	public function commit(){
		
		//使用主库
		$databaseObject = CDatabase::getDatabase($this->configName,true);
		return $databaseObject->commit();
	}
	
	/**
	 * 回滚一个事物
	 */
	public function rollback(){
		
		//使用主库
		$databaseObject = CDatabase::getDatabase($this->configName,true);
		return $databaseObject->rollback();
	}
}