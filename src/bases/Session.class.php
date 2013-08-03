<?php
//===========================================
// Session 操作类
//
// 功能：能够操作基于文件或Memcache的Session类
// 作者: heiyeluren
// 时间: 2008-9-1
//===========================================

include_once("main.class.php");
include_once("Cache.class.php");


//session存储类型为文件
define("SESS_TYPE_FILE",	1);

//session存储类型为Memcache
define("SESS_TYPE_MEM",		2);

class Session 
{
	/**
	 * 缓存对象
	 * @var object
	 */
	var $sess;


	/**
	 * 构造函数
	 *
	 * @param int $sessType session存储类型，参考类常量设置，能够选择文件和Memcache
	 * @param mix $param 使用文件存储的时候，设置次参数能够设置保存Session文件的路径
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
     * 启动Session操作
     *
     * @param int $expireTime - Session失效时间,缺省是0,当浏览器关闭的时候失效, 该值单位是秒
     */
    function start($expireTime = 0){
		$this->sess->start($expireTime);
    }
    
    /**
     * 判断某个Session变量是否注册
     *
     * @param string $varName - 
     * @return bool 存在返回true, 不存在返回false
     */
    function is_registered($varName){
		return $this->sess->is_registered($varName);
    }    
        
    /**
     * 注册一个Session变量
     *
     * @param string $varName - 需要注册成Session的变量名
     * @param mixed $varValue - 注册成Session变量的值
     * @return bool - 该变量名已经存在返回false, 注册成功返回true
     */
    function register($varName, $varValue){
		return $this->sess->register($varName, $varValue);
    }
    
    /**
     * 销毁一个已注册的Session变量
     *
     * @param string $varName - 需要销毁的Session变量名
     * @return bool 销毁成功返回true
     */
    function unregister($varName){
		return $this->sess->unregister($varName);
    }
    
    /**
     * 销毁所有已经注册的Session变量
     *
     * @return 销毁成功返回true
     */
    function destroy(){
		return $this->sess->destroy();
    }
    
    /**
     * 获取一个已注册的Session变量值
     *
     * @param string $varName - Session变量的名称
     * @return mixed - 不存在的变量返回false, 存在变量返回变量值
     */
    function get($varName){
		return $this->sess->get($varName);
    }    
    
    /**
     * 获取所有Session变量
     *
     * @return array - 返回所有已注册的Session变量值
     */
    function getAll(){
        return $this->sess->getAll();
    }
    
    /**
     * 获取当前的Session ID
     *
     * @return string 获取的SessionID
     */
    function getSid(){
        return $this->sess->getSid();
    }	

}





//===========================================
// 类名: MemcacheSession Class
// 功能: 自主实现基于Memcache存储的 Session 功能
// 描述: 这个类就是实现Session的功能, 基本上是通过设置客户端的Cookie来保存SessionID,
//         然后把用户的数据保存在服务器端,最后通过Cookie中的Session Id来确定一个数据是否是用户的, 
//         然后进行相应的数据操作, 目前的缺点是没有垃圾收集功能
//
//        本方式适合Memcache内存方式存储Session数据的方式，同时如果构建分布式的Memcache服务器，
//        能够保存相当多缓存数据，并且适合用户量比较多并发比较大的情况
// 注意: 本类必须要求PHP安装了Memcache扩展, 获取Memcache扩展请访问: http://pecl.php.net
//
// 作者: heiyeluren
// 时间: 2006-12-23
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
     * 构造函数
     *
     * @param bool $isInit - 是否实例化对象的时候启动Session
     */
    function SessionMemcache($isInit = false){
        if ($isInit){
            $this->start();
        }
    }

    //-------------------------
    //   外部方法
    //-------------------------
    
    /**
     * 启动Session操作
     *
     * @param int $expireTime - Session失效时间,缺省是0,当浏览器关闭的时候失效, 该值单位是秒
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
     * 判断某个Session变量是否注册
     *
     * @param string $varName - 
     * @return bool 存在返回true, 不存在返回false
     */
    function is_registered($varName){
        if (!isset($_SESSION[$varName])){
            return false;
        }
        return true;
    }    
        
    /**
     * 注册一个Session变量
     *
     * @param string $varName - 需要注册成Session的变量名
     * @param mixed $varValue - 注册成Session变量的值
     * @return bool - 该变量名已经存在返回false, 注册成功返回true
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
     * 销毁一个已注册的Session变量
     *
     * @param string $varName - 需要销毁的Session变量名
     * @return bool 销毁成功返回true
     */
    function unregister($varName){
        unset($_SESSION[$varName]);
        $this->_saveSession();
        return true;
    }
    
    /**
     * 销毁所有已经注册的Session变量
     *
     * @return 销毁成功返回true
     */
    function destroy(){
        $_SESSION = array();
        $this->_saveSession();
        return true;    
    }
    
    /**
     * 获取一个已注册的Session变量值
     *
     * @param string $varName - Session变量的名称
     * @return mixed - 不存在的变量返回false, 存在变量返回变量值
     */
    function get($varName){
        if (!isset($_SESSION[$varName])){
            return false;
        }
        return $_SESSION[$varName];
    }    
    
    /**
     * 获取所有Session变量
     *
     * @return array - 返回所有已注册的Session变量值
     */
    function getAll(){
        return $_SESSION;
    }
    
    /**
     * 获取当前的Session ID
     *
     * @return string 获取的SessionID
     */
    function getSid(){
        return $this->sessId;
    }

   
    
    //-------------------------
    //   内部接口
    //-------------------------
    
    /**
     * 生成一个Session ID
     *
     * @return string 返回一个32位的Session ID
     */
    function _getId(){
        return md5(uniqid(microtime()));
    }
    
    /**
     * 获取一个保存在Memcache的Session Key
     *
     * @param string $sessId - 是否指定Session ID
     * @return string 获取到的Session Key
     */
    function _getSessKey($sessId = ''){
        $sessKey = ($sessId == '') ? $this->sessKeyPrefix.$this->sessId : $this->sessKeyPrefix.$sessId;
        return $sessKey;
    }    
    /**
     * 检查保存Session数据的路径是否存在
     *
     * @return bool 成功返回true
     */
    function _initMemcacheObj(){
		if (!is_object($this->memObject)){
			$this->memObject = get_cache(CACHE_TYPE_MEM);
		}
        return true;
    }
    
    /**
     * 获取Session文件中的数据
     *
     * @param string $sessId - 需要获取Session数据的SessionId
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
     * 把当前的Session数据保存到Memcache
     *
     * @param string $sessId - Session ID
     * @return 成功返回true
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
     * 显示提示信息
     *
     * @param string $strMessage - 需要显示的信息内容
     * @param bool $isFailed - 是否是失败信息, 缺省是true
     */
    function _showMessage($strMessage, $isFailed = true){
        if ($isFailed){
            exit($strMessage);
        }
        echo $strMessage;
    }    
}




//=======================================
// 类名: FileSession Class
// 功能: 自主实现基于文件存储的 Session 功能
// 描述: 这个类就是实现Session的功能, 基本上是通过设置客户端的Cookie来保存SessionID,
//         然后把用户的数据保存在服务器端,最后通过Cookie中的Session Id来确定一个数据是否是用户的, 
//         然后进行相应的数据操作, 目前的缺点是没有垃圾收集功能
//
//        本方式适合保存在普通文件、共享内存(SHM)、NFS服务器等基于文件存储的方式，推荐保存在共享 
//        内存当中，因为共享内存存取效率比较高，但是空间比较小，重启后就销毁了
//
// 作者: heiyeluren
// 时间: 2006-12-22
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
     * 构造函数
     *
     * @param bool $isInit - 是否实例化对象的时候启动Session
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
    //   外部方法
    //-------------------------
    
    /**
     * 启动Session操作
     *
     * @param int $expireTime - Session失效时间,缺省是0,当浏览器关闭的时候失效, 该值单位是秒
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
     * 判断某个Session变量是否注册
     *
     * @param string $varName - 
     * @return bool 存在返回true, 不存在返回false
     */
    function is_registered($varName){
        if (!isset($_SESSION[$varName])){
            return false;
        }
        return true;
    }    
        
    /**
     * 注册一个Session变量
     *
     * @param string $varName - 需要注册成Session的变量名
     * @param mixed $varValue - 注册成Session变量的值
     * @return bool - 该变量名已经存在返回false, 注册成功返回true
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
     * 销毁一个已注册的Session变量
     *
     * @param string $varName - 需要销毁的Session变量名
     * @return bool 销毁成功返回true
     */
    function unregister($varName){
        unset($_SESSION[$varName]);
        $this->_writeFile();
        return true;
    }
    
    /**
     * 销毁所有已经注册的Session变量
     *
     * @return 销毁成功返回true
     */
    function destroy(){
        $_SESSION = array();
        $this->_writeFile();
        return true;    
    }
    
    /**
     * 获取一个已注册的Session变量值
     *
     * @param string $varName - Session变量的名称
     * @return mixed - 不存在的变量返回false, 存在变量返回变量值
     */
    function get($varName){
        if (!isset($_SESSION[$varName])){
            return false;
        }
        return $_SESSION[$varName];
    }    
    
    /**
     * 获取所有Session变量
     *
     * @return array - 返回所有已注册的Session变量值
     */
    function getAll(){
        return $_SESSION;
    }
    
    /**
     * 获取当前的Session ID
     *
     * @return string 获取的SessionID
     */
    function getSid(){
        return $this->sessId;
    }

    /**
     * 获取服务器端保存的Session数据的路径
     *
     * @return string 保存Session的路径
     */
    function getSavePath(){
        return $this->sessSavePath;
    }
    
    /**
     * 设置保存Session数据的路径
     *
     * @param string $savePath - 需要保存Session数据的绝对路径
     */
    function setSavePath($savePath){
        $this->sessSavePath = $savePath;
    }    
    
    
    //-------------------------
    //   内部接口
    //-------------------------
    
    /**
     * 生成一个Session ID
     *
     * @return string 返回一个32位的Session ID
     */
    function _getId(){
        return md5(uniqid(microtime()));
    }
    
    /**
     * 检查保存Session数据的路径是否存在
     *
     * @return bool 成功返回true
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
     * 获取Session文件中的数据
     *
     * @param string $sessId - 需要获取Session数据的SessionId
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
     * 把当前的Session数据写入到数据文件
     *
     * @param string $sessId - Session ID
     * @return 成功返回true
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
     * 显示提示信息
     *
     * @param string $strMessage - 需要显示的信息内容
     * @param bool $isFailed - 是否是失败信息, 缺省是true
     */
    function _showMessage($strMessage, $isFailed = true){
        if ($isFailed){
            exit($strMessage);
        }
        echo $strMessage;
    }    
}

