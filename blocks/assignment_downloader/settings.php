<?php

/**
 *  Time-stamp:  <2009-07-20 01:08:01 raskolnikov>
 *
 *  @file        settings.php
 *  @author      Juan Pedro Bolívar Puente <raskolnikov@es.gnu.org>
 *  @date        Fri Jul 17 17:36:53 2009
 *
 *  Configuracion del modulo block_assignment_downloader.
 */

/*
 *  Copyright (C) 2009 Juan Pedro Bolívar Puente
 *  
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

$settings->add (new admin_setting_configtext ('block_assignment_downloader_user_attribute',
					      get_string ('cfg_user_attribute', 'block_assignment_downloader'),
					      get_string ('cfg_user_attribute_desc', 'block_assignment_downloader'),
					      'dni',
					      PARAM_ALPHANUM));

$settings->add (new admin_setting_configtext ('block_assignment_downloader_group_attribute',
					      get_string ('cfg_group_attribute', 'block_assignment_downloader'),
					      get_string ('cfg_group_attribute_desc', 'block_assignment_downloader'),
					      'id',
					      PARAM_ALPHANUM));

$settings->add (new admin_setting_configtext ('block_assignment_downloader_assignment_attribute',
					      get_string ('cfg_assignment_attribute', 'block_assignment_downloader'),
					      get_string ('cfg_assignment_attribute_desc', 'block_assignment_downloader'),
					      'id',
					      PARAM_ALPHANUM));
?>
