<div id='front' class='front'>

<?php

	if ( ! file_exists(LIBRARYPATH."front.html") )
	{
		// Whoops, we don't have a page for that!
		show_404();
	}
	require_once(LIBRARYPATH."front.html");
	
?>

</div>