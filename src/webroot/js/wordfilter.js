var string_body = /非法关键字1|非法关键字2/i;
var string_title = /非法关键字1|非法关键字2/i;
var string_username = /非法关键字1|非法关键字2/i;

function wordFilter(input_content,deny_type){
	var regex_pattern;
	switch(deny_type){
		case 'B':
			regex_pattern = string_body;
			break;
		case 'T':
			regex_pattern = string_title;
			break;
		case 'U':
			regex_pattern = string_username;
			break;
		default:
			regex_pattern = string_body;
			break;
	}

	if(regex_pattern.test(input_content)){//true
		if(deny_type == 'B' || deny_type == 'T'){
			if(confirm(CONFIRM_DENY_WORDS)){
				window.open("/help/wordfilter_check.php");
			}
		}else{
			alert(FOUND_DENY_WORDS);
		}
		return true;
	}else{//false
		return false;
	}
}



function reg_deny_word(str)
{
	myRe=/非法关键字1|非法关键字2/gi;
	ii = 0 ;
	var regs = "" ;
	while(ii < str.length)
	{
		myArray = myRe.exec(str);
		if (myArray)
		{
			regs += ""+myArray[0]+"," ;
			ii = myRe.lastIndex ;
		}
		else
		{
			ii = str.length +1 ;
			break ;
		}
	}
	
	
	if (regs!='')
	{
		regs = regs.substr(0,(regs.length-1)) ;
		regs = "\""+regs+"\"";
	}
	return regs ;
}