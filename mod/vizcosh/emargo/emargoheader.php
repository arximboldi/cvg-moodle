<?php

/*
 * This file needs to be included in the html-header of all plugins 
 * that make use of the emargo-features. It contains all Javascript- 
 * and CSS-definitions needed for eMargo that need to be included in
 * the page-header
 */


// include Moodle's AJAX-Library
require_once($CFG->libdir . '/ajax/ajaxlib.php');

require_once('emargolib.php');

$emargoroot = $CFG->wwwroot . '/mod/vizcosh/emargo/';

$emargometadata = '
	<!-- eMargo Stylesheets -->
	<style type="text/css">@import url(' . $emargoroot . '/css/emargo.css);</style>
	<style type="text/css">@import url(' . $emargoroot . '/css/commentbox.css);</style>

	<!-- eMargo Javasript -->

	<script type="text/javascript" src="' . $emargoroot . '/js/emargo.js"></script>
	<script type="text/javascript" src="' . $emargoroot . '/js/json-parser.js"></script';

$emargometadata .= '

	
	
	
	<!-- YUI Stylesheets -->
	<link rel="stylesheet" type="text/css" href="' . $CFG->wwwroot . '/lib/yui/button/assets/skins/sam/button.css">
	<link rel="stylesheet" type="text/css" href="' . $CFG->wwwroot . '/lib/yui/container/assets/skins/sam/container.css">
	<style type="text/css">
		/* Custom YUI CSS-styles for marker-buttons */
		.yui-skin-sam .yui-button a { 
			font-size:12.5px;
			height:25px;
		}
		.yui-skin-sam .yui-button-focus {
			background-position:0 0;
		}
		.yui-skin-sam .yui-button-hover {
			background-position:0 -1300px;
		}

		.yui-button#togglebutton a {
			background: url(' . $emargoroot . '/pix/buttons/toggle_small.gif) no-repeat;
			padding-left:15px;
		}
		.yui-button#markerbutton a {
			background: url(' . $emargoroot . '/pix/buttons/highlight_small.png) no-repeat;
			padding-left:15px;
		}
		.yui-button#helpbutton a {
			background: url(' . $emargoroot . '/pix/buttons/help_small.png) no-repeat;
			padding-left:15px;
		}
	</style>

	<!-- YUI javascript files -->
	<script type="text/javascript" src="' . $CFG->wwwroot . '/lib/yui/yahoo/yahoo-min.js"></script> 
	<script type="text/javascript" src="' . $CFG->wwwroot . '/lib/yui/event/event-min.js"></script> 
	<script type="text/javascript" src="' . $CFG->wwwroot . '/lib/yui/connection/connection-min.js"></script> 
	<script type="text/javascript" src="' . $CFG->wwwroot . '/lib/yui/yahoo-dom-event/yahoo-dom-event.js"></script> 
	<script type="text/javascript" src="' . $CFG->wwwroot . '/lib/yui/element/element-beta-min.js"></script> 
	<script type="text/javascript" src="' . $CFG->wwwroot . '/lib/yui/button/button-min.js"></script> 
	<script type="text/javascript" src="' . $CFG->wwwroot . '/lib/yui/container/container-min.js"></script> 

	
	
		<script language="javascript" src="' . $CFG->wwwroot . '/lib/yui/dom/dom-min.js" type="text/javascript"></script>
	<script language="javascript" src="' . $CFG->wwwroot . '/lib/yui/animation/animation-min.js" type="text/javascript"></script>
	<script type="text/javascript">';

$emargometadata .= "
	function xGetElementById(e) {
			if(typeof(e)!='string') return e;
			if(document.getElementById) e=document.getElementById(e);
			else if(document.all) e=document.all[e];
			else e=null;
			return e;
		}
		
		function toggleSlider(e){
			if(document.getElementById('slider').style.height != this.title+'em') {
				var anim = new YAHOO.util.Anim('slider',{height:{to:this.title, unit: 'em' }},1,YAHOO.util.Easing.easeOut);
				anim.animate();
			}
			else{
			var anim = new YAHOO.util.Anim('slider',{height:{to:0}},1,YAHOO.util.Easing.easeIn);
			anim.animate();
			}
		}
		
		function attachEvents(e){
			//called when window has loaded.

			//attach events to links on the page.
			YAHOO.util.Event.addListener('toggletoc','click',toggleSlider, this.title);
			
		}
		
		YAHOO.util.Event.addListener(window,'load',attachEvents);
		
		
		
	</script>";
	
$emargometadata .= '	
	
	<script type="text/javascript"><!-- 
		/* language-dependant phrases used in Javascript */
		var strMarkertoolAlert = "' . get_string('markertool_alert', 'emargo') . '";
		var strOverview = "' . get_string('overview', 'emargo') . '";
		var strHelp = "' . get_string('help', 'emargo') . '";
		var strParagraph = "' . get_string('paragraph', 'emargo') . '";
		var strComments = "' . get_string('comments', 'emargo') . '";
		var strWholePage = "' . get_string('whole_page', 'emargo') . '";
		var strOnParagraph = "' . get_string('on_paragraph', 'emargo') . '";
		var strOnWholePage = "' . get_string('on_whole_page', 'emargo') . '";
		var strDeleteMarkings = "' . get_string('delete_markings', 'emargo') . '";
		var strLoadingChapterContent = "' . get_string('loading_chapter_content', 'emargo') . '";
		var strDeletingMarking = "' . get_string('deleting_marking', 'emargo') . '";
		var strSavingComment = "' . get_string('saving_comment', 'emargo') . '";
		var strDeletingComment = "' . get_string('deleting_comment', 'emargo') . '";
		var strDeleteCommentConfirmation = "' . get_string('delete_comment_confirmation', 'emargo') . '";
		var strEditComment = "' . get_string('edit_comment', 'emargo') . '";
		var strCancelEditing = "' . get_string('cancel_editing', 'emargo') . '";
		var strMakeEditingNotPublic = "' . get_string('make_editing_not_public', 'emargo') . '";
		var strSaveComment = "' . get_string('save_comment', 'emargo') . '";
		var strSavingMarking = "' . get_string('saving_marking', 'emargo') . '";
		var strSavingBookmark = "' . get_string('saving_bookmark_for_paragraph', 'emargo') . '";
		var strDeletingBookmark = "' . get_string('deleting_bookmark_for_paragraph', 'emargo') . '";
		var strSavingQuestionmark = "' . get_string('saving_questionmark_for_paragraph', 'emargo') . '";
		var strDeletingQuestionmark = "' . get_string('deleting_questionmark_for_paragraph', 'emargo') . '";
		var strPleaseFillInAllRequiredFields = "' . get_string('fill_in_required_fields', 'emargo') . '";
		var strMarkingToShort = "' . get_string('marking_to_short', 'emargo') . '";
		' /* . (ajaxenabled() ? '' : 'alert("' . get_string('ajax_unavailable', 'emargo') . '")') */ . ' 
		
		
		/* the following functions are triggered if someone clicks on YUI-buttons */
	  function showToggleTooltip() {
		  showButtonTooltip(\'' . get_string("toggle_markings", "emargo") . '\');
	  }
	  function showMarkerTooltip() {
		  showButtonTooltip(\'' . get_string("toggle_marker", "emargo") . '\');
	  }
	  function showDeleteTooltip() {
		  showButtonTooltip(\'' . get_string("delete_all_markings", "emargo") . '\');
	  }
	  function showOthersTooltip() {
		  showButtonTooltip(\'' . get_string("display_all_markings", "emargo") . '\');
	  }
	  function showHelpTooltip() {
		  showButtonTooltip(\'' . get_string("display_emargo_help", "emargo") . '\');
	  }
		
		/**
		 * this function transforms ordinary html-links into buttons and attaches 
		 * some event-based functions to these buttons (onmouseover, onmouseout) using 
		 * the YUI library
		 */
		function onButtonReady() {
	';

if ($moduleName != 'slides') {
	$emargometadata .= '
			var oToggleButton = new YAHOO.widget.Button("togglebutton");
			oToggleButton.on("mouseover", showToggleTooltip);
			oToggleButton.on("mouseout", hideButtonTooltip);
	
			var oMarkerButton = new YAHOO.widget.Button("markerbutton");
			oMarkerButton.on("mouseover", showMarkerTooltip);
			oMarkerButton.on("mouseout", hideButtonTooltip);
	
	';
}

$emargometadata .= '
			var oHelpButton = new YAHOO.widget.Button("helpbutton");
			oHelpButton.on("mouseover", showHelpTooltip);
			oHelpButton.on("mouseout", hideButtonTooltip);

		}
		
	//--></script>
	';	

?>