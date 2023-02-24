					<div class="searchKataArea">
						<section class="search01">
							<p><strong>メーカー・車名で検索する</strong></p>
							
							<!--自動車ﾀｲﾌﾟリスト メーカー・車名-->
							<!------------------------------------------------------------------------->									
							<?php
								echo $this->Form->select('car_type_maker_car', $car_types, 
									array(
										'onChange'  => 'javascript:selectCarTypeMakerCar();',
										'empty'   	=> '自動車タイプを選択',
										'class'		=> 'form-control input-sm',
										'label'		=> false,
									)
								);
							?>
							<!--メーカリスト-->
							<!------------------------------------------------------------------------->
							<?php
								echo $this->Form->select('maker_name',$maker_name, 
									array(
										'onChange'  => 'javascript:selectMakerName();',
										'empty'   	=> 'メーカーを選択',
										'class'	  	=> 'form-control input-sm disp-inline',
										'label'		=> false,
										'style'		=> 'width:49%;',
										'disabled'	=> true,
									)
								);
							?>
							<!--車名リスト-->
							<!------------------------------------------------------------------------->							
							<?php
								echo $this->Form->select('car_name',$car_name,
								array(
										'empty'   	=> '車名を選択',
										'onChange'  => 'javascript:selectCarName();',
										'class'	  	=> 'form-control input-sm disp-inline',
										'label'		=> false,
										'style'		=> 'width:49%;',
										'disabled'	=> true,
									)
								);
							?>						
							<!--submitボタンメーカ・車名-->
							<!------------------------------------------------------------------------->
								<?php echo $this->Form->button( 'メーカー・車名で検索',
										array(
										      'type' => 'button',
											  'class' => 'form-control btn btn-default btn-sm show01-1', 
											  'onclick'=>'javascript:searchMakerCar();',
											  'label' => false
											)
										);
								?>						
						</section>
						<section class="and"> <span>または</span> </section>
						<section class="search02">
							<p><strong>型式で検索する</strong></p>
							<!--自動車ﾀｲﾌﾟリスト　型式-->
							<!------------------------------------------------------------------------->									
							<?php
								echo $this->Form->select('car_type_model', $car_types, 
									array(
										'empty'   	=> '自動車タイプを選択',
										'class'		=> 'form-control input-sm',
										'onChange'	=> 'javascript:selectCarTypeModel();',
										'label'		=> false,
									)
								);
							?>	
							<!--テキストエリア　型式-->
							<!------------------------------------------------------------------------->
							<?php
								echo $this->Form->text('model',
									array(
										'id' => 'textareaModel',
										'class' =>'form-control input-sm',
										'style' => 'width:93%;ime-mode:disabled;',
										'maxlength' => '15',
										'onkeypress' => 'javascript:pressEnterKey(event.keyCode);',
										'onclick' => 'javascript:pressEnterKey(event.keyCode);',
										'placeholder' =>'型式を入力'
									)
								);
							?>
							<!--submitボタン　型式-->
							<!------------------------------------------------------------------------->
							<?php echo $this->Form->button( '型式で検索',
										array(
										      'type' => 'button',
											  'class' => 'form-control btn btn-default btn-sm show01-1', 
											  'onclick'=>'javascript:searchModel();',
											  'label' => false
											  )
										);
							?>
						</section>
							<?php echo $this->Form->hidden('submit_model')?>
							<?php echo $this->Form->hidden('submit_maker_car')?>
							<!--textarea ダミー-->
							<input type ="text" style ="display:none;">
					</div>