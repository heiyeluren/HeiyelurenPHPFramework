<?php
/*******************************************
 *  ���������ݿ��ȡ������
 *  ���ߣ�heiyeluren
 *  ������2007-04-03 14:11
 *  �޸ģ�2007-04-10 15:21
 *******************************************/


//��ʾ���볣��
define("__DB_OK", 1);
define("__DB_ERR_NOT_RESULT", 0);
define("__DB_ERR_ERROR", -1);
define("__DB_ERR_DSN_NOT_CONFIG", -2);
define("__DB_ERR_QUERY_FAILED", -3);
define("__DB_ERR_CONNECT_FAILED", -4);
define("__DB_ERR_SELECT_DB_FAILED", -5);
define("__DB_ERR_SQL_ERROR", -6);
define("__DB_ERR_NOT_LINK", -7);


//���������쳣������
include_once("Exception.class.php");


/**
 * ���ݿ���ʲ����ࣨMySQL��
 *
 * �������������ݿ��ȡ����
 */
class DB extends ExceptionClass
{
	/**
	 * ������Ϣ
	 * @var array
	 */
	var $dbDsn = array();

    /**
     * ���ӱ�ʶ
     * @var resource
     */
    var $dbLink;

    /**
     * ���ݿ��ѯ���
     * @var string
     */
    var $dbSql;

    /**
     * ��ѯ���
     * @var resource
     */
    var $dbResult;

    /**
     * ��ѯ��¼��
     * @var array
     */
    var $dbRecord;

    /**
     * ���ݿ��ַ���
     * @var string
     */
    var $dbCharset = 'GBK';

	/**
	 * MySQL�汾��
	 */
	 var $dbVersion = '5.0';

	 /**
	  * �Ƿ�Ҫ�����ַ���
	  */
	 var $isSetCharset = true;

    /**
     * ���ݿ�������ȡ��ʽ
     * @var int
     */
    var $fetchMode = MYSQL_ASSOC;

    /**
     * ��־����·��
     * @var string
     */
    var $logPath = '/tmp/mysql_error_log';
    
    /**
     * �Ƿ��¼SQL��ѯʧ�ܵ�SQL��־,ȱʡ��false
     * @var bool
     */
    var $isLog = false;

    /**
     * �Ƿ���SQL��ѯ�����ʱ����ʾ��������ֹ�ű�ִ��,ȱʡ��true
     *
     * @var bool
     */
    var $isError = true;
    
    
    //--------------------------
	//
    //       �ڲ��ӿ�
	//
    //--------------------------
    /**
     * ���캯��
     * 
     * @param string $dbHost ��������
     * @param string $dbUser �����û�
     * @param string $dbPasswd ���ݿ�����
     * @param string $dbName ���ݿ�
     * @param bool $isPconnect �Ƿ�����,Ĭ���Ƿ�
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
     * �������ݿ�
     *
     * @param string $db_host  ���ݿ�������ַ,����:localhost,���� localhost:3306
     * @param string $db_user �������ݿ���û�
     * @param string $db_passwd �û�����
     * @param string $db_name ���ݿ�����
     * @param boo $is_pconnect �Ƿ�ʹ�ó�����
     * @return resource ����������Դ��ʶ��
     */
    function connect(){
		if (!is_array($this->dbDsn) || empty($this->dbDsn)){
			return self::raiseError($this->getMessage(__DB_ERR_DSN_NOT_CONFIG), __DB_ERR_DSN_NOT_CONFIG, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}

		//�Ƿ�����
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

		//�����Ҫ�����ַ���
		if ($this->isSetCharset){
			$this->dbVersion = ($this->dbVersion!='' ? $this->dbVersion : $this->getOne("SELECT VERSION()"));
			if ($this->dbCharset!='' && preg_match("/^(5.|4.1)/", $this->dbVersion)){
				$this->query("SET NAMES '".$this->dbCharset."'", $this->dbLink);
			}        
		}
        return $this->dbLink;
    }

    /**
     * �ر����ݿ�����
     *
     * @return bool �Ƿ�ɹ��ر�����
     */
    function disconnect(){
        $ret = @mysql_close($this->dbLink);
        $this->dbLink = null;
        return $ret;
    }

	/**
	 * ����ѡ�����ݿ�
	 *
	 * @param string $dbName ����ѡ��һ�����ݿ�
	 * @return mixed ѡ���ɹ�����true, ʧ�ܷ��ش������
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
     * ��ѯ�����ĵײ�ӿ�
     *
     * @param string $sql Ҫִ�в�ѯ��SQL���
     * @return bool ִ�гɹ�����true,ʧ�ܷ���false
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
     * ���ò�ѯ���������������
     *
     * @param int $modeType ���ò�ѯ�����������,1Ϊ�����������������ж���,2Ϊʹ�ù�������,3Ϊʹ����������
     */
    function setFetchMode($modeType){
        switch ($modeType){
            case 1:    //���������͹�����������
                $this->fetchMode = MYSQL_BOTH;
                break;
            case 2:    //ʹ�ù�������
                $this->fetchMode = MYSQL_ASSOC;
                break;
            case 3: //ʹ����������
                $this->fetchMode = MYSQL_NUM;
                break;
            default://ȱʡʹ�ù�������
                $this->fetchMode = MYSQL_ASSOC;
        }
    }
    
    /**
     * �������ݿ�ͻ�����ȡ��������ַ�����
     *
     * @param string $charset ������ַ���,���� UTF8,GBK֮���,ȱʡ��GBK
     */
    function setCharset($charset){
        if ($charset != ''){
            $this->dbCharset = $charset;
        }
    }
    
    /**
     * ������־�洢·��
     *
     * @param string $log_path ��־·��,�ñ����ǿ�д��
     */
    function setLogPath($log_path){
        if ($log_path != ''){
            $this->logPath = $log_path;
        }
    }
    
    /**
     * дSQLִ����־
     *
     * @param string $sql ��ѯ��SQL���
     * @param string $file ��ǰִ�в�ѯ���ļ�
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
     * ��ʾ������SQLִ�д���Ĵ�����Ϣ
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
	 * ��ȡMySQL������Ϣ
	 *
	 * @return string ���ػ�ȡ��MySQL����źʹ����ַ�����Ϣ
	 */
	function getDBError(){
		return "[".mysql_errno($this->dbLink) ."] ". mysql_error($this->dbLink);
	}


	//--------------------------
	//
    //       ���ݻ�ȡ�ӿ�
	//
    //--------------------------
    /**
     * ��ȡSQLִ�е�ȫ�������(��ά����)
     *
     * @param string $sql ��Ҫִ�в�ѯ��SQL���
     * @return �ɹ����ز�ѯ����Ķ�ά����,ʧ�ܷ���false
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
     * ��ȡ���м�¼(һά����)
     *
     * @param string $sql ��Ҫִ�в�ѯ��SQL���
     * @return �ɹ����ؽ����¼��һά����,ʧ�ܷ���false
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
     * ��ȡһ������(һά����)
     *
     * @param string $sql ��Ҫ��ȡ���ַ���
     * @param string $field ��Ҫ��ȡ����,�����ָ��,Ĭ���ǵ�һ��
     * @return �ɹ�������ȡ�Ľ����¼��һά����,ʧ�ܷ���false
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
     * ��ȡһ������(��������)
     *
     * @param string $sql ��Ҫִ�в�ѯ��SQL
     * @return �ɹ����ػ�ȡ��һ������,ʧ�ܷ���false
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
     * ��ȡָ�����������ļ�¼
     *
     * @param string $table ����(���ʵ����ݱ�)
     * @param string $field �ֶ�(Ҫ��ȡ���ֶ�)
     * @param string $where ����(��ȡ��¼���������,������WHERE,Ĭ��Ϊ��)
     * @param string $order ����(����ʲô�ֶ�����,������ORDER BY,Ĭ��Ϊ��)
     * @param string $limit ���Ƽ�¼(��Ҫ��ȡ���ټ�¼,������LIMIT,Ĭ��Ϊ��)
     * @param bool $single �Ƿ�ֻ��ȡ������¼(�ǵ���getRow����getAll,Ĭ����false,������getAll)
     * @return �ɹ����ؼ�¼�����������,ʧ�ܷ���false
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
     * ��ȡָ����������ļ�¼(��getRecored����)
     *
     * @param string $table ����(���ʵ����ݱ�)
     * @param string $field �ֶ�(Ҫ��ȡ���ֶ�)
     * @param string $where ����(��ȡ��¼���������,������WHERE,Ĭ��Ϊ��)
     * @param array $order_arr ��������(��ʽ������: array('id'=>true), ��ô���ǰ���IDΪ˳������, array('id'=>false), ���ǰ���ID��������)
     * @param array $limit_arr ��ȡ���ݵ���������()
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
     * ��ȡָ�������ļ�¼
     *
     * @param string $table ����
     * @param int $start_pos ��ʼ��¼
     * @param int $offset ƫ����
     * @param string $field �ֶ���
     * @param string $where ����(��ȡ��¼���������,������WHERE,Ĭ��Ϊ��)
     * @param string $order ����(����ʲô�ֶ�����,������ORDER BY,Ĭ��Ϊ��)
     * @return �ɹ����ذ�����¼�Ķ�ά����,ʧ�ܷ���false
     */
    function getLimitRecord($table, $start_pos, $offset, $field='*', $where='', $oder=''){
        $sql = "SELECT $field FROM $table";
        $sql .= trim($where)!='' ? " WHERE $where " : $where;
        $sql .= trim($order)!='' ? " ORDER BY $order" : $order;
        $sql .= "LIMIT $start_pos,$offset";
        return $this->getAll($sql);
    }
    
    /**
     * ��ȡ�����¼
     *
     * @param string $table ����
     * @param string $order_field ��Ҫ������ֶ�(����id)
     * @param string $order_method ����ķ�ʽ(1Ϊ˳��, 2Ϊ����, Ĭ����1)
     * @param string $field ��Ҫ��ȡ���ֶ�(Ĭ����*,���������ֶ�)
     * @param string $where ����(��ȡ��¼���������,������WHERE,Ĭ��Ϊ��)
     * @param string $limit ���Ƽ�¼(��Ҫ��ȡ���ټ�¼,������LIMIT,Ĭ��Ϊ��)
     * @return �ɹ����ؼ�¼�Ķ�ά����,ʧ�ܷ���false
     */
    function getOrderRecord($table, $order_field, $order_method=1, $field='*', $where='', $limit=''){
        //$order_method��ֵΪ1��Ϊ˳��, $order_methodֵΪ2��2������������
        $sql = "SELECT $field FROM $table";
        $sql .= trim($where)!='' ? " WHERE $where " : $where;
        $sql .= " ORDER BY $order_field " . ( $order_method==1 ? "ASC" : "DESC");
        $sql .= trim($limit)!='' ? " LIMIT $limit" : $limit;
        return $this->getAll($sql);
    }
    
    /**
     * ��ҳ��ѯ(���Ʋ�ѯ�ļ�¼����)
     *
     * @param string $sql ��Ҫ��ѯ��SQL���
     * @param int $start_pos ��ʼ��¼������
     * @param int $offset ÿ�ε�ƫ����,��Ҫ��ȡ������
     * @return �ɹ����ػ�ȡ�����¼�Ķ�ά����,ʧ�ܷ���false
     */
    function limitQuery($sql, $start_pos, $offset){
        $start_pos = intval($start_pos);
        $offset = intval($offset);
        $sql = $sql . " LIMIT $start_pos,$offset ";
        return $this->getAll($sql);
    }    
    
    
    //--------------------------
	//
    //     �����ݷ��ز���
	//
    //--------------------------
    /**
     * ִ��ִ�з�Select��ѯ����
     *
     * @param string $sql ��ѯSQL���
     * @return bool  �ɹ�ִ�з���true, ʧ�ܷ���false
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
     * �Զ�ִ�в���(���Insert/Update����)
     *
     * @param string $table ����
     * @param array $field_array �ֶ�����(�����еļ��൱���ֶ���,����ֵ�൱��ֵ, ���� array( 'id' => 100, 'user' => 'heiyeluren')
     * @param int $mode ִ�в�����ģʽ (�ǲ��뻹�Ǹ��²���, 1�ǲ������Insert, 2�Ǹ��²���Update)
     * @param string $where ����Ǹ��²���,�������WHERE������
     * @return bool ִ�гɹ�����true, ʧ�ܷ���false
     */
    function autoExecute($table, $field_array, $mode, $where=''){
        if ($table=='' || !is_array($field_array) || empty($field_array)){
            return false;
        }
        //$modeΪ1�ǲ������(Insert), $modeΪ2�Ǹ��²���
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
	 * �����
	 *
	 * @param string $tblName ��Ҫ�����������
	 * @return mixed �ɹ�����ִ�н����ʧ�ܷ��ش������
	 */
	function lockTable($tblName){
		return $this->query("LOCK TABLES $tblName");
	}

	/**
	 * ����������н���
	 *
	 * @param string $tblName ��Ҫ�����������
	 * @return mixed �ɹ�����ִ�н����ʧ�ܷ��ش������
	 */	
	function unlockTable($tblName){
		return $this->query("UNLOCK TABLES $tblName");
	}

	/**
	 * �����Զ��ύģ��ķ�ʽ�����InnoDB�洢���棩
	 * һ������ǲ���Ҫʹ������ģʽ�������Զ��ύΪ1�������ܹ����InnoDB�洢�����ִ��Ч�ʣ����������ģʽ����ô��ʹ���Զ��ύΪ0
	 *
	 * @param bool $autoCommit �����true�����Զ��ύ��ÿ������SQL֮���Զ�ִ�У�ȱʡΪfalse
	 * @return mixed �ɹ�����true��ʧ�ܷ��ش������
	 */
	function setAutoCommit($autoCommit = false){
		$autoCommit = ( $autoCommit ? 1 : 0 );
		return $this->query("SET AUTOCOMMIT = $autoCommit");
	}

	/**
	 * ��ʼһ��������̣����InnoDB���棬����ʹ�� BEGIN �� START TRANSACTION��
	 * 
	 * @return mixed �ɹ�����true��ʧ�ܷ��ش������
	 */
	function startTransaction(){
		if (self::isError($result = $this->query("BEGIN"))){
			return $this->query("START TRANSACTION");
		}
	}

	/**
	 * �ύһ���������InnoDB�洢���棩
	 *
	 * @return mixed �ɹ�����true��ʧ�ܷ��ش������
	 */
	function commit(){
		if (self::isError($result =  $this->query("COMMIT"))){
			return $result;
		}
		return $this->setAutoCommit( true );
	}
	
	/**
	 * �������󣬻��һ���������InnoDB�洢���棩
	 *
	 * @return mixed �ɹ�����true��ʧ�ܷ��ش������
	 */

	function rollback(){
		if (self::isError($result =  $this->query("ROLLBACK"))){
			return $result;
		}
		return $this->setAutoCommit( true );
	}
    
    
    //--------------------------
	//
    //    ����������ز���
	//
    //--------------------------
    /**
     * ��ȡ���һ�β�ѯ��SQL���
     *
     * @return string �������һ�β�ѯ��SQL���
     */
    function getLastSql(){
        return $this->dbSql;
    }
        
    /**
     * ��ȡ�ϴβ�������ĵ�ID
     *
     * @return int ���û�����ӻ��߲�ѯʧ��,����0, �ɹ�����ID
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
     * ��ȡ��¼������ļ�¼���� (����Select����)
     *
     * @return int �����һ���޽�������߼�¼�����Ϊ��,����0, ���򷵻ؽ��������
     */
    function getNumRows(){
        if (!$this->dbLink){
			return self::raiseError($this->getMessage(__DB_ERR_NOT_LINK), __DB_ERR_NOT_LINK, __CLASS__, __METHOD__, __FILE__, __LINE__);
        }
        return mysql_num_rows($this->dbResult);
    }
    
    /**
     * ��ȡ�ܵ�Ӱ��ļ�¼���� (����Update/Delete/Insert����)
     *
     * @return int ���û�����ӻ���Ӱ���¼Ϊ��, ���򷵻�Ӱ���������
     */
    function getAffectedRows(){
        if (!$this->dbLink){
			return self::raiseError($this->getMessage(__DB_ERR_NOT_LINK), __DB_ERR_NOT_LINK, __CLASS__, __METHOD__, __FILE__, __LINE__);
        }
        return mysql_affected_rows($this->dbLink);
    }

	/**
	 * ��ȡ���ݿ����ʵ�汾��
	 */
	function getVersion(){
		return $this->getOne("SELECT VERSION()");
	}
}

