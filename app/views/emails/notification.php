<html>
<head><title></title></head>
<body style="background: #EFEFEF">
	
	<div style="border: 1px solid #ccc; padding:  30px;">

		<h1>Hello <?php echo $name; ?></h1>

		<p>Your time to pray is coming up in 15 minutes at <?php echo $time; ?>. Thank you for signing up to pray.</p>

		<p></p>

			<div style="border:1px solid #ccc; padding: 30px; width: 95%; margin: 0 auto; ">
				
				<h2><?php echo $date; ?></h2>

				<p><?php echo $reminder; ?></p>

			</div>

	</div>

	<div style="text-align: center;">
		<h4>Problem or question?</h4>
		<p><a href="mailto:info@email.com">info@email.com</a></p>
	</div>

</body>
</html>