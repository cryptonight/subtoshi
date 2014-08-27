function format8(str){
	str = xpnd(str);
	if(str.indexOf(".") == -1){
		return str;//+".00000000";
	}else{
		var part1 = str.split(".")[0];
		var part2 = str.split(".")[1];
		//while(part2.length < 8){
		//	part2 = part2 + "0";
		//}
		var part3 = "";
		if(part2.length > 8){
			if(parseInt(part2.substring(8)) != 0){
				part3 = "<span class='smallNumber'>" + part2.substring(8) + "</span>";
			}
			part2 = part2.substring(0,8);
		}
		return part1 + "." + part2 + part3;
	}
}

function format8input(str){
	str = xpnd(str);
	if(str.indexOf(".") == -1){
		return str;//+".00000000";
	}else{
		var part1 = str.split(".")[0];
		var part2 = str.split(".")[1];
		//while(part2.length < 8){
		//	part2 = part2 + "0";
		//}
		var part3 = "";
		if(part2.length > 8){
			if(parseInt(part2.substring(8)) != 0){
				part3 = "" + part2.substring(8) + "";
			}
			part2 = part2.substring(0,8);
		}
		return part1 + "." + part2 + part3;
	}
}

function xpnd(str){
	str = str+"";
	str = str.toLowerCase();
	str = str.split("+").join("");
	if(str.indexOf("e") == -1){
		return str;
	}

	if(str.indexOf("e-") != -1){
		var zerostoremove = str.split("e-")[1];
		str = str.split("e-")[0];
		for(var i=0;i<zerostoremove-1;i++){
			str = "0"+str;
		}
		return "0."+str.split(".").join("");
	}

	var esplit = str.split("e");
	var part1 = esplit[0];
	var zerostoadd = "";
	if(part1.indexOf(".") == -1){
		zerostoadd = esplit[1];
	}else{
		zerostoadd = math.eval(esplit[1]+"-"+part1.split(".")[1].length);
	}
	var part2 = "";
	for(var i=0;i<zerostoadd;i++){
		part2 += "0";
	}
	part1 = part1.split(".").join("");
	return part1+""+part2;
}

function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}