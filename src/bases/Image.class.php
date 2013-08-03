<?php
/*******************************************
 *  ����������ͼ������
 *  ���ߣ�heiyeluren <heiyeluren@gmail.com>
 *  ������2007-04-11 16:15
 *  �޸ģ�2007-04-12 17:02
 *******************************************/

//��������
define("__IMAGE_ERROR_NO", -1);

//�����ļ�
include_once("Exception.class.php");


/**
 * ����������ͼ���������
 * ��������;��������֤��ͼ
 */
class Image extends ExceptionClass
{
	/**
	 * ���캯��
	 */
	function Image(){
		
	}

	/**
	  * ��ͼƬ��������ͼ1
	  * @param string $srcFile	Դ�ļ�			
	  * @param string $dstFile	Ŀ���ļ�
	  * @param int $dstW		Ŀ��ͼƬ���		
	  * @param int $dstH		Ŀ���ļ��߶�
	  * @param string $dstFormat	Ŀ���ļ����ɵĸ�ʽ, ��png��jpg���ָ�ʽ
	  * @return ���󷵻ش������
	  */
	function makeThumbPic($srcFile, $dstFile, $dstW, $dstH, $dstFormat="png") {
		//��ͼƬ
		$data = GetImageSize($srcFile, &$info);
		switch ($data[2]){
			case 1:	$im = @ImageCreateFromGIF($srcFile); break;
			case 2:	$im = @imagecreatefromjpeg($srcFile); break;
			case 3:	$im = @ImageCreateFromPNG($srcFile); break;
		}
		if (!$im){
			return self::raiseError("Create image failed", __IMAGE_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}

		
		//�趨ͼƬ��С
		$srcW =	ImageSX($im);
		$srcH =	ImageSY($im);
		$ni   = ImageCreate($dstW,$dstH);
		ImageCopyResized($ni, $im, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

		//����ָ����ʽ��ͼƬ
		if ($dstFormat == "png"){
			imagepng($ni, $dstFile);
		}elseif ($dstFormat == "jpg"){
			ImageJpeg($ni, $dstFile);
		}else{
			imagepng($ni, $dstFile);
		}
	}


	 /**
	  * ��ͼƬ��������ͼ2
	  *
	  * @param string $srcFile	Դ�ļ�			
	  * @param string $dstFile	Ŀ���ļ�
	  * @param int $dstW		Ŀ��ͼƬ���		
	  * @param int $dstH		Ŀ���ļ��߶�
	  * @return ���󷵻ش������
	  */
	function makeThumbImage($sourFile, $targetFile, $width, $height) {
		$data = getimagesize($sourFile);
		$imageInfo["width"] = $data[0];
		$imageInfo["height"]= $data[1];
		$imageInfo["type"] = $data[2];
		$imageInfo["name"] = basename($sourFile);
		$imageInfo["size"] = filesize($sourFile);
		$newName = substr($sourFile, 0, strrpos($sourFile, ".")) . "_thumb.jpg";

		//��ͼƬ
		switch ($imageInfo["type"]){
			case 1:	$img = imagecreatefromgif($sourFile); break;
			case 2: $img = imagecreatefromjpeg($sourFile); break;
			case 3: $img = imagecreatefrompng($sourFile); break;
			default: return 0; break;
		}
		if (!$img){
			return self::raiseError("Create image failed", __IMAGE_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}

		//ͼƬ��С
		$width = ($width > $imageInfo["width"]) ? $imageInfo["width"] : $width;
		$height = ($height > $imageInfo["height"]) ? $imageInfo["height"] : $height;
		$srcW = $imageInfo["width"];
		$srcH = $imageInfo["height"];

		$height = min(round($srcH * $width / $srcW),$height);
		$width = min(round($srcW * $height / $srcH),$width);

		if (function_exists("imagecreatetruecolor")) { //GD2.0.1
			$new = imagecreatetruecolor($width, $height);
			ImageCopyResampled($new, $img, 0, 0, 0, 0, $width, $height, $imageInfo["width"], $imageInfo["height"]);
		}else{
			$new = imagecreate($width, $height);
			ImageCopyResized($new, $img, 0, 0, 0, 0, $width, $height, $imageInfo["width"], $imageInfo["height"]);
		}

		//����ͼƬ
		ImageJPEG($new, $targetFile, 100);
	}


	/**
	 * ����һ����֤��ͼƬ�����ñ�����֮ǰ�����ȵ���createRandomCode������
	 * 
	 * @param int $imgX ͼƬ��X��
	 * @param int $imgY  ͼƬY��
	 * @return void
	 */
	function makeCheckCodeImage($checkCode='', $imgX=65, $imgY=22) {
		//������֤��
		$this->mCheckCode = $checkCode;
		if ($this->mCheckCode == ''){
			$this->createCheckCode();
		}

		//����һ��ͼƬ
		$im = imagecreate($imgX, $imgY); 
		$black = ImageColorAllocate($im, 0, 0, 0);// ������ɫ
		$white = ImageColorAllocate($im, 255, 255, 255); // ǰ����ɫ
		$gray = ImageColorAllocate($im, 200, 200, 200); 
		imagefill($im, 68, 30,$gray); 

		//����֤�����ͼƬ 
		imagestring($im, 5, 8, 3, $this->mCheckCode, $white);

		//����������� 
		for($i=0;$i<200;$i++)
		{ 
			$randcolor = ImageColorallocate($im,rand(0,255),rand(0,255),rand(0,255));
			imagesetpixel($im, rand()%70 , rand()%30 , $randcolor); 
		}
		//���ͼ��
		Header("Content-type: image/PNG");
		ImagePNG($im); 
		ImageDestroy($im); 
	}

	/**
	 * ����һ����֤��
	 *
	 * @param int $length ��֤��ĳ��ȣ�������32λ��ȱʡΪ4λ
	 * @param bool $isUpperCase �Ƿ��Ǵ�д��ȱʡ��Сд
	 * @return ����������ɵ���֤��
	 */
	function createCheckCode($lenght=4, $isUpperCase=false){
		$code = substr(md5(rand()), 0, $lenght);
		$this->mCheckCode = ( $isUpperCase ? strtoupper($code) : strtolower($code) );
		return $this->mCheckCode;
	}


	/* 
	* PHPͼƬˮӡ (ˮӡ֧��ͼƬ������) 
	*  
	* @param string   $groundImage   ����ͼƬ������Ҫ��ˮӡ��ͼƬ����ֻ֧��GIF,JPG,PNG��ʽ�� 
	* @param int      $waterPos     ˮӡλ�ã���10��״̬��0Ϊ���λ�ã� 
	*                   1Ϊ���˾���2Ϊ���˾��У�3Ϊ���˾��ң� 
	*                   4Ϊ�в�����5Ϊ�в����У�6Ϊ�в����ң� 
	*                   7Ϊ�׶˾���8Ϊ�׶˾��У�9Ϊ�׶˾��ң� 
	* @param string   $waterImage     ͼƬˮӡ������Ϊˮӡ��ͼƬ����ֻ֧��GIF,JPG,PNG��ʽ�� 
	* @param string   $waterText     ����ˮӡ������������ΪΪˮӡ��֧��ASCII�룬��֧�����ģ� 
	* @param string   $textFont     ���ִ�С��ֵΪ1��2��3��4��5��Ĭ��Ϊ5�� 
	* @param string   $textColor     ������ɫ��ֵΪʮ��������ɫֵ��Ĭ��Ϊ#FF0000(��ɫ)�� 
	* @return ʧ�ܷ��ش������
	*
	* ע�⣺Support GD 2.0��Support FreeType��GIF Read��GIF Create��JPG ��PNG 
	*     $waterImage �� $waterText ��ò�Ҫͬʱʹ�ã�ѡ����֮һ���ɣ�����ʹ�� $waterImage�� 
	*     ��$waterImage��Чʱ������$waterString��$stringFont��$stringColor������Ч�� 
	*     ��ˮӡ���ͼƬ���ļ����� $groundImage һ���� 
	*/ 
	function makeImageWater($groundImage,$waterPos=0,$waterImage="",$waterText="",$textFont=5,$textColor="#FF0000"){ 

		$isWaterImage = false; 
		$formatMsg = "�ݲ�֧�ָ��ļ���ʽ������ͼƬ���������ͼƬת��ΪGIF��JPG��PNG��ʽ��"; 

		//��ȡˮӡ�ļ� 
		if(!empty($waterImage) && file_exists($waterImage)) { 
			$isWaterImage = TRUE; 
			$water_info = getimagesize($waterImage); 
			$water_w   = $water_info[0]; //ȡ��ˮӡͼƬ�Ŀ� 
			$water_h   = $water_info[1]; //ȡ��ˮӡͼƬ�ĸ� 

			switch($water_info[2]) { //ȡ��ˮӡͼƬ�ĸ�ʽ 
				case 1:$water_im = imagecreatefromgif($waterImage);break; 
				case 2:$water_im = imagecreatefromjpeg($waterImage);break; 
				case 3:$water_im = imagecreatefrompng($waterImage);break; 
				default:die($formatMsg); 
			} 
		} 

		//��ȡ����ͼƬ 
		if(!empty($groundImage) && file_exists($groundImage)) { 
			$ground_info = getimagesize($groundImage); 
			$ground_w   = $ground_info[0]; //ȡ�ñ���ͼƬ�Ŀ� 
			$ground_h   = $ground_info[1]; //ȡ�ñ���ͼƬ�ĸ� 

			switch($ground_info[2]) { //ȡ�ñ���ͼƬ�ĸ�ʽ 
				case 1:$ground_im = imagecreatefromgif($groundImage);break; 
				case 2:$ground_im = imagecreatefromjpeg($groundImage);break; 
				case 3:$ground_im = imagecreatefrompng($groundImage);break; 
				default:die($formatMsg); 
			} 
		} else { 
			return self::raiseError("��Ҫ��ˮӡ��ͼƬ�����ڣ�", __IMAGE_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		} 

		//ˮӡλ�� 
		if($isWaterImage) {//ͼƬˮӡ 
			$w = $water_w; 
			$h = $water_h; 
			$label = "ͼƬ��"; 
		} else {//����ˮӡ 
			$temp = imagettfbbox(ceil($textFont*5),0,"./cour.ttf",$waterText);//ȡ��ʹ�� TrueType ������ı��ķ�Χ 
			$w = $temp[2] - $temp[6]; 
			$h = $temp[3] - $temp[7]; 
			unset($temp); 
			$label = "��������"; 
		} 
		if( ($ground_w<$w) || ($ground_h<$h) ) 	{ 
			return self::raiseError("��Ҫ��ˮӡ��ͼƬ�ĳ��Ȼ��ȱ�ˮӡ".$label."��С���޷�����ˮӡ��", __IMAGE_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		} 
		switch($waterPos) { 
			case 0://��� 
				$posX = rand(0,($ground_w - $w)); 
				$posY = rand(0,($ground_h - $h)); 
				break; 
			case 1://1Ϊ���˾��� 
				$posX = 0; 
				$posY = 0; 
				break; 
			case 2://2Ϊ���˾��� 
				$posX = ($ground_w - $w) / 2; 
				$posY = 0; 
				break; 
			case 3://3Ϊ���˾��� 
				$posX = $ground_w - $w; 
				$posY = 0; 
				break; 
			case 4://4Ϊ�в����� 
				$posX = 0; 
				$posY = ($ground_h - $h) / 2; 
				break; 
			case 5://5Ϊ�в����� 
				$posX = ($ground_w - $w) / 2; 
				$posY = ($ground_h - $h) / 2; 
				break; 
			case 6://6Ϊ�в����� 
				$posX = $ground_w - $w; 
				$posY = ($ground_h - $h) / 2; 
				break; 
			case 7://7Ϊ�׶˾��� 
				$posX = 0; 
				$posY = $ground_h - $h; 
				break; 
			case 8://8Ϊ�׶˾��� 
				$posX = ($ground_w - $w) / 2; 
				$posY = $ground_h - $h; 
				break; 
			case 9://9Ϊ�׶˾��� 
				$posX = $ground_w - $w; 
				$posY = $ground_h - $h; 
				break; 
			default://��� 
				$posX = rand(0,($ground_w - $w)); 
				$posY = rand(0,($ground_h - $h)); 
				break;   
		} 

		//�趨ͼ��Ļ�ɫģʽ 
		imagealphablending($ground_im, true); 

		if($isWaterImage) {//ͼƬˮӡ 
			imagecopy($ground_im, $water_im, $posX, $posY, 0, 0, $water_w,$water_h);//����ˮӡ��Ŀ���ļ�       
		} else {//����ˮӡ 
			if( !empty($textColor) && (strlen($textColor)==7) ) { 
				$R = hexdec(substr($textColor,1,2)); 
				$G = hexdec(substr($textColor,3,2)); 
				$B = hexdec(substr($textColor,5)); 
			} else { 
				return self::raiseError("ˮӡ������ɫ��ʽ����ȷ��", __IMAGE_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
			} 
			imagestring ( $ground_im, $textFont, $posX, $posY, $waterText, imagecolorallocate($ground_im, $R, $G, $B));       
		} 

		//����ˮӡ���ͼƬ 
		@unlink($groundImage); 
		switch($ground_info[2]) {//ȡ�ñ���ͼƬ�ĸ�ʽ 
			case 1: imagegif($ground_im,$groundImage); break; 
			case 2: imagejpeg($ground_im,$groundImage); break; 
			case 3: imagepng($ground_im,$groundImage); break; 
			default: die($errorMsg); 
		} 

		//�ͷ��ڴ� 
		if(isset($water_info)) unset($water_info); 
		if(isset($water_im)) imagedestroy($water_im); 
		unset($ground_info); 
		imagedestroy($ground_im); 
	} 


}



