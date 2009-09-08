<?php

/**
 * This function does anything necessary to upgrade
 * older versions to match current functionality
 */
function vizcosh_upgrade($oldversion) {

	global $CFG;

	if ($oldversion < 2004060600) {
		execute_sql ("ALTER TABLE {$CFG->prefix}vizcosh
					CHANGE intro summary TEXT NOT NULL;
                     ");
	}
	if ($oldversion < 2004071100) {

		execute_sql ("ALTER TABLE {$CFG->prefix}vizcosh_chapters
					ADD importsrc VARCHAR(255);
                     ");
		execute_sql ("UPDATE {$CFG->prefix}vizcosh_chapters
					SET importsrc = '';
                     ");
		execute_sql ("ALTER TABLE {$CFG->prefix}vizcosh_chapters
					ALTER importsrc SET NOT NULL;
                     ");
		execute_sql ("ALTER TABLE {$CFG->prefix}vizcosh_chapters
					ALTER importsrc SET DEFAULT '';
                     ");
	}
	if ($oldversion < 2004071201) {
		execute_sql ("UPDATE {$CFG->prefix}log_display
					SET action = 'print'
					WHERE action = 'prINT';
                     ");
	}
	if ($oldversion < 2004081100) {
		execute_sql ("ALTER TABLE {$CFG->prefix}vizcosh
					ADD disableprinting INT2;
                     ");
		execute_sql ("UPDATE {$CFG->prefix}vizcosh
					SET disableprinting = '0';
                     ");
		execute_sql ("ALTER TABLE {$CFG->prefix}vizcosh
					ALTER disableprinting SET NOT NULL;
                     ");
		execute_sql ("ALTER TABLE {$CFG->prefix}vizcosh
					ALTER disableprinting SET DEFAULT '0';
                     ");
		execute_sql ("ALTER TABLE {$CFG->prefix}vizcosh
					ADD customtitles INT2;
                     ");
		execute_sql ("UPDATE {$CFG->prefix}vizcosh
					SET customtitles = '0';
                     ");
		execute_sql ("ALTER TABLE {$CFG->prefix}vizcosh
					ALTER customtitles SET NOT NULL;
                     ");
		execute_sql ("ALTER TABLE {$CFG->prefix}vizcosh
					ALTER customtitles SET DEFAULT '0';
                     ");
	}
	return true;
}

?>
