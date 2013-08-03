/********************************
 *  ����������
 *  2007-04-11
 ********************************/

//�޳��հ�
function trim(s){
	return s.replace(/(^\s*)|(\s*$)/g, "");
}

//�Ƿ���email��ַ
function isEmail(s){
	return s.search(/^\s*[\w\~\-\.]+\@[\w\~\-]+(\.[\w\~\-]+)+\s*$/g) >= 0;
}

//�Ƿ����ֻ�����
function isMobile(s){
	return /^(13|15)\d{9}$/.test(s);
}

//�Ƿ���һ������
function isNumber(s){
	return /^\d+$/.test(s);
}

//����Ƿ��ж���
function checkComma(s){
	return /,/.test(s);
}

//���������Ƿ���IE
function chk_broswer(){
	if (navigator.appName.indexOf("Microsoft")!=-1) {
		return(true);
	}else{
		return(false);
	}
}

//���������Ƿ���IE
function checkBrowser(){
	if (navigator.appName.indexOf("Microsoft")!=-1) {
		return(true);
	}else{
		return(false);
	}
}

//ѡ������ѡ��򣨸�ѡ�򡢵�ѡ��
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

//ѡ������select
function selectAll(objSelect, isSelected){
	if(objSelect.length){
		for(var i = 0; i < objSelect.length; i++){
			objSelect[i].selected = isSelected ? true : false;
		}
	}
}

//�����û��ѡ������һ����ѡ����ߵ�ѡ��
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

//�ж��Ƿ�ѡ����һ��select
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

//�����û�г��������
function checkMaxLength(textBox) {
	if(textBox.maxlength){
		if (textBox.value.length > textBox.maxlength){
			textBox.blur();
			textBox.value = textBox.value.substring(0, textBox.maxlength);
		}
	}
}

//���浱ǰpost
function saveCurrentPos(textBox){
	if (textBox.createTextRange) {
		textBox.currentPos = document.selection.createRange().duplicate();
	}
}

//����һ��Radio��ֵ
function setRadioValue(obj,value){
	for(var i = 0;i<obj.length;i++){
		if(obj[i].value == value){
			obj[i].checked = true;
			break;
		}
	}
}

//����һ�������ֵ
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

//��ȡһ��Radio��ֵ
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

//�Զ���Ӧframe
function resizeFrame(id){
	far w = eval(id);
	var f = document.getElementById(id);
	f.style.height = w.document.body.scrollHeight + "px";
}

//ģ��PHP��hex2bin
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

//��������������෴
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

//html��ǩ�滻
function text2html(t){
	return t.replace(/&/g, "&amp;").replace(/ /g, "&nbsp;").replace(/\t/g, "&nbsp;&nbsp;&nbsp;&nbsp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\r?\n/g, "<br />\n");
}

