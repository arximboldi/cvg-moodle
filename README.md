CVG Moodle plugins
==================

This project contains several Moodle addons developed in the
[University of Granada](http://www.ugr.es/) to better fit our needs in
a installation at the [Computer Vision Group](http://cvg.ugr.es/).

All these plugins are
[Free Software](http://www.gnu.org/philosophy/free-sw.html) which
makes you free to use, modify and redistribute them. Check the
[license](#license) for more information. This software is probably
faulty. [Contact](#contact) us for any problems or suggestions
regarding this software.

![screenshot](http://sinusoid.es/screens/jsxaal.png)

Add-ons
-------

### enrol_idlist

This simple module makes sure that any student willing to enrole a
course is contained in the text file `$moodledata/idlist/$courseid`,
where `$moodledata` is your Moodle data folder and $courseid is the
numerical course identifier. You can use any attribute (even
customized attributes are allowed) to use as user identifier, and the
identifiers are extracted from the text file using a customizable
regular expression.

### block_idlist

This module requires `enrol_idlist` to be installed. It allows
teachers of a given course to edit the course "allowed students" list
within Moodle, providing some features such as checking which students
are enroled but not on the file an so on.

### assignment_downloader

When you have several groups and many assignments it is sometimes a
nuissance to download asignments one by one. Specially in Computer
Science education, assignments often have some automatic
pre-evaluation, such as scripts that test wether a program compiles
correctly. `assignment_downloader` allows you to download all
assignments in a group (or in all groups) in a single compressed
package, with a script-friendly structured file hierarchy.

### cvg-groupselect

This is a modified version of the
[groupselect](http://moodle.org/mod/data/view.php?d=13&rid=2206&filter=1)
Moodle extension, with added support to independently change the
maximun size of each group. If you are already using previous versions
of groupselect up to august 2009 you can upgrade to this flawlesly.

### cvg-vizcosh

This is a modified version of the `vizcosh` Moodle module, developed
in the Darmstadt University. This module allows to create hyper-books
with algorithm visualizations embedded in different formats. It also
adds many communicative features to the hyperbook, such as inline
per-paragraph comments, a virtual marker, etc. Our modifications add
JSXaal support among other things.

Download
--------

You can find all the module packages in the
[download area](https://savannah.nongnu.org/files/?group=cvg-moodle)

Development
-----------

We use `git` as our version control system for our development. You
can get the latest development package by typing:

> ```
> git clone git://git.savannah.nongnu.org/cvg-moodle.git
> ```

All the development tracking is on the Savannah project page. There is
also a Github mirror.

Contact
-------

The best way to ask for support or submit bugs its to use the
[Savannah project page](https://savannah.nongnu.org/projects/cvg-moodle/). Otherwise
you can also contact directly
[our project managers](https://savannah.nongnu.org/project/memberlist.php?group=cvg-moodle).

License
-------

All these plugins are
[Free Software](http://www.gnu.org/philosophy/free-sw.html), released
under the General Public License version 3. You are free and
encouraged to use, modify and redistribute this software. For the
precise legal terms under which you are allowed to use these rights
read the [full license text](http://www.gnu.org/licenses/gpl.html).
