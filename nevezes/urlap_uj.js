$(function() {
	$("[name=adathordozo]").click(function(){
          if ($(this).val() == 3) {
            $("#upload").show('slow');
            ajaxGetFileList();
          } else {
            $("#upload").hide();
          }
	});
});

var currYear = (new Date()).getFullYear();

$(document).ready(function() {
	$('select').not('.csapattagok').formSelect();

	M.FormSelect.getInstance(document.getElementById("tipus")).dropdown.options.closeOnClick = false;
	$("#tipus").on('change', function() {
		M.FormSelect.getInstance($(this)).dropdown.close();
	});

	$("#bezar").click(function() {
		//window.parent.forceClose = true;
		//window.parent.$('#application').modal('hide');
		window.parent.hideApplicationFormModal();
	});

	$("#eltesz").click(function() {
		ajaxSaveData("eltesz");
	});

	$("#lezar").click(function() {
		ajaxCloseForm();
	});

	var selected = $("input[type='radio'][name='adathordozo']:checked");
	if (selected.length == 1 && selected.val() == 3) {
		selected.click();
	} else {
		$("#upload").hide();
	}

	document.getElementById("hiba").style.display = "none";
	document.getElementById("hiba_bottom").style.display = "none";

	csapatletszam();
});

function ajaxGetFileList() {
	var formData = new FormData();
	formData.append("cmd", "getfilelist");
	formData.append("id", $("input[name='id']").val());
	var oXHR = new XMLHttpRequest();
	oXHR.open("POST", "interface.php");
	oXHR.addEventListener("load", ajaxGetFileListDone, false);
	oXHR.send(formData);
}

function ajaxGetFileListDone(e) {
	var data = JSON.parse(this.response);
	if (data.response.code == 0) {
		showFileList(data);
	}
}

function ajaxDeleteFile(fid, id) {
	if (confirm("Biztosan törlöd a fájlt?")) {
		var formData = new FormData();
		formData.append("cmd", "deletefile");
		formData.append("id", id);
		formData.append("fid", fid);
		var oXHR = new XMLHttpRequest();
		oXHR.open("POST", "interface.php");
		oXHR.addEventListener("load", ajaxGetFileListDone, false);
		oXHR.send(formData);
	}
}

function showFileList(data) {
	var allFileRows = $(".filerow").map(function() {
		return this.parentNode.removeChild(this);
	}).get();
	var files_table = document.getElementById("fajl_tabla");
	if (data.response.files.length > 0)
		document.getElementById("feltoltes").style.display = "none";
	for (i=0; i<data.response.files.length; i++) {
		var file_row = document.createElement("tr");
		file_row.className = "row center filerow";
		file_td1 = document.createElement("td");
		file_td1.className = "cell dotted-cell";
		file_td1.appendChild(document.createTextNode(data.response.files[i].fname));
		file_row.appendChild(file_td1);
		file_td2 = document.createElement("td");
		file_td2.className = "cell dotted-cell";
		file_td2.appendChild(document.createTextNode((Math.round(data.response.files[i].filesize / 1024 / 1024 * 10) / 10) + " MB"));
		file_row.appendChild(file_td2);
		file_td3 = document.createElement("td");
		file_td3.className = "cell dotted-cell";
		file_td3.appendChild(document.createTextNode(data.response.files[i].upload_date));
		file_row.appendChild(file_td3);
		file_td4 = document.createElement("td");
		file_td4.className = "cell dotted-cell";
		file_td4_button = document.createElement("a");
		file_td4_button.className = "btn-floating red";
		file_td4_button.href = "javascript:ajaxDeleteFile(" + data.response.files[i].fid + ", " + $("input[name='id']").val() + ")";
		file_td4_button_content = document.createElement("i");
		file_td4_button_content.className = "material-icons";
		file_td4_button_content.innerHTML = "delete";
		file_td4_button.appendChild(file_td4_button_content);
		file_td4.appendChild(file_td4_button);
		file_row.appendChild(file_td4);
		files_table.appendChild(file_row);
	}
}

function csapatletszam() {
	var selects = document.querySelectorAll('select.csapattagok')
	for (var i = 0; i < selects.length; i++) {
		var tsel = M.FormSelect.getInstance(selects[i]);
		if (tsel != undefined) tsel.destroy();
	}
	var csapat_div = document.getElementById("csapatdiv");
	if (document.forms["urlap"].elements["csapat"].value == 2) {
		var allCsapattagDiv = $(".csapattag_container").map(function() {
			return this.parentNode.removeChild(this);
		}).get();
		if (document.forms["urlap"].elements["letszam"].value == 1) document.forms["urlap"].elements["letszam"].value = 2;
		var csapattagok_fieldset = document.getElementById("csapattagok");
		var csapattag_data = document.getElementById("csapattag_template");
		for (i=1; i<document.forms["urlap"].elements["letszam"].value; i++) {
			var uj_csapattag = csapattag_data.cloneNode(true);
			uj_csapattag.className = "csapattag_container";
			uj_csapattag.id = "csapattag_" + i;
			uj_csapattag.style.display = "block";
			uj_csapattag.getElementsByClassName('csapattag_nev')[0].id = "csapattag_nev_" + i;
			uj_csapattag.getElementsByClassName('csapattag_nem')[0].id = "csapattag_nem_" + i;
			uj_csapattag.getElementsByClassName('csapattag_szul')[0].id = "csapattag_szul_" + i;
			uj_csapattag.getElementsByClassName('csapattag_polo')[0].id = "csapattag_polo_" + i;

			csapattagok_fieldset.appendChild(uj_csapattag);
			$("#csapattag_szul_" + i).datepicker({
        defaultDate: csapattagok_array[i-1] && csapattagok_array[i-1].szul ? new Date(csapattagok_array[i-1].szul) : null,
				setDefaultDate: true,
				maxDate: new Date(),
        id: i,
				minDate: new Date(currYear-18,0,1),
				yearRange: [currYear-18, currYear],
				format: "yyyy-mm-dd",
				firstDay: 1,
				i18n: {
					cancel: "mégsem",
					weekdaysAbbrev: ['V','H','K','SZ','CS','P','SZ'],
					months:	['Január', 'Február', 'Március', 'Április', 'Május', 'Június', 'Július', 'Augusztus', 'Szeptember', 'Október', 'November', 'December'],
					monthsShort:	['Jan', 'Feb', 'Már', 'Ápr', 'Máj', 'Jún', 'Júl', 'Aug', 'Szep', 'Okt', 'Nov', 'Dec'],
					weekdays:	['Vasárnap', 'Hétfő', 'Kedd', 'Szerda', 'Csütörtök', 'Péntek', 'Szombat'],
					weekdaysShort: ['Va', 'Hé', 'Ke', 'Szed', 'Csü', 'Pé', 'Szo']
				},
				onDraw: function(){
          let id = this.id;
					setTimeout(function(){
						$(".datepicker-modal").css('top', $("#csapattag_szul_"+id).offset().top + "px");
					},500)
				}
			});
		}
		var nevLabels = document.getElementsByClassName("csapattag_sorszam");
		for (i=1; i<nevLabels.length; i++) {
			nevLabels[i].firstChild.innerHTML = (i+1) + ". csapattag neve";
		}
		var nevInputs = document.getElementsByClassName("csapattag_nev");
		for (i=1; i<nevInputs.length && i<=csapattagok_array.length; i++) {
			nevInputs[i].value = csapattagok_array[i-1].nev;
		}
		var nemSelects = document.getElementsByClassName("csapattag_nem");
		for (i=1; i<nemSelects.length && i<=csapattagok_array.length; i++) {
			nemSelects[i].value = csapattagok_array[i-1].nem;
		}
		var poloSelects = document.getElementsByClassName("csapattag_polo");
		for (i=1; i<poloSelects.length && i<=csapattagok_array.length; i++) {
			poloSelects[i].value = csapattagok_array[i-1].polo;
		}
		csapat_div.style.display = "block";
		document.forms["urlap"].elements["letszam"].disabled = false;

		for (i=1; i<document.forms["urlap"].elements["letszam"].value; i++) {
		  $('#csapattag_' + i + " .csapattag_nem").formSelect()
        M.FormSelect.getInstance($('#csapattag_' + i + " .csapattag_nem")).dropdown.options = false;
		  $('#csapattag_' + i + " .csapattag_nem").on('change', function() {
				M.FormSelect.getInstance($(this)).dropdown.close();
		  });
		  $('#csapattag_' + i + " .csapattag_polo").formSelect()
        M.FormSelect.getInstance($('#csapattag_' + i + " .csapattag_polo")).dropdown.options = false;
		  $('#csapattag_' + i + " .csapattag_polo").on('change', function() {
			M.FormSelect.getInstance($(this)).dropdown.close();
		  });
    }
	} else {
		document.forms["urlap"].elements["letszam"].value = 1;
		document.forms["urlap"].elements["letszam"].disabled = true;
		csapat_div.style.display = "none";
	}
        M.updateTextFields();
}

function ajaxSaveData(button) {
	document.getElementById("button_div").style.display = "none";
	var formData = new FormData(document.forms["urlap"]);
	formData.append("cmd", "savedata");
	var oXHR = new XMLHttpRequest();
	oXHR.open("POST", "interface.php");
	if (button == "eltesz") ajaxSaveDataDone = ajaxSaveDataDoneClose;
	if (button == "lezar") ajaxSaveDataDone = ajaxSaveDataDoneCheck;
	if (button == "feltolt") ajaxSaveDataDone = ajaxSaveDataDoneFile;
	oXHR.addEventListener("load", ajaxSaveDataDone, false);
	oXHR.send(formData);
}

function ajaxSaveDataDoneClose(e) {
	window.parent.hideApplicationFormModal();
}

function ajaxSaveDataDoneCheck(e) {
	var data = JSON.parse(this.response);
	if (data.response.code == 0) {
		document.forms["urlap"].elements["id"].value = data.response.nevezes_id;
	}
	var formData = new FormData(document.forms["urlap"]);
	formData.append("cmd", "closeform");
	var oXHR = new XMLHttpRequest();
	oXHR.open("POST", "interface.php");
	oXHR.addEventListener("load", ajaxCloseFormDone, false);
	oXHR.send(formData);
}

function ajaxSaveDataDoneFile(e) {
	var data = JSON.parse(this.response);
	if (data.response.code == 0) {
		document.forms["urlap"].elements["id"].value = data.response.nevezes_id;
		uploadFile(fileHandleEvent);
	}
}

function ajaxCloseForm() {
	document.getElementById("hiba").style.display = "none";
	document.getElementById("hiba_bottom").style.display = "none";
	document.getElementById("feltoltes").style.display = "none";
	$(".invalid").toggleClass("invalid");
	$(".input-alert").toggleClass("input-alert");
	ajaxSaveData("lezar");
}

function ajaxCloseFormDone(e) {
	document.getElementById("button_div").style.display = "block";
	var data = JSON.parse(this.response);
	if (data.response.code == 0) {
		window.parent.hideApplicationFormModal();
	} else if (data.response.code == 2) {
		document.getElementById("hiba").style.display = "block";
		document.getElementById("hiba_bottom").style.display = "block";
		for (i=0; i<data.response.errors.length; i++) {
			var item = document.forms["urlap"].elements[data.response.errors[i]]
			if (item) {
				if (item.length && item[0].type == "radio") {
					$("#radiobox_" + data.response.errors[i]).toggleClass("input-alert");
				} else if (item.id.indexOf("csapattag_nem") != -1 ) {
					$(item).parent().children().toggleClass("invalid");
				} else {
					$(item).toggleClass("invalid");
				}
			} else {
				e = document.getElementById(data.response.errors[i]);
				if (e) e.style.display = "block";
			}
		}
	}
}

function handleFileSelect(e) {
	var uFile = e.target.files[0];
	if (uFile.size > 1073741824) {
		alert("Túl nagy fál (>1G)!");
	} else {
		document.getElementById("progressbar_div").style.display = "block";
		document.getElementById("fileselect").style.display = "none";
		document.getElementById("button_div").style.display = "none";
		if (document.forms["urlap"].elements["id"].value == 0) {
			fileHandleEvent = e;
			ajaxSaveData("feltolt");
		} else {
			uploadFile(e);
		}
	}
}

function uploadFile(e) {
	document.getElementById("feltoltes").style.display = "none";
	var uFile = e.target.files[0];
	var formData = new FormData();
	formData.append("cmd", "uploadfile");
	formData.append("id", $("input[name='id']").val());
	formData.append("uploadfile", uFile, uFile.name);
	var oXHR = new XMLHttpRequest();
	oXHR.upload.filename = uFile.name;
	oXHR.upload.addEventListener("progress", uploadProgress , false);
	oXHR.addEventListener("load", uploadFinish, false);
	oXHR.addEventListener("error", uploadError, false);
	oXHR.addEventListener("abort", uploadAbort, false);
	oXHR.open("POST", "interface.php");
	oXHR.send(formData);
}

function uploadProgress(e) {
	if (e.lengthComputable) {
		var val = Math.round((e.loaded/e.total) * 100);
		$('.progress-bar').css('width', val+'%').attr('aria-valuenow', val);
	}
}

function uploadFinish() {
	$('.progress-bar').css('width', '0%').attr('aria-valuenow', 0);
	document.getElementById("progressbar_div").style.display = "none";
	document.getElementById("button_div").style.display = "block";
	document.getElementById("fileselect").style.display = "inline-block";
	var data = JSON.parse(this.response);
	if (data.response.code == 0) {
		showFileList(data);
	} else {
		uploadError();
	}
}

function uploadError() {
	alert("uploadError");
}

function uploadAbort() {
	alert("uploadAbort");
}

function selectFile() {
	document.getElementById("upFile").addEventListener("change", handleFileSelect, false);
	document.getElementById("upFile").sender = this;
	document.getElementById("upFile").click();
}
