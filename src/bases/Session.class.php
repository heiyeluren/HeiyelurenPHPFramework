<?php
//===========================================
// Session ������
//
// ���ܣ��ܹ����������ļ���Memcache��Session��
// ����: heiyeluren
// ʱ��: 2008-9-1
//===========================================

include_once("main.class.php");
include_once("Cache.class.php");


//session�洢����Ϊ�ļ�
define("SESS_TYPE_FILE",	1);

//session�洢����ΪMemcache
define("SESS_TYPE_MEM",		2);

class Session 
{
	/**
	 * �������
	 * @var object
	 */
	var $sess;


	/**
	 * ���캯��
	 *
	 * @param int $sessType session�洢���ͣ��ο��ೣ�����ã��ܹ�ѡ���ļ���Memcache
	 * @param mix $param ʹ���ļ��洢��ʱ�����ôβ����ܹ����ñ���Session�ļ���·��
	 */
	function Session($sessType = SESS_TYPE_FILE, $param = ''){
		switch($sessType){
			case SESS_TYPE_FILE:
				$this->sess  =& new SessionFile(true, $param);
				break;
			case SESS_TYPE_MEM:
				$this->sess =& new SessionMemcache(true);
				break;
			default:
				$this->sess  =& new SessionFile(true, $param);
		}
	}

    /**
     * ����Session����
     *
     * @param int $expireTime - SessionʧЧʱ��,ȱʡ��0,��������رյ�ʱ��ʧЧ, ��ֵ��λ����
     */
    function start($expireTime = 0){
		$this->sess->start($expireTime);
    }
    
    /**
     * �ж�ĳ��Session�����Ƿ�ע��
     *
     * @param string $varName - 
     * @return bool ���ڷ���true, �����ڷ���false
     */
    function is_registered($varName){
		return $this->sess->is_registered($varName);
    }    
        
    /**
     * ע��һ��Session����
     *
     * @param string $varName - ��Ҫע���Session�ı�����
     * @param mixed $varValue - ע���Session������ֵ
     * @return bool - �ñ������Ѿ����ڷ���false, ע��ɹ�����true
     */
    function register($varName, $varValue){
		return $this->sess->register($varName, $varValue);
    }
    
    /**
     * ����һ����ע���Session����
     *
     * @param string $varName - ��Ҫ���ٵ�Session������
     * @return bool ���ٳɹ�����true
     */
    function unregister($varName){
		return $this->sess->unregister($varName);
    }
    
    /**
     * ���������Ѿ�ע���Session����
     *
     * @return ���ٳɹ�����true
     */
    function destroy(){
		return $this->sess->destroy();
    }
    
    /**
     * ��ȡһ����ע���Session����ֵ
     *
     * @param string $varName - Session����������
     * @return mixed - �����ڵı�������false, ���ڱ������ر���ֵ
     */
    function get($varName){
		return $this->sess->get($varName);
    }    
    
    /**
     * ��ȡ����Session����
     *
     * @return array - ����������ע���Session����ֵ
     */
    function getAll(){
        return $this->sess->getAll();
    }
    
    /**
     * ��ȡ��ǰ��Session ID
     *
     * @return string ��ȡ��SessionID
     */
    function getSid(){
        return $this->sess->getSid();
    }	

}





//===========================================
// ����: MemcacheSession Class
// ����: ����ʵ�ֻ���Memcache�洢�� Session ����
// ����: ��������ʵ��Session�Ĺ���, ��������ͨ�����ÿͻ��˵�Cookie������SessionID,
//         Ȼ����û������ݱ����ڷ�������,���ͨ��Cookie�е�Session Id��ȷ��һ�������Ƿ����û���, 
//         Ȼ�������Ӧ�����ݲ���, Ŀǰ��ȱ����û�������ռ�����
//
//        ����ʽ�ʺ�Memcache�ڴ淽ʽ�洢Session���ݵķ�ʽ��ͬʱ��������ֲ�ʽ��Memcache��������
//        �ܹ������൱�໺�����ݣ������ʺ��û����Ƚ϶ಢ���Ƚϴ�����
// ע��: �������Ҫ��PHP��װ��Memcache��չ, ��ȡMemcache��չ�����: http://pecl.php.net
//
// ����: heiyeluren
// ʱ��: 2006-12-23
//===========================================
class SessionMemcache
{
    var $sessId                = '';
    var $sessKeyPrefix         = 'sess_';
    var $sessExpireTime        = 86400;
    var $cookieName			   = '__SessHandler';
    var $cookieExpireTime      = '';    
    var $memConfig             = array('host'=>'192.168.0.200', 'port'=>11211);
    var $memObject             = null;    
    
    
    /**
     * ���캯��
     *
     * @param bool $isInit - �Ƿ�ʵ���������ʱ������Session
     */
    function SessionMemcache($isInit = false){
        if ($isInit){
            $this->start();
        }
    }

    //-------------------------
    //   �ⲿ����
    //-------------------------
    
    /**
     * ����Session����
     *
     * @param int $expireTime - SessionʧЧʱ��,ȱʡ��0,��������رյ�ʱ��ʧЧ, ��ֵ��λ����
     */
    function start($expireTime = 0){
        $sessId = $_COOKIE[$this->cookieName];
        if (!$sessId){
            $this->sessId = $this->_getId();
            $this->cookieExpireTime = ($expireTime > 0) ? time() + $expireTime : 0;
            setcookie($this->cookieName, $this->sessId, $this->cookieExpireTime, "/", '');
            $this->_initMemcacheObj();
            $_SESSION = array();
            $this->_saveSession();
        } else {
            $this->sessId = $sessId;
            $_SESSION = $this->_getSession($sessId);
        }        
    }
    
    /**
     * �ж�ĳ��Session�����Ƿ�ע��
     *
     * @param string $varName - 
     * @return bool ���ڷ���true, �����ڷ���false
     */
    function is_registered($varName){
        if (!isset($_SESSION[$varName])){
            return false;
        }
        return true;
    }    
        
    /**
     * ע��һ��Session����
     *
     * @param string $varName - ��Ҫע���Session�ı�����
     * @param mixed $varValue - ע���Session������ֵ
     * @return bool - �ñ������Ѿ����ڷ���false, ע��ɹ�����true
     */
    function register($varName, $varValue){
        if (isset($_SESSION[$varName])){
            return false;
        }
        $_SESSION[$varName] = $varValue;
        $this->_saveSession();
        return true;
    }
    
    /**
     * ����һ����ע���Session����
     *
     * @param string $varName - ��Ҫ���ٵ�Session������
     * @return bool ���ٳɹ�����true
     */
    function unregister($varName){
        unset($_SESSION[$varName]);
        $this->_saveSession();
        return true;
    }
    
    /**
     * ���������Ѿ�ע���Session����
     *
     * @return ���ٳɹ�����true
     */
    function destroy(){
        $_SESSION = array();
        $this->_saveSession();
        return true;    
    }
    
    /**
     * ��ȡһ����ע���Session����ֵ
     *
     * @param string $varName - Session����������
     * @return mixed - �����ڵı�������false, ���ڱ������ر���ֵ
     */
    function get($varName){
        if (!isset($_SESSION[$varName])){
            return false;
        }
        return $_SESSION[$varName];
    }    
    
    /**
     * ��ȡ����Session����
     *
     * @return array - ����������ע���Session����ֵ
     */
    function getAll(){
        return $_SESSION;
    }
    
    /**
     * ��ȡ��ǰ��Session ID
     *
     * @return string ��ȡ��SessionID
     */
    function getSid(){
        return $this->sessId;
    }

   
    
    //-------------------------
    //   �ڲ��ӿ�
    //-------------------------
    
    /**
     * ����һ��Session ID
     *
     * @return string ����һ��32λ��Session ID
     */
    function _getId(){
        return md5(uniqid(microtime()));
    }
    
    /**
     * ��ȡһ��������Memcache��Session Key
     *
     * @param string $sessId - �Ƿ�ָ��Session ID
     * @return string ��ȡ����Session Key
     */
    function _getSessKey($sessId = ''){
        $sessKey = ($sessId == '') ? $this->sessKeyPrefix.$this->sessId : $this->sessKeyPrefix.$sessId;
        return $sessKey;
    }    
    /**
     * ��鱣��Session���ݵ�·���Ƿ����
     *
     * @return bool �ɹ�����true
     */
    function _initMemcacheObj(){
		if (!is_object($this->memObject)){
			$this->memObject = get_cache(CACHE_TYPE_MEM);
		}
        return true;
    }
    
    /**
     * ��ȡSession�ļ��е�����
     *
     * @param string $sessId - ��Ҫ��ȡSession���ݵ�SessionId
     * @return unknown
     */
    function _getSession($sessId = ''){
        $this->_initMemcacheObj();
        $sessKey = $this->_getSessKey($sessId);
        $sessData = $this->memObject->get($sessKey);
        if (!is_array($sessData) || empty($sessData)){
            $this->_showMessage('Failed: Session ID '. $sessKey .' session data not exists');
        }
        return $sessData;
    }
    
    /**
     * �ѵ�ǰ��Session���ݱ��浽Memcache
     *
     * @param string $sessId - Session ID
     * @return �ɹ�����true
     */
    function _saveSession($sessId = ''){
        $this->_initMemcacheObj();
        $sessKey = $this->_getSessKey($sessId);
        if (empty($_SESSION)){
            $ret = @$this->memObject->set($sessKey, $_SESSION, false, $this->sessExpireTime);
        }else{
            $ret = @$this->memObject->replace($sessKey, $_SESSION, false, $this->sessExpireTime);
        }
        if (!$ret){
            $this->_showMessage('Failed: Save sessiont data failed, please check memcache server');
        }
        return true;
    }
    
    /**
     * ��ʾ��ʾ��Ϣ
     *
     * @param string $strMessage - ��Ҫ��ʾ����Ϣ����
     * @param bool $isFailed - �Ƿ���ʧ����Ϣ, ȱʡ��true
     */
    function _showMessage($strMessage, $isFailed = true){
        if ($isFailed){
            exit($strMessage);
        }
        echo $strMessage;
    }    
}




//=======================================
// ����: FileSession Class
// ����: ����ʵ�ֻ����ļ��洢�� Session ����
// ����: ��������ʵ��Session�Ĺ���, ��������ͨ�����ÿͻ��˵�Cookie������SessionID,
//         Ȼ����û������ݱ����ڷ�������,���ͨ��Cookie�е�Session Id��ȷ��һ�������Ƿ����û���, 
//         Ȼ�������Ӧ�����ݲ���, Ŀǰ��ȱ����û�������ռ�����
//
//        ����ʽ�ʺϱ�������ͨ�ļ��������ڴ�(SHM)��NFS�������Ȼ����ļ��洢�ķ�ʽ���Ƽ������ڹ��� 
//        �ڴ浱�У���Ϊ�����ڴ��ȡЧ�ʱȽϸߣ����ǿռ�Ƚ�С���������������
//
// ����: heiyeluren
// ʱ��: 2006-12-22
//=======================================
class FileSession
{
    var $sessId				= '';
    var $sessSavePath		= '/tmp/';
    var $isCreatePath		= true;
    var $sessExpireTime		= '';
    var $sessFilePrefix		= 'sess_';
    var $cookieName			= '__SessHandler';
    
    /**
     * ���캯��
     *
     * @param bool $isInit - �Ƿ�ʵ���������ʱ������Session
     */
    function FileSession($isInit = false, $savePath = ''){
		if ($savePath != ''){
			$this->savePath = $savePath;
		}
        if ($isInit){
            $this->start();
        }
    }

    //-------------------------
    //   �ⲿ����
    //-------------------------
    
    /**
     * ����Session����
     *
     * @param int $expireTime - SessionʧЧʱ��,ȱʡ��0,��������رյ�ʱ��ʧЧ, ��ֵ��λ����
     */
    function start($expireTime = 0){
        $sessId = $_COOKIE[$this->cookieName];
        if (!$sessId){
            if (!$this->_checkSavePath()){
                $this->_showMessage('Session save path '. $this->sessSavePath .' not or create path failed');
            }
            $this->sessId = $this->_getId();
            $this->sessExpireTime = ($expireTime > 0) ? time() + $expireTime : 0;
            setcookie($this->cookieName, $this->sessId, $this->sessExpireTime, "/", '');            
            $_SESSION = array();
            $this->_writeFile();
        } else {
            $this->sessId = $sessId;
            $_SESSION = unserialize($this->_getFile($sessId));
        }        
    }
    
    /**
     * �ж�ĳ��Session�����Ƿ�ע��
     *
     * @param string $varName - 
     * @return bool ���ڷ���true, �����ڷ���false
     */
    function is_registered($varName){
        if (!isset($_SESSION[$varName])){
            return false;
        }
        return true;
    }    
        
    /**
     * ע��һ��Session����
     *
     * @param string $varName - ��Ҫע���Session�ı�����
     * @param mixed $varValue - ע���Session������ֵ
     * @return bool - �ñ������Ѿ����ڷ���false, ע��ɹ�����true
     */
    function register($varName, $varValue){
        if (isset($_SESSION[$varName])){
            return false;
        }
        $_SESSION[$varName] = $varValue;
        $this->_writeFile();
        return true;
    }
    
    /**
     * ����һ����ע���Session����
     *
     * @param string $varName - ��Ҫ���ٵ�Session������
     * @return bool ���ٳɹ�����true
     */
    function unregister($varName){
        unset($_SESSION[$varName]);
        $this->_writeFile();
        return true;
    }
    
    /**
     * ���������Ѿ�ע���Session����
     *
     * @return ���ٳɹ�����true
     */
    function destroy(){
        $_SESSION = array();
        $this->_writeFile();
        return true;    
    }
    
    /**
     * ��ȡһ����ע���Session����ֵ
     *
     * @param string $varName - Session����������
     * @return mixed - �����ڵı�������false, ���ڱ������ر���ֵ
     */
    function get($varName){
        if (!isset($_SESSION[$varName])){
            return false;
        }
        return $_SESSION[$varName];
    }    
    
    /**
     * ��ȡ����Session����
     *
     * @return array - ����������ע���Session����ֵ
     */
    function getAll(){
        return $_SESSION;
    }
    
    /**
     * ��ȡ��ǰ��Session ID
     *
     * @return string ��ȡ��SessionID
     */
    function getSid(){
        return $this->sessId;
    }

    /**
     * ��ȡ�������˱����Session���ݵ�·��
     *
     * @return string ����Session��·��
     */
    function getSavePath(){
        return $this->sessSavePath;
    }
    
    /**
     * ���ñ���Session���ݵ�·��
     *
     * @param string $savePath - ��Ҫ����Session���ݵľ���·��
     */
    function setSavePath($savePath){
        $this->sessSavePath = $savePath;
    }    
    
    
    //-------------------------
    //   �ڲ��ӿ�
    //-------------------------
    
    /**
     * ����һ��Session ID
     *
     * @return string ����һ��32λ��Session ID
     */
    function _getId(){
        return md5(uniqid(microtime()));
    }
    
    /**
     * ��鱣��Session���ݵ�·���Ƿ����
     *
     * @return bool �ɹ�����true
     */
    function _checkSavePath(){
        if (file_exists($this->sessSavePath)){
            return true;
        }
        if (!$this->isCreatePath){
            return false;
        }
        if (!@mkdir($this->sessSavePath)){
            $this->_showMessage('Failed: Session cache path '. $this->sessSavePath .'is not exists, create failed');
        }
        @chmod($this->sessSavePath, 0777);        
        return true;
    }
    
    /**
     * ��ȡSession�ļ��е�����
     *
     * @param string $sessId - ��Ҫ��ȡSession���ݵ�SessionId
     * @return unknown
     */
    function _getFile($sessId = ''){
        $sessId = ($sessId == '') ? $this->sessId : $sessId;
        $sessFile = $this->sessSavePath . $this->sessFilePrefix . $sessId;
        if (!file_exists($sessFile)){
            $this->_showMessage('Failed: Session file '. $sessFile .' not exists');
        }
        return file_get_contents($sessFile);
    }
    
    /**
     * �ѵ�ǰ��Session����д�뵽�����ļ�
     *
     * @param string $sessId - Session ID
     * @return �ɹ�����true
     */
    function _writeFile($sessId = ''){
        $sessId = ($sessId == '') ? $this->sessId : $sessId;
        $sessFile = $this->sessSavePath . $this->sessFilePrefix . $sessId;
        $sessStr = serialize($_SESSION);
        if (!$fp = @fopen($sessFile, "w+")){
            $this->_showMessage('Failed: Open session save file '. $sessFile .' failed');
        }
        if (!@fwrite($fp, $sessStr)){
            $this->_showMessage('Failed: Write session data to '. $sessFile .' failed');
        }
        @fclose($fp);
        return true;
    }
    
    /**
     * ��ʾ��ʾ��Ϣ
     *
     * @param string $strMessage - ��Ҫ��ʾ����Ϣ����
     * @param bool $isFailed - �Ƿ���ʧ����Ϣ, ȱʡ��true
     */
    function _showMessage($strMessage, $isFailed = true){
        if ($isFailed){
            exit($strMessage);
        }
        echo $strMessage;
    }    
}

