<div id="main">
	<!--フォーム開始-->
	<?php echo $this->Form->create('appform', array('id' => 'appform','url' => array( 'controller' => '/Search','action' => 'index'))); ?>	
	<h1>水災等地検索</h1>
	<section>
	<p>お住まいの地域の<a href="javascript:void(0);" id="mame01" class="mameLink">水災等地<i class="icon-mame"></i></a>が検索できます。<br>
		検索結果は、当機構で算出した参考純率上の住宅物件における水災等地になります。<br>
		各保険会社の取り扱いは異なる場合がございますので、火災保険をご契約する際は各保険会社へご確認ください。<br>
		なお、水災等地の設定にあたり<a href="javascript:void(0);" id="mame03" class="mameLink">内水氾濫<i class="icon-mame"></i></a>等の<a href="javascript:void(0);" id="mame02" class="mameLink">外水氾濫<i class="icon-mame"></i></a>以外の水災リスクも評価しているため、<a href="javascript:void(0);" id="mame04" class="mameLink">洪水ハザードマップ<i class="icon-mame"></i></a>と異なる可能性があります。
	</p>

		<!--検索条件指定部分エレメント-->
		<!--===============================================================================-->
		<?php echo $this->element('search_condition');?>
		
		<!--詳細情報表示エレメント-->
		<!--===============================================================================-->	
		<?php echo $this->element('search_detail');?>
		
		<!--エラー表示エレメント-->
		<!--===============================================================================-->	
		<?php echo $this->element('search_error');?>
	</section>

	<!--型式FAQ-->
	<?php include(SUISAI_FAQ)?>

	<?php				
		//画面遷移用項目
		//-----------------------------------------------------------------------------------
		echo $this->Form->hidden('page_from');
		echo $this->Form->hidden('selected_prefectures_idx');
		echo $this->Form->hidden('selected_municipality_idx');
	?>		
    <!--フォーム閉-->
    <?php echo $this->Form->end(); ?>

</div>