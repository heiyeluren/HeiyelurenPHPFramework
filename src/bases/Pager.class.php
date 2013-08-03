<?php
/*******************************************
 *  描述：简单分页类
 *  作者：heiyeluren
 *  创建：2007-04-09 15:11
 *  修改：2007-04-12 17:00
 *******************************************/



/**
 * 不依赖于任何实际数据环境的分页类
 *
 * 注意：本分页类不会进行任何数据级别的操作，
 *       忽略数据是来自数据库、文件、数组、Socket网络数据、Web Service等等获取的数据，
 *       本数据类都不关心，只是单纯的关心我们的分页显示结果，具体数据操作需要自行在调用程序中描述
 */
class Pager extends ExceptionClass
{
	/**
	 * 构造函数
	 */
	function Pager(){
		
	}

	/**
	 * 分页显示样式一
	 *
	 * @param int $allItemTotal 所有记录数量
	 * @param int $currPageNum 当前页数量
	 * @param int $pageSize  每页需要显示记录的数量
	 * @param string $pageName  当前页面的地址, 如果为空则由系统自动获取,缺省为空
	 * @param array $getParamList  页面中需要传递的URL参数数组, 数组中key代表变量民,value代表变量值
	 * @return string  返回最后解析出分页HTML代码, 可以直接使用
	 * @example 
	 * 	echo cps_split_page(100, 2, 10, 'page.php', array('uid'=>1001, 'gid'=>2008));
	 * 	
	 *  输出: [上一页]  1<<  [1] [2]  [3]  [4]  [5]  [6]  [7]  [8]  [9]  [10]  >>10 [下一页]
	 */
	function pageStyle1($allItemTotal, $currPageNum, $pageSize, $pageName='',  $getParamList = array()){
		if ($allItemTotal == 0) return "";
	
		//页面名称
		if ($pageName==''){
			$url = $_SERVER['PHP_SELF']."?page=";
		} else {
			$url = $pageName."?page=";
		}
		
		//参数
		$urlParamStr = "";
		foreach ($getParamList as $key => $val) {
			$urlParamStr .= "&amp;". $key ."=". $val;
		}
		//计算总页数
		$pagesNum = ceil($allItemTotal/$pageSize);
		
		//第一页显示
		$firstPage = ($currPageNum <= 1) ? $currPageNum ."</b>&lt;&lt;" : "<a href=". $url ."1". $urlParamStr ." title='第1页'>1&lt;&lt;</a>";
		
		//最后一页显示
		$lastPage = ($currPageNum >= $pagesNum)? "&gt;&gt;". $currPageNum : "<a href=". $url . $pagesNum . $urlParamStr." title='第". $pagesNum ."页'>&gt;&gt;". $pagesNum ."</a>";
		
		//上一页显示
		$prePage  = ($currPageNum <= 1) ? "上页" : "<a href=". $url . ($currPageNum-1) . $urlParamStr ." accesskey='p'  title='上一页'>[上一页]</a>";
		
		//下一页显示
		$nextPage = ($currPageNum >= $pagesNum) ? "下页" : "<a href=". $url . ($currPageNum+1) . $urlParamStr ."  title='下一页'>[下一页]</a>";
		
		//按页显示
		$listNums = "";
		for ($i=($currPageNum-4); $i<($currPageNum+9); $i++) {
			if ($i < 1 || $i > $pagesNum) continue;
			if ($i == $currPageNum) $listNums.= "[".$i."]&nbsp;";
			else $listNums.= "&nbsp;<a href=". $url . $i . $urlParamStr ." title='第". $i ."页'>[". $i ."]</a>&nbsp;";
		}
		
		$returnUrl = $prePage ."&nbsp;&nbsp;". $firstPage ." ". $listNums ."&nbsp;". $lastPage ."&nbsp;". $nextPage;
		
		return $returnUrl;
	}


	/**
	 * 分页显示样式二
	 * 
	 * @param int $allItemTotal 所有记录数量
	 * @param int $currPageNum 当前页数量
	 * @param int $pageSize  每页需要显示记录的数量
	 * @param string $pageName  当前页面的地址, 如果为空则由系统自动获取,缺省为空
	 * @param array $getParamList  页面中需要传递的URL参数数组, 数组中key代表变量民,value代表变量值
	 * @return string  返回最后解析出分页HTML代码, 可以直接使用
	 * @example 
	 *   echo pageStyle1(50, 2, 10, 's.php', array('id'=>1, 'name'=>'user'));
	 *
	 *   输出：上一页   1  2  3  4  5   下一页   [2] [GO]
	 */
	function pageStyle2($allItemTotal, $currPageNum, $pageSize, $pageName='',  $getParamList = array()){
		if ($allItemTotal == 0) return "";
	
		//页面名称
		if ($pageName==''){
			$url = $_SERVER['PHP_SELF']."?page=";
		} else {
			$url = $pageName."?page=";
		}
		
		//参数
		$urlParamStr = "";
		foreach ($getParamList as $key => $val) {
			$urlParamStr .= "&amp;". $key ."=". $val;
		}
		//计算总页数
		$pagesNum = ceil($allItemTotal/$pageSize);
		
		//上一页显示
		$prePage  = ($currPageNum <= 1) ? "上一页" : "<a href=". $url . ($currPageNum-1) . $urlParamStr ." accesskey='p'  title='上一页'>上一页</a>";
		
		//下一页显示
		$nextPage = ($currPageNum >= $pagesNum) ? "下一页" : "<a href=". $url . ($currPageNum+1) . $urlParamStr ."  title='下一页'>下一页</a>";
		
		//按页显示
		$listNums = "";
		for ($i=($currPageNum-4); $i<($currPageNum+9); $i++) {
			if ($i < 1 || $i > $pagesNum) continue;
			if ($i == $currPageNum) $listNums.= "&nbsp;".$i."&nbsp;";
			else $listNums.= "&nbsp;<a href=". $url . $i . $urlParamStr ." title='第". $i ."页'>". $i ."</a>&nbsp;";
		}
		
		$returnUrl = $prePage ."&nbsp;&nbsp;". $listNums ."&nbsp;&nbsp;". $nextPage;
		$gotoForm = '&nbsp&nbsp; <input type="text" size="2" id="page_input" value="'. $currPageNum .'" /><input type="button" value="Go" onclick="location.href=\''. $url .'\'+document.getElementById(\'page_input\').value+\''. $urlParamStr .'\'" />';
		
		return $returnUrl . $gotoForm;
	}


	/**
	 * 分页显示样式三
	 * 
	 * @param int $allItemTotal 所有记录数量
	 * @param int $currPageNum 当前页数量
	 * @param int $pageSize  每页需要显示记录的数量
	 * @param string $pageName  当前页面的地址, 如果为空则由系统自动获取,缺省为空
	 * @param array $getParamList  页面中需要传递的URL参数数组, 数组中key代表变量民,value代表变量值
	 * @return string  返回最后解析出分页HTML代码, 可以直接使用
	 * @example 
	 *   echo pageStyle1(50, 2, 10, 's.php', array('id'=>1, 'name'=>'user'));
	 *
	 *   输出：上一页  下一页
	 */	
	function pageStyle3($allItemTotal, $currPageNum, $pageSize, $pageName='',  $getParamList = array()){
		if ($allItemTotal == 0) return "";
	
		//页面名称
		if ($pageName==''){
			$url = $_SERVER['PHP_SELF']."?page=";
		} else {
			$url = $pageName."?page=";
		}
		
		//参数
		$urlParamStr = "";
		foreach ($getParamList as $key => $val) {
			$urlParamStr .= "&amp;". $key ."=". $val;
		}
		//计算总页数
		$pagesNum = ceil($allItemTotal/$pageSize);
		
		//上一页显示
		$prePage  = ($currPageNum <= 1) ? "上一页" : "<a href=". $url . ($currPageNum-1) . $urlParamStr ." accesskey='p'  title='上一页'>上一页</a>";
		
		//下一页显示
		$nextPage = ($currPageNum >= $pagesNum) ? "下一页" : "<a href=". $url . ($currPageNum+1) . $urlParamStr ."  title='下一页'>下一页</a>";
		
		$returnUrl = $prePage ."&nbsp;&nbsp;". $nextPage;		
		return $returnUrl;
	}

}


