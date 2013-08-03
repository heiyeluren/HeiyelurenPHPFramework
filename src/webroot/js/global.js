// ��ʼ������
/* Add String(Obj) And Array(Obj) Method */
String.prototype.trim=function(){return this.replace(/(^[\s]*)|([\s]*$)/g, "")}
String.prototype.inc=function(k1,k2){return k2==null?(this.indexOf(k1)>-1?true:false):(k2+this+k2).indexOf(k2+k1+k2)>-1?true:false}
String.prototype._slice="".slice
//String.prototype.slice=function(n1,n2){var v,b1=typeof(n1)=="number",b2=typeof(n2)=="number";if(!b1||typeof(n2)=="string"){v=eval("this._slice("+(b1?n1:this.indexOf(n1)+(n2==null?1:0)+(this.indexOf(n1)==-1?this.length:0))+(n2==null?"":(b2?n2:(this.indexOf(n2)==-1?"":","+this.indexOf(n2))))+")")}else{v=isIE5&&n1<0&&n2==null?this._slice(this.length-1):eval("this._slice(n1"+(n2==null?"":","+n2)+")")}return v}
String.prototype.css=function(key,def){var n1,n2,l=this;if(key=="")return "";if((";"+l+";").indexOf(";"+key+";")>-1)return true;n1=(";"+l+":").indexOf(";"+key+":");if(n1==-1)return def==null?"":def;n1+=(key+":").length;n2=(";"+l+";").indexOf(";",n1+1);return l.slice(n1,n2-1)}
String.prototype.qv=function(key){var l=this.replace("?","&")+"&",n1,n2;n1=l.indexOf("&"+key+"=");if(n1==-1)return "";n1=l.indexOf("=",n1)+1;n2=l.indexOf("&",n1);return l.slice(n1,n2)}
String.prototype.toArray=function(key){var l=this,v;if(key==null)key="|";v=key;if(key=="n"){l=l.replace(/\r/g,"");v="\n"};l=l.replace(new RegExp("(\\"+key+")+","g"),v).replace(new RegExp("^[\\"+key+"]*|[\\"+key+"]+$","g"),"");return l==""?new Array():l.split(v)}
String.prototype.reallength=function(){return this.replace(/[^\x00-\xff]/g,"^^").length;}
//Array.prototype.add=function(key){this[this.length]=key}	Ǳ�ڵ���selectѡ�������
String.prototype.reallength=function(){	return this.replace(/[^\x00-\xff]/g,"^^").length;}

/* Init And Check User Agent */
var body=document.body,agent=navigator.userAgent;

//�ַ�����ȡ 
function MBSubstring(str, begin, end)
{
	var tmp = '', one = '';
	var length = 0, flg = 0;
	if ((str == '') || (typeof(str) != 'string') )
	{
		return '';
	}

	if (begin > end)
	{
		return '';
	}

	length = str.length;

	for(i = 0; i < length; i++)
	{
		one = str.substr(i, 1 );
		flg += one.reallength();
		if ( ( flg > begin ) && (flg - 1) <= end )
		{
			tmp += one;
		}
	}

	return tmp;
}


/* ��֤�����ʼ� */
function validateEmail(s)
{
	if (s.length > 100)
	{
			window.alert("email��ַ���Ȳ��ܳ���100λ!");
			return false;
	}

	 var regu = "^(([0-9a-zA-Z]+)|([0-9a-zA-Z]+[_.0-9a-zA-Z-]*[0-9a-zA-Z]+))@([a-zA-Z0-9-]+[.])+([a-zA-Z]{2}|net|NET|com|COM|gov|GOV|mil|MIL|org|ORG|edu|EDU|int|INT)$"
	 var re = new RegExp(regu);
	 if (s.search(re) != -1) {
		   return true;
	 } else {
		   window.alert ("��������Ч�Ϸ���E-mail��ַ ��")
		   return false;
	 }
}

/* ��֤���� */
function validateDomain(s)
{
	if(s.search)
	{
		return (s.search(new RegExp("^[a-zA-Z]+[a-zA-Z0-9\_]+$","g"))>=0)
	}
}

/* ��֤���� */
function validateNum(s)
{
	if(s.search)
	{
		return (s.search(new RegExp("^[0-9]+$","g"))>=0)
	}
}

/* ��֤�Ƿ�����ĸ���� */
function validateChars(s)
{
	if(s.search)
	{
		return (s.search(new RegExp("^[a-zA-Z0-9]+$","g"))>=0)
	}
}

/* ��֤�Ƿ���Url */
function validateUrl(s)
{
	if(s.search)
	{
		return (s.search(new RegExp("^http\:\/\/[a-zA-Z0-9\.]+(\/)$","g"))>=0)
	}
}
/* ��֤�Ƿ��ǺϷ����� */
function validateDate(s)
{
	// ֻ������'-'�ָ������
	var the1st = s.indexOf('-');				// ��һ��'-'
	var the2nd = s.lastIndexOf('-');			// �ڶ���'-'
	
	if (the1st == the2nd) {
		return (false);						// ֻ��һ��'-'��û��'-'
	}
	
	else {
		var y = s.substring(0,the1st);			// ��
		var m = s.substring(the1st+1,the2nd);	// ��
		var d = s.substring(the2nd+1,s.length);	// ��
		var maxDays = 31;
		
		if (validateNum (m)==false || validateNum (d)==false || validateNum (y)==false) {
			return (false);
		}
		
		else if (y.length != 4) {
			return (false);					// �곤�Ȳ�Ϊ4
		}
		
		else if (d.length > 2) {
			return (false);
		}
		
		else if (m.length > 2) {
			return (false);
		}
		
		else if ((m<1) || (m>12)) {
			return (false);					// �·ݲ���
		}
		
		else if (m==4 || m==6 || m==9 || m==11)
			maxDays = 30;						// С��
		
		else if (m==2) {
			if (y % 4 > 0)
				maxDays = 28;					// ����һ������
			
			else if (y % 100 == 0 && y % 400 > 0)
				maxDays = 28;					// ������ÿ400��һ������
			
			else
				maxDays = 29;					// ����
		}
		
		if ((d<1) || (d>maxDays)) {
			return (false);					// ���ڲ���
		}
		
        else {
        	dateObj = new Date (y, m-1, d);
        	return (dateObj.getTime());
        }
	}
}

/* ��֤���� */
function varLen(v,min,max)
{
	var vlen=v.reallength();
	if (vlen>=min && vlen<=max)
	{	return true;
	}else
	{	return false;
	}
}

/* ��֤��ֵ��Χ */
function numLen(n,min,max)
{
	if (n>=min && n<=max)
	{	return true;
	}else
	{	return false;
	}
}

/* ��֤�Ƿ�ͬ�� */
function isSame(m,n)
{	
	if (m==n)
	{	return true;
	}else
	{	return false;
	}
}

/* ����Ƿ�����հ��ִ� */
function isFilled(input_blank){
	if (typeof(input_blank) == 'undefined')
	{
		return false;
	}
    while(input_blank.value.indexOf(" ") == 0)
        input_blank.value = input_blank.value.substring(1,input_blank.value.length);
    if((input_blank.value == "") || (input_blank.value == null)){
        return false;
    }
    else{
        return true;
    }
}

/*
 * ����checkbox
 * id = div , form id
 * checked = true ȫѡ, = false ȫ��ѡ, ���ȫѡȫ��ѡ���Զ��任
 */
var chgCheckboxFlg = false;
function chgCheckbox(id, checked)
{
	var all= $(id);
	var checks = all.getElementsByTagName("input");
	for(var i=0;i<checks.length;i++)
	{ 
		if (checks[i].type == "checkbox")
		{
			//alert(checks[i].value);
			if (typeof(checked) != 'undefined')
			{
				checks[i].checked = checked;
			}
			else
			{
				if (i == 0)
				{
					chgCheckboxFlg = !chgCheckboxFlg;
				}
				checks[i].checked = chgCheckboxFlg;	
			}
		}
	}
}

/*
 * ��÷�Χ�ڵ�checkboxֵ
 * id = div , form id
 */
function getCheckbox(id)
{
	var all= $(id);
	var checks = all.getElementsByTagName("input");
	var tmpstr = new Array(), k = 0;
	for(var i=0;i<checks.length;i++)
	{ 
		if ( (checks[i].type =="checkbox") && checks[i].checked)
		{
				tmpstr[k++] = checks[i].value;
		}
	}
	return tmpstr.join();
}

/**
 * ��÷�Χ�ڵ�raidoֵ
 */
function getRadio(id)
{
	var all= $(id);
	var checks = all.getElementsByTagName("input");
	var tmpstr = new Array(), k = 0;
	for(var i=0;i<checks.length;i++)
	{ 
		if ( (checks[i].type =="radio") && checks[i].checked)
		{
				tmpstr[k++] = checks[i].value;
		}
	}
	return tmpstr.join();
}

var isIE, isIE4, isIE5, isIE6, isOpr, isMoz;

isOpr = agent.inc("Opera");
isIE  = agent.inc("IE") && !isOpr;
isIE4 = agent.inc("IE 4");
isIE5 = agent.inc("IE 5") || agent.inc("IE 4");
isIE6 = isIE&&!isIE5;
isMoz = agent.inc("Mozilla") && !isOpr&&!isIE;

if(isIE4)
{
	document.getElementById=function(key){return document.all[key]}
	document.getElementsByName=function(key){var a=new Array(),ol=document.all;for(i=0;i<ol.length;i++){if(ol[i].name==key)a[a.length]=ol[i];}return a}
	document.getElementsByTagName=function(key){var a=new Array(),ol=document.all;for(i=0;i<ol.length;i++){if(ol[i].tagName.toLowerCase()==key)a[a.length]=ol[i];}return a}
}

if(isMoz)
{
	Event.prototype.__defineGetter__("srcElement",function(){var node=this.target;while(node.nodeType!=1){node=node.parentNode}return node})
	HTMLElement.prototype.__defineGetter__("children",function(){return this.childNodes})
	HTMLElement.prototype.__defineGetter__("parentElement",function(){return this.parentNode})
}

// Common Function 


// �����Ӧ���� 
function $(s){return getObj(s);}

// �����Ӧ������ 
function $f(s){return getFormObj(s);}

// ���֡���� 
function $$(s){return getFrameNode(s);}

// ����Ԫ�� 
function $c(s){return document.createElement(s);}

// �ж�Ŀ���Ƿ���� 
function exist(s){return $(s)!=null;}

// ������� 
function dw(s){document.write(s);}

// ���ػ���ʾĿ�� 
function hide(s){$(s).style.display=$(s).style.display=="none"?"":"none";}

// �ж��Ƿ�Ϊ�� 
function isNull(_sVal){return (_sVal == "" || _sVal == null || _sVal == "undefined");}

// ɾ��һ������ڵ� 
function removeNode(s)
{
	if(exist(s))
	{
		$(s).innerHTML = '';
		$(s).removeNode?$(s).removeNode():$(s).parentNode.removeChild($(s));
	}
}

// ����ҳ��ӵ��ղؼ��� 
function setHome(){try{window.external.AddFavorite(window.document.location,window.document.title)}catch(e){};}

// �ж��Ƿ��� IE 
function isIE(){return BROWSER.indexOf('ie') > -1;}

// ���ͻ���������� 
function browserDetect(){
	var sUA = navigator.userAgent.toLowerCase();
	var sIE = sUA.indexOf("msie");
	var sOpera = sUA.indexOf("opera");
	var sMoz = sUA.indexOf("gecko");
	if (sOpera != -1) return "opera";
	if (sIE != -1){
		nIeVer = parseFloat(sUA.substr(sIE + 5));
		if (nIeVer >= 6) return "ie6";
		else if (nIeVer >= 5.5) return "ie55";
		else if (nIeVer >= 5 ) return "ie5";
	} 
	if (sMoz != -1)	return "moz";
	return "other";
}
var BROWSER = browserDetect();

// ��ö��� 
function getObj(objstr)
{
	return getObjById(objstr);
}
function getObjById(objstr)
{
	return typeof(objstr)!="string"?objstr:(isIE5?document.all(objstr):document.getElementById(objstr));
}

// ���֡�ڵ� 
function getFrameNode(sNode){
	return document.frames ? document.frames[sNode] : document.getElementById(sNode).contentWindow;
}

// ��ñ����� ֧��name
function getFormObj(objstr)
{
	return typeof(objstr)!="string"?objstr:document.forms[objstr];
}

// ----------------------------------------------------


/**
 * JS���ú���Ⱥ
 * 
 */

 function getElement( name )
 {
	var el = document.getElementsByName( name );
	if( el[0] == null )
	{
    var e2 = document.getElementById( name );
    if(e2 == null)
    {
		alert( 'cannot find ' + name + ' ! ' );
    }
    else
    {
      return e2;
    }
	}
	else
	{
		return el[0];
	}
	
 }
 
 
/**
 * ����input text
 */
function set_text( name , value )
{
	var el = getElement( name );
	el.value = value;
}

/**
 * �������ڿ�
 */
function set_date( str , yname , mname , dname )
{
	var vs = str.split( '-' );
	set_select( yname , vs[0] );
	set_select( mname , vs[1] );
	set_select( dname , vs[2] );
}

/**
 * just setback year and month
 */
function set_year_month( str , yname , mname )
{
	var vs = str.split( '-' );
	set_select( yname , vs[0] );
	set_select( mname , vs[1] );
}

/**
 * ����������
 *
 * select �Ļ��ñ���ָ��value�������optionû��value������ʾΪ�հ�
 * select����Ϊ��ֵʱ��firefox���01���1����ie���ᣬ���뾫ȷƥ�� 
 */
function set_select( name , value )
{
	var sel = getElement( name );
	var ops = sel.options;
	for( var i = 0 ; i < ops.length ; i++ )
	{
		if( ops[i].value == value  )
		{
			try
			{
				if( i != ops.selectedIndex )
				{
					ops.selectedIndex = i;
					ops[i].selected = true;
				}
				
			}
			catch( e ) 
			{
				// alert( e.description );
				// ie���ڶ�̬���ɵ���������׳�һ������������selected���ԣ�δָ���Ĵ��󡱵��쳣
				// ԭ�������Ȳ�������
			}
			
			
		}
	}
}

/**
 * ���õ�ѡ��ť
 *
 * ͨ�����������Ƶ�radio���ʵ��
 */
function set_radio( name , value )
{
	var objRadio = document.getElementsByName( name );
	for(var i=0;i<objRadio.length;i++)
	{
		if(objRadio[i].type=="radio")
		{
			if( objRadio[i].value == value )
			{
				objRadio[i].checked = true;
			}
		}
	}
}

function set_checkbox( name , value )
{
	var obj = document.getElementsByName( name );
	for(var i=0;i<obj.length;i++)
	{
		if(obj[i].type=="checkbox")
		{
			if( obj[i].value == value )
			{
				obj[i].checked = true;
			}
		}
	}
}


/**
 * ��ʼ��select������
 *
 */
function ini_select( name , Karray , N  )
{
	var selObj = getElement( name );
	
	for( key in Karray )
	{
		
		if (key == 0 && N == 1)
		{
			//alert( Karray[key] );
			continue;
		}
		//alert(Karray[key] +":"+ key);
		if (Karray[key].toString().indexOf('(object)')==-1)
		{
			selObj.options[selObj.length]=new Option( Karray[key] , key );
		}
	}
}

/**
 * ���select��option�� 
 *
 */
function add_option( name , texts , value )
{
	var selObj = getElement( name );
	selObj.options[selObj.length]=new Option( texts , value );
}

/**
 * �Ƴ�select��ѡ��
 */
function remove_option( name , value )
{
	var selObj = getElement( name );
	
	for( var i = 0 ; i < selObj.length ; i++ )
	{
		if( selObj[i].value == value  )
		{
			try
			{
				selObj.remove( i );
			}
			catch( e ) 
			{
				// alert( e.description );
				//e.description
			}		
		}
	}
}

/*
 * ��Ԫ������¼�
 *
 * ��δ����в����ĺ�����
 * ʹ������function 
 * - �� add_event( name1 , "change" , function(){ adjust_select( name1 , name2 , array2 ) } );
 */
function add_event( name , event , func )
{
	var el = getElement( name );  
					
	var names = navigator.appName;
	if( names == "Microsoft Internet Explorer" )
	{
		// IE 
		el.attachEvent( "on" + event , func );
	}
	else
	{
		// ���������ʹ��addEventListener(��W3c�淶)
		el.addEventListener( event , func , false);
	}
	// if( names == "Netscape" )
	
	
}

/**
 * adjust_select���ڱ��ֶ�������֮���һ����
 * 
 * ��1��ѡ���Զ�����2��ѡ��
 * array2�Ǻ�array1���Ӧ��һ����������
 * ��ϸ��ʽ��city.js
 */
function adjust_select( name1 , name2 , array2 , N )
{
	var obj1 = getElement( name1 );
	
	var obj2 = getElement( name2 );
	
	// ȡ��obj1��ѡ�е���
	var str = parseInt( obj1.options[obj1.selectedIndex].value );
	
	if (!str)
	{
		//��һ���˵�ѡ����Ĭ�ϣ�valueΪ�գ�����Ŀʱ����������˵�
		//01-04-2006 By ZhangHao
		
		obj2.innerHTML="";
		//alert (name2);
		switch (name2) {
			case 'league_type2':
				add_option (name2, "����С��", '');
				break;
			case 'school':
				add_option (name2, "����ѧУ", '');
				break;
			case 'city':
				add_option (name2, "ѡ�����", '');
				break;
			case 'college':
				add_option (name2, "ѡ��ѧУ", '');
				break;
			case 'n_locus':
				break;
			case 'college_id':
				break;
			case 'sub_league_type':
				break;
			case 'school_s':
				add_option (name2, "����ѧУ", '');
				break;
			default:
		}
	}
	else if( str != NaN )
	{
		obj2.innerHTML="";
		if (name2 == 'school_s')
			add_option (name2, '����', '');
		ini_select( name2 , array2[str] , N );
	}
	else
	{
		obj2.innerHTML="";
	}
}

/**
 * �ɶ����˵���ֱֵ������һ���˵�
 */
function set_select_by_2( name1 , name2 , array2 , value2 )
{
	value2 = value2 + '';
	var pcode = value2.substr( 0 , 2 );
	set_select( name1 , pcode );
	adjust_select( name1 , name2 , array2 );
	set_select( name2 , value2 );
}

/**
 * ������������year
 *
 * ��beginyearС�ڵ�����ʱ��beginyearΪ����ڵ�ǰ��ݵ���ʼʱ�䣬endyearΪ����������
 * 0 , 5 - �ӽ��꿪ʼ���5��
 * -5 �� 10 - ��5��ǰ���10��
 */
function ini_year( yname , beginyear , endyear )
{
	var year = getElement( yname );
	
	year.innerHTML = '';
	
	var d = new Date();
	
	if( beginyear <= 0 )
	{
		beginyear += d.getFullYear();
		endyear += beginyear;
	}
	
	if( beginyear < endyear )
	{
		for( var i = beginyear ; i <= endyear ; i++  )
		{
			year.options[year.length] = new Option( i + '' , i );
		}
	}
	else
	{
		for( var i = beginyear ; i >= endyear ; i--  )
		{
			year.options[year.length] = new Option( i + '' , i );
		}
	}
}

function ini_month( name )
{
	ini_num( name , 12 );
}

function ini_date( name )
{
	ini_num( name , 31 );
}

function ini_num( name , num )
{
	var date = getElement( name )
	
	var o = date.value;
	
	date.innerHTML = '';
	//date.options[date.length] = new Option( '�ص�' , 0 );
	
	for( var i =1 ; i <= num ; i++ )
	{
		date.options[date.length] = new Option( i + '' , i );
	}

	if( o != NaN )
	{
		set_select( name , o );
	}
}

function year_set_ini( yname , beginyear , endyear , mname , dname)
{
	ini_year( yname , beginyear , endyear);
	ini_month( mname );
	ini_date( dname );
	add_event( mname , "change" , function(){ adjust_date( yname , mname , dname ) } );
	add_event( yname , "change" , function(){ adjust_date( yname , mname , dname ) } );
}

function year_set_back( yname , beginyear , endyear , mname , dname , value)
{
	year_set_ini( yname , beginyear , endyear , mname , dname);
	set_date( value , yname , mname , dname );
	adjust_date( yname , mname , dname );
}

function adjust_date( yname , mname , dname )
{
	var year = getElement( yname );
	var month = getElement( mname );
	var date = getElement( dname );
	
	var n = 31;
	var y = year.options[year.selectedIndex].value;
	var m = month.options[month.selectedIndex].value;
	if( m == 4 || m == 6 || m == 9 || m == 11  )
	{
		n = 30;
	}
	
	if( m == 2 )
	{
		if( y % 4 == 0 && y % 100 != 0 )
		{
			n = 29;
		}
		else
		{
			n =28;
		}
	}
	
	ini_num( dname , n );
	
}


function select_set_back( name , array , value , N )
{
	ini_select( name , array , N );
	if( value != '' )
	{
		set_select( name , value );
	}
}

function select_set_ini( name , array , N )
{
	ini_select( name , array , N );
}

function change_action( name , formname , action )
{
	add_event( name , "click" , function(){  var f = getElement( formname );f.action = action; } );
}

/**
 * ��ʼ�������������˵�����
 */
function dselect_set_back( name1 , array1 , name2 , array2 , value2 , N )
{
	//ini_select( name1 , array1  );	
	//add_event( name1 , "change" , function(){ adjust_select( name1 , name2 , array2 } );
	dselect_set_ini( name1 , array1 , name2 , array2 , N );
	if( value2 != '' )
	{
		set_select_by_2( name1 , name2 , array2 , value2  );
	}	
}

/**
 * ��ʼ�������˵�
 */
function dselect_set_ini( name1 , array1 , name2 , array2 , N  )
{
	ini_select( name1 , array1 , N );	
	add_event( name1 , "change" , function(){ adjust_select( name1 , name2 , array2 ) } );
}


function getYear()
{
	var d = new Date();
	return d.getFullYear();
}


function getMon()
{
  var d = new Date();
	return d.getMonth();
}

function getCurrentDate()
{
	var d = new Date();
	var y = d.getFullYear();
	var m = d.getMonth()+1;
	var dt = d.getDate();
	return y + '-' + m + '-' + dt ;
}

function set_minute(name)
{
  for (var i=0; i<=59 ; i++)
  {
    if (i < 10)
    {
      v = '0' + i;
    }else{
      v = i;
    }
    add_option(name, v, v);
  }
}

function set_hour(name)
{
  for (var i=0; i<=23 ; i++)
  {
    if (i < 10)
    {
      v = '0' + i;
    }else{
      v = i;
    }
    add_option(name, v, v);
  }
}


function GetObj(objName){
	if(document.getElementById){
		return eval('document.getElementById("' + objName + '")');
	}else if(document.layers){
		return eval("document.layers['" + objName +"']");
	}else{
		return eval('document.all.' + objName);
	}
}

/**
 * ͳ���ַ����ֽ���
 *
 * return	integer
 */
String.prototype.ByteCount = function()
{
	txt = this.replace(/(<.*?>)/ig,'');
	txt = txt.replace(/([\u0391-\uFFE5])/ig, '11');
	var count = txt.length;
	return count;
}

function check_length(str, name, min, max)
{
	var l = str.trim().ByteCount();
	if (min !='' && min >0 && l < min)
	{
		alert(name + "�ĳ��Ȳ�������" + min + "���ַ�");
		return false;
	}
	if (max !='' && max >0 && l > max)
	{
		alert(name + "�ĳ��Ȳ��ܶ���" + max + "���ַ�");
		return false;
	}
	return true;
}


function is_email(str)
{
	var reg_email = /\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/;
	if(!reg_email.test(str.trim()))
	{
		return false;
	}
	return true;
}

// ����false �����, true �򲻰���
function check_specialword(str)
{
	var val = />|<|,|\[|\]|\{|\}|\?|\/|\+|=|\||\'|\\|\"|:|;|\~|\!|\@|\#|\*|\$|\%|\^|\&|\(|\)|`/;
	return !val.test(str);
}

function is_plus(str)
{
	var reg = /^\d+$/;
	return reg.test(str.trim());
}

String.prototype.avail = function()
{
	str = this.trim();
	if(str == '' || str == 0 || str == NULL)
	{
		return false;
	}
	return true;
}
//js cookie����

//�趨Cookieֵ
function setCookie(name, value)
{
	var expdate = new Date();
	expdate.setTime(expdate.getTime() + 30 * 60 * 1000);
	document.cookie = name + "=" + escape (value) + ";expires=" + expdate.toGMTString() + ";path=/; domain=.5jia1.com;";
}

//ɾ��Cookie
function delCookie(name)
{
	var exp = new Date();
	exp.setTime (exp.getTime() - 1);
	var cval = getCookie (name);
	document.cookie = name + "=" + cval + "; expires="+ exp.toGMTString();
}

//���Cookie������ֵ
function getCookieVal(offset)
{
	var endstr = document.cookie.indexOf (";", offset);
	if (endstr == -1)
	endstr = document.cookie.length;
	return unescape(document.cookie.substring(offset, endstr));
}

//���Cookie��ԭʼֵ
function getCookie(name)
{
	var arg = name + "=";
	var alen = arg.length;
	var clen = document.cookie.length;
	var i = 0;
	while (i < clen)
	{
		var j = i + alen;
		if (document.cookie.substring(i, j) == arg)
		return getCookieVal (j);
		i = document.cookie.indexOf(" ", i) + 1;
		if (i == 0) break;
	}
	return null;
}

function initIndex(url, pars, z, meth)
{
	meth = meth ? meth : "get" ;
    var myAjax = new Ajax.Updater(
                      z, url, {method: meth, parameters: pars, evalScripts: true}
                      );
}