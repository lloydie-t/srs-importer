# SRS Importer

Moodle Student Record System (SRS) Importer

The SRS Importer Moodle tool allows you to import users from the Student Record System
, using the SRS API to get the records of newly added or updated students.
The records can be automatically imported using Moodle cron or manually imported via the SRS Importer Tools

## Installing via uploaded ZIP file

1. Download a zip copy of the plugin from https://github.com/lloydie-t/srs-importer/archive/refs/heads/main.zip
2. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
3. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
4. Check the plugin validation report and finish the installation.

## Installing manually

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/admin/tool/srs_import

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## Installing from Git repository

The plugin can also being installed by cloning into the tools respository using the following command

    $ git clone lloydiet/srsimport {your/moodle/dirroot}/admin/tool/srs_import

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

## Setup

The following settings are required for the SRS importer tool, that are requested on installation

1. Endpoint - The SRS API endpoint is required to collect the student data
2. Endpoint Key - The endpoint key is required to authorise the connection to the SRS API endpoint

## Usage

Got to Site Administration > Plugins > Admin Tools > SRS Importer

There are two tabs for this setting.

1. Settings to edit the SRS API endpoint parameters
2. SRS Imports - to view the last 10 imports and a button to manaually run an SRS import.

## License

2025 Lloyd Thomas lloydie.t@gmail.com

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program. If not, see <https://www.gnu.org/licenses/>.
