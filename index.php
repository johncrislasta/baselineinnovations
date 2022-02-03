<?php

//Begin
include 'includes/core.php';
include 'app/boot.php';

?><html>
<head>
<title>Test Page</title>
 <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
 <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.x/css/materialdesignicons.min.css" rel="stylesheet"> 
 <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<link rel="stylesheet" type="text/css" href="<?php echo APP_URL ?>/assets/css/your-app.css">
<link rel="stylesheet" type="text/css" href="<?php echo APP_URL ?>/assets/css/styles.css">
</head>
<body>


<div id="app">      
        <?php echo $appInstance; ?>
</div>

<script type="text/javascript">
  var vueData = <?php echo $appInstance->models(); ?>
</script>
<script type="text/javascript" src="<?php echo APP_URL ?>/assets/js/your-app.js"></script>


</body>
</html>