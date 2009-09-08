<?php
//$Id: upgrade.php,v 1.4 2007/09/24 19:15:39 stronk7 Exp $
//$Id: version.php,v 2.1 2007/12/16 12:07:00 vellaramkalayil Exp $
//This file keeps track of upgrades to 
//the assignment module
//
//Sometimes, changes between versions involve
//alterations to database structures and other
//major things that may break installations.
//
//The upgrade function in this file will attempt
//to perform all the necessary actions to upgrade
//your older installtion to the current version.
//
//If there's something it cannot do itself, it
//will tell you what you need to do.
//
//The commands in here will all be database-neutral,
//using the functions defined in lib/ddllib.php

function xmldb_vizcosh_upgrade($oldversion = 0) {
    global $CFG, $THEME, $db;
    $result = true;
    /// And upgrade begins here. For each one, you'll need one 
    /// block of code similar to the next one. Please, delete 
    /// this comment lines once this file start handling proper
    /// upgrade code.
    if ($result && $oldversion < 2007052001) {
        /// Changing type of field importsrc on table vizcosh_chapters to char
        $table = new XMLDBTable('vizcosh_chapters');
        $field = new XMLDBField('importsrc');
        $field->setAttributes(XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null, 'timemodified');
        /// Launch change of type for field importsrc
        $result = $result && change_field_type($table, $field);
    }
    if ($result && $oldversion < 2007113001) {
        /// Define table vizcosh_vizalgos to be created
        $table = new XMLDBTable('vizcosh_vizalgos');
        /// Adding fields to table vizcosh_vizalgos
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('title', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('description', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('author', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('date', XMLDB_TYPE_INTEGER, '8', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('data', XMLDB_TYPE_BINARY, 'big', null, null, null, null, null, null);
        $table->addFieldInfo('format', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('thumbnail', XMLDB_TYPE_BINARY, 'small', null, null, null, null, null, null);
        $table->addFieldInfo('topics', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null);
        /// Adding keys to table vizcosh_vizalgos
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array (
            'id'
        ));
        /// Launch create table for vizcosh_vizalgos
        $result = $result && create_table($table);
    }
    if ($result && $oldversion < 2007121600) {
        /// Define field jnlp to be added to vizcosh_vizalgos
        $table = new XMLDBTable('vizcosh_vizalgos');
        $field = new XMLDBField('jnlp');
        $field->setAttributes(XMLDB_TYPE_BINARY, 'medium', null, null, null, null, null, null, 'topics');
        /// Launch add field jnlp
        $result = $result && add_field($table, $field);
    }
    if ($result && $oldversion < 2007121601) {
/// Define field fnthumbnail to be added to vizcosh_vizalgos
        $table = new XMLDBTable('vizcosh_vizalgos');
        $field = new XMLDBField('fnthumbnail');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null, 'thumbnail');

    /// Launch add field fnthumbnail
        $result = $result && add_field($table, $field);
        
            /// Define table vizcosh_vizalgo_formats to be created
        $table = new XMLDBTable('vizcosh_vizalgo_formats');

    /// Adding fields to table vizcosh_vizalgo_formats
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('name', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('extension', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table vizcosh_vizalgo_formats
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Launch create table for vizcosh_vizalgo_formats
        $result = $result && create_table($table);
        
    }
    if ($result && $oldversion < 2007121603) {

    /// Define field fndata to be added to vizcosh_vizalgos
        $table = new XMLDBTable('vizcosh_vizalgos');
        $field = new XMLDBField('fndata');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null, null, null, 'data');

    /// Launch add field fndata
        $result = $result && add_field($table, $field);
    }
    if ($result && $oldversion < 2007121604) {

    /// Changing precision of field thumbnail on table vizcosh_vizalgos to (big)
        $table = new XMLDBTable('vizcosh_vizalgos');
        $field = new XMLDBField('thumbnail');
        $field->setAttributes(XMLDB_TYPE_BINARY, 'big', null, null, null, null, null, null, 'format');

    /// Launch change of precision for field thumbnail
        $result = $result && change_field_precision($table, $field);
    }
    
    if ($result && $oldversion < 2007121700) {

    /// Define field author to be added to vizcosh_vizalgo_formats
        $table = new XMLDBTable('vizcosh_vizalgo_formats');
        $field = new XMLDBField('author');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null, 'extension');

    /// Launch add field author
        $result = $result && add_field($table, $field);
        
    /// Define field date to be added to vizcosh_vizalgo_formats
        $table = new XMLDBTable('vizcosh_vizalgo_formats');
        $field = new XMLDBField('date');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '8', XMLDB_UNSIGNED, null, null, null, null, null, 'author');

    /// Launch add field date
        $result = $result && add_field($table, $field);
    }
    if ($result && $oldversion < 2007122201) {
        
    /// Define field jnlp_template to be added to vizcosh_vizalgo_formats
        $table = new XMLDBTable('vizcosh_vizalgo_formats');
        $field = new XMLDBField('jnlp_template');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'big', null, XMLDB_NOTNULL, null, null, null, null, 'date');

    /// Launch add field jnlp_template
        $result = $result && add_field($table, $field);
    }
    if ($result && $oldversion < 2008011500) {

    /// Changing nullability of field fndata on table vizcosh_vizalgos to null
        $table = new XMLDBTable('vizcosh_vizalgos');
        $field = new XMLDBField('fndata');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null, 'data');

    /// Launch change of nullability for field fndata
        $result = $result && change_field_notnull($table, $field);
         
        $field = new XMLDBField('jnlp');
        $field->setAttributes(XMLDB_TYPE_BINARY, 'medium', null, XMLDB_NOTNULL, null, null, null, null, 'topics');

    /// Launch change of nullability for field jnlp
        $result = $result && change_field_notnull($table, $field);
    } 
    if ($result && $oldversion < 2008011501) {

    /// Changing nullability of field extension on table vizcosh_vizalgo_formats to null
        $table = new XMLDBTable('vizcosh_vizalgo_formats');
        $field = new XMLDBField('extension');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'small', null, null, null, null, null, null, 'name');

    /// Launch change of nullability for field extension
        $result = $result && change_field_notnull($table, $field);
        
       /// Changing nullability of field author on table vizcosh_vizalgo_formats to not null
        $field = new XMLDBField('author');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null, null, null, 'extension');

    /// Launch change of nullability for field author
        $result = $result && change_field_notnull($table, $field);
        
        /// Changing nullability of field date on table vizcosh_vizalgo_formats to not null
        $field = new XMLDBField('date');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '8', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null, 'author');

    /// Launch change of nullability for field date
        $result = $result && change_field_notnull($table, $field);
    }
      
    
         
    return $result;
}
?>
