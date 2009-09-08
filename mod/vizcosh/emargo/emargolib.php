<?php 
/*
 * Local library file for eMargo-features. These are functions that 
 * are used only by eMargo.
 *
 * @author: Andreas Kothe
 */

// required for recursive threaded comment view
global $threaded_comments, $write_comment_depth;


/**
 * prints the form block
 *
 * @param int $cmid course-module-ID for current module
 * @param object $module full module record object
 * @return void
 **/
function emargo_print_button_block($cmid, $module, $paragraphs, $moduleName='vizcosh') {
	global $chapterid, $cm, $CFG;

	$context = get_context_instance(CONTEXT_MODULE, $cmid);

	$content = '
		  <div class="emargobuttons">
				<form id="buttonbar" class=" yui-skin-sam">';

		if ($moduleName != 'slides') {
			$content .= '
					<a id="togglebutton" href="javascript:toggleMarkings();"></a>						
					<a id="markerbutton" href="javascript:Marker.init();"></a> 				
					<a id="deletebutton" href="javascript:deleteAllMarkings();"></a>
					<a title="' . get_string('printcomments', 'emargo') . '" target="_blank" href="mycomments.php?id=' . $cm->id . '" onclick="this.target=\'_blank\'"><img align=top src="pix/print_comments.png" alt="' . get_string('printcomments', 'emargo') . '"/></a>
					<a title="' . get_string('showpublicandprivatecomments', 'emargo') . '" href="javascript:SwitchCommentFilter(0);"><img align=top src="pix/show_all_comments.png" style="margin:0px 2px 0px 2px;" alt="' . get_string('showpublicandprivatecomments', 'emargo') . '"/></a>
					<a title="' . get_string('showprivatecomments', 'emargo') . '" href="javascript:SwitchCommentFilter(1);"><img style="margin:0px 2px 0px 2px;" align=top src="pix/show_private_comments.png" alt="' . get_string('showprivatecomments', 'emargo') . '"/></a>';
		}
		
		$content .= '
					<a id="helpbutton" href="javascript:showEmargoHelp();"></a> 
				</form>
		</div>';

	$content .= '<div id="comments">' . emargo_print_commentbox($cmid, $module, $paragraphs, $moduleName) . '</div>';

	print_side_block(get_string('blocktitle', 'emargo'), $content, NULL, NULL, '', array('class' => 'emargoform'), get_string('blocktitle', 'emargo'));
}


/**
 * returns the emargo commentbox as string (needs to be reloaded using AJAX dynamically)
 **/
#Raphael
# $moduleName ist ein optionaler Parameter. Wird die Funktion ohne diesen aufgerufen (was in ajax_Commentbox_saveComment der Fall ist), wird er auf die leere Menge gesetzt.
# Ich habe daher fürs erste den Standardwert auf 'vizcosh' geändert. Das muss dann noch en detail für das Slides-Modul getestet werden. (TODO)
#vorher: function emargo_print_commentbox($cmid, $module, $chapter, $moduleName='') {
#todo $cmid überflüssig, oder?
function emargo_print_commentbox($cmid, $module, $paragraphs, $moduleName='vizcosh', $chapterid=-1, $private=0) {
	global $CFG, $threaded_comments, $cm;
			
	if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
		error('Course module is incorrect');
	}		
		
	if ($chapterid==-1)
		$chapterid = $GLOBALS["chapterid"];
	
	if ($moduleName == 'vizcosh') {
		#$cp_comments_width = $CFG->vizcosh_commentboxwidth - 35;
		$cp_comments_width = $CFG->vizcosh_commentboxwidth - 35;
		$cp_comments_height = $CFG->vizcosh_commentboxheight;
		$str_paragraph = get_string("paragraph", "emargo");
	} else {
		$cp_comments_width = $CFG->slides_commentboxwidth - 35;
		$cp_comments_height = $CFG->slides_commentboxheight;
		$str_paragraph = get_string("slide", "emargo");
	}

	$cp_cols = intval(($cp_comments_width + 35) / 10);
	
	$content = '
		<input type="hidden" name="comment_total_count" value="0" />
		<div id="cp_newComment"></div>

		<div class="activity_box" id="container_commentContent"> 
			<div id="cp_showAll"><a id="show_all_comments_action" href="javascript:void(0)">' . get_string("help", "emargo") . '<img src="' . $CFG->pixpath . '/help.gif" alt="' . get_string("help", "emargo") . '" title="' . get_string("help", "emargo") . '" border="0" style="vertical-align:middle;" /></a></div>
			
			<div id="comment_help">
				<h3>' . get_string("comments_howto_header", "emargo") . '</h3>
				<div id="cp_comments" style="width:' . $cp_comments_width . 'px; height:' . $cp_comments_height . 'px;">						
				 <div class="commentlist">
				 ' . get_string("comments_howto_text", "emargo") . '
				 </div>
				</div>
			</div>';						

			#Div that consists of a list of all paragraphs which contain comments
			$content .= '<div id="comment_index">
				<h3>' . get_string("comments_overview", "emargo") . '</h3>
				<div id="cp_comments" style="width:' .$cp_comments_width . 'px; height:' . $cp_comments_height . 'px;">
					<div class="commentlist"><table width=100%>';
							
							$all_post = 0;
							$all_notseen = 0;
							$paragraphnr = 0;		
							
							if ($paragraphs){
								foreach($paragraphs as $cparagraph){
										
									if ($cparagraph->chapterid == $chapterid){

										$paragraphnr = $paragraphnr + 1;
										if (emargo_get_comment_counter($chapterid, $cparagraph->id, $private, false) > 0) {
											$par_post = emargo_get_comment_counter($chapterid, $cparagraph->id, $private, false);
											#count all postings
											$all_post = $all_post + $par_post;
											#count all not seen postings
											$ch_notseen = emargo_get_comment_counter($chapterid, $cparagraph->id, $private, true);
											$all_notseen = $all_notseen + $ch_notseen; 
											
											$comment_count = emargo_get_comment_counter($chapterid, $cparagraph->id, $private, false);
											$unread_comment_count = emargo_get_comment_counter($chapterid, $cparagraph->id, $private, true);
											
											if ($unread_comment_count > 0)
												$ImgPostfix = "new";
											else
												$ImgPostfix = "all";	
											
											if (!$private)
												$content .= '<tr><td><a class="paragraph_read_index" id="cpar-' . $paragraphnr .'_id-'.$cparagraph->id.'" href="#' . $cparagraph->id . '">' . $str_paragraph . ' ' . $paragraphnr . '</a> </td><td style="text-align:right;"  title="Insgesamt '.$comment_count.' Kommentare, davon '.$unread_comment_count.' Kommentare ungelesen">' . $comment_count . '<img src="' . $CFG->wwwroot . '/mod/vizcosh/emargo/pix/buttons/discuss_paragraph_'.$ImgPostfix.'.png" border="0" align=top> </td><td title="'. get_string("last_comment", "emargo") .'"> <img style="margin-right: 3px;" src="' . $CFG->wwwroot . '/mod/vizcosh/emargo/pix/buttons/discuss_last_comment.png" border="0" align=top>';
											else
												$content .= '<tr><td><a class="paragraph_read_index" id="cpar-' . $paragraphnr .'_id-'.$cparagraph->id.'" href="#' . $cparagraph->id . '">' . $str_paragraph . ' ' . $paragraphnr . '</a> </td><td> <span title="Insgesamt '.$comment_count.' private Kommentare">' . $comment_count . '<img src="' . $CFG->wwwroot . '/mod/vizcosh/emargo/pix/buttons/discuss_paragraph_private.png" border="0" align=top><td title="'. get_string("last_comment", "emargo") .'"> <img style="margin-right: 3px;" src="' . $CFG->wwwroot . '/mod/vizcosh/emargo/pix/buttons/discuss_last_comment.png" border="0" align=top>';
											
											#selects the newest comment for this paragraph
											$comment = get_record_sql('SELECT subject,timecreated,message FROM '. $CFG->prefix . 'vizcosh_comments WHERE vizcoshid = '.$vizcosh->id.' AND chapter='.$chapterid.' AND paragraph='.$cparagraph->id.' AND TYPE='.$private.' ORDER BY timecreated DESC');
											
											if ($comment->subject != "")
												$CommentPreview = $comment->subject;
											else
												$CommentPreview = $comment->message;

											if (strlen($CommentPreview) > 25)
												$CommentPreview = substr($CommentPreview, 0, 25) . '...';
																					
											$content .= '<i>' . $CommentPreview . '</i></td></tr>';											
										}
									}
								}
							}

		$content .= '</table>';
					if (!$private)
						$content .= '
							<ul><li>Kommentare insgesamt: ' . $all_notseen . '<img src="' . $CFG->wwwroot . '/mod/vizcosh/emargo/pix/buttons/discuss_paragraph_new.png" border="0" align=top>&nbsp;&nbsp;' . $all_post . '<img src="' . $CFG->wwwroot . '/mod/vizcosh/emargo/pix/buttons/discuss_paragraph_all.png" border="0" align=top> </li></ul>';
					else
						$content .= '
							<ul><li>Private Kommentare insgesamt: ' . $all_notseen . '<img src="' . $CFG->wwwroot . '/mod/vizcosh/emargo/pix/buttons/discuss_paragraph_private.png" border="0" align=top></li></ul>';
							
					$content .= '
					</div>
				</div>
			</div>
		</div>';
		
		$content .= '<div id="comment_read">
			<h3>' . get_string("comments", "emargo") . ' <span class="normalLight"><span id="active_paragraph"></span></span></h3>
			<div id="cp_comments" style="width:' . $cp_comments_width . 'px; height:' . $cp_comments_height . 'px;">
				<div class="commentlist" id="commentlist">
					<p id="no_comments_yet">' . get_string("no_comments_yet", "emargo") . '</p>
				</div>';
		
			if ($paragraphs){					
				foreach($paragraphs as $cparagraph){
					$ShowPrivateOnly = "";				
					if ($private == 1)
						$ShowPrivateOnly = ' AND type = 1';
						
					if ($commentsInParagraph = get_records_select($moduleName . "_comments", 'chapter = ' . $chapterid . ' AND paragraph = ' . $cparagraph->id . $ShowPrivateOnly, 'timecreated')){
						#contains comments - proceed									
						$threaded_comments = array();
						$write_comment_depth = 0;			

						// if object returned by sql is not wrapped in an array, construct a new array containing this object 
						// (required for avoiding error messages with "foreach" below)
						if (!is_array($commentsInParagraph)) {
							$commentsInParagraph = array(0 => $commentsInParagraph);
						}

						foreach ($commentsInParagraph as $comment) {
							if ($private == 1)
								$content .= emargo_print_comment($comment);
							else
								$threaded_comments[$comment->parentid][] = $comment;
						}
						
						if ((!$private == 1) && (isset($threaded_comments[0]) && (is_array($threaded_comments[0])))) {
							foreach($threaded_comments[0] as $c) {
								$content .= emargo_print_comment(&$c);
							}
						}
					}
				}
			}
	
			$content .= '<div id="base_comment_box">
				<div id="addcomment">
					<a id="addcommentanchor" class="addcommentanchor"></a>
					<form id="commentform">
					<div class="add">
						<div id="reroot" style="display: none;">
							<small><a href="javascript:reRoot()">' . get_string("cancel_reply", "emargo") . '</a></small>
						</div>
						<!-- start -->
					 	<small>' . get_string("subject", "emargo") . ':</small> 
						<div>
								<input style="width:100%;" type="text" name="subject" id="comment_subject" class="textarea" value="" size="'.($cp_cols).'" tabindex="1" />
						</div>
						<small>' . get_string("message", "emargo") . '<img class="req" title="'.get_string('requiredelement', 'form').'" alt="'.get_string('requiredelement', 'form').'" src="'.$CFG->pixpath.'/req.gif'.'" />:</small>
						<div style="width: 100%;">						
							<textarea style="width:100%;" name="comment" id="comment_message" cols="'.$cp_cols.'" rows="9" tabindex="4"></textarea>
						</div>
						<div style="text-align:center;">
							<input type="hidden" id="redirect_to" name="redirect_to" value="" />
							
							<a href="javascript:onAddComment();"><img style="margin:10px 2px;" onclick="" src="' . $CFG->wwwroot . '/mod/vizcosh/emargo/pix/buttons/diskbutton.png" id="addcommentbutton" name="addcommentbutton" tabindex="5"/></a>
							
							<a href="javascript:onAddPrivateComment();"><img style="margin:10px 2px;" onclick="" src="' . $CFG->wwwroot . '/mod/vizcosh/emargo/pix/buttons/notizbutton.png" id="addprivatecommentbutton" name="addprivatecommentbutton" tabindex="6"/></a>													
						</div>
					</div>
					<input type="hidden" id="comment_reply_ID" name="comment_reply_ID" value="0" />
					<input type="hidden" id="comment_contentIndex" name="comment_contentIndex" value="-1" />
					<input type="hidden" id="comment_parent" name="comment_parent" value="0" />
					<input type="hidden" id="comment_switchstate" name="comment_switchstate" value="0" />
					</form>
				</div>
			</div>
			<div style="min-height:50px;">	</div>			
		</div>';
		
	return $content;
}

/**
 * returns all formatted comments in THREADED view (recursive(!) function)
 */
function emargo_print_comment(&$comment) {
	global $CFG, $USER, $course, $cm, $write_comment_depth, $threaded_comments, $moduleName;
	$content = '';
	$context = get_context_instance(CONTEXT_MODULE, $cm->id);
		
	if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
		error('Course module is incorrect');
	}			
		
	#Todo: Besser in Array schreiben, als jedes Mal die DB abfragen? Bei vielen Comments sind das viele Abfragen
	$paragraphReadTime = (int) get_field_select($moduleName . "_commentread", 'readtime', 'vizcoshid = '.$vizcosh->id.' AND chapterid = ' . $comment->chapter . ' AND paragraphid = ' . $comment->paragraph . ' AND userid = ' . $USER->id);	
	
	#ungelesener Kommentar
	if ($comment->modifiedbyuserid != 0){
#		if ($comment->modifiedbyuserid != $USER->id)
			$CommentUnread = ($comment->modifiedbyuserid > $paragraphReadTime);
#		else
#			$CommentUnread = false;
	}
	else{
#		if ($comment->author != $USER->id)
			$CommentUnread = ($comment->timecreated > $paragraphReadTime);
#		else
			#never mark own comments as unread
#			$CommentUnread = false;
	}
	
	$is_private_note = ($comment->type == 1);

	if (has_capability('moodle/course:create', $context, $comment->author))
		$coursemaster = ' style="border: solid green 1px; background-color: #b7fcb5; padding:1px 10px; vertical-align: middle;"';
	else                                                                                     
		$coursemaster = '';

	if ($is_private_note){
		$comment_symbol	= 'discuss_paragraph_private.png';
		$comment_style_unread = '';
	}	
	else{
		if ($CommentUnread){
			$comment_style_unread = 'style="background-color:#f0f871;"';
			$comment_symbol	= 'discuss_paragraph_new.png';
		}
		else{
			$comment_symbol	= 'discuss_paragraph_all.png';		
			$comment_style_unread = '';
		}
	}
	
	// fetch the author's name from database
	$userinfo = get_record_select('user', 'id = ' . $comment->author);

	if ($comment->modifiedbyuserid != 0) {
		$modifiedbyusersinfo = get_record_select('user', 'id = ' . $comment->modifiedbyuserid);
	}

	$allowdelete = false;
	$allowedit = false;
	// only allow the deletion of comments by teachers (or if it is a private note by a user)
	if (has_capability('moodle/course:manageactivities', $context) || (($comment->author == $USER->id) && $is_private_note)) {
		$allowedit = true;
	}
	
	// required for format_text()
	$nocleanoption = new object();
	
	//required for strftime() (otherwise, strftime throws error message)
	date_default_timezone_set("Europe/Paris");

	// don't display private notes of other users
	// (private notes have message->type = 1)

	if ((!$is_private_note) || (($is_private_note) && ($comment->author == $USER->id))) {
	
		$content = '
		<div class="cp_commentBody" id="comment-index-' . $comment->paragraph . '">
				<div id="div-comment-' . $comment->id . '" class="comment">';
	
					// special stylesheet for own private notes
					if ($is_private_note)
						$private_note_style = 'style="border-color:#3399cc; background-color: #e7eff3;"';
					else
						$private_note_style = "";
	
					$content .= '
					<div style="display:none;"><a id="comment-link-' . $comment->id . '" name="comment-' . $comment->id . '">1</a></div>
					<div style="display:block;">
						<div class="commenticons">';
								if ($allowedit) {
									$content .= '<a href="javascript:showEditForm(' . $comment->id . ');" title="' . get_string("edit_comment", "emargo") . '"><img src="' . $CFG->wwwroot . '/mod/vizcosh/emargo/pix/buttons/edit-comment.png" border="0" /></a> ';
								}
								#if ($allowdelete) {
								#	$content .= '<a href="javascript:deleteComment(' . $comment->id . ');" title="' . get_string("delete_comment", "emargo") . '"><img src="' . $CFG->wwwroot . '/mod/vizcosh/emargo/pix/buttons/delete-comment.png" border="0" /></a>';
								#}
																
								$content .= '
							</div>							
							<div style="vertical-align: middle;" class="title">';
							
							$content .= print_user_picture($userinfo, $course->id, $userinfo->picture, 20, true, true);

							$content .= '<strong><span';
								
							if ($is_private_note)
								$content .= '>'.get_string("private_note_hint", "emargo");
							else
								$content .= $coursemaster.'>'. $userinfo->firstname . ' ' . $userinfo->lastname;

							$content .= '</span><a href="#comment-' . $comment->id . '"></a></strong><span class="author_on_paragraph"> ' . get_string("on_paragraph", "emargo") . ' ' . $comment->paragraph . '</span>
							</div>';
																
					$content .= '
					</div>
					<div class="body">
						<div ' . $comment_style_unread . ' class="content" '. $private_note_style . 'id="comment-content-' . $comment->id . '">
							<p class="subject"><img src="' . $CFG->wwwroot . '/mod/vizcosh/emargo/pix/buttons/'. $comment_symbol .'" border="0" align=absmiddle>&nbsp;' . format_text($comment->subject, FORMAT_PLAIN, $nocleanoption, $course->id) . '</p>
							<p>' . format_text($comment->message, FORMAT_MOODLE, $nocleanoption, $course->id) . '</p>
							' . (($comment->timemodified != 0) ? '<p style="margin-top:5px;"><small><em>' . get_string("lastupdate_by", "emargo") . ' ' . $modifiedbyusersinfo->firstname . ' ' . $modifiedbyusersinfo->lastname . ' ' .  get_string("lastupdate_at", "emargo") . ' '. userdate($comment->timemodified, get_string('strftimerecentfull')) . '</em></small></p>' : '') . '
						</div>						
						<div class="cp_commentMeta">
							<div class="cp_replyLink">';
							// don't display reply link in private notes						
							if (!$is_private_note) {
								$content .= '
									<a class="reply_link" href=\'javascript:moveAddCommentBelow("div-comment-' . $comment->id . '", ' . $comment->id . ', false, "' . format_text($comment->subject, FORMAT_PLAIN, $nocleanoption, $course->id) . '")\'>' . get_string("reply", "emargo") . ' &raquo;</a>';
							}
							$content .= '
								</div>
							<div class="cp_datePosted"><img align=absmiddle src="' . $CFG->wwwroot . '/mod/vizcosh/emargo/pix/buttons/smallclock.png" border="0" />&nbsp;' . userdate($comment->timecreated, get_string('strftimerecentfull')) . '</div>
						</div>';
						// end of special style for private notes
#						if ($is_private_note) {
#							$content .= '</div>';
#						}
					$content .= '
					</div>';
		
		if(isset($threaded_comments[$comment->id])) {
			$id = $comment->id;
			foreach($threaded_comments[$id] as $c) {
				$write_comment_depth++;
				//fire up the recursion
				$content .= emargo_print_comment($c);
				$write_comment_depth--;
			}
		}
	
		$content .= '
			</div>
		</div>';
	}

	return $content;
	
}

/**
 * Returns the comment-counter for a certain paragraphID (not the paragraphs orderposition!).
 */
function emargo_get_comment_counter($chapterid, $paragraphId, $messageType, $returnUnreadComments=false) {
	global $USER, $moduleName, $cm;
	
	$whereSql = "";
	if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
		error('Course module is incorrect');
	}			
		
	if ($messageType == 1) {
		//count only own private notes, not the private notes of other user
		$whereSql = " AND author=" . $USER->id;
	} else {
		// if comment is not a private notice: check if there are unread posts in the paragraph for the current user
		$paragraphReadTime = (int) get_field_select($moduleName . "_commentread", 'readtime', 'vizcoshid = '.$vizcosh->id.' AND chapterid = ' . $chapterid . ' AND paragraphid = ' . $paragraphId . ' AND userid = ' . $USER->id);
		if ($paragraphReadTime != 0 && $returnUnreadComments) {
			$whereSql = " AND timecreated >= " . $paragraphReadTime;
		} 
	}

	$commentCount = count_records_select($moduleName . "_comments", 'chapter = ' . $chapterid . ' AND paragraph = ' . $paragraphId . ' AND type = ' . $messageType  . $whereSql);
	
	return $commentCount;
}

/**
 * Returns the count of comments for the provided user and eMargo.
 */
function emargo_get_count_unread_posts($cm, $course) {
    global $CFG, $USER;
	
	if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
		error('Course module is incorrect');
	}				

	$UnreadComments = 0;

	$chapters = get_records_select('vizcosh_chapters', 'vizcoshid = '.$vizcosh->id.' AND hidden = 0', 'pagenum', 'id, pagenum, title, hidden');
	if ($chapters){
		foreach($chapters as $ch) {	
			$paragraphs = get_records_select('vizcosh_paragraphs', "vizcoshid = $vizcosh->id AND chapterid = $ch->id", 'chapterid, orderposition', 'id, orderposition');

			if ($paragraphs){
				foreach($paragraphs as $cparagraph){
					$paragraphReadTime = (int) get_field_select($cm->modname . "_commentread", 'readtime', 'vizcoshid = '.$vizcosh->id.' AND chapterid = ' . $ch->id . ' AND userid = ' . $USER->id . ' AND paragraphid = ' . $cparagraph->id);
					if ($paragraphReadTime != 0) {
						$whereSql = " AND timecreated >= " . $paragraphReadTime;
					}
					else
						$whereSql = "";
						
				$UnreadComments = $UnreadComments + ((int) count_records_select($cm->modname . "_comments", 'chapter = ' . $ch->id . ' AND paragraph = ' . $cparagraph->id . ' AND type = 0' . $whereSql));
				}
			}
		}
	}
	
	return $UnreadComments;

}

/**
 * checks whether or not a certain paragraph has been bookmarked by the current user
 */
function emargo_paragraph_is_bookmarked($chapterid, $paragraphId) {
	global $USER, $cm;
		
	if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
		error('Course module is incorrect');
	}		

	$bookmarkState = count_records_select('vizcosh_bookmarks', 'vizcoshid = '.$vizcosh->id. ' AND chapter = ' . $chapterid . ' AND paragraph = ' . $paragraphId . ' AND author = ' . $USER->id);
	return ($bookmarkState > 0);
}

/**
 * checks whether or not a certain paragraph has been "questionmarked" by the current user
 */
function emargo_paragraph_is_questionmarked($chapterid, $paragraphId) {
	global $USER, $cm;
		
	if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
		error('Course module is incorrect');
	}		

	$questionmarkState = count_records_select('vizcosh_questionmarks', 'vizcoshid = '.$vizcosh->id. ' AND chapter = ' . $chapterid . ' AND paragraph = ' . $paragraphId . ' AND author = ' . $USER->id);
	return ($questionmarkState > 0);
}

/**
 * counts how often a certain paragraph has been "questionmarked" by ALL users
 */
function emargo_count_questionmarks($chapterid, $paragraphId) {
	global $USER, $cm;
		
	if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
		error('Course module is incorrect');
	}		
	
	$questionmarkCount = count_records_select('vizcosh_questionmarks', 'vizcoshid = '.$vizcosh->id. ' AND chapter = ' . $chapterid . ' AND paragraph = ' . $paragraphId);
	return $questionmarkCount;
}


/**
 * prints the context-menu that appears if a marking gets clicked. This context-menu is used to delete single markings.
 */
function emargo_print_marker_contextmenu() {
	global $CFG;
	
	$strdeletemarking = get_string("delete_marking", "emargo");
	$stroptions = get_string("options", "emargo");
	
	// this is the context menu
	$contextmenu = '<div class="header" style="width:130px; height:18px; visibility:hidden;" id="markercontextmenu">
<a href="javascript:deleteMarking();"><img style="margin-right:3px;" src="' . $CFG->wwwroot . '/mod/vizcosh/pix/delete_marking.png" alt="' . $strdeletemarking . '" title="' . $strdeletemarking . '" />' . $strdeletemarking . '</a> <a href="javascript:hideMarkerContextmenu();"><img style="margin-left:10px;" src="' . $CFG->wwwroot . '/mod/vizcosh/emargo/pix/close.gif" /></a>
			</div>';

	// this is an additional hidden layer that is only set to "visible" if someone moves the mouse 
	// over a marker-button
	$buttontooltip = '<div style="visibility:hidden;" id="buttontooltip"></div>';

	return $contextmenu . $buttontooltip;
}



?>