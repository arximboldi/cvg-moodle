<?php
/**
 * this file contains all functions that are being used to handle specific AJAX-requests
 * @author: Andreas Kothe
 */ 

require_once ($CFG->dirroot . '/mod/vizcosh/lib.php');

$emargoroot = $CFG->wwwroot . '/mod/vizcosh/emargo/';

/**
 * returns the text marked by the current user
 * this function is used when "display my markings" is enabled
 * $chapterID is from mdl_vizcosh_chapter
 */

function ajax_Marker_loadMarkedText ()
{
  global $USER, $chapterid, $cm;
  
  if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance))
    error (get_string ('wrong_param', 'vizcosh'));
  
  #Load the chapter (for the chapter title)
  if (!$chapter = get_record('vizcosh_chapters', 'id', $chapterid))
    ajaxReply(601, get_string ('wrong_param', 'vizcosh'));
  
  #Load all paragraphs of the $chapterID into a recordset
  if (!$all_paragraphs_array = get_recordset_select('vizcosh_paragraphs',
						    'vizcoshid = '.$vizcosh->id.
						    ' AND chapterid = '.$chapterid,
						    'orderposition'))
    ajaxReply(601, get_string ('wrong_param', 'vizcosh'));
  else
    {
      if ($all_paragraphs_array->_numOfRows > 0)
	{
	  #contains paragraphs, now convert to an array and renumber index
	  $all_paragraphs_array = array_values(recordset_to_array($all_paragraphs_array));
	
	  #highlight markings
	  Marker::markTextForUser($all_paragraphs_array, $chapterid, $USER->id);
	  $markedContent = getWrappingHtmlCode($chapter->title, $all_paragraphs_array);	
	}
      else
	{
	  //contains no paragraphs
	
	  $markedContent = '<div id="contentblock_0">'.get_string ('no_content', 'vizcosh');
	  if (isset($USER->editing) && ($USER->editing==1))
	    {
	      $markedContent .=
		'<br><br><a title="'.get_string('edit').
		'" href="editparagraph.php?id='.$cm->id.'&amp;chapterid='.$chapterid.
		'&paragraphid=-1&orderposition=1">'.
		'<img src="pix/add.gif" height="11" class="iconsmall" alt="'.
		get_string('edit').'" />'.get_string ('add_paragraph', 'vizcosh').'</a>';
	    }
	  else
	    {
	      $markedContent .=
		get_string ('change_to_edit_mode', 'vizcosh') .'</div>';	  
	    }
	}
    }		

  $content = vizcosh_post_process_content_emargo ($markedContent);
  ajaxReply (201,'Data Follows', array('content' => $content));
}

/**
 * returns the unmarked text 
 this function is used when "display my markings" is disabled
*/
function ajax_Marker_loadUnmarkedText()
{
  global $USER, $chapterid, $cm;
  
  if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance))
    error (get_string ('wrong_param', 'vizcosh'));
  
  #Load the chapter (for the chapter title)
  if (!$chapter = get_record('vizcosh_chapters', 'id', $chapterid))
    ajaxReply(601, 'Error reading eMargo chapters.');
  
  #Load all paragraphs of the $chapterID into a recordset
  if (!$all_paragraphs_array = get_recordset_select ('vizcosh_paragraphs',
						     'vizcoshid = '.$vizcosh->id.
						     ' AND chapterid = '.$chapterid,
						     'orderposition'))
    ajaxReply(601, 'Error reading eMargo chapters.');
  else
    {
      if ($all_paragraphs_array->_numOfRows > 0)
	{
	  #contains paragraphs, now convert to an array and renumber index
	  $all_paragraphs_array = array_values(recordset_to_array($all_paragraphs_array));
	  
	  $Content = getWrappingHtmlCode($chapter->title, $all_paragraphs_array);
	}
      else{
	//contains no paragraphs
	$Content = '<div id="contentblock_0">'.get_string ('empty_chapter', 'vizcosh');
	if (isset($USER->editing) && ($USER->editing==1)){
	  $Content .= '<br><br><a title="'.get_string('edit').
	    '" href="editparagraph.php?id='.$cm->id.'&amp;chapterid='.
	    $chapterid.'&paragraphid=-1&orderposition=1">'.
	    '<img src="pix/add.gif" height="11" class="iconsmall" alt="'.
	    get_string('edit').'" />Absatz hinzuf&uuml;gen</a>';
	}
	else{
	  $Content .=
	    get_string ('change_to_edit_mode', 'vizcosh') .'</div>';
	}
      }
    }

  $content = vizcosh_post_process_content_emargo ($Content);
  ajaxReply(201,'Data Follows', array('content' => $content));
}

function ajax_Marker_loadJsxaalAnims()
{
  global $USER, $chapterid, $cm;
  
  if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance))
    error (get_string ('wrong_param', 'vizcosh'));
  
  #Load the chapter (for the chapter title)
  if (!$chapter = get_record('vizcosh_chapters', 'id', $chapterid))
    ajaxReply(601, 'Error reading eMargo chapters.');
  
  #Load all paragraphs of the $chapterID into a recordset
  if (!$all_paragraphs_array = get_recordset_select ('vizcosh_paragraphs',
						     'vizcoshid = '.$vizcosh->id.
						     ' AND chapterid = '.$chapterid,
						     'orderposition'))
    ajaxReply(601, 'Error reading eMargo chapters.');
  else
    {
      if ($all_paragraphs_array->_numOfRows > 0)
	{
	  #contains paragraphs, now convert to an array and renumber index
	  $all_paragraphs_array = array_values(recordset_to_array($all_paragraphs_array));
	  
	  $Content = getWrappingHtmlCode($chapter->title, $all_paragraphs_array);
	}
    }

  $content = vizcosh_create_jsxaal_command ($Content);
  ajaxReply(201,'Data Follows', array('content' => $content));
}


/**
 * deletes all markings for the current user
 */
function ajax_Marker_deleteAllMarkings() {
  global $USER, $chapterid;
  #Marker::deleteMarkersOfChapter($chapterid, $USER->id);
  //TODO: Fehlerbehandlung
  ajaxReply(200,'Ok');
}


/**
 * deletes a single marking for the current user
 */
function ajax_Marker_deleteMarking() {
  global $USER, $chapterid;
  $markingId	= required_param('markingId', PARAM_INT);
  Marker::deleteMarker(array(0 => $markingId));
  //TODO: Fehlerbehandlung
  ajaxReply(200,'Ok');
}


/**
 * saves the marked text into the database
 */
function ajax_Marker_markText() {
  global $USER, $chapterid;
  
  $markedText	= required_param('markedText', PARAM_RAW);
  $markedText = stripslashes($markedText);
  $paragraphId= required_param('paragraphId', PARAM_RAW);
  
  $marker = new Marker();
  $marker->setChapterID($chapterid);
  $marker->setParagraphID($paragraphId);
  $marker->setMarkedText($markedText);
  $marker->setUserID($USER->id);
  
  $marker->insertMarker();
  
  ajaxReply(200,'Ok');
}

/**
 * 
 */
function ajax_Commentbox_switchComments() {
  global $CFG, $USER, $moduleInstance, $courseModuleId, $chapterid, $moduleName, $cm;
  
  $switchstate = required_param('switchstate', PARAM_INT);	
  
  if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
    error('Course module is incorrect');
  }

  // re-init commentbox contents	
  if (!$paragraphs = get_records_select('vizcosh_paragraphs',
					'vizcoshid = '.$moduleInstance->id,
					'chapterid, orderposition',
					'id, chapterid, orderposition')){
    ajaxReply(601, 'Error reading paragraphs.');
  }
  
  $commentBoxContent = emargo_print_commentbox($courseModuleId, $moduleInstance, $paragraphs, $moduleName, $GLOBALS["chapterid"], $switchstate);
  
  ajaxReply(201,'Data Follows', array('content' => $commentBoxContent));
}

/**
 * saves a new comment submitted from the form in the commentbox into the database 
 * and returns the updated commentbox-content using an ajax-reply
 */
function ajax_Commentbox_saveComment() {
  global $CFG, $USER, $moduleInstance, $courseModuleId, $chapterid, $moduleName, $cm;
  
  if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
    error('Course module is incorrect');
  }	
  
  $comment = new object();
  $comment->vizcoshid = $vizcosh->id;
  $comment->parentid = required_param('parentId', PARAM_INT);
  $comment->chapter = $chapterid;
  $comment->paragraph = required_param('paragraphId', PARAM_INT);
  $comment->subject = required_param('subject', PARAM_TEXT);
  $comment->author = $USER->id;
  $comment->message = required_param('message', PARAM_NOTAGS);
  $comment->timecreated = time();
  $comment->timemodified = time();
  $comment->modifiedbyuserid = $USER->id;
  $comment->type = required_param('messageType', PARAM_INT);

  /*	$resi = fopen("/tmp/dumpp.txt", "a+");
	fputs($resi, "code: " +comment)
  	fclose($resi);*/
  // save comment into DB
  if (!$insertId = insert_record('vizcosh_comments', $comment)) {
    ajaxReply(601, 'Error saving comment.');
  }

  // re-init commentbox contents	
  if (!$paragraphs = get_records_select('vizcosh_paragraphs', 'vizcoshid = '.$moduleInstance->id, 'chapterid, orderposition', 'id, chapterid, orderposition')){
    ajaxReply(601, 'Error reading paragraphs.');
  }
  
  $commentBoxContent = emargo_print_commentbox($courseModuleId, $moduleInstance, $paragraphs, $moduleName, $GLOBALS["chapterid"]);
  
  ajaxReply(201,'Data Follows', array('content' => $commentBoxContent));
}

/**
 * updates an existing comment in the database and returns the updated commentbox-content using 
 * an ajax-reply
 */
function ajax_Commentbox_updateComment() {
  global $CFG, $USER, $moduleInstance, $courseModuleId, $chapterid, $cm, $moduleName;

  if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
    error('Course module is incorrect');
  }	
  
  $commentId = required_param('commentId', PARAM_INT);
  $subject = required_param('subject', PARAM_TEXT);
  $message = required_param('message', PARAM_NOTAGS);
  $makenotpublic = required_param('makenotpublic', PARAM_NOTAGS);

  $comment = get_record_select('vizcosh_comments', 'vizcoshid = ' . $vizcosh->id .' AND id = ' . $commentId);

  $context = get_context_instance(CONTEXT_MODULE, $cm->id);
  $allowedit = false;
  // only allow teachers to edit comments 
  if (has_capability('moodle/course:manageactivities', $context) || (($comment->author == $USER->id) && ($comment->type == 1))) {
    $allowedit = true;
  }

  if (!$allowedit) {
    ajaxReply(601, 'Unauthorized access.');
  } else {
    $comment->subject = $subject;
    $comment->message = $message;
    if ($makenotpublic != 1){
      $comment->modifiedbyuserid = $USER->id;
      $comment->timemodified = time();
    }

    // save comment into DB
    if (!update_record('vizcosh_comments', $comment)) {
      ajaxReply(601, 'Error saving comment.');
    }
  }

  // re-init commentbox contents	
  if (!$paragraphs = get_records_select('vizcosh_paragraphs', 'vizcoshid = '.$moduleInstance->id, 'chapterid, orderposition', 'id, chapterid, orderposition')){
    ajaxReply(601, 'Error reading paragraphs.');
  }
  
  $commentBoxContent = emargo_print_commentbox($courseModuleId, $moduleInstance, $paragraphs, $moduleName, $GLOBALS["chapterid"]);

  ajaxReply(201,'Data Follows', array('content' => $commentBoxContent));
}


/**
 * returns the data of a single comment
 */
function ajax_Commentbox_getSingleComment() {
  global $CFG, $USER, $courseModuleId, $moduleInstance, $chapterid, $cm;
  $commentId = required_param('commentId', PARAM_INT);
  
  if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
    error('Course module is incorrect');
  }		

  $comment = get_record_select('vizcosh_comments', 'vizcoshid = ' . $vizcosh->id .' AND id = ' . $commentId);
  $context = get_context_instance(CONTEXT_MODULE, $cm->id);

  $allowedit = false;
  // only allow teachers to edit comments 
  if (has_capability('moodle/course:manageactivities', $context) || (($comment->author == $USER->id) && ($comment->type == 1))) {
    $allowedit = true;
  }

  if (!$allowedit) {
    ajaxReply(601, 'Unauthorized access.');
  } else {
    ajaxReply(201,'Data Follows', array('content' => $comment));
  }
}


/**
 * deletes a comment from the database and returns the updated commentbox-content using an ajax-reply
 */
function ajax_Commentbox_deleteComment() {
  #deactivated - not needed
  /**	global $CFG, $USER, $courseModuleId, $moduleInstance, $chapterid, $cm;
	$commentId = required_param('commentId', PARAM_INT);

	$comment = get_record_select('vizcosh_comments', 'id = ' . $commentId);
	$context = get_context_instance(CONTEXT_MODULE, $cm->id);
	$allowdelete = false;
	// only allow the deletion of comments by teachers (or if it is a private note by a user)
	if (has_capability('moodle/course:manageactivities', $context) || (($comment->author == $USER->id) && ($comment->type == 1))) {
	$allowdelete = true;
	}

	if ($allowdelete) {
	// remove comment from DB
	if (!delete_records('vizcosh_comments', 'id', $commentId)) {
	ajaxReply(601, 'Error deleting comment.');
	}
	}

	// re-init commentbox contents	
	if (!$chapter = get_record('vizcosh_chapters', 'id', $chapterid)) {
	ajaxReply(601, 'Error reading vizcosh chapters.');
	}
	$commentBoxContent = emargo_print_commentbox($courseModuleId, $moduleInstance, $chapter);
	ajaxReply(201,'Data Follows', array('content' => $commentBoxContent));
	
  */	
}


/**
 * saves the bookmark-state for a certain paragraph (if a user has clicked the flag-symbol)
 */
function ajax_Commentbox_saveBookmark() {
  global $CFG, $USER, $chapterid, $cm;
  
  if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
    error('Course module is incorrect');
  }		

  $bookmark = new object();
  $bookmark->paragraph = required_param('paragraphId', PARAM_INT);
  $bookmark->vizcoshid = $vizcosh->id;
  $bookmark->chapter = $chapterid;
  $bookmark->author = $USER->id;

  // save bookmark into DB
  if (!$insertId = insert_record('vizcosh_bookmarks', $bookmark)) {
    ajaxReply(601, 'Error saving bookmark.');
  }
  ajaxReply(200,'Ok');
}


/**
 * removes a bookmark-entry for a certain paragraph from the database for the current user
 */
function ajax_Commentbox_deleteBookmark() {
  global $CFG, $USER, $chapterid, $cm;
  
  $paragraphId = required_param('paragraphId', PARAM_INT);

  if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
    error('Course module is incorrect');
  }		
  
  // remove bookmark entry from DB
  if (!delete_records_select('vizcosh_bookmarks', 'vizcoshid = '.$vizcosh->id. ' AND author = '. $USER->id . ' AND chapter = ' . $chapterid . ' AND paragraph = ' . $paragraphId)) {
    ajaxReply(601, 'Error deleting bookmark.');
  }

  ajaxReply(200,'Ok');
}


/**
 * saves the questionmark-state for a certain paragraph (if a user has clicked the questionmark-symbol)
 */
function ajax_Commentbox_saveQuestionmark() {
  global $CFG, $USER, $chapterid, $cm;

  if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
    error('Course module is incorrect');
  }	
  
  $questionmark = new object();
  $questionmark->paragraph = required_param('paragraphId', PARAM_INT);
  $questionmark->vizcoshid = $vizcosh->id;
  $questionmark->chapter = $chapterid;
  $questionmark->author = $USER->id;

  // save questionmark into DB
  if (!$insertId = insert_record('vizcosh_questionmarks', $questionmark)) {
    ajaxReply(601, 'Error saving questionmark.');
  }
  ajaxReply(200,'Ok');
}


/**
 * removes a questionmark-entry for a certain paragraph from the database for the current user
 * (if a user has un-checked the questionmark-symbol)
 */
function ajax_Commentbox_deleteQuestionmark() {
  global $CFG, $USER, $chapterid, $cm;
  
  $paragraphId = required_param('paragraphId', PARAM_INT);

  if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
    error('Course module is incorrect');
  }		
  
  // remove questionmark entry from DB
  if (!delete_records_select('vizcosh_questionmarks', 'vizcoshid = '.$vizcosh->id. ' AND author = '. $USER->id . ' AND chapter = ' . $chapterid . ' AND paragraph = ' . $paragraphId)) {
    ajaxReply(601, 'Error deleting questionmark.');
  }

  ajaxReply(200,'Ok');
}


/**
 * marks a certain paragraph as "read" if the user has opened the comments belonging 
 * to this paragraph. 
 */
function ajax_Commentbox_markParagraphRead() {
  global $CFG, $USER, $chapterid, $cm;		
  
  $paragraphId = required_param('paragraphId', PARAM_INT);

  if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
    error('Course module is incorrect');
  }		
  
  // check in DB if the paragraph has already been marked in the past
  $paragraphMarkedReadInThePast = get_record_select('vizcosh_commentread', 'vizcoshid = '.$vizcosh->id.' AND chapterid = ' . $chapterid . ' AND paragraphid = ' . $paragraphId . ' AND userid=' . $USER->id);
  
  // insert or update the new paragraph-read-state into DB
  $newParagraphReadObject = new object();
  $newParagraphReadObject->vizcoshid = $vizcosh->id;
  $newParagraphReadObject->userid = $USER->id;
  $newParagraphReadObject->chapterid = $chapterid;
  $newParagraphReadObject->paragraphid = $paragraphId;
  $newParagraphReadObject->readtime = time();
  
  if ($paragraphMarkedReadInThePast == false) {
    // save into DB the time when the user read the paragraph		
    if (!$insertId = insert_record('vizcosh_commentread', $newParagraphReadObject)) {
      ajaxReply(601, 'Error marking paragraph as read.');
    }	
  } else {
    // update DB-entry with new read-time
    $newParagraphReadObject->id = $paragraphMarkedReadInThePast->id;

    if (!update_record('vizcosh_commentread', $newParagraphReadObject)) {
      ajaxReply(601, 'Error updateing paragraph-read-state.');
    }
  }
  ajaxReply(200,'Ok');
}


/**
 * Returns the html-code that will be wrapped around ajax-responses for the VizCoSH-plugin. 
 * @param String $title the chapter-title 
 * @param array $paragraphsArray An array containing all paragraph-texts
 */
function getWrappingHtmlCode($title, $paragraphsArray) {
  global $USER, $CFG, $course, $chapterid, $cm, $emargoroot;
  
  $nocleanoption = new object();
  $nocleanoption->noclean = true;

  // print document title
  $content = '	
	<div id="contentblock_0">
		<a name="0"></a><div class="cbcontent">
			<p class="vizcosh_chapter_title">' .
  format_text($title, FORMAT_HTML, $nocleanoption, $course->id) . 
    '</p>
	</div>';

  // print all paragraphs
  //$paragraphsArray enthält alle Absätze
  for($i=0; $i<count($paragraphsArray); $i++)
    {
      //$currentParagraph enthält den kompletten Absatz
      $currentParagraph = $paragraphsArray[$i]->content;
      $comment_count =
	emargo_get_comment_counter ($chapterid, $paragraphsArray[$i]->id, 0, false);
      $unread_comment_count =
	emargo_get_comment_counter ($chapterid, $paragraphsArray[$i]->id, 0, true);
      $private_note_count =
	emargo_get_comment_counter ($chapterid, $paragraphsArray[$i]->id, 1, false);
      $paragraph_is_bookmarked =
	emargo_paragraph_is_bookmarked ($chapterid, $paragraphsArray[$i]->id);
      $paragraph_is_questionmarked =
	emargo_paragraph_is_questionmarked ($chapterid, $paragraphsArray[$i]->id);
      $questionmark_count =
	emargo_count_questionmarks ($chapterid, $paragraphsArray[$i]->id);
      $questionmark_fontsize = (($questionmark_count > 9) ? 9 : $questionmark_count);

      $paragraphinfo =
	$comment_count . ' ' . get_string("comments", 'vizcosh') . ', ' .
	$unread_comment_count . ' ' . get_string("unread", 'vizcosh') . ' ' .
	get_string("and", 'vizcosh') . ' ' .
	$private_note_count  .' ' . get_string("notes", 'vizcosh')  . ' ' .
	get_string("on_paragraph", 'vizcosh') . ' ' . ($i + 1);
    
      $content .= '<div title="' . ($i + 1) . '" id="contentblock_' . $paragraphsArray[$i]->id . '"'.
	' onmouseover="javascript:toggleParagraphIcons(' . ($i + 1) . ');"'.
	' onmouseout="javascript:toggleParagraphIcons(' . ($i + 1) . ');"> '.
	' <a name="' . $paragraphsArray[$i]->id . '"></a> '.
	'<div class="icons"> '.
	'<a title="' . $paragraphinfo . '" href="#' . $paragraphsArray[$i]->id .  '">'.
	'<img class="paragraph_read" id="para-' . ($i + 1) . '_id-'. $paragraphsArray[$i]->id.'" '.
	'src="' . $emargoroot . '/pix/buttons/discuss_paragraph_big.png" border="0" '.
	'onmouseover="this.src=\'' . $emargoroot . '/pix/buttons/discuss_paragraph_big_hover.png\'" '.
	'onmouseout="this.src=\'' . $emargoroot . '/pix/buttons/discuss_paragraph_big.png\'" /></a>';
    
      if ($unread_comment_count > 0)
	$ImgPostfix = "new";
      else
	$ImgPostfix = "all";
    
      $content .= '<div title="' . $paragraphinfo . '"><font size=1>' . $comment_count .
	'<img src="' . $emargoroot . '/pix/buttons/discuss_paragraph_'.$ImgPostfix.'.png" '.
	'border="0" align=top>&nbsp;'.
	$private_note_count.
	'<img src="' . $emargoroot . '/pix/buttons/discuss_paragraph_private.png" '.
	'border="0" align=top></font></div>';
    
      #Manages the display of bookmarks
      $content .=
	'<div ' . ($paragraph_is_bookmarked ?
		   '' :
		   'class="bookmark-hidden" id="bookmark-' . ($i+1) . '"') . '>'.
	'<a title="' .
	($paragraph_is_bookmarked ?
	 get_string("remove_bookmark_from_paragraph", 'vizcosh') :
	 get_string("set_bookmark_on_paragraph", 'vizcosh') )  .
	' ' . ($i+1) .  '" href="javascript:' . ($paragraph_is_bookmarked ?
						 'delete' :
						 'save') .
	'Bookmark(' . $paragraphsArray[$i]->id . ');">'.
	'<img onmouseover="this.src=\'' . $emargoroot . '/pix/buttons/bookmark' .
	(!$paragraph_is_bookmarked ? '' : '_gray') . '.png\'" '.
	'onmouseout="this.src=\'' . $emargoroot . '/pix/buttons/bookmark' .
	($paragraph_is_bookmarked ? '' : '_gray') . '.png\'" '.
	'src="' . $emargoroot . '/pix/buttons/bookmark' .
	($paragraph_is_bookmarked ? '' : '_gray') . '.png" class="bookmarkimg" border="0" /></a></div>';
    
      #Manages the display of the questionmarks, (used to mark a parapgraph as difficult to understand)
      #Todo: Muss ($i+1) auch durch $paragraphsArray[$i]->id ersetzt werden? Was bewirkt die id="questionmark-n"? analog Zeile 429
      $content .= '<div ' . ($paragraph_is_questionmarked ? 'class="questionmark" id="questionmark-' . ($i+1) . '"' : 'class="questionmark-hidden" id="questionmark-hidden' . ($i+1) . '"') . ' style="font-size:1.' . $questionmark_fontsize . 'em;"><a title="' . ($paragraph_is_questionmarked ? get_string("remove_questionmark", 'vizcosh') : get_string("set_questionmark", 'vizcosh') )  . '" href="javascript:' . ($paragraph_is_questionmarked ? 'delete' : 'save') . 'Questionmark(' . $paragraphsArray[$i]->id . ');">?</a></div></div></div>';
    
      $content .= '<div class="paragraph_number">';

      if (isset($USER->editing) && ($USER->editing==1))
	{
	  //Todo Durch Ajax Drag and Drop ersetzen
	  $content .=
	    '<div>'.

	    '<a href="moveparagraph.php?id='.$cm->id.'&chapterid='.$chapterid.
	    '&position='.($i+1).'&paragraphid='.$paragraphsArray[$i]->id.
	    '&moveup=1&merge=0&sesskey='.$USER->sesskey.'">'.
	    '<img src="'.$CFG->pixpath.'/t/up.gif" height="11" class="iconsmall" '.
	    'alt="' . get_string('move_up') . '" /></a><br>'.

	    '<a href="moveparagraph.php?id='.$cm->id.'&chapterid='.$chapterid.
	    '&position='.($i+1).'&paragraphid='.$paragraphsArray[$i]->id.
	    '&moveup=1&merge=1&sesskey='.$USER->sesskey.'">'.
	    '<img src="'.$CFG->pixpath.'/t/moveleft.gif" height="11" class="iconsmall" '.
	    'alt="' . get_string('merge_up') . '" /></a><br>'.

	    '<a href="moveparagraph.php?id='.$cm->id.'&chapterid='.$chapterid.
	    '&position='.($i+1).'&paragraphid='.$paragraphsArray[$i]->id.
	    '&moveup=0&merge=1&sesskey='.$USER->sesskey.'">'.
	    '<img src="'.$CFG->pixpath.'/t/removeright.gif" height="11" class="iconsmall" '.
	    'alt="' . get_string('merge_down') . '" /></a><br>'.

	    '<a href="moveparagraph.php?id='.$cm->id.'&chapterid='.$chapterid.
	    '&position='.($i+1).'&paragraphid='.$paragraphsArray[$i]->id.
	    '&moveup=0&merge=0sesskey='.$USER->sesskey.'">'.
	    '<img src="'.$CFG->pixpath.'/t/down.gif" height="11" class="iconsmall" '.
	    'alt="' . get_string('move_down') . '" /></a><br>';

	  
	  #edit, add and remove paragraphs (symbols)
	  $content .=
	    '<a title="'.get_string('edit').'" href="editparagraph.php?id='.$cm->id.'&amp;chapterid='.$chapterid.'&paragraphid='.$paragraphsArray[$i]->id.'&orderposition='.$paragraphsArray[$i]->orderposition.'"><img src="'.$CFG->pixpath.'/t/edit.gif" height="11" class="iconsmall" alt="'.get_string('edit').'" /></a><br><a title="'.get_string('addafter', 'vizcosh').'" href="editparagraph.php?id='.$cm->id.'&amp;chapterid='.$chapterid.'&paragraphid=-1&orderposition='.$paragraphsArray[$i]->orderposition.'"><img src="pix/add.gif" height="11" class="iconsmall" alt="'.get_string('addafter', 'vizcosh').'" /></a><br><a title="'.get_string('delete').'" href="deleteparagraph.php?id='.$cm->id.'&amp;chapterid='.$chapterid.'&paragraphid='.$paragraphsArray[$i]->id.'&amp;sesskey='.$USER->sesskey.'"><img src="'.$CFG->pixpath.'/t/delete.gif" height="11" class="iconsmall" alt="'.get_string('delete').'" /></a></div>';
	}
    
      #This is the DIV-Class where the paragraphs content is displayed
      #This displays the left paragraph numbers
      $content .= '</div>'.	
	'<div id="'.$paragraphsArray[$i]->id.'" class="cbcontent" onmouseout="Marker.disable();" onmouseover="Marker.enable();" onmouseup="markText(this.id);">' . $currentParagraph . '<div style="font-size: 0.6em; float: right; vertical-align: bottom;"><a target="_self" href="#' . $paragraphsArray[$i]->id . '" id="paragraph_number_' . ($i + 1) . '">('.($i + 1) . ')</a></div></div>' . (($i != count($paragraphsArray) -1) ? '<br />' : '') . '
		</div><div style="clear:both;"></div>';
    }	
  
  return $content;
}

?>