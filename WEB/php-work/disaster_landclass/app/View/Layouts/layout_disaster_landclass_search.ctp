<!DOCTYPE html>
<html lang="ja">

<head>
<?php echo $this->element(HEAD);?>
<?php echo $this->element(HEAD_CSS);?>
<?php echo $this->element(HEAD_JS); ?>
</head>

<body id="ratemaking" class="r-touchi">
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
				<?php include(RATE_FIRE_SUISAI); ?>
		</div>
		<!--フッター-->
		<?php echo $this->element(PAGE_TOP);?>		
		<?php include(FOOTER); ?>
	</div>
	<!--sp-->
		<?php include(NAV_SP); ?>
</body>
</html>