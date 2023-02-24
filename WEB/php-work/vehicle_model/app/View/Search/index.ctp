<div id="main">
	<!--フォーム開始-->
	<?php echo $this->Form->create('appform', array('id' => 'appform','url' => array( 'controller' => '/Search','action' => 'index'))); ?>	
    <h1>型式別料率クラス検索</h1>
	<section>
		<p>「メーカー・車名」もしくは「
        <a href="javascript:void(0);" id="mame01" class="mameLink">型式<i class="icon-mame"></i></a>            		
        」からお車の
        <a href="javascript:void(0);" id="mame02" class="mameLink">型式別の料率クラス<i class="icon-mame"></i></a>            		        
        が検索できます。<br>
        検索結果は、当機構で算出した参考純率上の料率クラスになります。<br>
		各保険会社で使用している料率クラスとは異なる場合がございますので、自動車保険をご契約する際は各保険会社へご確認ください。<br>
		なお、クラスが高い（数字が大きい）からといって、そのお車が危ない自動車であるということではありません。</p>
		<div id="attention" class="accArea mgnBtm40">
			<div class="">
				<h3 class="acc_trigger"><span><i></i></span>検索いただけないお車について</h3>
				<div class="acc_container">
					<div class="block">
						<p class="kome">※自家用普通乗用車・自家用小型乗用車・自家用軽四輪乗用車以外、
                        <a href="javascript:void(0);" id="mame03" class="mameLink">改造車<i class="icon-mame"></i></a>
                        、
                        <a href="javascript:void(0);" id="mame04" class="mameLink">型式不明車<i class="icon-mame"></i></a>
                        、
                        <a href="javascript:void(0);" id="mame05" class="mameLink">並行輸入車<i class="icon-mame"></i></a>
                        および未発売・発売して間もない型式</p>
					</div>
				</div>
			</div>
		</div>
		
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
</div>