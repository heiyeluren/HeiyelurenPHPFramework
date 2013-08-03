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
