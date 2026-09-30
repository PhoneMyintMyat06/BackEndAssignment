<?php
session_start();
session_destroy();
header("Location: index.php");
?>

<!--
session_start() starts the session so its data can be accessed.
session_destroy() removes the session data, including login data.
header() redirects the browser to another page.
-->
