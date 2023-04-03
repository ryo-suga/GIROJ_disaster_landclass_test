/**
 * ブラウザバックへの対応として利用。(都道府県、市区町村のパラメータ保持）
 */
window.onload = function(){
	setSelectNameHistory();
}

/**
 * もどるボタン調整
 */
var displayed_page = new Array();
var backup_searchdata = new Array(2);
var displaying_page = PAGE_CONDITION;
//履歴管理
function addHistoryPage(page) {
	switch(page) {
		case PAGE_CONDITION:
			var add_array = new Array(2);
			add_array[0] = PAGE_CONDITION;
			displayed_page.push(add_array);
			break;
		case PAGE_DETAIL:
			var add_array = new Array(2);
			add_array[0] = PAGE_DETAIL;
			var searchdata = new Array(2);
			searchdata[0] = backup_searchdata[0];
			searchdata[1] = backup_searchdata[1];
			add_array[1] = model;	
			displayed_page.push(add_array);
			break;
	}
}

/**
 *　都道府県選択
 */
function selectPrefectures() {
	//hiddenパラムをnull
	document.getElementById("appformSelectedMunicipalityIdx").value = null;

	//市区町村再検索
	setMunicipality();
}

/**
 *　市区町村選択
 */
function selectMunicipalityName() {
	//hiddenパラムをセット
	document.getElementById("appformSelectedMunicipalityIdx").value = document.getElementById("appformMunicipality").selectedIndex;
}

//リスト取得
//=================================================================================================
/**
 *　市区町村リスト取得
 */
function setMunicipality() {
	var prefectures = $("#appformPrefectures").val();
	var form_info = { "prefectures" : prefectures };
	$.post({
		url:METHOD_GET_MUNICIPALITYLIST,
		data:form_info,
	}).done(function(d){
			var data = JSON.parse(d);
			$("#appformMunicipality").children().remove();
			for(var i in data) {
				$("#appformMunicipality").append($("<option>").html(escapeHtml(data[i]["view"])).val(escapeHtml(data[i]["value"])));
			}
			if(data.length <= 1) {document.getElementById("appformMunicipality").disabled = true;}
			else { document.getElementById("appformMunicipality").disabled = false; }
		}
	).fail( function(){
		//エラー表示
		showErrorInternal();
		}
	);
}

//ページ遷移用 リスト表示処理
//-------------------------------------------------------------------------------------------------
/**
 *　選択履歴反映
 */
function setSelectNameHistory() {
	var prefectures = $("#appformPrefectures").val();
	var form_info = { "prefectures" : prefectures };
	$.post({
		url:METHOD_GET_MUNICIPALITYLIST,
		data:form_info,
	}).done(function(d){
			var data = JSON.parse(d);
			$("#appformMunicipality").children().remove();
			for(var i in data) {
				$("#appformMunicipality").append($("<option value>").html(escapeHtml(data[i]["view"])).val(escapeHtml(data[i]["value"])));
			}
			if(data.length <= 1) {document.getElementById("appformMunicipality").disabled = true;}
			else { document.getElementById("appformMunicipality").disabled = false; }
			//選択履歴がある
			if( document.getElementById("appformSelectedMunicipalityIdx").value != null 
			&& document.getElementById("appformSelectedMunicipalityIdx").value != "")  {
				//hiddenparamを選択
				var selected_idx = document.getElementById("appformSelectedMunicipalityIdx").value;
				document.getElementById("appformMunicipality").options[selected_idx].selected = true;
			}
		}
	).fail( function(){
		//エラー表示
		showErrorInternal();
		}
	);
}
//条件検索機能
//=================================================================================================

/**
 *　検索関数
 */
 function searchLandclassData() {
	var prefectures = document.getElementById('appformPrefectures').value;
	var municipality = document.getElementById('appformMunicipality').value;
	
	selectList(prefectures,municipality);
}

//詳細表示機能
//=================================================================================================
/**
 *　リスト選択
 *　@oaram　String prefectures, municipality
 */
function selectList(prefectures,municipality) {
	var form_info = { "prefectures"   : prefectures,
					  "municipality"	 : municipality
					};
	$.post({
		url:METHOD_SEARCH_DETAIL,
		data:form_info,
		}).done( function(d){ 
				var data = JSON.parse(d);
				if (data["is_error"] == true) {
					showControlError(data["result_data"]);
				}
				if (data["is_error"] == false) {
					setDetail(data["result_data"]);
				}
			}
		).fail( function() {
				//エラー処理
				showErrorInternal();
				}
		);	
}

/**
 *　詳細表示（HTML生成）
 *　@oaram　String array data
 */
function setDetail(data) {
	
	document.getElementById("prefectureValue").textContent 				= escapeHtml(data[0]["prefectures"]);
	document.getElementById("municipalityValue").textContent 					= escapeHtml(data[0]["municipality"]);

	$("#landClassItem0").hide();
	$("#landClassItem1").hide();
	$("#landClassItem2").hide();
	$("#landClassItem3").hide();
	$("#landClassItem4").hide();
	$("#landClassItem5").hide();

	for(var i in data) {
		$("#landClassItem" + i).show();
		document.getElementById("landClassName" + i).innerHTML		= escapeHtml(data[i]["landclass_name"]);
		document.getElementById("landClass" + i).innerHTML		= "<strong>" + escapeHtml(data[i]["landclass"]) + "</strong>";
	}
		
	displaying_page = PAGE_DETAIL;
	backup_searchdata[0] = data["prefectures"];
	backup_searchdata[1] = data["municipality"];

	//表示
	$('.searchTDetail').show();
	$(".searchError").hide();
	$('.searchTArea').hide();
	
	//スクロール
	scroll('.searchTDetail');	
}

/**
 *　エラー表示
 *　@oaram　String array error_msgs
 */
function showControlError(error_msgs) {
	var disp_error = "";
	for(var i= 0; i<error_msgs.length ; i++) {
		disp_error += "<p class='red tCenter'>" + escapeHtml(error_msgs[i]) + "</p>";
	}
	$(".searchError").html(disp_error);
	$('.searchTArea').hide();
	$('.searchTDetail').hide();		
	$(".searchError").show();
	//スクロール
	scroll('.searchError');
	//クリア
	displayed_page = new Array();
	displaying_page = PAGE_CONDITION;
}

/**
 *　もどるボタン押下
 */
function backPage() {
	if(displayed_page[0] == null) { addHistoryPage(PAGE_CONDITION);}
	var target = displayed_page.pop();
	switch (target[0]) {
		case PAGE_CONDITION:
			$('.searchTArea').hide();
			$('.searchTDetail').hide();
			displaying_page = PAGE_CONDITION;
			break;
		case PAGE_DETAIL:
			var prefectures = target[1][0];
			var municipality = target[1][1];
			selectList(prefectures,municipality);
			break;
	}
}

//エラー処理
//=================================================================================================
function showErrorNotFound(){
	window.location.href = METHOD_ERROR_400;
}

function showErrorInternal(){
	window.location.href = METHOD_ERROR_500;
}

//テキストボックス上でのエンターキー制御
//=================================================================================================
function pressEnterKey(key_pressed) {
	if( key_pressed === 13) { searchLandclassData(); }
}

//文字列のエスケープ
//=================================================================================================
function escapeHtml(str) {
	//String型変換
	str = str+'';
    str = str.replace(/&/g, '&amp;');
    str = str.replace(/</g, '&lt;');
    str = str.replace(/>/g, '&gt;');
    str = str.replace(/"/g, '&quot;');
    str = str.replace(/'/g, '&#39;');
    return str;
}

//画面のスクロール処理
//=================================================================================================
function scroll(tag) {
	var off = $(tag).offset();
	
	//レイアウト担当作成関数利用。
	$('body,html').animate({
	scrollTop : off.top
	},550);
}