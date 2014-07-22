<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Student List</title>

	<style type="text/css">

	#container{
		margin: 10px;
		border: 1px solid #D0D0D0;
		-webkit-box-shadow: 0 0 8px #D0D0D0;
	}
	#body{
		margin: 0 15px 0 15px;
	}
	</style>
</head>
<body>

<div id="container">

	<div id="body">
            <h1>Student List</h1>
		<?php 
                    echo( $students ); 
                ?>
            <h1>Students Added</h1>
                <?php
                    echo ( $role_updates );
                ?>
            <h1>Name Changes</h1>
                <?php
                    echo ( $name_updates );
                ?>
            <h1>Students Dropped</h1>
                <?php
                    echo ( $dropped_students );
                ?>
        </div>

	<p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
</div>

</body>
</html>