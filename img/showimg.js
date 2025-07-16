var offsetfrommouse=[5,5];
var defaultimageheight = 2;
var defaultimagewidth = 2;

function gettrailobj(){
	return document.getElementById("showimg_div").style
}

function gettrailobjnostyle(){
	return document.getElementById("showimg_div")
}


function truebody(){
	return (!window.opera && document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}


function hideimage(){	
	gettrailobj().display= "none";
}

function showimage(imagename,vv){
	if(imagename == "user_-1") {
		return;
	}
	if (vv == 1) {
		var img_src = document.getElementById(imagename).src;
		var width = document.getElementById(imagename).width;
		var height = document.getElementById(imagename).height;
	}
	var docwidth=document.all? truebody().scrollLeft+truebody().clientWidth : pageXOffset+window.innerWidth - offsetfrommouse[0]
	var docheight=document.all? Math.min(truebody().scrollHeight, truebody().clientHeight) : Math.min(window.innerHeight)

	if( (navigator.userAgent.indexOf("Konqueror")==-1  || navigator.userAgent.indexOf("Firefox")!=-1 || (navigator.userAgent.indexOf("Opera")==-1 && navigator.appVersion.indexOf("MSIE")!=-1)) && (docwidth>650 && docheight>500)) {
		( width == 0 ) ? width = defaultimagewidth: '';
		( height == 0 ) ? height = defaultimageheight: '';
			
		defaultimageheight = height
		defaultimagewidth = width
	
		document.onmousemove=followmouse;
		
		newHTML = '<img src="' + img_src + '"  width="'+width+'" height="'+height+'" />'; 
		
		if(navigator.userAgent.indexOf("MSIE")!=-1 && navigator.userAgent.indexOf("Opera")==-1 ){
			newHTML = newHTML+'<iframe src="about:blank" scrolling="no" frameborder="0" width="'+width+'" height="'+height+'"></iframe>';
		}

		gettrailobjnostyle().innerHTML = newHTML;
		gettrailobj().display="block";
	}
}

function followmouse(e){

	var xcoord=offsetfrommouse[0]
	var ycoord=offsetfrommouse[1]

	var docwidth=document.all? truebody().scrollLeft+truebody().clientWidth : pageXOffset+window.innerWidth-15
	var docheight=document.all? Math.min(truebody().scrollHeight, truebody().clientHeight) : Math.min(window.innerHeight)

	if (typeof e != "undefined"){
		if (docwidth - e.pageX < defaultimagewidth + 2*offsetfrommouse[0]){
			xcoord = e.pageX - xcoord - defaultimagewidth;
		} else {
			xcoord += e.pageX;
		}
		if (docheight - e.pageY < defaultimageheight + 2*offsetfrommouse[1]){
			ycoord += e.pageY - Math.max(0,(2*offsetfrommouse[1] + defaultimageheight + e.pageY - docheight - truebody().scrollTop));
		} else {
			ycoord += e.pageY;
		}

	} else if (typeof window.event != "undefined"){
		if (docwidth - event.clientX < defaultimagewidth + 2*offsetfrommouse[0]){
			xcoord = event.clientX + truebody().scrollLeft - xcoord - defaultimagewidth;
		} else {
			xcoord += truebody().scrollLeft+event.clientX
		}
		if (docheight - event.clientY < (defaultimageheight + 2*offsetfrommouse[1])){
			ycoord += event.clientY + truebody().scrollTop - Math.max(0,(2*offsetfrommouse[1] + defaultimageheight + event.clientY - docheight));
		} else {
			ycoord += truebody().scrollTop + event.clientY;
		}
	}
	gettrailobj().left=xcoord+"px"
	gettrailobj().top=ycoord+"px"

}