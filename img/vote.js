var diamSelColor = '#00ff00';
var diamTexColor = '#ff0000';

var isvote = 0;
function GetObj(id) {
	return document.getElementById(id);
}

function diamChgBgColor(tdObj) {
	var x;
	var y;
	var bb = GetObj('VBOX');
	
	if (tdObj.bgColor != diamSelColor) {
		tdObj.bgColor = diamSelColor;
		tdObj.style.color = diamTexColor;
	}
	
	for (y=0; y<bb.rows.length; y++) {
		for (x=0; x<bb.rows[y].cells.length; x++) {
			if (bb.rows[y].cells[x] != tdObj) {
				bb.rows[y].cells[x].bgColor = '';
				bb.rows[y].cells[x].style.color = '';
			}
		}
	}
	
}

function diamChgBgColor2(tdObj) {
	if (isvote == 0 && tdObj.bgColor != diamSelColor) {
		tdObj.bgColor = diamSelColor;
		tdObj.style.color = diamTexColor;
		isvote = 1;
	} else {
		if (isvote == 1 && tdObj.bgColor == diamSelColor) {
			isvote = 0;
		}
		tdObj.bgColor = '';
		tdObj.style.color = '';
	}
}
function diamCheckNum(str) {
	var x;
	var y;
	var c = 0;
	var n = '';
	var bb = GetObj('VBOX');
	var vs = GetObj('situation');
	
	
	for (y=0; y<bb.rows.length; y++) {
		for (x=0; x<bb.rows[y].cells.length; x++) {
			if (bb.rows[y].cells[x].bgColor == diamSelColor) {
				c++;
				n = bb.rows[y].cells[x].name;
				if (typeof(n) == 'undefined' || n == '') {
					n = bb.rows[y].cells[x].id;
				}
			}
		}
	}
	
	if (c == 0) {
		if(vs == null) {
			alert('請選擇');
			return false;
		} else {
			if(!(vs.value.equals('OWLMAN_DO')) || (vs.value.equals('PENGU_DO'))) { 
				alert('請選擇');
				return false;
			} else {
				alert('已經放棄行動');
			}
		}
	}
	if (c > 1) {
		alert('不能選一項以上');
		return false;
	}
	
	if (confirm("確定此動作?") == false) {
    	return false;
    }
	
	bb = GetObj('diamvote');
	str.value = n;
	bb.submit();
}