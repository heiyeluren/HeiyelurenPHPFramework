<?php
/*******************************************
 *  描述：数据库存取访问类
 *  作者：heiyeluren
 *  创建：2007-04-03 14:11
 *  修改：2007-04-10 15:21
 *******************************************/


//提示代码常量
define("__DB_OK", 1);
define("__DB_ERR_NOT_RESULT", 0);
define("__DB_ERR_ERROR", -1);
define("__DB_ERR_DSN_NOT_CONFIG", -2);
define("__DB_ERR_QUERY_FAILED", -3);
define("__DB_ERR_CONNECT_FAILED", -4);
define("__DB_ERR_SELECT_DB_FAILED", -5);
define("__DB_ERR_SQL_ERROR", -6);
define("__DB_ERR_NOT_LINK", -7);


//包含基础异常处理类
include_once("Exception.class.php");


/**
 * 数据库访问操作类（MySQL）
 *
 * 包含基本的数据库存取方法
 */
class DB extends ExceptionClass
{
	/**
	 * 连接信息
	 * @var array
	 */
	var $dbDsn = array();

    /**
     * 连接标识
     * @var resource
     */
    var $dbLink;

    /**
     * 数据库查询语句
     * @var string
     */
    var $dbSql;

    /**
     * 查询结果
     * @var resource
     */
    var $dbResult;

    /**
     * 查询记录集
     * @var array
     */
    var $dbRecord;

    /**
     * 数据库字符集
     * @var string
     */
    var $dbCharset = 'GBK';

	/**
	 * MySQL版本号
	 */
	 var $dbVersion = '5.0';

	 /**
	  * 是否要设置字符集
	  */
	 var $isSetCharset = true;

    /**
     * 数据库结果集提取方式
     * @var int
     */
    var $fetchMode = MYSQL_ASSOC;

    /**
     * 日志保存路径
     * @var string
     */
    var $logPath = '/tmp/mysql_error_log';
    
    /**
     * 是否记录SQL查询失败的SQL日志,缺省是false
     * @var bool
     */
    var $isLog = false;

    /**
     * 是否在SQL查询出错的时候显示错误并且终止脚本执行,缺省是true
     *
     * @var bool
     */
    var $isError = true;
    
    
    //--------------------------
	//
    //       内部接口
	//
    //--------------------------
    /**
     * 构造函数
     * 
     * @param string $dbHost 连接主机
     * @param string $dbUser 连接用户
     * @param string $dbPasswd 数据库密码
     * @param string $dbName 数据库
     * @param bool $isPconnect 是否长连接,默认是否
     * @return SimpleDB
     */
    function DB($dbHost, $dbUser, $dbPasswd, $dbName, $isPconnect=false){
		$this->dbDsn = array(
			"dbHost"	 => $dbHost,
			"dbUser"	 => $dbUser,
			"dbPasswd"	 => $dbPasswd,
			"dbName"	 => $dbName,
			"isPconnect" => $isPconnect		
		);
    }
    
    /**
     * 连接数据库
     *
     * @param string $db_host  数据库主机地址,例如:localhost,或者 localhost:3306
     * @param string $db_user 连接数据库的用户
     * @param string $db_passwd 用户密码
     * @param string $db_name 数据库名字
     * @param boo $is_pconnect 是否使用长连接
     * @return resource 返回连接资源标识符
     */
    function connect(){
		if (!is_array($this->dbDsn) || empty($this->dbDsn)){
			return self::raiseError($this->getMessage(__DB_ERR_DSN_NOT_CONFIG), __DB_ERR_DSN_NOT_CONFIG, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}

		//是否长连接
        if ($this->dbDsn['isPconnect']){
			$this->dbLink = @mysql_pconnect($this->dbDsn['dbHost'], $this->dbDsn['dbUser'], $this->dbDsn['dbPasswd']);
		} else {
			$this->dbLink = @mysql_connect($this->dbDsn['dbHost'], $this->dbDsn['dbUser'], $this->dbDsn['dbPasswd']);
		}
		if (!$this->dbLink){
			return self::raiseError($this->getMessage(__DB_ERR_CONNECT_FAILED) . $this->dbDsn['dbUser'] ."@". $this->dbDsn['dbHost'], __DB_ERR_CONNECT_FAILED, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		if (!@mysql_select_db($this->dbDsn[dbName], $this->dbLink)){
			return self::raiseError($this->getMessage(__DB_ERR_SELECT_DB_FAILED) . $this->dbDsn['dbUser'] ."@". $this->dbDsn['dbHost'] ."/". $this->dbDsn['dbName'], __DB_ERR_SELECT_DB_FAILED, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}

		//如果需要设置字符集
		if ($this->isSetCharset){
			$this->dbVersion = ($this->dbVersion!='' ? $this->dbVersion : $this->getOne("SELECT VERSION()"));
			if ($this->dbCharset!='' && preg_match("/^(5.|4.1)/", $this->dbVersion)){
				$this->query("SET NAMES '".$this->dbCharset."'", $this->dbLink);
			}        
		}
        return $this->dbLink;
    }

    /**
     * 关闭数据库连接
     *
     * @return bool 是否成功关闭连接
     */
    function disconnect(){
        $ret = @mysql_close($this->dbLink);
        $this->dbLink = null;
        return $ret;
    }

	/**
	 * 重新选定数据库
	 *
	 * @param string $dbName 重新选定一个数据库
	 * @return mixed 选定成功返回true, 失败返回错误对象
	 */
	function selectDB($dbName){
        if (!$this->dbLink || !is_resource($this->dbLink)){
			return self::raiseError($this->getMessage(__DB_ERR_NOT_LINK), __DB_ERR_NOT_LINK, __CLASS__, __METHOD__, __FILE__, __LINE__);
        }
		if (!@mysql_select_db($dbName, $this->dbLink)){
			return self::raiseError($this->getMessage(__DB_ERR_SELECT_DB_FAILED) . $this->dbDsn[dbUser] ."@". $this->dbDsn[dbHost] ."/". $this->dbDsn[dbName], __DB_ERR_SELECT_DB_FAILED, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		return true;
	}

    /**
     * 查询操作的底层接口
     *
     * @param string $sql 要执行查询的SQL语句
     * @return bool 执行成功返回true,失败返回false
     */
    function query($sql){
        if (!$this->dbLink || !is_resource($this->dbLink)){
			return self::raiseError($this->getMessage(__DB_ERR_NOT_LINK), __DB_ERR_NOT_LINK, __CLASS__, __METHOD__, __FILE__, __LINE__);
        }
        $this->dbSql = $sql;
        $this->dbResult = null;
        $this->dbResult = @mysql_query($sql, $this->dbLink);
        if ($this->dbResult === false){
			if ($this->isLog){
                $this->writeLog($sql, __FILE__);
            }
			return self::raiseError($this->getMessage(__DB_ERR_QUERY_FAILED) . $this->getDBError(), __DB_ERR_QUERY_FAILED, __CLASS__, __METHOD__, __FILE__, __LINE__);
        }
        return __DB_OK;    
    }  

    /**
     * 设置查询结果返回数据类型
     *
     * @param int $modeType 设置查询结果返回设置,1为关联索引和数字所有都有,2为使用关联索引,3为使用数字索引
     */
    function setFetchMode($modeType){
        switch ($modeType){
            case 1:    //数字索引和关联索引都有
                $this->fetchMode = MYSQL_BOTH;
                break;
            case 2:    //使用关联索引
                $this->fetchMode = MYSQL_ASSOC;
                break;
            case 3: //使用数字索引
                $this->fetchMode = MYSQL_NUM;
                break;
            default://缺省使用关联索引
                $this->fetchMode = MYSQL_ASSOC;
        }
    }
    
    /**
     * 设置数据库客户端提取结果集的字符编码
     *
     * @param string $charset 编码的字符串,比如 UTF8,GBK之类的,缺省是GBK
     */
    function setCharset($charset){
        if ($charset != ''){
            $this->dbCharset = $charset;
        }
    }
    
    /**
     * 设置日志存储路径
     *
     * @param string $log_path 日志路径,该必须是可写的
     */
    function setLogPath($log_path){
        if ($log_path != ''){
            $this->logPath = $log_path;
        }
    }
    
    /**
     * 写SQL执行日志
     *
     * @param string $sql 查询的SQL语句
     * @param string $file 当前执行查询的文件
     */
    function writeLog($sql, $file){
        if (!file_exists($this->logPath)){
                @mkdir($this->logPath);
        }
        $log_file = $this->logPath ."/mysql_".date("Y-m-d").".log";
        $log_msg = "[".date("Y-m-d H:i:s")."] - ".$file.": ".$sql." ";
        error_log($log_msg, 3, $log_file);                
    }
    
    /**
     * 显示上依次SQL执行错误的错误信息
     */
    function getMessage($msgCode){
		$arrMessage = array(
			__DB_OK						=> 'DB ok',
			__DB_ERR_NOT_RESULT			=> 'DB not result',
			__DB_ERR_ERROR				=> 'unknow error',
			__DB_ERR_DSN_NOT_CONFIG		=> 'DB dsn not configure',
			__DB_ERR_QUERY_FAILED		=> 'DB query failed: ',
			__DB_ERR_CONNECT_FAILED		=> 'Connection db server failed: ',
			__DB_ERR_SELECT_DB_FAILED	=> 'Select db failed: ',
			__DB_ERR_SQL_ERROR			=> 'SQL syntax error',
			__DB_ERR_NOT_LINK			=> 'Not availability db connection',
		);
		if (!array_key_exists($msgCode, $arrMessage)){
			return '';
		}
		return $arrMessage[$msgCode];
    }
    
	/**
	 * 获取MySQL错误信息
	 *
	 * @return string 返回获取的MySQL错误号和错误字符串信息
	 */
	function getDBError(){
		return "[".mysql_errno($this->dbLink) ."] ". mysql_error($this->dbLink);
	}


	//--------------------------
	//
    //       数据获取接口
	//
    //--------------------------
    /**
     * 获取SQL执行的全部结果集(二维数组)
     *
     * @param string $sql 需要执行查询的SQL语句
     * @return 成功返回查询结果的二维数组,失败返回false
     */
    function getAll($sql){
        if (self::isError($query = $this->query($sql))){
            return $query;
        }
        $this->dbRecord = array();
        while ($row = @mysql_fetch_array($this->dbResult, $this->fetchMode)) {
            $this->dbRecord[] = $row;
        }
        @mysql_free_result($this->dbResult);
        if (!is_array($this->dbRecord) || empty($this->dbRecord)){
            return false;
        }
        return $this->dbRecord;
    }
    
    /**
     * 获取单行记录(一维数组)
     *
     * @param string $sql 需要执行查询的SQL语句
     * @return 成功返回结果记录的一维数组,失败返回false
     */
    function getRow($sql){
        if (self::isError($query = $this->query($sql))){
            return $query;
        }
        $this->dbRecord = array();
        $this->dbRecord = @mysql_fetch_array($this->dbResult, $this->fetchMode);
        @mysql_free_result($this->dbResult);
        if (!is_array($this->dbRecord) || empty($this->dbRecord)){
            return false;
        }
        return $this->dbRecord;
    }
    
    /**
     * 获取一列数据(一维数组)
     *
     * @param string $sql 需要获取的字符串
     * @param string $field 需要获取的列,如果不指定,默认是第一列
     * @return 成功返回提取的结果记录的一维数组,失败返回false
     */
    function getCol($sql, $field=''){
        if (self::isError($query = $this->query($sql))){
            return $query;
        }
        $this->dbRecord = array();
        while($row = @mysql_fetch_array($this->dbResult, $this->fetchMode)){
            if (trim($field) == ''){
                $this->dbRecord[] = current($row);
            } else {
                $this->dbRecord[] = $row[$field];
            }
        }
        @mysql_free_result($this->dbResult);
        if (!is_array($this->dbRecord) || empty($this->dbRecord)){
            return false;
        }
        return $this->dbRecord;        
    }
    
    /**
     * 获取一个数据(当条数组)
     *
     * @param string $sql 需要执行查询的SQL
     * @return 成功返回获取的一个数据,失败返回false
     */
    function getOne($sql, $field=''){
        if (self::isError($query = $this->query($sql))){
            return $query;
        }
        $this->dbRecord = array();
        $row = @mysql_fetch_array($this->dbResult, $this->fetchMode);
        @mysql_free_result($this->dbResult);
        if (!is_array($row) || empty($row)){
            return false;
        }
        if (trim($field) != ''){
            $this->dbRecord = $row[$field];
        }else{
            $this->dbRecord = current($row);
        }
        return $this->dbRecord;
    }

    /**
     * 获取指定各种条件的记录
     *
     * @param string $table 表名(访问的数据表)
     * @param string $field 字段(要获取的字段)
     * @param string $where 条件(获取记录的条件语句,不包括WHERE,默认为空)
     * @param string $order 排序(按照什么字段排序,不包括ORDER BY,默认为空)
     * @param string $limit 限制记录(需要提取多少记录,不包括LIMIT,默认为空)
     * @param bool $single 是否只是取单条记录(是调用getRow还是getAll,默认是false,即调用getAll)
     * @return 成功返回记录结果集的数组,失败返回false
     */
    function getRecord($table, $field='*', $where='', $order='', $limit='', $single=false){
        $sql = "SELECT $field FROM $table";
        $sql .= trim($where)!='' ? " WHERE $where " : $where;
        $sql .= trim($order)!='' ? " ORDER BY $order" : $order;
        $sql .= trim($limit)!='' ? " LIMIT $limit" : $limit;
        if ($single){
            return $this->getRow($sql);
        }
        return $this->getAll($sql);
    }
    
    /**
     * 获取指点各种条件的记录(跟getRecored类似)
     *
     * @param string $table 表名(访问的数据表)
     * @param string $field 字段(要获取的字段)
     * @param string $where 条件(获取记录的条件语句,不包括WHERE,默认为空)
     * @param array $order_arr 排序数组(格式类似于: array('id'=>true), 那么就是按照ID为顺序排序, array('id'=>false), 就是按照ID逆序排序)
     * @param array $limit_arr 提取数据的限制数组()
     * @return unknown
     */
    function getSpecifyRecord($table, $field='*', $where='', $order_arr=array(), $limit_arr=array()){
        $sql = "SELECT $field FROM $table";
        $sql .= trim($where)!='' ? " WHERE $where " : $where;
        if (is_array($order_arr) && !empty($order_arr)){
            $arr_key = key($order_arr);
            $sql .= " ORDER BY $arr_key " . ($order_arr[$arr_key] ? "ASC" : "DESC");
        }
        if (is_array($limit_arr) && !empty($limit_arr)){
            $start_post = intval(array_shift($limit_arr));
            $offset = intval(array_shift($limit_arr));
            $sql .= " LIMIT $start_post,$offset";
        }
        return $this->getAll($sql);
    }    
    
    /**
     * 获取指定条数的记录
     *
     * @param string $table 表名
     * @param int $start_pos 开始记录
     * @param int $offset 偏移量
     * @param string $field 字段名
     * @param string $where 条件(获取记录的条件语句,不包括WHERE,默认为空)
     * @param string $order 排序(按照什么字段排序,不包括ORDER BY,默认为空)
     * @return 成功返回包含记录的二维数组,失败返回false
     */
    function getLimitRecord($table, $start_pos, $offset, $field='*', $where='', $oder=''){
        $sql = "SELECT $field FROM $table";
        $sql .= trim($where)!='' ? " WHERE $where " : $where;
        $sql .= trim($order)!='' ? " ORDER BY $order" : $order;
        $sql .= "LIMIT $start_pos,$offset";
        return $this->getAll($sql);
    }
    
    /**
     * 获取排序记录
     *
     * @param string $table 表名
     * @param string $order_field 需要排序的字段(比如id)
     * @param string $order_method 排序的方式(1为顺序, 2为逆序, 默认是1)
     * @param string $field 需要提取的字段(默认是*,就是所有字段)
     * @param string $where 条件(获取记录的条件语句,不包括WHERE,默认为空)
     * @param string $limit 限制记录(需要提取多少记录,不包括LIMIT,默认为空)
     * @return 成功返回记录的二维数组,失败返回false
     */
    function getOrderRecord($table, $order_field, $order_method=1, $field='*', $where='', $limit=''){
        //$order_method的值为1则为顺序, $order_method值为2则2则是逆序排列
        $sql = "SELECT $field FROM $table";
        $sql .= trim($where)!='' ? " WHERE $where " : $where;
        $sql .= " ORDER BY $order_field " . ( $order_method==1 ? "ASC" : "DESC");
        $sql .= trim($limit)!='' ? " LIMIT $limit" : $limit;
        return $this->getAll($sql);
    }
    
    /**
     * 分页查询(限制查询的记录条数)
     *
     * @param string $sql 需要查询的SQL语句
     * @param int $start_pos 开始记录的条数
     * @param int $offset 每次的偏移量,需要获取多少条
     * @return 成功返回获取结果记录的二维数组,失败返回false
     */
    function limitQuery($sql, $start_pos, $offset){
        $start_pos = intval($start_pos);
        $offset = intval($offset);
        $sql = $sql . " LIMIT $start_pos,$offset ";
        return $this->getAll($sql);
    }    
    
    
    //--------------------------
	//
    //     无数据返回操作
	//
    //--------------------------
    /**
     * 执行执行非Select查询操作
     *
     * @param string $sql 查询SQL语句
     * @return bool  成功执行返回true, 失败返回false
     */
    function execute($sql){
        if (self::isError($query = $this->query($sql))){
            return $query;
        }
        $count = @mysql_affected_rows($this->dbLink);
        if ($count <= 0){
            return __DB_ERR_NOT_RESULT;
        }
        return __DB_OK;
    }
    
    /**
     * 自动执行操作(针对Insert/Update操作)
     *
     * @param string $table 表名
     * @param array $field_array 字段数组(数组中的键相当于字段名,数组值相当于值, 类似 array( 'id' => 100, 'user' => 'heiyeluren')
     * @param int $mode 执行操作的模式 (是插入还是更新操作, 1是插入操作Insert, 2是更新操作Update)
     * @param string $where 如果是更新操作,可以添加WHERE的条件
     * @return bool 执行成功返回true, 失败返回false
     */
    function autoExecute($table, $field_array, $mode, $where=''){
        if ($table=='' || !is_array($field_array) || empty($field_array)){
            return false;
        }
        //$mode为1是插入操作(Insert), $mode为2是更新操作
        if ($mode == 1){
            $sql = "INSERT INTO $table SET ";
        }else{
            $sql = "UPDATE $table SET ";
        }
        foreach ($field_array as $key => $value){
            $sql .= "$key='$value',";
        }
        $sql = rtrim($sql, ',');
        if ($mode==2 && $where!=''){
            $sql .= "WHERE $where";
        }
        return $this->execute($sql);
    }
	
	/**
	 * 锁表表
	 *
	 * @param string $tblName 需要锁定表的名称
	 * @return mixed 成功返回执行结果，失败返回错误对象
	 */
	function lockTable($tblName){
		return $this->query("LOCK TABLES $tblName");
	}

	/**
	 * 对锁定表进行解锁
	 *
	 * @param string $tblName 需要锁定表的名称
	 * @return mixed 成功返回执行结果，失败返回错误对象
	 */	
	function unlockTable($tblName){
		return $this->query("UNLOCK TABLES $tblName");
	}

	/**
	 * 设置自动提交模块的方式（针对InnoDB存储引擎）
	 * 一般如果是不需要使用事务模式，建议自动提交为1，这样能够提高InnoDB存储引擎的执行效率，如果是事务模式，那么就使用自动提交为0
	 *
	 * @param bool $autoCommit 如果是true则是自动提交，每次输入SQL之后都自动执行，缺省为false
	 * @return mixed 成功返回true，失败返回错误对象
	 */
	function setAutoCommit($autoCommit = false){
		$autoCommit = ( $autoCommit ? 1 : 0 );
		return $this->query("SET AUTOCOMMIT = $autoCommit");
	}

	/**
	 * 开始一个事务过程（针对InnoDB引擎，兼容使用 BEGIN 和 START TRANSACTION）
	 * 
	 * @return mixed 成功返回true，失败返回错误对象
	 */
	function startTransaction(){
		if (self::isError($result = $this->query("BEGIN"))){
			return $this->query("START TRANSACTION");
		}
	}

	/**
	 * 提交一个事务（针对InnoDB存储引擎）
	 *
	 * @return mixed 成功返回true，失败返回错误对象
	 */
	function commit(){
		if (self::isError($result =  $this->query("COMMIT"))){
			return $result;
		}
		return $this->setAutoCommit( true );
	}
	
	/**
	 * 发生错误，会滚一个事务（针对InnoDB存储引擎）
	 *
	 * @return mixed 成功返回true，失败返回错误对象
	 */

	function rollback(){
		if (self::isError($result =  $this->query("ROLLBACK"))){
			return $result;
		}
		return $this->setAutoCommit( true );
	}
    
    
    //--------------------------
	//
    //    其他数据相关操作
	//
    //--------------------------
    /**
     * 获取最后一次查询的SQL语句
     *
     * @return string 返回最后一次查询的SQL语句
     */
    function getLastSql(){
        return $this->dbSql;
    }
        
    /**
     * 获取上次插入操作的的ID
     *
     * @return int 如果没有连接或者查询失败,返回0, 成功返回ID
     */
    function getLastId(){
        if (!$this->dbLink){
			return self::raiseError($this->getMessage(__DB_ERR_NOT_LINK), __DB_ERR_NOT_LINK, __CLASS__, __METHOD__, __FILE__, __LINE__);
        }
        if (($last_id = mysql_insert_id($this->dbLink)) > 0){
            return $last_id;
        }
        return $this->getOne("SELECT LAST_INSERT_ID()");
    }
    
    /**
     * 获取记录集里面的记录条数 (用于Select操作)
     *
     * @return int 如果上一次无结果集或者记录结果集为空,返回0, 否则返回结果集数量
     */
    function getNumRows(){
        if (!$this->dbLink){
			return self::raiseError($this->getMessage(__DB_ERR_NOT_LINK), __DB_ERR_NOT_LINK, __CLASS__, __METHOD__, __FILE__, __LINE__);
        }
        return mysql_num_rows($this->dbResult);
    }
    
    /**
     * 获取受到影响的记录数量 (用于Update/Delete/Insert操作)
     *
     * @return int 如果没有连接或者影响记录为空, 否则返回影响的行数量
     */
    function getAffectedRows(){
        if (!$this->dbLink){
			return self::raiseError($this->getMessage(__DB_ERR_NOT_LINK), __DB_ERR_NOT_LINK, __CLASS__, __METHOD__, __FILE__, __LINE__);
        }
        return mysql_affected_rows($this->dbLink);
    }

	/**
	 * 获取数据库的真实版本号
	 */
	function getVersion(){
		return $this->getOne("SELECT VERSION()");
	}
}

