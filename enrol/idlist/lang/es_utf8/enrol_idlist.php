<?php

/**
 *  Time-stamp:  <2009-07-20 11:42:10 raskolnikov>
 *
 *  @file        enrol_idlist.php
 *  @author      Juan Pedro Bolívar Puente <raskolnikov@es.gnu.org>
 *  @date        Thu Jul  2 12:53:10 2009
 *
 *  SPANISH UTF-8 translation strings for enrol_idlist.
 */

/*
 *  Copyright (C) 2009 Juan Pedro Bolívar Puente
 *
 *  This file is part of cvg-moodle.
 *   
 *  cvg-moodle is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  cvg-moodle is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

$string['description'] = 'Con este método de matriculación la matriculación de los alumnos es manual pero se comprueba contra una lista en texto plano de identificadores -por ejemplo DNI.';
$string['enrolname'] = 'Matriculación por lista de ID.';
$string['enrol_idlist_idattr'] = 'Atributo del estudiante a usar como ID. Puede ser un campo personalizado.';
$string['enrol_idlist_path'] = 'Carpeta dónde estarán las listas de IDs. Si la ruta no es absoluta se asume relativa al directorio de datos de Moodle.';
$string['enrol_idlist_regexp'] = 'Expresión regular con la que se extraerán los identificadores del fichero.';
$string['enrolment_id_error'] = 'Parece que no eres un miembro válido de este curso. Por favor contacta a tu profesor en caso de que seas un participante legítimo de este curso.';
$string['enrolment_msg'] = 'Ahora vas a matricularte en el curso. Tenga en cuenta que debe estar inscrito en la lista de participantes legítimos de este curso. Contacte a su profesor en caso de tener problemas con la matriculación.';
$string['enrol_idlist_strict_check_p'] = 'Si se deshabilita el ID del usuario se filtrará con la expresión regular antes de ser comprobado.';
$string['enrol_idlist_strict_hint'] = 'Error que se mostrará al usuario cuando con strict_check no se corresponda el ID del usuario con el ID del usuario filtrado por la expresión regular.';
$string['enrol_idlist_default_strict_hint'] = 'Tu DNI no es válido. Sólo se permiten caracteres numéricos en el DNI.';

?>
