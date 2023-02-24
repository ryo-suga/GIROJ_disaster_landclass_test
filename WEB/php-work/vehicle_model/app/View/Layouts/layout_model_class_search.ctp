<!DOCTYPE html>
<html lang="ja">

<head>
<?php echo $this->element(HEAD);?>
<?php echo $this->element(HEAD_CSS);?>
<?php echo $this->element(HEAD_JS); ?>
</head>

<body id="ratemaking" class="r-vehicle_model">
	<div id="contaner" class="automobileMenu">
	<div id="overlay"></div>
		
		<!--ボディヘッダー部-->
		<?php include(HEADER);?>
		<div id="wrapper" class="cf">
			<!--パンくずリスト-->
				<?php include(BREAD_CRUMB); ?>
			<!--検索メイン部分-->
				<?php echo $this->fetch('content'); ?>
			<!--サイドメニュー ...～ まめ知識-->
				<?php include(SUBNAV_RATEMAKING)?>
				<?php include(AUTO_MOBILE_VEHICLE_MODEL); ?>
		</div>
		<!--フッター-->
		<?php echo $this->element(PAGE_TOP);?>		
		<?php include(FOOTER); ?>
	</div>
	<!--sp-->
		<?php include(NAV_SP); ?>
	<!--pop_aeb-->
		<?php include(POP_AEB);?>
</body>
</html>