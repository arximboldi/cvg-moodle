<?PHP // $Id: backuplib.php,v 1.1 2006/03/12 18:39:59 skodak Exp $

  //This php script contains all the stuff to backup/restore
  //vizcosh mods

  //Todo
			
  //This is the 'graphical' structure of the vizcosh mod:
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

  //This function executes all the backup procedure about this mod

function vizcosh_backup_mods($bf,$preferences)
{
  global $CFG;		
		
  $status = true;

  ////Iterate over vizcosh table
  if ($vizcoshs = get_records ('vizcosh', 'course',
			       $preferences->backup_course, 'id'))
    {
      foreach ($vizcoshs as $vizcosh)
	{
	  if (backup_mod_selected($preferences,'vizcosh',$vizcosh->id))
	    {
	      $status = vizcosh_backup_one_mod($bf,$preferences,$vizcosh);
	    }
	}
    }
  return $status;
}

function vizcosh_backup_one_mod($bf,$preferences,$vizcosh)
{
  global $CFG;	

  if (is_numeric($vizcosh))
    $vizcosh = get_record('vizcosh','id',$vizcosh);

  $status = true;

  //Start mod
  fwrite ($bf,start_tag('MOD',3,true));
  //Print vizcosh data
  fwrite ($bf,full_tag('ID',4,false,$vizcosh->id));
  fwrite ($bf,full_tag('MODTYPE',4,false,'vizcosh'));
  fwrite ($bf,full_tag('NAME',4,false,$vizcosh->name));
  fwrite ($bf,full_tag('SUMMARY',4,false,$vizcosh->summary));
  fwrite ($bf,full_tag('NUMBERING',4,false,$vizcosh->numbering));
  fwrite ($bf,full_tag('DISABLEPRINTING',4,false,$vizcosh->disableprinting));
  fwrite ($bf,full_tag('DISABLEEMARGO',4,false,$vizcosh->disableemargo));
  fwrite ($bf,full_tag('ENABLEGROUPFUNCTION',4,false,$vizcosh->enablegroupfunction));
  fwrite ($bf,full_tag('CUSTOMTITLES',4,false,$vizcosh->customtitles));
  fwrite ($bf,full_tag('TIMECREATED',4,false,$vizcosh->timecreated));
		
  //back up the chapters
  $status = backup_vizcosh_chapters($bf,$preferences,$vizcosh);
		
  //End mod
  $status = fwrite($bf,end_tag('MOD',3,true));

  return $status;
}

//Backup vizcosh_chapters contents (executed from vizcosh_backup_mods)
function backup_vizcosh_chapters($bf,$preferences,$vizcosh) {
  global $CFG;

  $status = true;
		
  //Print vizcosh's chapters
  if ($chapters = get_records('vizcosh_chapters', 'vizcoshid', $vizcosh->id, 'id')) {
    //Write start tag
    $status =fwrite ($bf,start_tag('CHAPTERS',4,true));
    foreach ($chapters as $ch) {
      //Start chapter
      fwrite ($bf,start_tag('CHAPTER',5,true));
      //Print chapter data
      fwrite ($bf,full_tag('ID',6,false,$ch->id));
      fwrite ($bf,full_tag('VIZCOSHID',6,false,$vizcosh->id));
      fwrite ($bf,full_tag('PAGENUM',6,false,$ch->pagenum));
      fwrite ($bf,full_tag('TITLE',6,false,$ch->title));
      fwrite ($bf,full_tag('HIDDEN',6,false,$ch->hidden));
      fwrite ($bf,full_tag('TIMECREATED',6,false,$ch->timecreated));
      fwrite ($bf,full_tag('TIMEMODIFIED',6,false,$ch->timemodified));
      fwrite ($bf,full_tag('IMPORTSRC',6,false,$ch->importsrc));
      //End chapter

      $status = backup_vizcosh_paragraphs($bf,$preferences,$vizcosh,$ch);	

      $status = fwrite ($bf,end_tag('CHAPTER',5,true));
				
    }
			
    //Write end tag
    $status = fwrite ($bf,end_tag('CHAPTERS',4,true));
  }
  return $status;
}

//Backup vizcosh_paragraphs contents (executed from backup_vizcosh_chapters)
function backup_vizcosh_paragraphs($bf,$preferences,$vizcosh, $ch) {
  global $CFG;

  $status = true;
		
  //Print vizcosh's chapters
  if ($paragraphs = get_records_sql('SELECT * FROM '. $CFG->prefix.'vizcosh_paragraphs'.
				    ' WHERE vizcoshid = '.$vizcosh->id.
				    ' AND chapterid = '.$ch->id.
				    ' ORDER BY orderposition'))
    {		
    //Write start tag
    $status =fwrite ($bf,start_tag('PARAGRAPHS',6,true));
    foreach ($paragraphs as $paragraph) {
      //Start paragraph
      fwrite ($bf,start_tag('PARAGRAPH',7,true));
      //Print paragraph data
      fwrite ($bf,full_tag('ID',8,false,$paragraph->id));
      fwrite ($bf,full_tag('VIZCOSHID',8,false,$vizcosh->id));
      fwrite ($bf,full_tag('CHAPTERID',8,false,$paragraph->chapterid));
      fwrite ($bf,full_tag('ORDERPOSITION',8,false,$paragraph->orderposition));
      fwrite ($bf,full_tag('CONTENT',8,false,$paragraph->content));
      
#Todo
# Anzeigen mdl_vizcosh_bookmarks
# Anzeigen mdl_vizcosh_commentread
# Anzeigen mdl_vizcosh_comments
# Anzeigen mdl_vizcosh_markings
# Anzeigen mdl_vizcosh_questionmarks
				
      //End paragraph
      $status = fwrite ($bf,end_tag('PARAGRAPH',7,true));
    }

    //Write end tag
    $status = fwrite ($bf,end_tag('PARAGRAPHS',6,true));
  }
  return $status;
}
	
//Return a content encoded to support interactivities linking. Every module
//should have its own. They are called automatically from the backup procedure.
function vizcosh_encode_content_links ($content,$preferences) {

  global $CFG;

  $base = preg_quote($CFG->wwwroot,"/");

  $result = $content;

  //Link to the list of vizcoshs
  $buscar="/(".$base."\/mod\/vizcosh\/index.php\?id\=)([0-9]+)/";
  $result= preg_replace($buscar,'$@BOOKINDEX*$2@$',$result);

  //Link to vizcosh's specific chapter
  $buscar="/(".$base."\/mod\/vizcosh\/view.php\?id\=)([0-9]+)\&chapterid\=([0-9]+)/";
  $result= preg_replace($buscar,'$@BOOKCHAPTER*$2*$3@$',$result);

  //Link to vizcosh's first chapter
  $buscar="/(".$base."\/mod\/vizcosh\/view.php\?id\=)([0-9]+)/";
  $result= preg_replace($buscar,'$@BOOKSTART*$2@$',$result);

  return $result;
}

////Return an array of info (name,value)
function vizcosh_check_backup_mods($course,$user_data=false,$backup_unique_code,$instances=null) {

  if (!empty($instances) && is_array($instances) && count($instances)) {
    $info = array();
    foreach ($instances as $id => $instance) {
      $info += vizcosh_check_backup_mods_instances($instance,$backup_unique_code);
    }
    return $info;
  }

  //First the course data
  $info[0][0] = get_string('modulenameplural','vizcosh');
  $info[0][1] = count_records('vizcosh', 'course', $course);

  //No user data for vizcoshs ;-)

  return $info;
}

////Return an array of info (name,value)
function vizcosh_check_backup_mods_instances($instance,$backup_unique_code) {
  $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
  $info[$instance->id.'0'][1] = '';

  return $info;
}

?>
