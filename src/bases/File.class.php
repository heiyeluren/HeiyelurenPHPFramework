<?php
/*******************************************
 *  描述：基本文件操作类（上传、下载、读写）
 *  作者：heiyeluren 
 *  创建：2007-04-10 09:49
 *  修改：2007-04-11 15:14
 *******************************************/

//错误常量
define("__FILE_ERROR_NO", "-1");

//包含文件
include_once("Exception.class.php");


/**
 * 本程序的主要功能是针对文件的基本操作：读、写、上传、下载、相关属性设置等等
 * 
 * 说明：
 * 本程序来源于开源程序，原作者： Antoine BOU?T <antoinebouet@free.fr> 
 * 主要修改了部分注释和异常处理方式
 */
class File extends ExceptionClass
{
    /**
     * used as the dentifier.
	 *
     * @access public
     * @var string
     */
    var $fileName;
    /**
     * Directory where to find/put the file. Will be appended to fileName
	 *
     * @access public
     * @var string
     */
    var $filePath = "";
    /**
     * File size in Bytes
     * @access public
     * @var numeric
     */  
    var $fileSize;
    /**
     * @access public
     * @var string
     */  
    var $fileOwner;
    /**
     * @access public
     * @var string
     */  
    var $fileGroup;
    /**
     * @access public
     * @var numeric
     */  
    var $filePerm = 0777;
    /**
     * @access public
     * @var string
     */  
    var $extension;
    /**
     * Mime type ( for this version are based on Apache 1.3.27 )
     * @access public
     * @var string
     */  
    var $fileType;
    /**
     * @access public
     * @var numeric
     */  
    var $folderPerm    = 0777;
    /**
     * array of extensions allowed for upload
     * @access public
     * @var array
     */      
    var $allowedFiles = array(".doc", ".xls",".txt",".pdf",".gif",".bmp",".jpg",".jpeg",".zip",".rar",".tgz",".gz",".ppt",".mp3",".wma",".wmv");
    /**
     * array of extensions not allowed for upload.
     * @access public
     * @var array
     */  
    var $disallowedFiles = array(".exe",".bat",".msi",".sh","");
    /**
     * @access private
     * @var string
     */  
    var $_tmpName; 
    /**
     * @access private
     * @var array
     */  
    var $_mimeArray;
    /**
     * @access private
     * @var string
     */  
    var $_errCode;
	/**
	 * @access private
	 * @var resource
	 */
	var $_handler;


	
	//=======================
	//
	//       构造函数
	//
	//=======================

    /**
     * @access Public
     * @param string [$_file] File Name
     */      
    function File( $_file ){
		if (trim($_file) != ''){
			$this->fileName = $_file;
			$this->getExtension();
			$this->getFileType();
		}
    }

	//========================
	// 
	//    单文件操作方法
	//
	//========================

	 /**
	  * Opens the file in the specified mode
	  * http://www.php.net/manual/en/function.fopen.php
	  * Mode by default is 'r' (read only)
	  * Returns 'false' if operation failed
	  *
	  * @param mode The mode in which the file is opened
	  * @return false if the operation failed
	  */
	 function open( $mode = "r" ){
		 if (!self::isReadable()){
			$this->_errCode = 9;
			return self::raiseError($this->getErrMessage(), __FILE_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		 }
		 $this->_handler = fopen( $this->fileName, $mode );
		 $this->_mode = $mode;
		 return $this->_handler;
	 }

	 /**
	  * Closes the stream currently being held by this object
	  *
	  * @return nothing
	  */
	 function close(){
		 if ($this->_handler){
			fclose( $this->_handler );
		 }
	 }


	 /**
	  * Reads bytes from the currently opened file
	  *
	  * @param size Amount of bytes we'd like to read from the file. It is
	  * set to 4096 by default.
	  * @return Returns the read contents
	  */
	 function read( $size = 8192 ){
		 if (!$this->_handler){
			return false;
		 }
		 return( fread( $this->_handler, $size ));
	 }

	 /**
	  * Reads one line file data
	  *
	  * @param size Amount of bytes we'd like to read from the file. It is
	  * set to 4096 by default.
	  * @return Returns the read contents
	  */
	 function readLine( $size = 10240 ){
		 if (!$this->_handler){
			return false;
		 }
		return fgets( $this->_handler, $size);
	 }

	 /**
	  * checks whether we've reached the end of file
	  *
	  * @return True if we reached the end of the file or false otherwise
	  */
	 function eof(){
		 if (!$this->_handler){
			return false;
		 }
		 return feof( $this->_handler );
	 }

	 /**
	  * Writes data to disk
	  *
	  * @param data The data that we'd like to write to disk
	  * @return returns the number of bytes written, or false otherwise
	  */
	 function write( $data ){
		 if (!$this->_handler){
			return false;
		 }
		 return fwrite( $this->_handler, $data );
	 }

	 /**
	  * Writes an array of text lines to the file.
	  *
	  * @param lines The array with the text.
	  * @return Returns true if successful or false otherwise.
	  */
	 function writeLines( $lines ){
		 // truncate the file to remove the old contents
		 $this->truncate();

		 foreach( $lines as $line ) {
			 //print("read: \"".htmlentities($line)."\"<br/>");
			 if( !$this->write( $line, strlen($line))) {
				 return false;
			 }
			 /*else
				print("written: \"".htmlentities($line)."\"<br/>");*/
		 }
		 return true;
	 }


	 /**
	  * truncates the currently opened file to a given length
	  *
	  * @param length Lenght at which we'd like to truncate the file
	  * @return true if successful or false otherwise
	  */
	 function truncate( $length = 0 ){
		 if (!$this->_handler){
			return false;
		 }
		 return ftruncate( $this->_handler, $length );
	 }




	//========================
	// 
	//     组合操作方法
	//
	//========================

	/**
	 * Opens, reads the file and return the content
	 *
	 * @access Public
	 * @return string File Content
	 */
	function readAll( $file = "" ){
		$_filename = $file != "" ? $file : $this->filePath.$this->fileName;
		if (!self::isReadable($_filename)){
			return false;
		}
		if ( phpversion() >= '4.3.0' ){
			return file_get_contents($_filename);
		}
		$fp = fopen($_filename, 'r');
		$_file = "";
		while(!feof($fp)){
			$_file .= fread($fp, 8192);
		}
		fclose($fp);
		return $_file;
	}

	 /**
	  * Reads the whole file and put it into an array, where every position
	  * of the array is a line of the file (new-line characters not
	  * included)
	  *
	  * @return An array where every position is a line from the file.
	  */
	 function readArray( $file = "" ){
		$_filename = $file != "" ? $file : $this->filePath.$this->fileName;
		if (!self::isReadable($_filename)){
			return false;
		}
		 $contents = Array();
		 $contents = file( $_filename );
		 for( $i = 0; $i < count( $contents ); $i++ ){
			 $contents[$i] = rtrim( $contents[$i], "\r\n" );
		 }
		 return $contents;
	 }

    /**
     * write data to a file
	 *
     * @access Public
     * @param string [$_content] data to write into the file
     * @return boolean
     */  
    function writeAll( $_content, $file = "" ){
        $_filename = $file=="" ? $this->filePath.$this->fileName : $file;
		if (!self::isWritable($_filename) || $_content == ""){
			return false;
		}
		if ( phpversion() >= '5.0.0' ){
			return file_put_contents($_filename, $_content);
		}
		$fp = fopen($_filename, "w");
		$size = fwrite($fp, $_content);
        @chmod( $_filename, $this->filePerm);
        @chown( $_filename, $this->fileOwner);

		return $size;
    }


    /**
     * Delete the file
	 *
     * inline {@internal checks the OS php is running on, and execute appropriate command}}
     * @access Public
     * @return string File Content
     */
    function remove( $file = ''){
		$file = $file == '' ? $this->filePath.$this->fileName : $file;
        //if Windows
        if (substr(php_uname(), 0, 7) == "Windows") {
            $_filename  = str_replace( '/', '\\', $file);
            @system( 'del /F "'.$_filename.'"', $_result );
            if( $_result == 0 ){
                return true;
            } else {
                $this->_errCode = 'FILE_DEL'.$_result;
				return self::raiseError($this->getErrMessage(), __FILE_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
            }
        //else unix assumed
        } else {
            @chmod( $file, 0775 );
            return @unlink( $file );
        }
    }


	 /**
	  * renames a file
	  *
	  * http://www.php.net/manual/en/function.rename.php
	  *
	  * This function can be used as static if inFile and outFile are both
	  * not empty. if outFile is empty, then the internal file of the
	  * current object will be used as the input file and the first
	  * parameter of this method will become the destination file name.
	  *
	  * @param inFile Original file
	  * @param outFile Destination file.
	  * @return Returns true if file was renamed ok or false otherwise.
	  */
	  function rename( $inFile, $outFile = null ){
		  // check how many parameters we have
		  if( $outFile == null ) {
			  $outFile = $inFile;
			  $inFile  = $this->fileName;
		  }

		  // Checkt the $inFile and $outFile are the same file or not
		  if ( realpath( dirname( $inFile ) ) == realpath( dirname( $outFile ) ) &&
			   basename( $inFile ) == basename( $outFile ) )
			  return true;

		  // In order to work around the bug in php versions older
		  // than 4.3.3, where rename will not work across different
		  // partitions, this will be a copy and delete of the original file

		  // copy the file to the new location
		  if (!@copy($inFile, $outFile)) {
			  // The copy failed, return false
			  return false;
		  }

		  // Now delete the old file
		  // NOTICE, we are not checking the error here.  It is possible
		  // the the original file will remain and the copy will exist.
		  //
		  // One way to potentially fix this is to look at the result of
		  // unlink, and then delete the copy if unlink returned FALSE,
		  // but this call to unlink could just as easily fail
		  @unlink( $inFile );

		  return true;
	  }

	 /**
	  * copies a file from one place to another.
	  * This method is always static
	  *
	  * @param inFile
	  * @param destFile
	  * @return True if successful or false otherwise
	  * @static
	  */
	 function copy( $inFile, $outFile ){
		 return @copy( $inFile, $outFile );
	 }


    /**
     * Create a directory.
	 *
     * @access Public
     * @param string [$_path] path to locate the directory
     * @param string [$_DirName] name of the directory to create       
     * @return boolean
     */  
    function makeDir($_path, $_DirName){
        if(!file_exists($_path."/".$_DirName)){
            $_oldumask = @umask($this->umask); 
            $_action = @mkdir($_path."/".$_DirName, $this->folderPerm);
            @umask($_oldumask);
            if($_action == true){
                return true;
            } else {
                $this->_errCode = 'DIR03';
				return self::raiseError($this->getErrMessage(), __FILE_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
            }
        } else{
            $this->_errCode = 'DIR04';
			return self::raiseError($this->getErrMessage(), __FILE_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
        }
    }

	 /**
	  * removes a directory, optinally in a recursive fashion
	  *
	  * @param dirName
	  * @param recursive Whether to recurse through all subdirectories that
	  * are within the given one and remove them.
	  * @param onlyFiles If the recursive mode is enabled, setting this to 'true' will
	  * force the method to only remove files but not folders. The directory will not be
	  * removed but all the files included it in (and all subdirectories) will be.
	  * @return True if successful or false otherwise
	  * @static
	  */
	 function removeDir( $dirName, $recursive = false, $onlyFiles = false ){
		// if the directory can't be read, then quit with an error
		if( !File::isReadable( $dirName ) || !File::exists( $dirName )) {
			return false;
		}

		// if it's not a file, let's get out of here and transfer flow
		// to the right place...
		if( !File::isDir( $dirName )) {
			return File::remove( $dirName );
		}

		// Glob::myGlob is easier to use than Glob::glob, specially when
		// we're relying on the native version... This improved version
		// will automatically ignore things like "." and ".." for us,
		// making it much easier!
		$files = Glob::myGlob( $dirName, "*" );
		foreach( $files as $file ) {
			if( File::isDir( $file )) {
				// perform a recursive call if we were allowed to do so
				if( $recursive )
					File::deleteDir( $file, $recursive, $onlyFiles );
			}

			// File::delete can remove empty folders as well as files
			if( File::isReadable( $file ))
				File::remove( $file );			
		}

		// finally, remove the top-level folder but only in case we
		// are supposed to!
		if( !$onlyFiles )
			File::remove( $dirName );

		return true;
	 }


    /**
     * download a file
	 *
     * @access Public
     * @param string [$_content] data to write into the file
     * @return boolean
     */  
    function download(){
        header( "Content-type: ".$this->fileType );
        header( "Content-Length: ".$this->fileSize );
        header( "Content-Disposition: filename=".$this->filePath.$this->fileName );
        header( "Content-Description: Download Data" );
        echo $this->Content;
    }

    /**
     * upload a file
	 *
     * @access Public
     * @param string [$_handler] html file field name
     * @param string [$_rename] new name for the uploaded file. Keep same name if empty (optional)
     * @param boolean [$_OverWrite] Overwrite existing file (Yes/No)
     * @return boolean
     */          
    function upload($_handler, $_rename='', $_OverWrite=false){
        $this->_errCode = 0;
        $this->fileName = $_FILES[$_handler]['name'];
        $this->fileSize = $_FILES[$_handler]['size'];
        $this->fileType = $_FILES[$_handler]['type'];
        $this->_tmpName = $_FILES[$_handler]['tmp_name'];
        $this->getExtension();
        // Check if extension is allowed
        if ( !$this->type_check() ){
            $this->_errCode = 1;
            return self::raiseError($this->getErrMessage(), __FILE_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
        }
        //set the name for the uploaded file
        if($_rename){
            $_filename = $_rename;
        }else{
            $_filename = $this->fileName;
        }
        // if file exists and no overwrite, then error
        if ( file_exists( $this->filePath.$_filename ) && !$_OverWrite ){
            $this->_errCode = 4;
            return self::raiseError($this->getErrMessage(), __FILE_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
        }
        //copy the uploaded file to specified location
        $_status = move_uploaded_file ( $this->_tmpName, $this->filePath.$_filename);
        if( !$_status ){
            $this->_errCode = 6;
            return self::raiseError($this->getErrMessage(), __FILE_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
        }
        //if rename = true, then update property
        if($_rename){ $this->fileName = $_rename ;}
        return $_status;
    }

    /**
     * File type check
	 *
     * @access Public
     * @return boolean
     */
    function checkType(){
        # check against disallowed files
        foreach ( $this->disallowedFiles as $_idx=>$_val ) {
            if ( $_val == $this->extension ) {
				return false;
			}
        }
        # check against allowed files
        # if the allowed list is populated then the file must be in the list.
        if ( empty( $this->allowedFiles ) ) { 
			return true; 
		}
        foreach ( $this->allowedFiles as $_idx=>$_val ) {
            if ( $_val == $this->extension ) { return true; }
        }
        return false;
    }



	
	//===========================
	//
	//      设置属性类方法
	//
	//===========================

    /**
     * Set file owner
	 *
     * @access Public
     * @param string [$_owner] file owner
     * @return boolean
     */  
    function setOwner($_owner){
        $_filename = $this->filePath.$this->fileName;
        if(chown($_filename, $_owner)){
            $this->fileOwner = $_owner;
        }else{
            $this->fileOwner = false;
        }
    }

    /**
     * Set file group
	 *
     * @access Public
     * @param string [$_grp] file group
     * @return boolean
     */  
    function setGroup($_grp){
        $_filename = $this->filePath.$this->fileName;
        if(chgrp($_filename, $_grp)){
            $this->fileGroup = $_grp;
        }else{
            $this->fileGroup = false;        
		}
    }

    /**
     * set the directory in which the file is
	 *
     * @access Public      
     * @param String [$_dir] Name of directory we upload to
     */
    function setDir( $_dir ){
        $this->filePath = $_dir;
    }

    /**
     * Add an addtional extension to the disallowed file array
	 *
     * @access Public      
     * @param mixed [$_Extension] string or array of extensions to be added
     */  
    function setdisallowedFiles($_Extension){
        if( is_array($_Extension) ){
            $this->disallowedFiles .= $_Extension;
        }else{
            $this->disallowedFiles[] = $_Extension;
        }
        array_unique ( $this->disallowedFiles );
    }

    /**
     *  Add an addtional extension to the allowed file array
	 *
     *  @access Public      
     *  @param mixed [$_Extension] string or array of extensions to be added
     */
    function setAllowedFiles($_Extension){
        if( is_array( $_Extension)){
            $this->allowedFiles .= $_Extension;
        }else{
            $this->allowedFiles[] = $_Extension;
        }
        array_unique ( $this->allowedFiles );
    }

    /**
     *  reset the array to blank
	 *
     *  @access Public      
     */  
    function resetDisallowedFiles(){
        unset($this->disallowedFiles);
    }

    /**
     *  reset the array to blank
	 *
     *  @access Public      
     */  
    function resetAllowedFiles(){
        unset($this->allowedFiles);
    }  
	


	//================================
	//
	//        获取属性类方法
	//
	//================================

    /**
     *  Get the owner of a file
	 *
     *  @access Public      
     */  
    function getOwner(){
        $_filename = $this->filePath.$this->fileName;
        $this->fileOwner = fileowner( $_filename );
    }

    /**
     *  Get the group of the file owner
	 *
     *  @access Public      
     */  
    function getGroup(){
        $_filename = $this->filePath.$this->fileName;
        $this->fileGroup = filegroup( $_filename);
    }

    /**
     *  Get the file size
	 *
     *  @access Public      
     */  
    function getSize(){
        if( !$this->fileSize ){
            $this->fileSize = @filesize( $this->filePath.$this->fileName );
        }
    }

	 /**
	  * Returns true wether the file is a directory. See
	  * http://fi.php.net/manual/en/function.is-dir.php for more details.
	  *
	  * @param file The filename we're trying to check. If omitted, the
	  * current file will be used (note that this function can be used as
	  * static as long as the file parameter is provided)
	  * @return Returns true if the file is a directory.
	  */
	 function isDir( $file = null ){
		 if( $file == null )
			 $file = $this->fileName;

		 return is_dir( $file );
	 }

	 /**
	  * Returns true if the file is writable by the current user.
	  * See http://fi.php.net/manual/en/function.is-writable.php for more
	  * details.
	  *
	  * @param file The filename we're trying to check. If omitted, the
	  * current file will be used (note that this function can be used as
	  * static as long as the file parameter is provided)
	  * @return Returns true if the file is writable, or false otherwise.
	  */
	 function isWritable( $file = null ){
		 if( $file == null )
			 $file = $this->fileName;

		 return is_writable( $file );
	 }

	 /**
	  * returns true if the file is readable. Can be used as static if a
	  * filename is provided
	  *
	  * @param if provided, this method can be used as an static method and
	  * it will check for the readability status of the file
	  * @return true if readable or false otherwise
	  */
	 function isReadable( $file = null ){
		 if( $file == null )
			 $file = $this->fileName;

		clearstatcache();
		return is_readable( $file );
	 }
	 /**
	  * returns true if the file exists.
	  *
	  * Can be used as an static method if a file name is provided as a
	  *  parameter
	  * @param fileName optinally, name of the file whose existance we'd
	  * like to check
	  * @return true if successful or false otherwise
	  */
	 function exists( $fileName = null ){
		 if( $fileName == null )
			 $fileName = $this->fileName;

		clearstatcache();
		return file_exists( $fileName );
	 }


    /**
     *   Return everything after the . of the file name (including the .)
	 *
     *   @access Public      
     */  
    function getExtension(){
        $this->extension = strrchr( $this->fileName, "." );
		return $this->extension;
    } 
	
    /**
     *  Get the mime type of a file
	 *
     *  @access Public      
     */      
    function getFileType(){
        $_mimetypes = array(
			".ai" => "application/postscript",
			".aif" => "audio/x-aiff",
			".asc" => "text/plain",
			".asf" => "video/x-ms-asf",
			".au" => "audio/basic",
			".avi" => "video/x-msvideo",
			".awf" => "application/vnd.adobe.workflow",
			".bcpio" => "application/x-bcpio",
			".bmp" => "application/x-bmp",
			".bot" => "application/x-bot",
			".bz2" => "application/x-bzip",
			".c4t" => "application/x-c4t",
			".c90" => "application/x-c90",
			".cal" => "application/x-cals",
			".cat" => "application/vnd.ms-pki.seccat",
			".cdr" => "application/x-cdr",
			".cel" => "application/x-cel",
			".cer" => "application/x-x509-ca-cert",
			".cg4" => "application/x-g4",
			".cgm" => "application/x-cgm",
			".cit" => "application/x-cit",
			".class" => "java/*",
			".cmp" => "application/x-cmp",
			".cmx" => "application/x-cmx",
			".cot" => "application/x-cot",
			".cpio" => "application/x-cpio",
			".cpt" => "application/mac-compactpro",
			".crl" => "application/pkix-crl",
			".csh" => "application/x-csh",
			".csi" => "application/x-csi",
			".css" => "text/css",
			".cut" => "application/x-cut",
			".dbf" => "application/x-dbf",
			".dbm" => "application/x-dbm",
			".dbx" => "application/x-dbx",
			".dcr" => "application/x-director",
			".dcx" => "application/x-dcx",
			".dgn" => "application/x-dgn",
			".dib" => "application/x-dib",
			".djvu" => "image/vnd.djvu",
			".dll" => "application/x-msdownload",
			".doc" => "application/msword",
			".drw" => "application/x-drw",
			".dvi" => "application/x-dvi",
			".dwf" => "application/x-dwf",
			".dwg" => "application/x-dwg",
			".dxb" => "application/x-dxb",
			".dxf" => "application/x-dxf",
			".edn" => "application/vnd.adobe.edn",
			".emf" => "application/x-emf",
			".eml" => "message/rfc822",
			".epi" => "application/x-epi",
			".eps" => "application/postscript",
			".etd" => "application/x-ebx",
			".etx" => "text/x-setext",
			".ez" => "application/andrew-inset",
			".fax" => "image/fax",
			".fdf" => "application/vnd.fdf",
			".fif" => "application/fractals",
			".frm" => "application/x-frm",
			".gbr" => "application/x-gbr",
			".gcd" => "application/x-gcd",
			".gif" => "image/gif",
			".gl2" => "application/x-gl2",
			".gp4" => "application/x-gp4",
			".gtar" => "application/x-gtar",
			".gz" => "application/x-gzip",
			".hdf" => "application/x-hdf",
			".hgl" => "application/x-hgl",
			".hlp" => "application/mshelp",
			".hmr" => "application/x-hmr",
			".hpg" => "application/x-hpgl",
			".hpl" => "application/x-hpl",
			".hqx" => "application/mac-binhex40",
			".hrf" => "application/x-hrf",
			".hta" => "application/hta",
			".htc" => "text/x-component",
			".htt" => "text/webviewhtml",
			".icb" => "application/x-icb",
			".ice" => "x-conference-xcooltalk",
			".ico" => "application/x-ico",
			".ief" => "image/ief",
			".iff" => "application/x-iff",
			".iges" => "model/iges",
			".igs" => "application/x-igs",
			".iii" => "application/x-iphone",
			".img" => "application/x-img",
			".ins" => "application/x-internet-signup",
			".ivf" => "video/x-ivf",
			".jpeg" => "image/jpeg ",
			".jpg" => "image/jpeg",
			".js" => "text/javascript",
			".kar" => "audio/midi",
			".la1" => "audio/x-liquid-file",
			".lar" => "application/x-laplayer-reg",
			".latex" => "application/x-latex",
			".lavs" => "audio/x-liquid-secure",
			".lbm" => "application/x-lbm",
			".lmsff" => "audio/x-la-lms",
			".ls" => "application/x-javascript",
			".ltr" => "application/x-ltr",
			".m1v" => "video/x-mpeg",
			".m3u" => "audio/mpegurl",
			".m4e" => "video/mpeg4",
			".mac" => "application/x-mac",
			".man" => "application/x-troff-man",
			".mdb" => "application/x-mdb",
			".me" => "application/x-troff-me",
			".mi" => "application/x-mi",
			".mid" => "audio/x-midi",
			".mil" => "application/x-mil",
			".mnd" => "audio/x-musicnet-download",
			".mns" => "audio/x-musicnet-stream",
			".mov" => "video/quicktime",
			".movie" => "video/x-sgi-movie",
			".mp1" => "audio/mp1",
			".mp2" => "audio/x-mpeg",
			".mp3" => "audio/mpeg",
			".mpa" => "video/x-mpg",
			".mpd" => "application/vnd.ms-project",
			".mpeg" => "video/mpeg ",
			".mpg" => "video/mpeg",
			".mpga" => "audio/rn-mpeg",
			".mpv" => "video/mpg",
			".ms" => "application/x-troff-ms",
			".msh" => "model/mesh",
			".mxp" => "application/x-mmxp",
			".mxu" => "video/vnd.mpegurl",
			".nc" => "application/x-netcdf",
			".net" => "image/pnetvue",
			".nrf" => "application/x-nrf",
			".oda" => "application/oda",
			".odc" => "text/x-ms-odc",
			".out" => "application/x-out",
			".p10" => "application/pkcs10",
			".p12" => "application/x-pkcs12",
			".p7b" => "application/x-pkcs7-certificates",
			".p7c" => "application/pkcs7-mime",
			".p7r" => "application/x-pkcs7-certreqresp",
			".p7s" => "application/pkcs7-signature",
			".pac" => "application/x-ns-proxy-autoconfig",
			".pbm" => "image/x-portable-bitmap",
			".pc5" => "application/x-pc5",
			".pci" => "application/x-pci",
			".pcl" => "application/x-pcl",
			".pcx" => "application/x-pcx",
			".pdb" => "chemical/x-pdb",
			".pdf" => "application/pdf",
			".pdx" => "application/vnd.adobe.pdx",
			".pgl" => "application/x-pgl",
			".pgm" => "image/x-portable-graymap",
			".pgn" => "application/x-chess-pgn",
			".php" => "application/x-httpd-php",
			".pic" => "application/x-pic",
			".pko" => "application/vnd.ms-pki.pko",
			".pl" => "application/x-perl",
			".pls" => "audio/scpls",
			".plt" => "application/x-plt",
			".png" => "application/x-png",
			".pnm" => "image/x-portable-anymap",
			".pot" => "application/mspowerpoint",
			".ppa" => "application/vnd.ms-powerpoint",
			".ppm" => "application/x-ppm",
			".ppt" => "application/mspowerpoint ",
			".pr" => "application/x-pr",
			".prf" => "application/pics-rules",
			".prn" => "application/x-prn",
			".prt" => "application/x-prt",
			".ptn" => "application/x-ptn",
			".qt" => "video/quicktime ",
			".r3t" => "text/vnd.rn-realtext3d",
			".ra" => "audio/vnd.rn-realaudio",
			".ram" => "application/vnd.rn-realmedia",
			".rar" => "application/rar",
			".ras" => "application/x-ras",
			".rat" => "application/rat-file",
			".rec" => "application/vnd.rn-recording",
			".red" => "application/x-red",
			".rgb" => "application/x-rgb",
			".rjs" => "application/vnd.rn-realsystem-rjs",
			".rjt" => "application/vnd.rn-realsystem-rjt",
			".rlc" => "application/x-rlc",
			".rle" => "application/x-rle",
			".rmf" => "application/vnd.adobe.rmf",
			".rmi" => "audio/mid",
			".rmj" => "application/vnd.rn-realsystem-rmj",
			".rmm" => "audio/x-pn-realaudio",
			".rmp" => "application/vnd.rn-rn_music_package",
			".rms" => "application/vnd.rn-realmedia-secure",
			".rmvb" => "video/vnd.rn-realvideo",
			".rmx" => "application/vnd.rn-realsystem-rmx",
			".rnx" => "application/vnd.rn-realplayer",
			".rp" => "image/vnd.rn-realpix",
			".rpm" => "audio/x-pn-realaudio-plugin",
			".rsml" => "application/vnd.rn-rsml",
			".rt" => "text/vnd.rn-realtext",
			".rtf" => "application/rtf",
			".rtx" => "text/richtext",
			".sam" => "application/x-sam",
			".sat" => "application/x-sat",
			".sdp" => "application/sdp",
			".sdw" => "application/x-sdw",
			".sgml" => "text/sgml",
			".sh" => "application/x-sh",
			".shar" => "application/x-shar",
			".sig" => "application/pgp-signature",
			".sit" => "application/x-stuffit",
			".skp" => "application/x-koan",
			".slb" => "application/x-slb",
			".sld" => "application/x-sld",
			".slk" => "drawing/x-slk",
			".smi" => "application/smil",
			".smk" => "application/x-smk",
			".spl" => "application/futuresplash",
			".src" => "application/x-wais-source",
			".ssm" => "application/streamingmedia",
			".sst" => "application/vnd.ms-pki.certstore",
			".stl" => "application/vnd.ms-pki.stl",
			".sty" => "application/x-sty",
			".sv4cpio" => "application/x-sv4cpio",
			".sv4crc" => "application/x-sv4crc",
			".swf" => "application/x-shockwave-flash",
			".t" => "application/x-troff",
			".tar" => "application/x-tar",
			".tar.gz" => "application/x-tgz",
			".tbz" => "application/x-bzip-compressed-tar",
			".tcl" => "application/x-tcl",
			".tdf" => "application/x-tdf",
			".tex" => "application/x-tex",
			".texinfo" => "application/x-texinfo",
			".tg4" => "application/x-tg4",
			".tga" => "application/x-tga",
			".tif" => "application/x-tif",
			".tiff" => "image/tiff",
			".top" => "drawing/x-top",
			".torrent" => "application/x-bittorrent",
			".tsv" => "text/tab-seperated-values",
			".uin" => "application/x-icq",
			".uls" => "text/iuls",
			".ustar" => "application/x-ustar",
			".vcd" => "application/x-cdlink",
			".vcf" => "text/x-vcard",
			".vda" => "application/x-vda",
			".vdx" => "application/vnd.visio",
			".viv" => "video/vnd.vivo",
			".vpg" => "application/x-vpeg005",
			".vsd" => "application/x-vsd",
			".vst" => "application/x-vst",
			".wav" => "audio/x-wav",
			".wax" => "audio/x-ms-wax",
			".wb1" => "application/x-wb1",
			".wb2" => "application/x-wb2",
			".wb3" => "application/x-wb3",
			".wbmp" => "image/vnd.wap.wbmp",
			".wbxml" => "application/vnd.wap.wbxml",
			".wk3" => "application/x-wk3",
			".wk4" => "application/x-wk4",
			".wkq" => "application/x-wkq",
			".wks" => "application/x-wks",
			".wm" => "video/x-ms-wm",
			".wma" => "audio/x-ms-wma",
			".wmd" => "application/x-ms-wmd",
			".wmf" => "application/x-wmf",
			".wml" => "text/vnd.wap.wml",
			".wmlc" => "application/vnd.wap.wmlc",
			".wmls" => "text/vnd.wap.wmlscript",
			".wmlsc" => "application/vnd.wap.wmlscriptc",
			".wmv" => "video/x-ms-wmv",
			".wmx" => "video/x-ms-wmx",
			".wmz" => "application/x-ms-wmz",
			".wp6" => "application/x-wp6",
			".wpd" => "application/x-wpd",
			".wpg" => "application/x-wpg",
			".wpl" => "application/vnd.ms-wpl",
			".wq1" => "application/x-wq1",
			".wr1" => "application/x-wr1",
			".wri" => "application/x-wri",
			".wrk" => "application/x-wrk",
			".wrl" => "model/vrml",
			".ws" => "application/x-ws",
			".wsc" => "text/scriptlet",
			".wvx" => "video/x-ms-wvx",
			".x_b" => "application/x-x_b",
			".x_t" => "application/x-x_t",
			".xbm" => "image/x-xbitmap",
			".xdp" => "application/vnd.adobe.xdp",
			".xfd" => "application/vnd.adobe.xfd",
			".xfdf" => "application/vnd.adobe.xfdf",
			".xht" => "application/xhtml+xml",
			".xhtml" => "text/html",
			".xla" => "application/msexcel",
			".xls" => "application/msexcel ",
			".xlw" => "application/x-xlw",
			".xml" => "text/xml",
			".xpm" => "image/x-xpixmap",
			".xwd" => "image/x-windowdump",
			".xyz" => "chemical/x-xyz",
			".zip" => "application/zip",
        );
        // return mime type for extension
        if (isset( $_mimetypes[$this->extension] ) ) {
            $this->fileType = $_mimetypes[$this->extension];
        // if the extension wasn't found return octet-stream         
        } else {
            $this->fileType = 'application/octet-stream';
        }   
    }

    /**
     *  Return the error message of an action made on a file (upload, delete, write...)
	 *
     *  @return string error message        
     */  
    function getErrMessage(){
        switch( $this->_errCode ){
            case 0:
                    $_msg = "The file ".$this->fileName." was succesfully uploaded.";
                    break;
            case 1:
                    $_msg = $this->fileName." was not uploaded. ".$this->extension." Extension is not accepted!";
                    break;
            case 2:
                    $_msg = "The file ". $this->cls_filename ." is too big or does not exists!";
                    break;
            case 3:
                    $_msg = "Remote file could not be deleted!";
                    break;
            case 4:
                    $_msg = "The file ".$this->fileName." exists and overwrite is not set in class!";
                    break;
            case 5:
                    $_msg = "Copy successful, but renaming the file failed!";
                    break;
            case 6:
                    $_msg = "Unable to copy file";
                    break;
            case 7:
                    $_msg = "You don't have permission to use this script!";
                    break;
            case 8:
                    $_msg = ""; // if user does not select a file
                    break;
			case 9:
					$_msg = "File can't readable";
					break;
            case "DIR01":
                    $_msg = "Can't write File [no fwrite]";
                    break;
            case "DIR02":
                    $_msg = "Can't write File [no filename | no content]";
                    break;
            case "DIR02":
                    $_msg = "Can't create Folder [mkdir failed]";
                    break;
            case "DIR04":
                    $_msg = "Folder exists";
                    break;
            case "FILE_DEL1":
                    $_msg = "File deletion impossible";
                    break;
            default:
                    $_msg = "Unknown error!";
        }
        return $_msg ;
    }
}

