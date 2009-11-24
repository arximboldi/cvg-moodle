<?PHP // $Id: restorelib.php,v 1.2 2006/03/29 06:21:56 skodak Exp $
  //This php script contains all the stuff to backup/restore
  //vizcosh mods
	
  //Todo

  //This is the "graphical" structure of the vizcosh mod:
  //
  //                       vizcosh
  //                     (CL,pk->id)
  //                        |
  //                        |
  //                        |
  //                     vizcosh_chapters
  //               (CL,pk->id, fk->vizcoshid)
  //
  // Meaning: pk->primary key field of the table
  //          fk->foreign key to link with parent
  //          nt->nested field (recursive data)
  //          CL->course level info
  //          UL->user level info
  //          files->table may have files)
  //
  //-----------------------------------------------------------


  /*
   This function executes all the restore procedure about this mod
  */
function vizcosh_restore_mods ($mod,$restore)
{
  global $CFG;

  $status = true;

  //Get record from backup_ids
  $data = backup_getid ($restore->backup_unique_code,
			$mod->modtype,
			$mod->id);

  if ($data)
    {
      // Now get completed xmlized object
      $info = $data->info;
      // traverse_xmlize($info);                          //Debug
      // print_object ($GLOBALS['traverse_array']);       //Debug
      // $GLOBALS['traverse_array']="";                   //Debug

      //Now, build the BOOK record structure
      $vizcosh->course = $restore->course_id;
      $vizcosh->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
      $vizcosh->summary = backup_todb($info['MOD']['#']['SUMMARY']['0']['#']);
      $vizcosh->numbering = backup_todb($info['MOD']['#']['NUMBERING']['0']['#']);
      $vizcosh->disableprinting = backup_todb($info['MOD']['#']['DISABLEPRINTING']['0']['#']);
      $vizcosh->disableemargo = backup_todb($info['MOD']['#']['DISABLEEMARGO']['0']['#']);
      $vizcosh->enablegroupfunction = backup_todb($info['MOD']['#']['ENABLEGROUPFUNCTION']['0']['#']);
      $vizcosh->customtitles = backup_todb($info['MOD']['#']['CUSTOMTITLES']['0']['#']);
      $vizcosh->timecreated = $info['MOD']['#']['TIMECREATED']['0']['#'];

      //The structure is equal to the db, so insert the vizcosh
      $newid = insert_record ('vizcosh',$vizcosh);

      //Do some output
      if (!defined('RESTORE_SILENTLY'))
	{
	  echo '<ul><li>'.get_string('modulename','vizcosh').' "'.$vizcosh->name.'"<br>';
	}
      backup_flush(300);
      
      if ($newid)
	{
	  // We have the newid, update backup_ids and load the chapters.
	  backup_putid ($restore->backup_unique_code,
			$mod->modtype,
			$mod->id, $newid);

	  $status = vizcosh_formats_restore ($mod->id, $newid, $info, $restore);
	  $status = vizcosh_vizalgos_restore ($mod->id, $newid, $info, $restore);
	  $status = vizcosh_chapters_restore ($mod->id, $newid, $info, $restore);
	}
      else
	{
	  $status = false;
	}
      // Finalize ul
      if (!defined('RESTORE_SILENTLY'))
	{
	  echo "</ul>";
	}
    }
  else
    {
      $status = false;
    }

  return $status;
}

// This function restores the vizcosh_formats
function vizcosh_formats_restore ($old_vizcosh_id,
				  $new_vizcosh_id,
				  $info, $restore)
{
  global $CFG;

  $status = true;

  // Get the formats array
  $formats = $info['MOD']['#']['FORMATS']['0']['#']['FORMAT'];
		
  for($i = 0; $i < sizeof($formats); $i++)
    {
      $sub_info = $formats [$i];
					
      // traverse_xmlize($sub_info);                //Debug
      // print_object ($GLOBALS['traverse_array']); //Debug
      // $GLOBALS['traverse_array']="";             //Debug

      // We'll need this later!!
      $old_id = backup_todb ($sub_info['#']['ID']['0']['#']);
			
      // Now, build the ASSIGNMENT_FORMATS record structure
      $format->name = backup_todb($sub_info['#']['NAME']['0']['#']);
      $format->version = backup_todb($sub_info['#']['VERSION']['0']['#']);
      $format->extension = backup_todb($sub_info['#']['EXTENSION']['0']['#']);
      $format->author = backup_todb($sub_info['#']['AUTHOR']['0']['#']);
      $format->date = backup_todb($sub_info['#']['DATE']['0']['#']);
      $format->jnlp_template = backup_todb($sub_info['#']['JNLP_TEMPLATE']['0']['#']);
      
      //The structure is equal to the db, so insert the vizcosh_formats
      $newid = false;

      if ($record = get_record ('vizcosh_vizalgo_formats',
				'name', $format->name,
				'version', $format->version,
				'date', $format->date))
	$newid = $record->id;
      else
	$newid = insert_record ('vizcosh_vizalgo_formats', $format);
      
      if ($newid)
	backup_putid ($restore->backup_unique_code,
		      'vizcosh_vizalgo_formats',
		      $old_id, $newid);
      else
	$status = false;
    }
  return $status;
}

// This function restores the vizcosh_vizalgos
function vizcosh_vizalgos_restore ($old_vizcosh_id,
				   $new_vizcosh_id,
				   $info, $restore)
{
  global $CFG;

  $status = true;

  // Get the vizalgos array
  $vizalgos = $info['MOD']['#']['VIZALGOS']['0']['#']['VIZALGO'];
		
  for($i = 0; $i < sizeof($vizalgos); $i++)
    {
      $sub_info = $vizalgos [$i];
					
      // traverse_xmlize($sub_info);                //Debug
      // print_object ($GLOBALS['traverse_array']); //Debug
      // $GLOBALS['traverse_array']="";             //Debug

      // We'll need this later!!
      $old_id = backup_todb ($sub_info['#']['ID']['0']['#']);
			
      // Now, build the ASSIGNMENT_VIZALGOS record structure
      $vizalgo->title = backup_todb($sub_info['#']['TITLE']['0']['#']);
      $vizalgo->description = backup_todb($sub_info['#']['DESCRIPTION']['0']['#']);
      $vizalgo->author = backup_todb($sub_info['#']['AUTHOR']['0']['#']);
      $vizalgo->date = backup_todb($sub_info['#']['DATE']['0']['#']);
      $vizalgo->data =
	addslashes (base64_decode (backup_todb($sub_info['#']['DATA']['0']['#'])));
      $vizalgo->fndata = backup_todb($sub_info['#']['FNDATA']['0']['#']);
      $vizalgo->format =
	backup_getid ($restore->backup_unique_code,
		      'vizcosh_vizalgo_formats',
		      backup_todb ($sub_info['#']['FORMAT']['0']['#']));
      $vizalgo->format = $vizalgo->format->new_id;
      $vizalgo->thumbnail =
	addslashes (base64_decode (backup_todb($sub_info['#']['THUMBNAIL']['0']['#'])));
      $vizalgo->fnthumbnail = backup_todb($sub_info['#']['FNTHUMBNAIL']['0']['#']);
      $vizalgo->topics = backup_todb($sub_info['#']['TOPICS']['0']['#']);
      $vizalgo->course = $restore->course_id;
      
      //The structure is equal to the db, so insert the vizcosh_vizalgos
      if ($record = get_record ('vizcosh_vizalgos',
				'title', $vizalgo->title,
				'course', $vizalgo->course,
				'date', $vizalgo->date))
	$newid = $record->id;
      else
	$newid = insert_record ('vizcosh_vizalgos', $vizalgo);

      if ($newid)
	backup_putid ($restore->backup_unique_code,
		      'vizcosh_vizalgos',
		      $old_id, $newid);
      else
	$status = false;
    }
  
  return $status;
}

//This function restores the vizcosh_chapters
function vizcosh_chapters_restore ($old_vizcosh_id,
				   $new_vizcosh_id,
				   $info, $restore)
{
  global $CFG;

  $status = true;

  //Get the chapters array
  $chapters = $info['MOD']['#']['CHAPTERS']['0']['#']['CHAPTER'];
		
  for($i = 0; $i < sizeof($chapters); $i++)
    {
      $sub_info = $chapters[$i];
					
      // traverse_xmlize($sub_info);                //Debug
      // print_object ($GLOBALS['traverse_array']); //Debug
      // $GLOBALS['traverse_array']="";             //Debug

      // We'll need this later!!
      $old_id = backup_todb ($sub_info['#']['ID']['0']['#']);
			
      // Now, build the ASSIGNMENT_CHAPTERS record structure
      $chapter->vizcoshid = $new_vizcosh_id;
      $chapter->pagenum = backup_todb($sub_info['#']['PAGENUM']['0']['#']);
      $chapter->title = backup_todb($sub_info['#']['TITLE']['0']['#']);
      $chapter->hidden = backup_todb($sub_info['#']['HIDDEN']['0']['#']);
      $chapter->timecreated = backup_todb($sub_info['#']['TIMECREATED']['0']['#']);
      $chapter->timemodified = backup_todb($sub_info['#']['TIMEMODIFIED']['0']['#']);
      $chapter->importsrc = backup_todb($sub_info['#']['IMPORTSRC']['0']['#']);
      
      //The structure is equal to the db, so insert the vizcosh_chapters
      $newid = insert_record ('vizcosh_chapters',$chapter);

      //Do some output
      if (($i+1) % 50 == 0)
	{
	  if (!defined('RESTORE_SILENTLY')) {
	    echo '.';
	    if (($i+1) % 1000 == 0) {
	      echo '<br>';
	    }
	  }
	  backup_flush(300);
	}

      if ($newid)
	{
	  // We have the newid, update backup_ids and restore paragraphs
	  backup_putid($restore->backup_unique_code,'vizcosh_chapters',$old_id,
		       $newid);
			
	  $status = vizcosh_paragraphs_restore ($new_vizcosh_id, $newid,
						$sub_info, $restore);			 
	}
      else
	{
	  $status = false;
	}
    }
  return $status;
}

//This function restores the vizcosh_paragraphs
function vizcosh_paragraphs_restore ($new_vizcosh_id,
				     $new_chapter_id,
				     $chapter,
				     $restore)
{
  global $CFG;

  $status = true;

  // Get the paragraphs array
  // Todo: Check if at least one paragraph exists - otherwise an error
  // appears on restore (not tragical)
  $paragraphs = $chapter ['#']['PARAGRAPHS']['0']['#']['PARAGRAPH'];	   

  for($i = 0; $i < sizeof($paragraphs); $i++)
    {
      $sub_info = $paragraphs[$i];

      //traverse_xmlize($sub_info);                   //Debug
      //print_object ($GLOBALS['traverse_array']);    //Debug
      //$GLOBALS['traverse_array']="";                //Debug
      
      //We'll need this later!!
      $old_id = backup_todb($sub_info['#']['ID']['0']['#']);

      //Now, build the ASSIGNMENT_PARAGRAPHS record structure
      $paragraph->vizcoshid = $new_vizcosh_id;
      $paragraph->chapterid = $new_chapter_id;
      $paragraph->orderposition = backup_todb($sub_info['#']['ORDERPOSITION']['0']['#']);
      $paragraph->content = backup_todb($sub_info['#']['CONTENT']['0']['#']);

      //The structure is equal to the db, so insert the vizcosh_paragraphs
      $newid = insert_record ('vizcosh_paragraphs',$paragraph);

      //Do some output
      if (($i+1) % 50 == 0)
	{
	  if (!defined('RESTORE_SILENTLY'))
	    {
	      echo '.';
	      if (($i+1) % 1000 == 0) 
		echo '<br>';
	    }
	  backup_flush(300);
	}

      if ($newid)
	{
	  // We have the newid, update backup_ids
	  backup_putid ($restore->backup_unique_code,'vizcosh_paragraphs',
			$old_id, $newid);
							 
	  // TODO:
	  // mdl_vizcosh_bookmarks
	  // mdl_vizcosh_commentread
	  // mdl_vizcosh_comments
	  // mdl_vizcosh_markings
	  // mdl_vizcosh_questionmarks					 
	}
      else
	{
	  $status = false;
	}
    }
  return $status; 
}
	
//This function returns a log record with all the necessay transformations
//done. It's used by restore_log_module() to restore modules log.
function vizcosh_restore_logs($restore,$log)
{
  $status = false;

  //Depending of the action, we recode different things
  switch ($log->action)
    {
    case "update":

    case "view": //TO DO ... verify!!!
      if ($log->cmid)
	{
	  //Get the new_id of the chapter (to recode the url field)
	  $ch = backup_getid ($restore->backup_unique_code,
			      'vizcosh_chapters', $log->info);
	  //todo
	  if ($pag)
	    {
	      $log->url = "view.php?id=".$log->cmid."&chapterid=".$ch->new_id;
	      $log->info = $ch->new_id;
	      $status = true;
	    }
	}
    break;

    case "view all":
      if ($log->cmid)
	{
	  //Get the new_id of the module (to recode the info field)
	  $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
	  if ($mod)
	    {
	      $log->url = "view.php?id=".$log->cmid;
	      $log->info = $mod->new_id;
	      $status = true;
	    }
	}
      break;

    case "export":
      if ($log->cmid)
	{
	  //Get the new_id of the module (to recode the info field)
	  $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
	  if ($mod)
	    {
	      $log->url = "export.php?id=".$log->cmid;
	      $log->info = $mod->new_id;
	      $status = true;
	    }
	}
      break;

    case "print":
      if ($log->cmid)
	{
	  //Get the new_id of the module (to recode the info field)
	  $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
	  if ($mod)
	    {
	      $log->url = "print.php?id=".$log->cmid;
	      $log->info = $mod->new_id;
	      $status = true;
	    }
	}
      break;

    default:
      if (!defined('RESTORE_SILENTLY'))
	{
	  echo "action (".$log->module."-".$log->action.") unknown. Not restored<br>"; //Debug
	}
      break;
    }

  if ($status)
    {
      $status = $log;
    }
  return $status;
}

// Return a content decoded to support interactivities linking. Every
// module should have its own. They are called automatically from
// vizcosh_decode_content_links_caller() function in each module in
// the restore process

function vizcosh_decode_content_links ($content, $restore)
{
  global $CFG;

  $result = $content;

  $searchstring='/\$@(VIZCOSH_INDEX)\*([0-9]+)@\$/';
  preg_match_all ($searchstring, $content, $foundset);
  
  if ($foundset[0])
    {
      foreach($foundset[2] as $old_id)
	{
	  $rec = backup_getid($restore->backup_unique_code,
			      "course",
			      $old_id);
	  $searchstring='/\$@(VIZCOSH_INDEX)\*('.$old_id.')@\$/';
	  if($rec->new_id)
	    {
	      $result= preg_replace($searchstring,
				    $CFG->wwwroot.
				    '/mod/vizcosh/index.php?id='.$rec->new_id,
				    $result);
	    }
	  else
	    {
	      $result= preg_replace($searchstring,
				    $restore->original_wwwroot.
				    '/mod/vizcosh/index.php?id='.$old_id,
				    $result);
	    }
	}
    }

  $searchstring = '/\$@(VIZCOSH_CHAPTER)\*([0-9]+)\*([0-9]+)@\$/';
  preg_match_all ($searchstring, $result, $foundset);
  
  if ($foundset[0])
    {
      foreach($foundset[2] as $key => $old_id)
	{
	  $old_id2 = $foundset[3][$key];

	  $rec = backup_getid ($restore->backup_unique_code,
			       'course_modules', $old_id);
	  $rec2 = backup_getid ($restore->backup_unique_code,
				'vizcosh_chapters', $old_id2);
	  $searchstring='/\$@(VIZCOSH_CHAPTER)\*('.$old_id.')\*('.$old_id2.')@\$/';
	  
	  if($rec->new_id && $rec2->new_id)
	    {
	      $result= preg_replace ($searchstring,
				     $CFG->wwwroot.'/mod/vizcosh/view.php?id='.
				     $rec->new_id.
				     '&chapterid='.$rec2->new_id,
				     $result);
	    }
	  else
	    {
	      $result = preg_replace ($searchstring,
				      $restore->original_wwwroot.
				      '/mod/vizcosh/view.php?id='.$old_id.
				      '&chapterid='.$old_id2,
				      $result);
	    }
	}
    }

  $searchstring='/\$@(VIZCOSH_START)\*([0-9]+)@\$/';
  preg_match_all($searchstring,$result,$foundset);
  if ($foundset[0])
    {
      foreach($foundset[2] as $old_id)
	{
	  $rec = backup_getid ($restore->backup_unique_code,
			       'course_modules',
			       $old_id);
	  $searchstring='/\$@(VIZCOSH_START)\*('.$old_id.')@\$/';
	  if($rec->new_id)
	    {
	      $result = preg_replace ($searchstring,
				      $CFG->wwwroot.
				      '/mod/vizcosh/view.php?id='.$rec->new_id,
				      $result);
	    }
	  else
	    {
	      $result = preg_replace ($searchstring,
				      $restore->original_wwwroot.
				      '/mod/vizcosh/view.php?id='.$old_id,
				      $result);
	    }
	}
    }

  $searchstring = '/\$@(VIZCOSH_JNLP)\*([0-9]+)@\$/';
  preg_match_all ($searchstring, $result, $foundset);
  if ($foundset[0])
    {
      foreach($foundset[2] as $old_id)
	{
	  $rec = backup_getid ($restore->backup_unique_code,
			       'vizcosh_vizalgos',
			       $old_id);
	  $searchstring='/\$@(VIZCOSH_JNLP)\*('.$old_id.')@\$/';
	  if($rec->new_id)
	    {
	      $result = preg_replace ($searchstring,
				      'dl_jnlp.php?id='.$rec->new_id,
				      $result);
	    }
	}
    }

  
  $searchstring = '/\$@(VIZCOSH_THUMB)\*([0-9]+)@\$/';
  preg_match_all ($searchstring, $result, $foundset);
  if ($foundset[0])
    {
      foreach($foundset[2] as $old_id)
	{
	  $rec = backup_getid ($restore->backup_unique_code,
			       'vizcosh_vizalgos',
			       $old_id);
	  $searchstring='/\$@(VIZCOSH_THUMB)\*('.$old_id.')@\$/';
	  if($rec->new_id)
	    {
	      $result = preg_replace ($searchstring,
				      'dl_thumb.php?id='.$rec->new_id,
				      $result);
	    }
	}
    }
  
  return $result;
}

// This function makes all the necessary calls to
// xxxx_decode_content_links() function in each module, passing them
// the desired contents to be decoded from backup format to
// destination site/course in order to mantain inter-activities
// working in the backup/restore process. It's called from
// restore_decode_content_links() function in restore process

function vizcosh_decode_content_links_caller ($restore)
{
  global $CFG;
  $status = true;

  if ($vizcoshs = get_records_sql ("SELECT b.id, b.summary
                                   FROM {$CFG->prefix}vizcosh b
                                   WHERE b.course = $restore->course_id"))
    {
      foreach ($vizcoshs as $vizcosh)
	{
	  $content = $vizcosh->summary;
	  $result = restore_decode_content_links_worker($content,$restore);

	  if ($result != $content)
	    {
	      $vizcosh->summary = addslashes($result);
	      $status = update_record('vizcosh',$vizcosh);
	    } 
	}
    }
  
  if ($paragraphs = get_records_sql ("SELECT ch.id, ch.content
                                   FROM {$CFG->prefix}vizcosh b,
                                        {$CFG->prefix}vizcosh_paragraphs ch
                                   WHERE b.course = $restore->course_id AND
                                         ch.vizcoshid = b.id"))
    {
      foreach ($paragraphs as $paragraph)
	{
	  $content = $paragraph->content;
	  $result = restore_decode_content_links_worker($content,$restore);
	  if ($result != $content)
	    {
	      $paragraph->content = addslashes($result);
	      $status = update_record('vizcosh_paragraphs', $paragraph);
	    }
	  
	  backup_flush(300);
	}
    }

  return $status;
}

?>
