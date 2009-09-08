<?php
/*
 * Created on 11.06.2007 by Anselmo Stelzer
 * Modified for Moodle by Andreas Kothe
 * Modified by Raphael Fetzer, not every function remains now functioning, should undergo intensiv testing/rewriting
 */
 
require_once('db_marker.php');

$emargoroot = $CFG->wwwroot . '/mod/vizcosh/emargo/';

/**
 * This class provides functions to set the "marker tags"
 * for eMargo's marker function
 */
class Marker {
    
    /** String to be tagged with marker tags */
    var $markedString;
    
    /** marker class for marker tags */
    var $markerClass;
    
    /** marker start tag */
    var $startTag;
    
    /** marker end tag */
    var $endTag   = '</span>';
    
    /** chapter id of the marked text */
    var $chapter_id;
    
    /** paragraph id of the marked text */
    var $paragraph_id;
    
    /** user_id of the current user, -1 for all users */
    var $user_id = -1;
    
    /** todo
     * Constructor takes a String that shall be tagged
     * and a optional marker class
     */
    function Marker( $markerClass = 'emargomarker' ) {
        $this->markerClass = $markerClass;
        $this->startTag = '<span style="cursor: pointer;" title="Klicken, um Optionen anzuzeigen" class="' . $markerClass . '">';		
    }
    
    /**
     * highlights marked text within a given text for a given user
     * 
     * @param String $text the text within the markers occur
     * @param int $chapter_id the chapter's id
     * @param int $par_id the paragraph's id
     * @param int $user_id the user's id
     */
    static function markTextForUser(&$paragraphsArray, $chapter_id, $user_id) {
        
		for($i=0; $i<count($paragraphsArray); $i++) {
			#get markings for the paragraph
			$markersDB = get_markers($chapter_id, $paragraphsArray[$i]->id, $user_id);

			// create new marker object
			$marker = new Marker();
			$marker->setChapterID($chapter_id);
			$marker->setParagraphID($paragraphsArray[$i]->id);
	
			#apply markings
			$paragraphsArray[$i]->content = $marker->markText($paragraphsArray[$i]->content, $markersDB);
			
			/* Todo
			* if $makersDB contains markers of all users
			* necessarity to join them first
			*/
			if ($user_id == -1) {
				$markersDB = $marker->rek_joinDBMarkers($markersDB);
				$marker->setMarkerClass('allmarker');
			}
	#        return $marker->markText($text, $markersDB);
		}
    }
    
    /**
     * marks all markers given in $markers in the given text ($text) for the
     * current paragraph and chapter
     * 
     * @param String $text the text within the markers appear
     * @param array $makers the markers that appear in the given text
     */
    function markText($text, $markers) {
      global $CFG, $emargoroot;
				
        foreach ($markers as $marker) {		
			#todo: zu langsam? 
			$marked_regex = "#" . str_replace(" ", "[ ]*?([\<][\/]?.*?[>])*?[ ]*?", preg_quote($marker['marked_text'])) . "#";
			
			#Sonst wird ...Neuen Medien</b>, und... nicht erkannt:
			#$marked_regex = str_replace(",", "[ ]*?([\<][\/]?.*?[>])*?[ ]*?,", $marked_regex);
			#$marked_regex = str_replace("\.", "[ ]*?([\<][\/]?.*?[>])*?[ ]*?\.", $marked_regex);
			#$marked_regex = str_replace(";", "[ ]*?([\<][\/]?.*?[>])*?[ ]*?;", $marked_regex);
			#$marked_regex = str_replace("-", "[ ]*?([\<][\/]?.*?[>])*?[ ]*?-", $marked_regex);
			
			# The  maximum  length of a compiled pattern is 65539
			if (strlen($marked_regex)<=65539){
				$search = array("\n", "\r\n", "\r");
				$text = str_replace($search, ' ', $text);	

				if (preg_match($marked_regex, $text, $regs))
					$text = str_replace($regs[0], '<span id="annotation' . $marker['id'] . '" class="emargomarker" onmousedown="showMarkerContextmenu(this.id)" title="Klicken, um Optionen anzuzeigen" style="cursor: url(' . $emargoroot . '/pix/buttons/eraser.ico), pointer;">'.$regs[0].'</span>', $text);
			}
			else
				$text .= '<p><span id="annotation' . $marker['id'] . '" class="emargomarker" onmousedown="showMarkerContextmenu(this.id)" title="Klicken, um Optionen anzuzeigen" style="border:1px solid red; text-size:1.2em; background-color:#DC6A39; cursor: url(' . $emargoroot . '/pix/buttons/eraser.ico), pointer;">Sie haben in diesem Kapitel eine Markierung vorgenommen, die zu gro&szlig; ist und  nicht verarbeitet werden kann. Zum L&ouml;schen dieser Markierung klicken Sie bitte auf diesen Hinweis.</span></p>';
		}
		
		return $text;

    /** Vorher		
		//initializing
        #$lastMarkersEndPos = 0;
       # $markedText = '';
        #$i = 0;

        //concate not marked and marked parts
 #       foreach ($markers as $marker) {
 #       	$markedText .= substr($text, $lastMarkersEndPos, ($marker['start_pos'] - $lastMarkersEndPos - 1));
 
 
            #$newStartTag = str_replace('class=', 'onmousedown="showMarkerContextmenu(this.id)" id="annotation' . $markerid . '" class=', $this->startTag);

            #$markedText .= $this->insertTags(substr($text, $marker['start_pos'] - 1, $marker['end_pos'] - $marker['start_pos'] + 1), $marker['id']);
 #           $lastMarkersEndPos = ($marker['end_pos']);
 #           $i++;
#        }
        
        //finalize: add unmarked rest
       # $markedText .= substr($text, $lastMarkersEndPos);
      
        #return $markedText;
		*/
    }
    
    /**
     * sets the marker tags within the string of the member
     * variable $markedString
     */
    function insertTags($text, $markerid) {

	/**	erstmal überflüssig 
        // regex for tags
        #$regex = "!(<[^>]+>|</[^>]+>)!U";
        
        // split text to insert marker tags
        $textArr = preg_split($regex, $text, -1, PREG_SPLIT_DELIM_CAPTURE);	

        $returnString = '';
        foreach ($textArr as $arrElem) {
            if (preg_match($regex,$arrElem)) {
                $returnString .= $arrElem;
            } else {
            	$newStartTag = str_replace('class=', 'onmousedown="showMarkerContextmenu(this.id)" id="annotation' . $markerid . '" class=', $this->startTag);
//            	$newStartTag = str_replace('class=', 'class=', $this->startTag);
              $returnString .= $newStartTag . $arrElem . $this->endTag;
            }
        }
                
        return $returnString;
	*/
    }
    
    /**
     * extracts positions of occurences of the marked text and returns it
     * comma separated (start,end). The return value is an array containing
     * one or more positions, depending on how often the marked text appears.
     * 
     * returns false if the marked text is not found in the paragraph
     */
    function extractPositions() {
      // Text in which markers occur
    	$text = get_paragraph($this->chapter_id, $this->paragraph_id);		
    	
    	$text = str_replace("\r", " ", $text);
    	$text = str_replace("\n", " ", $text);
    	// Leerzeichen kürzen
    	// nicht gebraucht!!  $text = preg_replace("/([ ]{2,})/", " ", $text);
    	
        // marked Text
    	$mText = stripslashes($this->markedString);
    	
        /* regular expression to match any html tag */
        $regEx = '(<[^>]+>|</[^>]+>)*';
        
        // start regex
        $regExMarkedString = '!' . $regEx;
        
        /* 
		* between each letter of the marked string the regex is inserted 
		*/
        $len = strlen($mText);
        for ($i = 0; $i < $len; $i++) {
			if (preg_match("/\n/",$mText[$i]) || preg_match("/\r/",$mText[$i])) { 
				$regExMarkedString .= '(\s*)';
        	} else if (preg_match("/\s/",$mText[$i])) { 
        		// other whitespace found
            	$regExMarkedString .= '(\s*)';
        	} else {
            	$regExMarkedString .= preg_quote(htmlspecialchars($mText[$i], ENT_COMPAT),'!');
            }
            $regExMarkedString .= $regEx;
        }

        //end regex
        $regExMarkedString .= '!U';
        
        // search for all occurances of the marked String in the text
        preg_match_all($regExMarkedString,$text,$foundPatterns);

        // remove double entries from $foundPatterns
        $foundMatches = array_unique($foundPatterns[0]);

		
        $inTagArrayText = array();
        $inTag = false;
        for($i=0; $i < strlen($text); $i++) {
        	if ($text[$i] == '<') {
        		$inTag = true;
        		$inTagArrayText[$i] = 1;
        	} else if ($text[$i] == '>') {
        		$inTag = false;
        		$inTagArrayText[$i] = 1;
        	} else if (!$inTag) {
        		$inTagArrayText[$i] = 0;
        	} else {
        		$inTagArrayText[$i] = 1;
        	}
        }
		
		// counter for array key
        $k = 0;
        $positions = array();
        foreach ($foundMatches as $match) {
        	// skip first html-tags (this is neccessary, do NOT delete!)
          $inTagArrayMatch = array();
	        $inTag = false;
	        for($i=0; $i < strlen($match); $i++) {
	        	if ($match[$i] == '<') {
	        		$inTag = true;
	        		$inTagArrayMatch[$i] = 1;
	        	} else if ($match[$i] == '>') {
	        		$inTag = false;
	        		$inTagArrayMatch[$i] = 1;
	        	} else if (!$inTag) {
	        		$inTagArrayMatch[$i] = 0;
	        	} else {
	        		$inTagArrayMatch[$i] = 1;
	        	}
	        }
	        $count = strlen($match);
			for ($i = 0; $i < $count; $i++) {
				if ($inTagArrayMatch[$i] == 1) {
					$match = substr($match, 1, strlen($match) - 1);               			
				} else if ($inTagArrayMatch[$i] == 0) {
					break;
				}
			}
			
        	/*
             * the marked text should be in the paragraph, thus an array of at least
             * two elements is created
             */
        	$arr = explode($match, $text);
        	if ($arr) {
            	$count = count($arr);
                $totalPos = 0;
                for ($i = 0; $i < $count - 1; $i++) {
                	$totalPos += strlen($arr[$i]);
	                $startPos = $totalPos + 1;
	                $endPos = $startPos + strlen($match) - 1;
	                if ($inTagArrayText[$totalPos] == 0) {
	                    $positions [$i]['start_pos']  = $startPos;
	                    $positions [$i]['end_pos']    = $endPos;
                	} 
                    $totalPos = $endPos;
                    $k++;
                }
            }
        }

        if (isset($positions))
            return $positions;
        else { //marked text not found in paragraph
            return false;
        }
    }
    
    /**
     * extracts the text for marker start and end positions.
     * used to extract the text after marker positions changed
     * due to a join.
     */
    function extractText($start, $ende) {
        $text = get_paragraph($this->chapter_id, $this->paragraph_id);
        return substr($text, $start - 1, $ende - $start + 1);
    }
    
    /**
     * inserts a new marker into the database
     * old markers are taken and joined, if necessary
     * @return boolean false if marking did work out, true otherwise
     */
    function insertMarker() {
	
		#Remove new lines from markedstring
		$search = array("\n", "\r\n", "\r");
		$this->markedString = str_replace($search, ' ', $this->markedString);
		#Remove double whitespaces
		$this->markedString = str_replace('  ', ' ', $this->markedString);
	
		insert_marker($this->chapter_id, $this->paragraph_id, $this->markedString, $this->user_id);

/**	
        $newPositions = $this->extractPositions();

        $currentMarkers = get_markers($this->chapter_id, $this->paragraph_id, $this->user_id);
        
        if ($newPositions && $currentMarkers)
            $newPositions = $this->rek_joinMarkers($newPositions, $currentMarkers);
        
        if (is_array($newPositions))    
            $this->insertPositions($newPositions);
	*/
    }
    
    /**
     * takes an array that contains new marker positions and checks each against each
     * from an array with existing positions if there is an overlapping. If yes, they
     * are joined and conflicting are removed.
     * 
     * @param array $newPositions contains the new marker positions
     * @param array $oldPositions contains the exisiting markers from the database
     */
    function rek_joinMarkers(array $newPositions, array $oldMarkers) { 
        foreach ($newPositions as $newID => $curNewPos) {
            foreach ($oldMarkers as $oldID => $curOldMarker) {

                $joinedPos = $this->joinMarker($curNewPos['start_pos'], $curNewPos['end_pos'],
                                        $curOldMarker['start_pos'], $curOldMarker['end_pos']);
                if ($joinedPos) {
                    //delete overlapping old marker
                    delete_marker($oldID);
                    unset($oldMarkers[$oldID]);
                    //remove overlapping new marker
                    unset($newPositions[$newID]);
                    //indert joined marker
                    array_push($newPositions, $joinedPos);
                    //restart joining procedure
                    return $this->rek_joinMarkers($newPositions, $oldMarkers);
                }
            }
        }
        return $newPositions;
    }
    
    /**
     * takes an array that contains marker positions of the database and checks each
     * against each other if there is an overlapping. If yes, they will be joined,
     * the joined marker will be added to the array and the conflicting ones removed
     * This function operates only on markers from the DB and is meant to be used for
     * showing all markers later on.
     * 
     * @param array $markers the markers of the database
     */
    function rek_joinDBMarkers(array $markers) { 
        foreach ($markers as $id1 => $marker1) {
            foreach ($markers as $id2 => $marker2) {

                if ($id1 != $id2) {
                    $joinedPos = $this->joinMarker($marker1['start_pos'], $marker1['end_pos'],
                                            $marker2['start_pos'], $marker2['end_pos']);
                    if ($joinedPos) {
                        //remove overlapping markers
                        unset($markers[$id1]);
                        //overwrite second overlapping marker by inserting joined marker
                        $markers[$id2] = $joinedPos;
                        //restart joining procedure
                        return $this->rek_joinDBMarkers($markers);
                    }
                }
            }
        }
        return $markers;
    }
    
  /**
     * inserts all given positions in an array
     * -todo update insert_marker(
     */
    function insertPositions(array $positions) {
        foreach ($positions as $position) {
            $markedString = $this->extractText($position['start_pos'], $position['end_pos']);
            insert_marker($this->chapter_id, $this->paragraph_id, $markedString,
                                $position['start_pos'], $position['end_pos'], $this->user_id);
        }
    }
    
    /**
     * Joins the position of two markers.
     * Returns a new comma separated position if the 
     * markers where overlapping, false if they were not.
     */
    function joinMarker($startA, $stopA, $startB, $stopB) {
        if ($stopB < $startA || $stopA < $startB)
            //keine Überlappung
            $return = false;
        else if ($startA <= $startB && $stopA >= $stopB) {
            //A umschliesst B --> A zurückgeben
            $return['start_pos'] = $startA;
            $return['end_pos'] = $stopA;
        }
        else if ($startB <= $startA && $stopB >= $stopA) {
            //B umschliesst A --> B zurückgeben
            $return['start_pos'] = $startB;
            $return['end_pos'] = $stopB;
        }
        else if ($startA <= $startB && $stopA <= $stopB) {
            //teilweise Ueberlappung, A vorne
            $return['start_pos'] = $startA;
            $return['end_pos'] = $stopB;
        }
        else if ($startB <= $startA && $stopB <= $stopA) {
            //teilweise Ueberlappung, B vorne
            $return['start_pos'] = $startB;
            $return['end_pos'] = $stopA;
        }

        return $return;
    }
    
    /**
     * deletes all markers which IDs appear in the provided array
     */
    static function deleteMarker(array $markerIDs) {
        foreach ($markerIDs as $markerID) {
            delete_marker($markerID);
        }
    }
    
    /**
     * returns true if the given paragraph of the given chapter contains a marker
     * of the given user, false otherwise
     */
    static function isParagraphMarkedByUser($chapter_id, $par_id, $user_id) {

        $markers = get_markers($chapter_id, $par_id, $user_id);

        if ($markers)
            return true;
        else
            return false;
    }
    
    /**
     * returns true if the given paragraph of the given chapter contains a marker
     * of the given user, false otherwise
     */
    static function isParagraphMarked($chapter_id, $par_id) {

        $markers = get_markers($chapter_id, $par_id, -1);

        if ($markers)
            return true;
        else
            return false;
    }
    
    /**
     * deletes all markers for the given paragraph of the given chapter marked by
     * the given user
     * @param int $chapter_id the chapter's id
     * @param int $par_id the paragraph's id
     * @param int $user_id the user's id
     */
    static function deleteMarkersOfChapter($chapter_id, $user_id) {
        
        $markers = get_markers($chapter_id, -1, $user_id);
 
		foreach ($markers as $id => $marker) {
            delete_marker($id);
        }
    }
    
    /**
     * reassigns the positions to all markers of the given paragraph in the given chapter
     * @param int $chapter_id the chapter's id
     * @param int $par_id the paragraph's id
     */
    static function reassignPositions($chapter_id, $par_id){
        
        $markers = get_markers($chapter_id, $par_id);
        
        foreach ($markers as $id => $marker) {
            //delete old marker
            delete_marker($id);
            
            //reinsert marker if possible
            $markObj = new Marker();
            $markObj->setChapterID($chapter_id);
            $markObj->setParagraphID($par_id);
            $markObj->setMarkedText($marker['marked_text']);
            $markObj->setUserID($marker['author']);
            $markObj->insertMarker();
        }
    }
    
    function setMarkedText($string) {
        $this->markedString = $string;
    }
    
    function setParagraphID($id) {
        $this->paragraph_id = $id;
    }
    
    function setChapterID($id) {
        $this->chapter_id = $id;
    }

    function setUserID($id) {
        $this->user_id = $id;
    }
    
    function setMarkerClass($class) {
        $this->markerClass = $class;
        $this->startTag = '<span class="' . $this->markerClass . '">';
    }
    
}
?>
