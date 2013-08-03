var EDITOR_DEFAULT_VALUE = "";				//全局变量,用于向Editor控件传递值

function init()
{
	var obj = document.getElementById("taContent");
	obj.value = "<b>粗体</b><i>斜体</i>";
}

//containername:存放iframe的容器名称 framename:HtmlEditor的iframe名称 editorpath:编辑器index.html的路径
function createEditor(containername,framename,editorpath)
{
	if(!document.getElementById(framename))
	{
		var HTMLEDITOR = document.createElement("iframe");
		HTMLEDITOR.id = framename;
		HTMLEDITOR.name = framename;
		HTMLEDITOR.src = editorpath;
		HTMLEDITOR.frameBorder = "0";
		HTMLEDITOR.marginHeight = "0";
		HTMLEDITOR.marginWidth = "0";
		HTMLEDITOR.height = "238";
		HTMLEDITOR.width = "400";
		
		document.getElementById(containername).appendChild(HTMLEDITOR);
	}
}

//设置初始值
function setEditorDefaultValue(text)
{
	EDITOR_DEFAULT_VALUE = text;
}

//得到textarea的值
function getTextareaValue()
{
	var obj = document.getElementById("taContent");
	return obj.value;
}

//获得HtmlEditor的带格式文本 framename:HtmlEditor的iframe名称
function getEditorHTML(framename)
{
	var html = window.frames[framename].frames["HtmlEditor"].document.getElementsByTagName("BODY")[0].innerHTML;
	if ( (html.toLowerCase() == "<p>&nbsp;</p>") || (html.toLowerCase() == "<p></p>") )
	{
		html = "";
	}
	return html;
}

//设置HtmlEditor的文本 framename:HtmlEditor的iframe名称 html_text:带格式的文本
function setEditorText(framename,html_text)
{	
	HtmlEditor_Default_Value = html_text;
	
	if(window.frames[framename].frames["HtmlEditor"] != null)
	{
		var html = window.frames[framename].frames["HtmlEditor"].document.getElementsByTagName("BODY")[0];
		html.innerHTML = HtmlEditor_Default_Value;
	}
}