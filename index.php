<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
	<title>Action Tri-Cities</title>
	<link rel="stylesheet" href="css/style.css">
</head>

<body>

<div id="log-in-bar">
<?php

    if (!isset($_SESSION['state'])) {
        echo "<a href='authenticate.php'>Log-in with MapMyRun</a>";
    } else if ($_SESSION['state'] == 2)
    {
    	echo "You must click 'Authorize' on <a href='authenticate.php'>MapMyRun.com</a>";
    } else if ($_SESSION['first_name'] == "")
    {
        echo "<a href='authenticate.php'>Log-in with MapMyRun</a>";
    } else {
        echo "Hello, " . $_SESSION['first_name'] . " <a href='logout.php'>[LOGOUT]</a>";
    }
?>

</div>
</body>
</html>