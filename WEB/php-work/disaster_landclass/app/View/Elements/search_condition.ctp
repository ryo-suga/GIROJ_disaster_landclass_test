					<div class="searchKataArea">
						<section class="search01">
							<p><strong>都道府県を選択してください。</strong></p>
							
							<!--都道府県リスト-->
							<?php
								echo $this->Form->select('prefectures',$prefectures_name, 
									array(
										'onChange'  => 'javascript:selectPrefectures();',
										'empty'   	=> '都道府県を選択',
										'class'		=> 'form-control input-sm',
										'label'		=> false,
									)
								);
							?>
							<!--市区町村リスト-->
							<?php
								echo $this->Form->select('municipality',$municipality_name, 
									array(
										'onChange'  => 'javascript:selectMunicipalityName();',
										'empty'   	=> '市区町村を選択',
										'class'	  	=> 'form-control input-sm disp-inline',
										'label'		=> false,
										'style'		=> 'width:100%;',
										'disabled'	=> true,
									)
								);
							?>			
							<!--submitボタン検索-->
							<?php echo $this->Form->button( '検索',
									array(
											'type' => 'button',
											'class' => 'form-control btn btn-default btn-sm show01-1', 
											'onclick'=>'javascript:searchLandclassData();',
											'label' => false
										)
									);
							?>						
						</section>
							<?php echo $this->Form->hidden('submit')?>
							<!--textarea ダミー-->
							<input type ="text" style ="display:none;">
					</div>