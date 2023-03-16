<?php 
	/**
	 * ダミーである空のレイアウト　
	 * 呼び出し元でincludeしたものが以下にフェッチされます。
	 */
?>	 
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<?php
		echo $this->Html->meta('icon');
		echo $this->Html->css('');
	?>
</head>
<body>
	<div id="container">
		<div id="content">
			<?php echo $this->fetch('content'); ?>
		</div>
	</div>
</body>
</html>
