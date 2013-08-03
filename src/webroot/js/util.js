/********************************
 *  公共函数库
 *  2007-04-11
 ********************************/

//剔除空白
function trim(s){
	return s.replace(/(^\s*)|(\s*$)/g, "");
}

//是否是email地址
function isEmail(s){
	return s.search(/^\s*[\w\~\-\.]+\@[\w\~\-]+(\.[\w\~\-]+)+\s*$/g) >= 0;
}

//是否是手机号码
function isMobile(s){
	return /^(13|15)\d{9}$/.test(s);
}

//是否是一个数字
function isNumber(s){
	return /^\d+$/.test(s);
}

//检测是否有逗号
function checkComma(s){
	return /,/.test(s);
}

//检测浏览器是否是IE
function chk_broswer(){
	if (navigator.appName.indexOf("Microsoft")!=-1) {
		return(true);
	}else{
		return(false);
	}
}

//检测浏览器是否是IE
function checkBrowser(){
	if (navigator.appName.indexOf("Microsoft")!=-1) {
		return(true);
	}else{
		return(false);
	}
}

//选择所有选择框（复选框、单选框）
function checkAll(inputName, isChecked){
	var boxes = document.getElementsByName(inputName);
	if(boxes){
		for(var i = 0; i < boxes.length; i++){
			if(boxes[i].diSabled){
				continue;
			}
			boxes[i].checked = isChecked ? true : false;
		}
	}
}

//选定所有select
function selectAll(objSelect, isSelected){
	if(objSelect.length){
		for(var i = 0; i < objSelect.length; i++){
			objSelect[i].selected = isSelected ? true : false;
		}
	}
}

//检测有没有选择任意一个复选框或者单选框
function checkBoxSelected(inputName){
	var boxes = document.getElementsByName(inputName);
	if(boxes){
		for(var i = 0; i < boxes.length; i++){
			if(boxes[i].checked){
				return true;
			}
		}
	}
	return false;
}

//判断是否选择了一个select
function pickBoxSelected(pickBox){
	if(pickBox){
		for(var i = 0; i < pickBox.length; i++){
			if(pickBox[i].selected){
				return true;
			}
		}
	}
	return false;
}

//检查有没有超过最长长度
function checkMaxLength(textBox) {
	if(textBox.maxlength){
		if (textBox.value.length > textBox.maxlength){
			textBox.blur();
			textBox.value = textBox.value.substring(0, textBox.maxlength);
		}
	}
}

//保存当前post
function saveCurrentPos(textBox){
	if (textBox.createTextRange) {
		textBox.currentPos = document.selection.createRange().duplicate();
	}
}

//设置一个Radio的值
function setRadioValue(obj,value){
	for(var i = 0;i<obj.length;i++){
		if(obj[i].value == value){
			obj[i].checked = true;
			break;
		}
	}
}

//设置一个对象的值
function setValue(obj, value){
	if(obj){
		switch(obj.type){
			case "text" :
			case "password" :
			case "file" :
			case "textarea" :
			case "hidden" :
				obj.value = value;
				break;

			case "select-one" :
				for(var i = 0;i<obj.length;i++){
					if(obj.options[i].value == value){
						obj.options[i].selected = true;
						break;
					}
				}
				break;

			case "radio" :
				for(var i = 0;i<obj.length;i++){
					if(obj[i].value == value){
						obj[i].checked = true;
						break;
					}
				}
				break;

			default :
				obj.value = value;
				break;
		}
	}
}

//获取一个Radio的值
function getRadioValue(radioName){
	var boxes = document.getElementsByName(radioName);
	if(boxes){
		for(var i = 0;i<boxes.length;i++){
			if(boxes[i].checked){
				return boxes[i].value;
			}
		}
	}
	return "";
}

//自动适应frame
function resizeFrame(id){
	far w = eval(id);
	var f = document.getElementById(id);
	f.style.height = w.document.body.scrollHeight + "px";
}

//模仿PHP的hex2bin
function hex2bin(hex){
	var result = "";
	if(hex && hex.length && hex.length % 2 == 0){

		for(var i = 0 ;i<hex.length;i+=2){
			result += "%";
			result += hex.substr(i, 2);
		}
		result = decodeURIComponent(result);
	}
	return result;
}

//跟上面这个函数相反
function bin2hex(bin){
	var result = "";
	var temp = "";
	for(var i=0;i<bin.length;i++){
		var chr = bin.charCodeAt(i);
		if(chr>127){
			chr = encodeURIComponent(bin.charAt(i));
		}else{   
			chr = chr.toString(16);  
		}
			result += chr;
	} 

	for(var i=0;i<result.length;i++){
		var chr = result.charAt(i);
		if(chr!='%'){
			temp+=chr;
		}
	} 
	return temp.toLowerCase();
}

//html标签替换
function text2html(t){
	return t.replace(/&/g, "&amp;").replace(/ /g, "&nbsp;").replace(/\t/g, "&nbsp;&nbsp;&nbsp;&nbsp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\r?\n/g, "<br />\n");
}

