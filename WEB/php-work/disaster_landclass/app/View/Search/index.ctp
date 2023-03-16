<div id="main">
	<!--フォーム開始-->
	<?php echo $this->Form->create('appform', array('id' => 'appform','url' => array( 'controller' => '/Search','action' => 'index'))); ?>	
				<form action="/Search" id="appform" method="post" accept-charset="utf-8">
					<div style="display:none;"><input type="hidden" name="_method" value="POST" /></div>
					<h1>水災等地検索</h1>
					<section>
					<p>お住まいの地域の水災等地が検索できます。<br>
						検索結果は、当機構で算出した参考純率上の水災等地<sup>※</sup>になります。<br>
						各保険会社で使用している水災等地とは異なる場合がございますので、火災保険をご契約する際は各保険会社へご確認ください。<br>
						なお、水災等地の決定にあたり外水氾濫以外にも内水氾濫や高潮、土砂災害などを考慮しているため、ハザードマップと異なる可能性があります。
					</p>
					<p class="kome"><small>※ 住宅物件</small></p>
					
					
					<!--検索条件指定部分エレメント-->
		<!--===============================================================================-->
		<?php echo $this->element('search_condition');?>
		
		<!--検索結果一覧表示エレメント-->
		<!--===============================================================================-->
		<?php echo $this->element('search_list');?>
		
		<!--詳細情報表示エレメント-->
		<!--===============================================================================-->	
		<?php echo $this->element('search_detail');?>
		
		<!--エラー表示エレメント-->
		<!--===============================================================================-->	
		<?php echo $this->element('search_error');?>
	</section>

	<!--型式FAQ-->
	<?php include(KATA_FAQ)?>

	<?php				
		//画面遷移用項目
		//-----------------------------------------------------------------------------------
		echo $this->Form->hidden('page_from');
		echo $this->Form->hidden('selected_maker_idx');
		echo $this->Form->hidden('selected_car_idx');
	?>		
    <!--フォーム閉-->
    <?php echo $this->Form->end(); ?>
					</section>
				</form>
			<!--virtual="/common/inc/suisai_faq.txt" -->
			<!--#include virtual="/ratemaking/automobile/vehicle_model/css/../common/inc/suisai_faq.txt" -->
		</div>