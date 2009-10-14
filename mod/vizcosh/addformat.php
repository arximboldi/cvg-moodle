<!--$Id: addvis.php,v 1.4 2008/02/03 22:32:00 vellaramkalayil Exp $ -->
<!-- List Algorithm Visualization Formats from DB
- Fill a table listing all the algorithm visualization formats
- Print this table
-->
<?PHP
require_once ('../../config.php');
require_once ('lib.php');
// =========================================================================
// security checks START - only teachers add visualizations
// =========================================================================
require_login();
//use session variable "temp_edit_form" which should have been set from the chapter editing page (edit.php)
if (isset ($_SESSION['temp_edit_form'])) {
    $temp = $_SESSION['temp_edit_form'];
    $id = $temp->id; // Course Module ID
    $chapterid = $temp->chapterid; // Chapter ID
} else {
    error('Session variable error.');
}
if (!$cm = get_coursemodule_from_id('vizcosh', $id)) {
    error('Course Module ID was incorrect');
}
if (!$course = get_record('course', 'id', $cm->course)) {
    error('Course is misconfigured');
}
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('moodle/course:manageactivities', $context);
if (!$vizcosh = get_record('vizcosh', 'id', $cm->instance)) {
    error('Course module is incorrect');
}
// =========================================================================
// security checks END
// =========================================================================
// =========================================================================
// List Algorithm Visualization Formats available in DB
// =========================================================================
//create tabs: only one for listing the available vizalgo formats
$tabs = $row = $inactive = $activated = array ();
$row[] = new tabobject('list', "addeditor.php?id=$cm->id", get_string('listtab', 'vizcosh'), get_string('listtab', 'vizcosh'));
$tabs[] = $row;
//query database for all vizalgo formats
$vizalgosformats = get_records('vizcosh_vizalgo_formats');
//fill a table with the formats received from the database
$table->head = array (
    get_string('formatname', 'vizcosh'),
    get_string('formatextension', 'vizcosh'),
    get_string('formatauthor', 'vizcosh'),
    get_string('formatdate', 'vizcosh'),
    "<a class='formatediting_new' title='".get_string('new')."' href='editorformat.php?formatid=-1&modus=new'><img src='pix/add.gif' class='iconbig'  alt='".get_string ('new')."' /></a>");
$table->align = array (
    'left',
    'left',
    'center',
    'center',
    'center',
    'center'
);
if ($vizalgosformats) {
    $i = 0;
    foreach ($vizalgosformats as $vizformat) {
        $options = array (
            'id' => $cm->id,
            'selected_vizalgo' => $vizformat->id
        );
        //if current user is author of a format she is also allowed to update or delete the vizalgo
        //->button for updating and deleting is added to this particular format entry in the table
        if ($vizformat->author == $USER->firstname . " " . $USER->lastname) {
            $formatdata[$i] = array (
                $vizformat->name,
                $vizformat->extension,
                $vizformat->author,
                $vizformat->date,
                "<a class='editingformat_update' title='Update' href='editorformat.php?formatid=" . $vizformat->id . "&modus=edit'><img src='../..//pix/t/edit.gif' class='iconbig'  alt='Update' /></a>" . " " . "<a class='editingformat_delete' title='Delete' href='editorformat.php?formatid=" . $vizformat->id . "&modus=delete'><img src='../../pix/t/delete.gif' class='iconbig'  alt='Delete' /></a>"
            );
        } else {
            $formatdata[$i] = array (
                $vizformat->name,
                $vizformat->extension,
                $vizformat->author,
                $vizformat->date,
                ""
            );
        }
        $i = $i +1;
    }
}
if (isset ($formatdata))
    $table->data = $formatdata;
//Print the page with its tab and algorithm visualization formats table
//prepare the page header
$strvizcosh = get_string('modulename', 'vizcosh');
$strvizcoshs = get_string('modulenameplural', 'vizcosh');
$stredit = get_string('editchapter', 'vizcosh');
$strlist = get_string('addvis', 'vizcosh');
$streditor = get_string('editoralvis', 'vizcosh');
$strformatadd = get_string('addformat', 'vizcosh');
$pageheading = get_string('listalvisformats', 'vizcosh');
if ($course->category) {
    $navigation = '<a href="../../course/view.php?id=' . $course->id . '">' . $course->shortname . '</a> ->';
} else {
    $navigation = '';
}
$editorvislink = $streditor;
if (isset ($_SESSION['editor_vizalgoid']) && isset ($_SESSION['editor_modus'])) {
    $tempvizalgoid = $_SESSION['editor_vizalgoid'];
    $tempmodus = $_SESSION['editor_modus'];
    $editorvislink = "<a href=\"editorvis.php?vizalgo=" . $tempvizalgoid . '&modus=' . $tempmodus . "\">" . $streditor . "</a>";
}
print_header("$course->shortname: $vizcosh->name", $course->fullname, "<a href=\"index.php?id=$course->id\">$strvizcoshs</a> -> 
                <a href=\"view.php?id=$cm->id\">$vizcosh->name</a> -> 
                <a href=\"edit.php?id=$cm->id&chapterid=$chapterid\">$stredit</a> ->
                <a href=\"addvis.php?tab=list\">$strlist</a> ->
                $editorvislink ->
                $strformatadd", '', '', true, '', '');
print_heading_with_help($pageheading, 'addformathelp', 'vizcosh');
print_simple_box_start('center', '');
print_tabs($tabs, 'list', $inactive, $activated);    
print_table($table);
print_simple_box_end();
print_footer($course);
?>
