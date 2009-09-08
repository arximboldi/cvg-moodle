<?php

/*
 * This file needs to be included at the bottom (right before the </body>-tag) 
 * of all plugins that make use of the emargo-features. It contains all 
 * Javascript- and CSS-definitions needed for eMargo that need to be included in
 * the page-footer.
 */

$emargoroot = $CFG->wwwroot . '/mod/vizcosh/emargo/';

	echo '
		<script type="text/javascript"><!-- 
			/* global variables used for eMargo features */
			var moduleName = "' . $moduleName . '";
			var commentBoxIsInitialized = false;
			var courseModuleId = ' . $id . ';
			var currentParagraph = -1;
			var chapterid = ' . $chapterid . ';
			var markingsHidden = false;
			var markingsOfOthersHidden = true;
			var statusDivName = "statusbar";
			var markingToDelete = 0;
			var contentDiv = document.getElementById("cbcontent");
			var wwwroot = "' . $CFG->wwwroot . '";
			var ajaxUrl = "' . $emargoroot . '/ajax/ajaxpost.php";
		//--></script>
	';

	// Javascript required for commentbox
	require_js($emargoroot .'/js/commentbox/threading.js');
	require_js($emargoroot .'/js/commentbox/utilities.js');
	require_js($emargoroot .'/js/commentbox/jquery.js');
	require_js($emargoroot .'/js/commentbox/frivolous.js');

	// Javascript required for handling of AJAX-requests
	require_js($emargoroot .'js/ajax.js');

	echo '
		<script type="text/javascript"><!--
			// load marked Text using AJAX
			';
			if ($moduleName != 'slides') {
				echo 'loadMarkedText();';
			} else {
				echo 'initializeCommentbox();
				commentBoxIsInitialized	= true;';
			}
			echo '
			// start the capturing of the current mouse position
			captureMouseActions();
		
			// assures that the event-based functions will be attached to the YUI-marker-buttons, but not 
			// before the html-code of all buttons has been loaded by the browser
			YAHOO.util.Event.onContentReady("buttonbar", onButtonReady);

		// --></script>';

?>