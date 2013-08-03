<?php
/*******************************************
 *  �������򵥷�ҳ��
 *  ���ߣ�heiyeluren
 *  ������2007-04-09 15:11
 *  �޸ģ�2007-04-12 17:00
 *******************************************/



/**
 * ���������κ�ʵ�����ݻ����ķ�ҳ��
 *
 * ע�⣺����ҳ�಻������κ����ݼ���Ĳ�����
 *       �����������������ݿ⡢�ļ������顢Socket�������ݡ�Web Service�ȵȻ�ȡ�����ݣ�
 *       �������඼�����ģ�ֻ�ǵ����Ĺ������ǵķ�ҳ��ʾ������������ݲ�����Ҫ�����ڵ��ó���������
 */
class Pager extends ExceptionClass
{
	/**
	 * ���캯��
	 */
	function Pager(){
		
	}

	/**
	 * ��ҳ��ʾ��ʽһ
	 *
	 * @param int $allItemTotal ���м�¼����
	 * @param int $currPageNum ��ǰҳ����
	 * @param int $pageSize  ÿҳ��Ҫ��ʾ��¼������
	 * @param string $pageName  ��ǰҳ��ĵ�ַ, ���Ϊ������ϵͳ�Զ���ȡ,ȱʡΪ��
	 * @param array $getParamList  ҳ������Ҫ���ݵ�URL��������, ������key���������,value�������ֵ
	 * @return string  ��������������ҳHTML����, ����ֱ��ʹ��
	 * @example 
	 * 	echo cps_split_page(100, 2, 10, 'page.php', array('uid'=>1001, 'gid'=>2008));
	 * 	
	 *  ���: [��һҳ]  1<<  [1] [2]  [3]  [4]  [5]  [6]  [7]  [8]  [9]  [10]  >>10 [��һҳ]
	 */
	function pageStyle1($allItemTotal, $currPageNum, $pageSize, $pageName='',  $getParamList = array()){
		if ($allItemTotal == 0) return "";
	
		//ҳ������
		if ($pageName==''){
			$url = $_SERVER['PHP_SELF']."?page=";
		} else {
			$url = $pageName."?page=";
		}
		
		//����
		$urlParamStr = "";
		foreach ($getParamList as $key => $val) {
			$urlParamStr .= "&amp;". $key ."=". $val;
		}
		//������ҳ��
		$pagesNum = ceil($allItemTotal/$pageSize);
		
		//��һҳ��ʾ
		$firstPage = ($currPageNum <= 1) ? $currPageNum ."</b>&lt;&lt;" : "<a href=". $url ."1". $urlParamStr ." title='��1ҳ'>1&lt;&lt;</a>";
		
		//���һҳ��ʾ
		$lastPage = ($currPageNum >= $pagesNum)? "&gt;&gt;". $currPageNum : "<a href=". $url . $pagesNum . $urlParamStr." title='��". $pagesNum ."ҳ'>&gt;&gt;". $pagesNum ."</a>";
		
		//��һҳ��ʾ
		$prePage  = ($currPageNum <= 1) ? "��ҳ" : "<a href=". $url . ($currPageNum-1) . $urlParamStr ." accesskey='p'  title='��һҳ'>[��һҳ]</a>";
		
		//��һҳ��ʾ
		$nextPage = ($currPageNum >= $pagesNum) ? "��ҳ" : "<a href=". $url . ($currPageNum+1) . $urlParamStr ."  title='��һҳ'>[��һҳ]</a>";
		
		//��ҳ��ʾ
		$listNums = "";
		for ($i=($currPageNum-4); $i<($currPageNum+9); $i++) {
			if ($i < 1 || $i > $pagesNum) continue;
			if ($i == $currPageNum) $listNums.= "[".$i."]&nbsp;";
			else $listNums.= "&nbsp;<a href=". $url . $i . $urlParamStr ." title='��". $i ."ҳ'>[". $i ."]</a>&nbsp;";
		}
		
		$returnUrl = $prePage ."&nbsp;&nbsp;". $firstPage ." ". $listNums ."&nbsp;". $lastPage ."&nbsp;". $nextPage;
		
		return $returnUrl;
	}


	/**
	 * ��ҳ��ʾ��ʽ��
	 * 
	 * @param int $allItemTotal ���м�¼����
	 * @param int $currPageNum ��ǰҳ����
	 * @param int $pageSize  ÿҳ��Ҫ��ʾ��¼������
	 * @param string $pageName  ��ǰҳ��ĵ�ַ, ���Ϊ������ϵͳ�Զ���ȡ,ȱʡΪ��
	 * @param array $getParamList  ҳ������Ҫ���ݵ�URL��������, ������key���������,value�������ֵ
	 * @return string  ��������������ҳHTML����, ����ֱ��ʹ��
	 * @example 
	 *   echo pageStyle1(50, 2, 10, 's.php', array('id'=>1, 'name'=>'user'));
	 *
	 *   �������һҳ   1  2  3  4  5   ��һҳ   [2] [GO]
	 */
	function pageStyle2($allItemTotal, $currPageNum, $pageSize, $pageName='',  $getParamList = array()){
		if ($allItemTotal == 0) return "";
	
		//ҳ������
		if ($pageName==''){
			$url = $_SERVER['PHP_SELF']."?page=";
		} else {
			$url = $pageName."?page=";
		}
		
		//����
		$urlParamStr = "";
		foreach ($getParamList as $key => $val) {
			$urlParamStr .= "&amp;". $key ."=". $val;
		}
		//������ҳ��
		$pagesNum = ceil($allItemTotal/$pageSize);
		
		//��һҳ��ʾ
		$prePage  = ($currPageNum <= 1) ? "��һҳ" : "<a href=". $url . ($currPageNum-1) . $urlParamStr ." accesskey='p'  title='��һҳ'>��һҳ</a>";
		
		//��һҳ��ʾ
		$nextPage = ($currPageNum >= $pagesNum) ? "��һҳ" : "<a href=". $url . ($currPageNum+1) . $urlParamStr ."  title='��һҳ'>��һҳ</a>";
		
		//��ҳ��ʾ
		$listNums = "";
		for ($i=($currPageNum-4); $i<($currPageNum+9); $i++) {
			if ($i < 1 || $i > $pagesNum) continue;
			if ($i == $currPageNum) $listNums.= "&nbsp;".$i."&nbsp;";
			else $listNums.= "&nbsp;<a href=". $url . $i . $urlParamStr ." title='��". $i ."ҳ'>". $i ."</a>&nbsp;";
		}
		
		$returnUrl = $prePage ."&nbsp;&nbsp;". $listNums ."&nbsp;&nbsp;". $nextPage;
		$gotoForm = '&nbsp&nbsp; <input type="text" size="2" id="page_input" value="'. $currPageNum .'" /><input type="button" value="Go" onclick="location.href=\''. $url .'\'+document.getElementById(\'page_input\').value+\''. $urlParamStr .'\'" />';
		
		return $returnUrl . $gotoForm;
	}


	/**
	 * ��ҳ��ʾ��ʽ��
	 * 
	 * @param int $allItemTotal ���м�¼����
	 * @param int $currPageNum ��ǰҳ����
	 * @param int $pageSize  ÿҳ��Ҫ��ʾ��¼������
	 * @param string $pageName  ��ǰҳ��ĵ�ַ, ���Ϊ������ϵͳ�Զ���ȡ,ȱʡΪ��
	 * @param array $getParamList  ҳ������Ҫ���ݵ�URL��������, ������key���������,value�������ֵ
	 * @return string  ��������������ҳHTML����, ����ֱ��ʹ��
	 * @example 
	 *   echo pageStyle1(50, 2, 10, 's.php', array('id'=>1, 'name'=>'user'));
	 *
	 *   �������һҳ  ��һҳ
	 */	
	function pageStyle3($allItemTotal, $currPageNum, $pageSize, $pageName='',  $getParamList = array()){
		if ($allItemTotal == 0) return "";
	
		//ҳ������
		if ($pageName==''){
			$url = $_SERVER['PHP_SELF']."?page=";
		} else {
			$url = $pageName."?page=";
		}
		
		//����
		$urlParamStr = "";
		foreach ($getParamList as $key => $val) {
			$urlParamStr .= "&amp;". $key ."=". $val;
		}
		//������ҳ��
		$pagesNum = ceil($allItemTotal/$pageSize);
		
		//��һҳ��ʾ
		$prePage  = ($currPageNum <= 1) ? "��һҳ" : "<a href=". $url . ($currPageNum-1) . $urlParamStr ." accesskey='p'  title='��һҳ'>��һҳ</a>";
		
		//��һҳ��ʾ
		$nextPage = ($currPageNum >= $pagesNum) ? "��һҳ" : "<a href=". $url . ($currPageNum+1) . $urlParamStr ."  title='��һҳ'>��һҳ</a>";
		
		$returnUrl = $prePage ."&nbsp;&nbsp;". $nextPage;		
		return $returnUrl;
	}

}


