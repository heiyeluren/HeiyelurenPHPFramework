<?php
/**
 * 缓存通用类
 *
 * 主要是包装了Memcache、Apc、File Cache的前端抽象层，便于上层随意调用Memcache和Apc缓存
 *
 * 作者: heiyeluren
 * 创建: 2008-03-26
 * 修改: 2008-09-01
 */

//缓存类型为Memcache
define("CACHE_TYPE_MEM",	1);

//缓存类型为APC
define("CACHE_TYPE_APC",	2);

//缓存类型为文件
define("CACHE_TYPE_FILE",	3);

//Memcache缺省端口
define("MEMSERVER_DEFAULT_PORT", 11211);


/**
 * Cache Class Interface (adapter interface)
 *
 */
class Cache {
    
   /**
     * @var object 
     */
    var $cacheObj;
        
    /**
     * construct
     */
    function Cache($cacheType = CACHE_TYPE_MEM, $param = array()){
        switch ($cacheType) {
			//如果param不为空，则应该是一个保存cache数据的文件路径
			case CACHE_TYPE_FILE:
				$this->cacheObj =& new CacheFile($param);
				break;
			//如果是Memcache的情况，必须传递一个数组参数，包含了Memcache主机列表
            case CACHE_TYPE_MEM:
                $this->cacheObj =& new CacheMemcache($param);
                break;
            case CACHE_TYPE_APC:
                $this->cacheObj =& new CacheApc;
                break;
            default:
                $this->cacheObj =& new CacheFile($param);
        }
    }
    

    /**
     * Set variable to cache 
     * 
     * @param $key
     * @param $value
     * @param $flag
     * @param $expire
     * @return bool
     */
    function set($key, $value, $expire = 0) {
        return $this->cacheObj->set($key, $value, $expire);
    }

    /**
     * Fetch variable from cache
     *
     * @param $key
     * @return mixed (data/false/null)
     */
    function get($key) {
        return $this->cacheObj->get($key);
    }

    /**
     * Replace variable by cache
     *
     * @param $key
     * @param $value
     * @return bool
     */
    function replace($key, $value, $expire = 0) {
        return $this->cacheObj->replace($key, $value, $expire);
    }

    /**
     * Delete variable from cache
     *
     * @brief
     * @param $key
     * @return bool
     */
    function remove($key) {
        return $this->cacheObj->remove($key);
    }

    /**
     * Allocate cache key name
     * @brief
     * @param $key
     * @return string
     */
    function getKey($key, $module) {
        return "yk_" . $module ."_". $key;
    }

}




/**
 * Cache APC operate common class
 *
 * @package Cache
 * @subpackage cache apc
 * @author: heiyeluren
 * @created: 2008-03-26
 * @lastModifed: 2008-03-26
 */
class CacheApc {
    
    /**
     * Set variable to apc 
     * 
     * @param $key
     * @param $value
     * @param $flag
     * @param $expire
     * @return bool
     */
    function set($key, $value, $expire = 0) {
        if(empty($key)) {
            return false;
        }
        return apc_store($key, $value, $expire);
    }

    /**
     * Fetch variable from apc
     *
     * @param $key
     * @return false or null
     */
    function get($key) {
        return apc_fetch($key);
    }

    /**
     * Replace variable by apc
     *
     * @param $key
     * @param $value
     * @return bool
     */
    function replace($key, $value, $expire = 0) {
        apc_delete($key);
        return apc_store($key, $value, $expire);
    }

    /**
     * Delete variable from apc
     *
     * @brief
     * @param $key
     * @return bool
     */
    function remove($key) {
        return apc_delete($key);
    }

}



/**
 * Cache Memcache operate common class
 *
 * @package cache
 * @subpackage cache memcache
 * @author: heiyeluren
 * @created: 2008-03-26
 * @lastModifed: 2008-03-26
 */
class CacheMemcache {

	/**
	 * 保存Memcache主机列表二维数组
	 * @var array
	 */
	var $hosts = array();

	/**
	 * 构造函数
	 *
	 * @param array $hosts Memcache 主机列表数组，二维数组，例：
	 *			array(
	 *				array('192.168.0.1', 11211), 
	 *				array('192.168.0.2', 11211), 
	 *				array('192.168.0.3', 11211),
	 *			)
	 *	如果不写设定端口号，则默认为 11211
	 */
	function CacheMemcache($hosts) {
		$this->hosts = $hosts;
	}


    /**
     * Get memcache object
     *
	 * @return object
     */
    function getMemcacheObj($hosts = array()) {
        static $memObj;
        if(!$memObj){
			if (!is_array($this->hosts) || empty($this->hosts)){
				return null;
			}
            $memcache = new Memcache();
            foreach($this->hosts as $host){
                if(isset($host[1])){
                    $memcache->addServer($host[0], $host[1]);
                } else {
                    $memcache->addServer($host[0], MEMSERVER_DEFAULT_PORT);
                }
            }
            $memcache->setCompressThreshold(10000, 0.2);
            $memObj = $memcache;
        }
        return $memObj;
    }

    /**
     * Set variable to memcache 
     * 
     * @param $key
     * @param $value
     * @param $flag
     * @param $expire
     * @return bool
     */
    function set($key, $value, $expire = 0) {
        if(empty($key)) {
            return false;
        }
        $memObj = self::getMemcacheObj();
        return $memObj->set($key, $value, false, $expire);

    }

    /**
     * Fetch variable from memcache
     *
     * @param $key
     * @return false or null
     */
    function get($key) {
        $memObj = self::getMemcacheObj();
        return $memObj->get($key);
    }

    /**
     * Replace variable by memcache
     *
     * @param $key
     * @param $value
     * @return bool
     */
    function replace($key, $value, $expire = 0) {
        $memObj = self::getMemcacheObj();
        return $memObj->replace($key, $value, false, $expire);
    }

    /**
     * Delete variable from memcache
     *
     * @brief
     * @param $key
     * @return bool
     */
    function remove($key) {
        $memObj = self::getMemcacheObj();
        return $memObj->delete($key);
    }

}



/**
 * Cache File operate common class
 *
 * @package Cache
 * @subpackage cache file
 * @author: heiyeluren
 * @created: 2008-9-1
 * @lastModifed: 2008-9-1
 */
class CacheFile {
	
	/**
	 * cache data path
	 * @var string
	 */
	var $cachePath = '/tmp/';
	/**
	 * cache file prefix
	 * @var string
	 */
	var $prefix = 'hfcache_';


	/**
	 * construct
	 * 
	 * @paran string $cachePath save cache data file path
	 */
	function CacheFile($cachePath = ''){
		if (!empty($cacheFile)){
			$this->cachePath = $cachePath;
		}
	}


    /**
     * Set variable to file 
     * 
     * @param $key
     * @param $value
     * @param $flag
     * @param $expire
     * @return bool
     */
    function set($key, $value, $expire = 0) {
		$cacheFile = $this->_getFilePath($key);		
		if (file_exists($cacheFile)){
			$fp = fopen($cacheFile, "r+");
			$line = $this->_readMeta($fp);
			if ($line['expired']==0 || $line['expired'] > time()){
				fclose($fp);
				return false;
			}
		}
		$fp = fopen($cacheFile, "w+");
		$meta = $this->_getMeta($key, $expire);
		$data = $meta . serialize($value);
		fwrite($fp, $data);
		fclose($fp);

		return true;
    }

    /**
     * Fetch variable from memcache
     *
     * @param $key
     * @return false or null
     */
    function get($key) {
		$cacheFile = $this->_getFilePath($key);	
		if (!file_exists($cacheFile)){
			return '';
		}
		$fp = fopen($cacheFile, 'r');
		$meta = $this->_readMeta($fp);
		if ($meta['expired']!=0 && $meta['expired']<time()){
			fclose($fp);
			unlink($cacheFile);
			return '';
		}
		$ret = '';
		$str = '';
		while($str = fread($fp, 8192)){
			$ret .= $str;
		}
		fclose($fp);
		return unserialize($ret);

    }

    /**
     * Replace variable by memcache
     *
     * @param $key
     * @param $value
     * @return bool
     */
    function replace($key, $value, $expire = 0) {
		$cacheFile = $this->_getFilePath($key);		
		$fp = fopen($cacheFile, "w+");
		$meta = $this->_getMeta($key, $expire);
		$data = $meta . serialize($value);
		fwrite($fp, $data);
		fclose($fp);

		return true;
    }

    /**
     * Delete variable from memcache
     *
     * @brief
     * @param $key
     * @return bool
     */
    function remove($key) {
		$cacheFile = $this->_getFilePath($key);		
		if (file_exists($cacheFile)){
			unlink($cacheFile);
		}
		return true;
    }

	/**
	 * Remove all expired cache file
	 *
	 * @return int remove cache file total
	 */
	function flush(){
		$path = $this->cachePath .'/'. $this->prefix .'*';
		$files = glob($path);
		$rm_total = 0;
		if (is_array($files) && !empty($files)){
			foreach($files as $file){
				$fp = fopen($file, "r");
				$meta = $this->_readMeta($fp);
				if ($meta['expired']!=0 && $meta['expired']<time()){
					fclose($fp);
					unlink($file);
					++$rm_total;
				}
			}
		}
		return $rm_total;
	}

	/**
	 * Destory all cache file
	 *
	 * @return bool
	 */
	function destroy(){
		$path = $this->cachePath .'/'. $this->prefix .'*';
		$files = glob($path);
		if (is_array($files) && !empty($files)){
			foreach($files as $file){
				@unlink($file);
			}
		}
		return true;
	}


	/**
	 * Get cache file path
	 *
	 * @param string $key
	 * @return string
	 */
	function _getFilePath($key){
		return $this->savePath .'/'. $this->prefix . md5($key);
	}

	/**
	 * Read cache data file meta information
	 *
	 * @param resource $fp cache file pointer
	 * @return array
	 */
	function _readMeta($fp){
		$line = fgets($fp, 8192);
		$arr = explode("|", trim($line));
		$ret = array(
			"hash"		=> strval($arr[0]),
			"created"	=> intval($arr[1]),
			"expired"	=> intval($arr[2]),
		);
		return $ret;
	}

	/**
	 * Get meta information
	 *
	 * @param string $key
	 * @param $expired expiry time
	 * @return string
	 */
	function _getMeta($key, $expired = 0){
		$e = ( $expired == 0 ? 0 : time() + $expired );
		$line = md5($key) ."|". time() ."|". $e ."\n";
		return $line;
	}

}

