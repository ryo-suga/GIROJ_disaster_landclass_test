/**
 * ブラウザバックへの対応として利用。(メーカー名、車名、のパラメータ保持）
 */
window.onload = function(){
	setMakerNameHistory();
}

/**
 * もどるボタン調整
 */
var displayed_page = new Array();
var backup_model = new Array(3);
var displaying_page = PAGE_CONDITION;
//履歴管理
function addHistoryPage(page) {
	switch(page) {
		case PAGE_CONDITION:
			var add_array = new Array(2);
			add_array[0] = PAGE_CONDITION;
			displayed_page.push(add_array);
			break;
		case PAGE_LIST:
			if (displayed_page[displayed_page.length-1][0] != PAGE_LIST) {
				var add_array = new Array(2);
				add_array[0] = PAGE_LIST;
				displayed_page.push(add_array);
			}
			break;
		case PAGE_DETAIL:
			var add_array = new Array(2);
			add_array[0] = PAGE_DETAIL;
			var model = new Array(3);
			model[0] = backup_model[0];
			model[1] = backup_model[1];
			model[2] = backup_model[2];
			add_array[1] = model;	
			displayed_page.push(add_array);
			break;
	}
}

/**
 *　自動車ﾀｲﾌﾟ選択　型式
 */
function selectCarTypeModel() {
	//hiddenパラムをnull
	document.getElementById("appformSelectedMakerIdx").value = null;
	document.getElementById("appformSelectedCarIdx").value = null;

	//型式テキストエリアのクリア
	//----------------------------------------------------------------------------------------------
	document.getElementById("textareaModel").value = "";
	//メーカー名・車名検索項目リセット
	document.getElementById("appformPrefectures").value = "";
	//メーカ名再検索
	setMakerName();
	//メーカ名クリア
	document.getElementById("appformMakerName").value = "";
	//車名再検索
	setCarName();
}

/**
 *　自動車ﾀｲﾌﾟ選択　メーカー名・車名
 */
function selectCarTypeMakerCar() {
	//hiddenパラムをnull
	document.getElementById("appformSelectedMakerIdx").value = null;
	document.getElementById("appformSelectedCarIdx").value = null;

	//メーカ名再検索
	setMunicipality();
	//メーカ名クリア
	document.getElementById("appformMunicipality").value = "";
	//車名再検索
	//setCarName();

	//型式検索項目リセット
	//document.getElementById("appformCarTypeModel").value = "";
	//document.getElementById("textareaModel").value = "";
}

/**
 *　メーカ名選択
 */
function selectMakerName() {
	//hiddenパラムをセット
	document.getElementById("appformSelectedMakerIdx").value = document.getElementById("appformMakerName").selectedIndex;
	//hiddenパラムをnull
	document.getElementById("appformSelectedCarIdx").value = null;
	setCarName();

}

/**
 *　車名選択
 */
function selectCarName() {
	//hiddenパラムをセット
	document.getElementById("appformSelectedCarIdx").value = document.getElementById("appformCarName").selectedIndex;
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
		url:METHOD_GET_MUNICIPALITYRLIST,
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

/**
 *　車名リスト取得
 */
function setCarName() {
	var car_type_maker_car = $("#appformPrefectures").val();
	var maker_name = $("#appformMakerName").val();
	var form_info = {   "car_type_maker_car" : car_type_maker_car,
						"maker_name" : maker_name 
					};
	$.post({
		url:METHOD_GET_CARLIST,
		data:form_info,
	}).done(function(d){
				var data = JSON.parse(d);
				$("#appformCarName").children().remove();
				for(var i in data) {
					$("#appformCarName").append($("<option>").html(escapeHtml(data[i]["view"])).val(escapeHtml(data[i]["value"])));
				}
				if(data.length <= 1) { document.getElementById("appformCarName").disabled = true; }
				else { document.getElementById("appformCarName").disabled = false; }
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
 *　メーカー名リスト取得
 */
function setMakerNameHistory() {
	var car_type_maker_car = $("#appformPrefectures").val();
	var form_info = { "car_type_maker_car" : car_type_maker_car };
	$.post({
		url:METHOD_GET_MAKERLIST,
		data:form_info,
	}).done(function(d){
			var data = JSON.parse(d);
			$("#appformMakerName").children().remove();
			for(var i in data) {
				$("#appformMakerName").append($("<option value>").html(escapeHtml(data[i]["view"])).val(escapeHtml(data[i]["value"])));
			}
			if(data.length <= 1) {document.getElementById("appformMakerName").disabled = true;}
			else { document.getElementById("appformMakerName").disabled = false; }
			//選択履歴がある
			if( document.getElementById("appformSelectedMakerIdx").value != null 
			&& document.getElementById("appformSelectedMakerIdx").value != "")  {
				//hiddenparamを選択
				var selected_idx = document.getElementById("appformSelectedMakerIdx").value;
				document.getElementById("appformMakerName").options[selected_idx].selected = true;
				setCarNameHistory();
			}
		}
	).fail( function(){
		//エラー表示
		showErrorInternal();
		}
	);
}
/**
 *　車名リスト取得
 */
function setCarNameHistory() {
	var car_type_maker_car = $("#appformPrefectures").val();
	var maker_name = $("#appformMakerName").val();
	var form_info = {   "car_type_maker_car" : car_type_maker_car,
						"maker_name" : maker_name 
					};
	$.post({
		url:METHOD_GET_CARLIST,
		data:form_info,
	}).done(function(d){
				var data = JSON.parse(d);
				$("#appformCarName").children().remove();
				for(var i in data) {
					$("#appformCarName").append($("<option>").html(escapeHtml(data[i]["view"])).val(escapeHtml(data[i]["value"])));
				}
				if(data.length <= 1) { document.getElementById("appformCarName").disabled = true; }
				else { document.getElementById("appformCarName").disabled = false; }
				//選択履歴がある
				if( document.getElementById("appformSelectedCarIdx").value != null 
				&& document.getElementById("appformSelectedCarIdx").value != "")　　{
					//hiddenparamを選択
					var selected_idx = document.getElementById("appformSelectedCarIdx").value;
					document.getElementById("appformCarName").options[selected_idx].selected = true;
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
 *　検索関数（型式）
 */
function searchModel() {
	var car_type_model = document.getElementById("appformCarTypeModel").value;
	var model 		   = document.getElementById("textareaModel").value;
	var form_info = { "car_type_model" : car_type_model,
					  "model"	 : model
					};
	$.post({
		url:METHOD_SEARCH_MODEL,
		data:form_info,
	}).done( function(d) {			
				var data = JSON.parse(d);
				if (data["is_error"] == true) {
					showControlError(data["result_data"]);
				}
				//検索結果反映
				if (data["is_error"] == false) {
					//一件なら詳細表示へ遷移。
					if( data["result_data"].length == 1) {
						if(displaying_page == PAGE_DETAIL) {
							addHistoryPage(PAGE_DETAIL);
						} else if(displaying_page == PAGE_LIST){
						} else {
							addHistoryPage(PAGE_CONDITION);
						}
						selectList(data["result_data"][0]["model"], data["result_data"][0]["maker_name"], data["result_data"][0]["car_name"]);
					}
					else {
						addHistoryPage(PAGE_CONDITION);
						setList(data["result_data"],"「型式で検索」");
					}
				}
			}
		).fail( function(){
			//エラー表示
			showErrorInternal();
			}
		);
}

/**
 *　検索関数（メーカー名、車名）
 */
 function searchMakerCar() {
	var car_type_maker_car = document.getElementById('appformPrefectures').value;
	var maker_name		   = document.getElementById('appformMakerName').value;
	var car_name 		   = document.getElementById('appformCarName').value;
	var form_info = { "car_type_maker_car" : car_type_maker_car,
					  "maker_name"	 : maker_name,
					  "car_name"	 : car_name
					};
	$.post({
        url:METHOD_SEARCH_MAKER_CAR,
		data:form_info,
	}).done( function(d) {
			var data = JSON.parse(d);
			if (data["is_error"] == true) {
				showControlError(data["result_data"]);
			}
			//検索結果反映
			if (data["is_error"] == false) {
				//一件なら詳細表示へ遷移。
				if( data["result_data"].length == 1) {
					if(displaying_page == PAGE_DETAIL) {
						addHistoryPage(PAGE_DETAIL);
					} else if(displaying_page == PAGE_LIST){
					} else {
					addHistoryPage(PAGE_CONDITION);
					}
					selectList(data["result_data"][0]["model"],maker_name,car_name);
				}
				else {
					addHistoryPage(PAGE_CONDITION);
					setList(data["result_data"],"「メーカー名・車名で検索」");					
				}
			}
		}
	).fail( function() {
		//エラー表示
		showErrorInternal();
		}
	);
}

/**
 *　一覧表示（HTML生成）
 *　@param　String array result_list,
 * @param String search_method
 */
function setList(result_list, search_method) {
	var previous_maker_name = "";
	var disp_html = "";	
	for(var i = 0; i < result_list.length; i++) {
		//結果ヘッダ　
		if(previous_maker_name != result_list[i]["maker_name"]) {
			disp_html += "<tr class ='makerName'>\n"
					  + "<th colspan = 2 >メーカー： " + escapeHtml(result_list[i]["maker_name"]) + "</th>\n"
					  + "</tr>\n"
					  + "<tr>\n"
					  + "<th>車名</th>\n"
					  + "<th>型式</th>\n"
					  + "</tr>\n";
		}
		//結果値
		disp_html += "<tr class ='linkTr show02' onclick=\"javascript:selectList('" + escapeHtml(result_list[i]["model"]) + "','" + escapeHtml(result_list[i]["maker_name"]) + "','"
				  + escapeHtml(result_list[i]["car_name"]) + "');\">\n"
				 //検索ヒット値;車名
				  + "<td>" + escapeHtml(result_list[i]["car_name"])	+ "</td>\n"
				 //検索ヒット値：型式
				  + "<td class ='icon'>" + escapeHtml(result_list[i]["model"]) + "</td>\n"
				  + "</tr>\n";
		previous_maker_name = result_list[i]["maker_name"];
	}
	document.getElementById("searchMethod").textContent = escapeHtml(search_method) + "検索結果";
	document.getElementById("tableSearchList").outerHTML = "<table id=\"tableSearchList\"><tbody>" + disp_html + "</tbody></table>";
	//表示
	$('.searchTArea').show();
    $('.searchTDetail').hide();
	$(".searchError").hide();
	
	//スクロール
	scroll('.searchTArea');

	//ページ番号記録
	addHistoryPage(PAGE_LIST);
	displaying_page = PAGE_LIST;
}

//詳細表示機能
//=================================================================================================
/**
 *　リスト選択
 *　@oaram　String model,maker_name,car_name
 */
function selectList(model,maker_name,car_name) {
	var form_info = { "maker_name"   : maker_name,
					  "car_name"	 : car_name,
					  "model"		 : model
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
	
	//▼　（保守項目) 保険始期2017年以前の場合、ABS保険は'-'-------
	if(data["start_year"] <= 2017){
		data["disp_guarantee"] = "－";
	}
	if(data["last_start_year"] <= 2017){
		data["last_disp_guarantee"] = "－";
	}
	//▲-----------------------------------------------------

	document.getElementById("makerNameValue").textContent = "メーカー : " + escapeHtml(data["maker_name"]);
	document.getElementById("carNameValue").textContent 				= escapeHtml(data["car_name"]);
	document.getElementById("modelValue").textContent 					= escapeHtml(data["model"]);
	document.getElementById("startYearValue").textContent 				= "保険始期" + escapeHtml(data["start_year"])+"年1月1日～12月31日";
	document.getElementById("interpersonalClassValue").innerHTML		= "<strong>" + escapeHtml(data["interpersonal_class"]) + "</strong>";
	document.getElementById("objectveClassValue").innerHTML 			= "<strong>" + escapeHtml(data["objectve_class"]) + "</strong>";
	document.getElementById("personalAccidentClassValue").innerHTML 	= "<strong>" + escapeHtml(data["personal_accident_class"]) + "</strong>";
	document.getElementById("vehicleClassValue").innerHTML 				= "<strong>" + escapeHtml(data["vehicle_class"]) + "</strong>";
	document.getElementById("dispGuarantee").innerHTML 					= escapeHtml(data["disp_guarantee"]);
	//坦種目は値が無ければ表示しない。
	if(data['collateral_event'] == '') {
		document.getElementById("collateralEventRow").style.display ="none";
	}
	else { 
		document.getElementById("collateralEvent").innerHTML = "<strong>" + escapeHtml(data["collateral_event"]) + "</strong>";
		document.getElementById("collateralEventRow").style.display ="table-row";
	}
	
	document.getElementById("lastStartYearValue").textContent 			= "保険始期" + escapeHtml(data["last_start_year"])+"年1月1日～12月31日";
	document.getElementById("lastInterpersonalClassValue").innerHTML	= "<strong>" + escapeHtml(data["last_interpersonal_class"]) + "</strong>";
	document.getElementById("lastObjectveClassValue").innerHTML 		= "<strong>" + escapeHtml(data["last_objectve_class"]) + "</strong>";
	document.getElementById("lastPersonalAccidentClassValue").innerHTML	= "<strong>" + escapeHtml(data["last_personal_accident_class"]) + "</strong>";
	document.getElementById("lastVehicleClassValue").innerHTML 			= "<strong>" + escapeHtml(data["last_vehicle_class"]) + "</strong>";
	document.getElementById("lastDispGuarantee").innerHTML 				=  escapeHtml(data["last_disp_guarantee"]);
	//坦種目(n-1)は値が無ければ表示しない。
	if(data['last_collateral_event'] == '') {
		document.getElementById("lastCollateralEventRow").style.display ="none";
	}
	else { 
		document.getElementById("lastCollateralEvent").innerHTML = "<strong>" + escapeHtml(data["last_collateral_event"]) +  "</strong>";
		document.getElementById("lastCollateralEventRow").style.display ="table-row";
	}
	
	displaying_page = PAGE_DETAIL;
	backup_model[0] = data["model"];
	backup_model[1] = data["maker_name"];
	backup_model[2] = data["car_name"];
	
	//表示
	$('.searchTDetail').show();
	$(".searchError").hide();
	$('.searchTArea').hide();
	
	//料率クラス説明
	showClassExplain(data["is_kei"]);
	//ポップの表示
	showNotice();
	
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
		case PAGE_LIST:
			addHistoryPage(PAGE_LIST);
			$('.searchTArea').show();
			$('.searchTDetail').hide();
			displaying_page = PAGE_LIST;
			break;
		case PAGE_DETAIL:
			var model = target[1][0];
			var maker_name = target[1][1];
			var car_name = target[1][2];
			selectList(model,maker_name,car_name);
			break;
	}
}

//料率クラス説明表示
//=================================================================================================
function showClassExplain(is_kei) {
	if(is_kei) {
		$("#futu-kogata").hide();
		$("#kei").show();	
	} else {
		$("#futu-kogata").show();
		$("#kei").hide();
	}
}
	
//AEB装着による割引ポップ
//=================================================================================================
function showNotice() {
	$(this).blur() ; //ボタンからフォーカスを外す
	$( "#overlay").fadeIn("slow");
	$( "#pop_aeb").fadeIn("slow");
	$( "#overlay,#modal-close" ).unbind().click( function(){
		$( "#pop_aeb,#overlay" ).fadeOut( "slow", function(){});
	});
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
	if( key_pressed === 13) { searchModel(); }
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