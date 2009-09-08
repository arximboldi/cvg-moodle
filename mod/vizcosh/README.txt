
///////////////////////////////////////////////////////////////////////////
//                                                                       //
//  Moodle-Modules for Interactive Lecture Notes and Interactive Slides  //
//                        with annotation support                        //
//                                                                       //
//                                                                       //
// based on old VizCoSH-module by Teena Vellaramkalayil                  //
// which is based on book-module by Petr Skoda (petr.skoda@vslib.cz)     //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

Created by:

- Andreas Kothe:
	- all annotation and collaboration features, VizCoSH-enhancements and interactive slides module
- Teena Vellaramkalayil:
	- all algorithm visualization features
-	Petr Skoda:
	- book module
- Mojmir Volf:
	- CSS formatted printer friendly format for book module


Developed for Technische Universität Darmstadt (Germany).
Many ideas and code were taken from the standalone eMargo-version. 
	-> visit www.emargo.de for more information
	
Installation:

1. Upgrade your Moodle installation to version 1.9.3 or later

2. Download the zipped file, unzip it and upload all files to your moodle 
   directory (note: not only the /mod-folder, but also /lib and /lang). If you 
   have installed other languages than english (e.g. german), you also have to 
   copy the german language files to the folder where you installed the german 
   language ("de_utf") - most probably this is your "moodledata"-folder.  

3. Go to http://yoursite.com/admin and click on "Notifications" - all necessary 
   tables will be created

4. Now go to "Modules", then to "Activities" in the "Site Administration"-block
   and you should find that two new modules have been added to the list of recognized 
   modules.
   
5. Change all options under "Modules -> Activities -> VizCoSH" and "Modules -> 
   Activities -> Interactive Slides" to fit your needs.
