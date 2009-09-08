<?php
/*
 * DB-functions for marker.class.php
 * @author Andreas Kothe
 */

/**
 * returns the paragraph  content
 */
function get_paragraph($chapter_id, $paragraph_id) {
	return get_field('vizcosh_chapters', 'content', 'id', $chapter_id);
}

/**
 * returns markers that exist so far in the database
 * if no user_id is provided, it returns all markers
 * if no paragraph_id is provided, it returns all paragraphs
 */
function get_markers($chapter_id, $paragraph_id = -1, $user_id = -1) {
	global $cm;
		
	if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
		error('Course module is incorrect');
	}	
	
	$author_string = "";
	$paragraph_string = "";
	
	if ($user_id != -1) {
		$author_string .= " AND author = $user_id";
	}	
	
	if ($paragraph_id != -1) {
		$paragraph_string .= " AND paragraphid = $paragraph_id";
	}
	
	$markers = get_records_select('vizcosh_markings', "vizcoshid = ".$vizcosh->id." AND chapter = $chapter_id" . $paragraph_string . $author_string);

	// convert the object returned by Moodle's DB-funktioncs to an associative array 
	// (class Marker needs this array instead of an object)
	$assoc_array = array();
	if (isset($markers) and $markers != 0) {
		foreach ($markers as $marker_object) {
			$obj2array = get_object_vars($marker_object);
			$key = $obj2array['id'];
			$assoc_array[$key] = $obj2array;
		}
	}
	return $assoc_array;	
}


/**
 * inserts a marked text to the database
 */
function insert_marker($chapter_id, $paragraph_id, $text, $user_id) {
	global $cm;
		
	if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
		error('Course module is incorrect');
	}	

	$insertmarker = new stdClass;
	$insertmarker->chapter = $chapter_id;
	$insertmarker->vizcoshid = $vizcosh->id;
	$insertmarker->paragraphid = $paragraph_id;
	$insertmarker->marked_text = htmlspecialchars($text);
	$insertmarker->author = $user_id;
	
	return insert_record('vizcosh_markings', $insertmarker);
}

/**
 * deletes a marker entry from the database
 */
function delete_marker($id) {
	global $cm;
		
	if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
		error('Course module is incorrect');
	}	
	return delete_records('vizcosh_markings', 'vizcoshid = '.$vizcosh->id.' AND id', $id);
}
?>
