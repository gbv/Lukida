<div id='start' class='start'>

<?php

	$Start	= (isset($_SESSION["config_discover"]["start"]["content"]) && $_SESSION["config_discover"]["start"]["content"] != "" ) ? $_SESSION["config_discover"]["start"]["content"] : "start.html";	
	if ( ! file_exists(LIBRARYPATH.$Start) )
	{
		// Whoops, we don't have a page for that!
		show_404();
	}
	require_once(LIBRARYPATH.$Start);
	
?>

</div>