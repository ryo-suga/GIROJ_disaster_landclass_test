						<div class="searchTDetail cf" style ="display:none;" >
							<h2>型式別料率クラス</h2>
								<table class='kata'>
									<tr class='makerName'>
										<th colspan='2' id="makerNameValue">メーカー：******</th>
									</tr>
									<tr>
										<th>車名</th>
										<th>型式</th>
									</tr>
									<tr class = 'linkTr show02'>
										<td id ="carNameValue">******</td>
										<td class='icon' id = "modelValue">******</td>
									</tr>
								</table>
								<div class='searchClass cf'>
									<table class='fLeft'>
										<caption id="startYearValue">
											保険始期******年1月1日～12月31日
										</caption>
										<tr class='midashi'>
											<th>補償内容</th>
											<th class='tLeft nowrap'>料率クラス</th>
										</tr>
										<tr>
											<th>対人賠償責任保険</th>
											<td id="interpersonalClassValue"></td>
										</tr>
										<tr>
											<th>対物賠償責任保険</th>
											<td id="objectveClassValue"></td>
										</tr>
										<tr>
											<th>人身傷害保険</th>
											<td id="personalAccidentClassValue"></td>
										</tr>
										<tr>
											<th>車両保険</th>
											<td id="vehicleClassValue"></td>
										</tr>
										<tr id = "collateralEventRow">
											<th>担保種目</th>
											<td id="collateralEvent"></td>
										</tr>
                                            <th class="aeb"><a href="javascript:void(0);" id="mame07" class="mameLink">AEB<i class="icon-mame"></i></a>                                            
                                            の装着による保険料の割引</th>
											<td id="dispGuarantee"></td>
										</tr>
									</table>
									<table class='fRight'>
										<caption id="lastStartYearValue">
										保険始期******年1月1日～12月31日
										</caption>
										<tr class='midashi'>
											<th>補償内容</th>
											<th class='tRight nowrap'>料率クラス</th>
										</tr>
										<tr>
											<th>対人賠償責任保険</th>
												<td id="lastInterpersonalClassValue"></td>
										</tr>
										<tr>
											<th>対物賠償責任保険</th>
											<td id="lastObjectveClassValue"></td>
										</tr>
										<tr>
											<th>人身傷害保険</th>
											<td id="lastPersonalAccidentClassValue"></td>
										</tr>
										<tr>
											<th>車両保険</th>
											<td id="lastVehicleClassValue"></td>
										</tr>
										<tr id="lastCollateralEventRow">
											<th>担保種目</th>
											<td id="lastCollateralEvent"></td>
										</tr>

										<tr>
											<th class="aeb"><a href="javascript:void(0);" id="mame07" class="mameLink">AEB<i class="icon-mame"></i></a>                                            
                                            の装着による保険料の割引</th>
											<td id="lastDispGuarantee"></td>
										</tr>
									</table>
								</div>
								<p class='red kome'></p>

							<p id='backBtn'><input type='button' id='back_btn' name='data[appform][back_btn]' class='form-control btn btn-white btn-sm show01' value='もどる' onclick = "javascript:backPage();"></p>
							<br>
							<!--注釈情報-->
							<?php // include(KATA_DETAIL_ANNOTATION)
							?>
						</div>