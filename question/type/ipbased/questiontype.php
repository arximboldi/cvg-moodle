<?php  // $Id: questiontype.php,v 1.12.2.9 2009/02/17 06:14:48 tjhunt Exp $
/**
 * Class for the ipbased question type.
 *
 * The ipbased question type does not have any options. When the question is
 * attempted, it picks a question at ipbased from the category it is in (and
 * optionally its subcategories). For details see create_session_and_responses.
 * Then all other method calls as delegated to that other question.
 *
 * @package questionbank
 * @subpackage questiontypes
 */
class ipbased_qtype extends default_questiontype {
    var $selectmanual;
    var $excludedqtypes = null;
    var $manualqtypes = null;

    function ipbased_qtype() {
        $this->selectmanual = get_config('qtype_ipbased', 'selectmanual');
    }

    // Caches questions available as ipbaseds sorted by category
    // This is a 2-d array. The first key is question category, and the
    // second is whether to include subcategories.
    var $catipbaseds = array();

    function name() {
        return 'ipbased';
    }

    function menu_name() {
        // Don't include this question type in the 'add new question' menu.
        return false;
    }

    function is_manual_graded() {
        return $this->selectmanual;
    }

    function is_question_manual_graded($question, $otherquestionsinuse) {
        if (!$this->selectmanual) {
            return false;
        }
        // We take our best shot at working whether a particular question is manually
        // graded follows: We look to see if any of the questions that this ipbased
        // question might select if of a manually graded type. If a category contains
        // a mixture of manual and non-manual questions, and if all the attempts so
        // far selected non-manual ones, this will give the wrong answer, but we
        // don't care. Even so, this is an expensive calculation!
        $this->init_qtype_lists();
        if (!$this->manualqtypes) {
            return false;
        }
        if ($question->questiontext) {
            $categorylist = question_categorylist($question->category);
        } else {
            $categorylist = $question->category;
        }
        return record_exists_select('question',
                "category IN ($categorylist)
                     AND parent = 0
                     AND hidden = 0
                     AND id NOT IN ($otherquestionsinuse)
                     AND qtype IN ($this->manualqtypes)");
    }

    function is_usable_by_random() {
        return false;
    }

    /**
     * This method needs to be called before the ->excludedqtypes and
     *      ->manualqtypes fields can be used.
     */
    function init_qtype_lists() {
        global $QTYPES;
        if (is_null($this->excludedqtypes)) {
            $excludedqtypes = array();
            $manualqtypes = array();
            foreach ($QTYPES as $qtype) {
                $quotedname = "'" . $qtype->name() . "'";
                if (!$qtype->is_usable_by_random()) {
                    $excludedqtypes[] = $quotedname;
                } else if ($this->selectmanual && $qtype->is_manual_graded()) {
                    $manualqtypes[] = $quotedname;
                }
            }
            $this->excludedqtypes = implode(',', $excludedqtypes);
            $this->manualqtypes = implode(',', $manualqtypes);
        }
    }

    function get_question_options(&$question) {
        // Don't do anything here, because the ipbased question has no options.
        // Everything is handled by the create- or restore_session_and_responses
        // functions.
        return true;
    }

    /**
     * Ipbased questions always get a question name that is Ipbased (cateogryname).
     * This function is a centralised place to calculate that, given the category.
     */
    function question_name($category) {
        return get_string('ipbased', 'quiz') .' ('. $category->name .')';
    }

    function save_question($question, $form, $course) {
        $form->name = '';
        // Name is not a required field for ipbased questions, but parent::save_question
        // Assumes that it is.

        $q =  parent::save_question($question, $form, $course);

        // Save the equation formulae.
        $qdata = new stdClass;
        $qdata->question = $q->id;
        $qdata->ipbasedeq = $question->ipbasedeq;
        insert_record ('question_ipbased', $qdata);
        
        return $q;
    }

    function save_question_options($question) {
        // No options, as such, but we set the parent field to the question's
        // own id. Setting the parent field has the effect of hiding this
        // question in various places.
        $updateobject = new stdClass;
        $updateobject->id = $question->id;
        $updateobject->parent = $question->id;

        // We also force the question name to be 'Ipbased (categoryname)'.
        if (!$category = get_record('question_categories', 'id', $question->category)) {
            error('Could retrieve question category');
        }
        $updateobject->name = addslashes($this->question_name($category));
        return update_record('question', $updateobject);
    }

    /**
     * Get all the usable questions from a particular question category.
     *
     * @param integer $categoryid the id of a question category.
     * @param boolean whether to include questions from subcategories.
     * @param string $questionsinuse comma-separated list of question ids to exclude from consideration.
     * @return array of question records.
     */
    function get_usable_questions_from_category($categoryid, $subcategories, $questionsinuse) {
        $this->init_qtype_lists();
        if ($subcategories) {
            $categorylist = question_categorylist($categoryid);
        } else {
            $categorylist = $categoryid;
        }
        if (!$catrandoms = get_records_select('question',
                "category IN ($categorylist)
                     AND parent = 0
                     AND hidden = 0
                     AND id NOT IN ($questionsinuse)
                     AND qtype NOT IN ($this->excludedqtypes)", '', 'id')) {
            $catrandoms = array();
        }
        return $catrandoms;
    }

    function create_session_and_responses(&$question, &$state, $cmoptions, $attempt) {
        global $QTYPES;
        // Choose a ipbased question from the category:
        // We need to make sure that no question is used more than once in the
        // quiz. Therfore the following need to be excluded:
        // 1. All questions that are explicitly assigned to the quiz
        // 2. All ipbased questions
        // 3. All questions that are already chosen by an other ipbased question
        // 4. Deleted questions
        
        if (!isset($cmoptions->questionsinuse)) {
            $cmoptions->questionsinuse = $attempt->layout;
        }

        if (!isset($this->catrandoms[$question->category][$question->questiontext])) {
            $catrandoms = $this->get_usable_questions_from_category($question->category,
                    $question->questiontext == "1", $cmoptions->questionsinuse);
            $this->catrandoms[$question->category][$question->questiontext] =
                swapshuffle_assoc($catrandoms);
        }

        $ipbasedeq = get_record ('question_ipbased', 'question', $question->id);
        if ($ipbasedeq)
            $ipbasedeq = $ipbasedeq->ipbasedeq;
        else
            echo "FIXME (ipbased qtype)<br/>";
        $total = count ($this->catrandoms[$question->category][$question->questiontext]);
        $ipbasedeq = str_replace ('$ip', '/*!*/', $ipbasedeq);
        if (preg_match('/[a-zA-Z]/', $ipbasedeq))
        {
            $question->questiontext = '<span class="notifyproblem">'.
                get_string('wrongforumula', 'ipbased'). '</span>';
            $question->qtype = 'description';
            $state->responses = array('' => '');
            return true;
        }
        
        $ipbasedeq = str_replace ('/*!*/', '$ip', $ipbasedeq);
        $ip = split('.', $_SERVER['REMOTE_ADDR']);
        $index = eval ('return ' . $ipbasedeq . ';') % $total;

        $wrappedquestion = array_values($this->catrandoms[$question->category][$question->questiontext]);
        $wrappedquestion = $wrappedquestion[$index];
        
        $wrappedquestion = get_record('question', 'id', $wrappedquestion->id);
        $QTYPES[$wrappedquestion->qtype]->get_question_options($wrappedquestion);
        $QTYPES[$wrappedquestion->qtype]->create_session_and_responses(
            $wrappedquestion, $state, $cmoptions, $attempt);
        $wrappedquestion->name_prefix = $question->name_prefix;
        $wrappedquestion->maxgrade    = $question->maxgrade;
        $cmoptions->questionsinuse .= ",$wrappedquestion->id";
        $state->options->question = &$wrappedquestion;
        return true;
        
    }

    function restore_session_and_responses(&$question, &$state) {
        /// The raw response records for ipbased questions come in two flavours:
        /// ---- 1 ----
        /// For responses stored by Moodle version 1.5 and later the answer
        /// field has the pattern ipbased#-* where the # part is the numeric
        /// question id of the actual question shown in the quiz attempt
        /// and * represents the student response to that actual question.
        /// ---- 2 ----
        /// For responses stored by older Moodle versions - the answer field is
        /// simply the question id of the actual question. The student response
        /// to the actual question is stored in a separate response record.
        /// -----------------------
        /// This means that prior to Moodle version 1.5, ipbased questions needed
        /// two response records for storing the response to a single question.
        /// From version 1.5 and later the question type ipbased works like all
        /// the other question types in that it now only needs one response
        /// record per question.
        global $QTYPES;
        if (!ereg('^ipbased([0-9]+)-(.*)$', $state->responses[''], $answerregs)) {
            if (empty($state->responses[''])) {
                // This is the case if there weren't enough questions available in the category.
                $question->questiontext = '<span class="notifyproblem">'.
                 get_string('toomanyipbased', 'quiz'). '</span>';
                $question->qtype = 'description';
                return true;
            }
            // this must be an old-style state which stores only the id for the wrapped question
            if (!$wrappedquestion = get_record('question', 'id', $state->responses[''])) {
                notify("Can not find wrapped question {$state->responses['']}");
            }
            // In the old model the actual response was stored in a separate entry in
            // the state table and fortunately there was only a single state per question
            if (!$state->responses[''] = get_field('question_states', 'answer', 'attempt', $state->attempt, 'question', $wrappedquestion->id)) {
                notify("Wrapped state missing");
            }
        } else {
            if (!$wrappedquestion = get_record('question', 'id', $answerregs[1])) {
                // The teacher must have deleted this question by mistake
                // Convert it into a description type question with an explanation to the student
                $wrappedquestion = clone($question);
                $wrappedquestion->id = $answerregs[1];
                $wrappedquestion->questiontext = get_string('questiondeleted', 'quiz');
                $wrappedquestion->qtype = 'missingtype';
            }
            $state->responses[''] = (false === $answerregs[2]) ? '' : $answerregs[2];
        }

        if (!$QTYPES[$wrappedquestion->qtype]
         ->get_question_options($wrappedquestion)) {
            return false;
        }

        if (!$QTYPES[$wrappedquestion->qtype]
         ->restore_session_and_responses($wrappedquestion, $state)) {
            return false;
        }
        $wrappedquestion->name_prefix = $question->name_prefix;
        $wrappedquestion->maxgrade    = $question->maxgrade;
        $state->options->question = &$wrappedquestion;
        return true;
    }

    function save_session_and_responses(&$question, &$state) {
        global $QTYPES;
        $wrappedquestion = &$state->options->question;

        // Trick the wrapped question into pretending to be the ipbased one.
        $realqid = $wrappedquestion->id;
        $wrappedquestion->id = $question->id;
        $QTYPES[$wrappedquestion->qtype]
         ->save_session_and_responses($wrappedquestion, $state);

        // Read what the wrapped question has just set the answer field to
        // (if anything)
        $response = get_field('question_states', 'answer', 'id', $state->id);
        if(false === $response) {
            return false;
        }

        // Prefix the answer field...
        $response = "ipbased$realqid-$response";

        // ... and save it again.
        if (!set_field('question_states', 'answer', addslashes($response), 'id', $state->id)) {
            return false;
        }

        // Restore the real id
        $wrappedquestion->id = $realqid;
        return true;
    }

    function get_correct_responses(&$question, &$state) {
        global $QTYPES;
        $wrappedquestion = &$state->options->question;
        return $QTYPES[$wrappedquestion->qtype]
         ->get_correct_responses($wrappedquestion, $state);
    }

    // ULPGC ecastro
    function get_all_responses(&$question, &$state){
        global $QTYPES;
        $wrappedquestion = &$state->options->question;
        return $QTYPES[$wrappedquestion->qtype]
         ->get_all_responses($wrappedquestion, $state);
    }

    // ULPGC ecastro
    function get_actual_response(&$question, &$state){
        global $QTYPES;
        $wrappedquestion = &$state->options->question;
        return $QTYPES[$wrappedquestion->qtype]
         ->get_actual_response($wrappedquestion, $state);
    }

    function get_html_head_contributions(&$question, &$state) {
        global $QTYPES;
        $wrappedquestion = &$state->options->question;
        return $QTYPES[$wrappedquestion->qtype]
                ->get_html_head_contributions($wrappedquestion, $state);
    }

    function print_question(&$question, &$state, &$number, $cmoptions, $options) {
        global $QTYPES;
        $wrappedquestion = &$state->options->question;
        $wrappedquestion->ipbasedquestionid = $question->id;
        $QTYPES[$wrappedquestion->qtype]
         ->print_question($wrappedquestion, $state, $number, $cmoptions, $options);
    }

    function grade_responses(&$question, &$state, $cmoptions) {
        global $QTYPES;
        $wrappedquestion = &$state->options->question;
        return $QTYPES[$wrappedquestion->qtype]
         ->grade_responses($wrappedquestion, $state, $cmoptions);
    }

    function get_texsource(&$question, &$state, $cmoptions, $type) {
        global $QTYPES;
        $wrappedquestion = &$state->options->question;
        return $QTYPES[$wrappedquestion->qtype]
         ->get_texsource($wrappedquestion, $state, $cmoptions, $type);
    }

    function compare_responses(&$question, $state, $teststate) {
        global $QTYPES;
        $wrappedquestion = &$teststate->options->question;
        return $QTYPES[$wrappedquestion->qtype]
         ->compare_responses($wrappedquestion, $state, $teststate);
    }

    function restore_recode_answer($state, $restore) {
        // The answer looks like 'ipbasedXX-ANSWER', where XX is
        // the id of the used question and ANSWER the actual
        // response to that question.
        // However, there may still be old-style states around,
        // which store the id of the wrapped question in the
        // state of the ipbased question and store the response
        // in a separate state for the wrapped question

        global $QTYPES;
        $answer_field = "";

        if (ereg('^ipbased([0-9]+)-(.*)$', $state->answer, $answerregs)) {
            // Recode the question id in $answerregs[1]
            // Get the question from backup_ids
            if(!$wrapped = backup_getid($restore->backup_unique_code,"question",$answerregs[1])) {
              echo 'Could not recode question in ipbased-'.$answerregs[1].'<br />';
              return($answer_field);
            }
            // Get the question type for recursion
            if (!$wrappedquestion->qtype = get_field('question', 'qtype', 'id', $wrapped->new_id)) {
              echo 'Could not get qtype while recoding question ipbased-'.$answerregs[1].'<br />';
              return($answer_field);
            }
            $newstate = $state;
            $newstate->question = $wrapped->new_id;
            $newstate->answer = $answerregs[2];
            $answer_field = 'ipbased'.$wrapped->new_id.'-';

            // Recode the answer field in $answerregs[2] depending on
            // the qtype of question with id $answerregs[1]
            $answer_field .= $QTYPES[$wrappedquestion->qtype]->restore_recode_answer($newstate, $restore);
        } else {
            // Handle old-style states
            $answer_link = backup_getid($restore->backup_unique_code,"question",$state->answer);
            if ($answer_link) {
                $answer_field = $answer_link->new_id;
            }
        }

        return $answer_field;
    }

}
//// END OF CLASS ////

//////////////////////////////////////////////////////////////////////////
//// INITIATION - Without this line the question type is not in use... ///
//////////////////////////////////////////////////////////////////////////
question_register_questiontype(new ipbased_qtype());

?>
