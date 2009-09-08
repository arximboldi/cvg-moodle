<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en" xml:lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="http://localhost/xampp/moodle/theme/standard/styles.php" />
<link rel="stylesheet" type="text/css" href="http://localhost/xampp/moodle/theme/standardwhite/styles.php" />
<style type="text/css">@import url(http://localhost/xampp/moodle/mod/vizcosh/vizcosh_theme.css);</style>
<!--[if IE 7]>
    <link rel="stylesheet" type="text/css" href="http://localhost/xampp/moodle/theme/standard/styles_ie7.css" />
<![endif]-->
<!--[if IE 6]>
    <link rel="stylesheet" type="text/css" href="http://localhost/xampp/moodle/theme/standard/styles_ie6.css" />
<![endif]-->


    <meta name="keywords" content="moodle, MeK: Mein erstes VizCoSH (Mein erstes VizCoSH-Kapitel) " />
    <title>MeK: Mein erstes VizCoSH (Mein erstes VizCoSH-Kapitel)</title>
    <link rel="shortcut icon" href="http://localhost/xampp/moodle/theme/standardwhite/favicon.ico" />
    <!--<style type="text/css">/*<![CDATA[*/ body{behavior:url(http://localhost/xampp/moodle/lib/csshover.htc);} /*]]>*/</style>-->

<script type="text/javascript" src="http://localhost/xampp/moodle/lib/javascript-static.js"></script>
<script type="text/javascript" src="http://localhost/xampp/moodle/lib/javascript-mod.php"></script>
<script type="text/javascript" src="http://localhost/xampp/moodle/lib/overlib.js"></script>
<script type="text/javascript" src="http://localhost/xampp/moodle/lib/cookies.js"></script>
<script type="text/javascript" src="http://localhost/xampp/moodle/lib/ufo.js"></script>

<script type="text/javascript" defer="defer">

//<![CDATA[

setTimeout('fix_column_widths()', 20);

function openpopup(url,name,options,fullscreen) {
  fullurl = "http://localhost/xampp/moodle" + url;
  windowobj = window.open(fullurl,name,options);
  if (fullscreen) {
     windowobj.moveTo(0,0);
     windowobj.resizeTo(screen.availWidth,screen.availHeight);
  }
  windowobj.focus();
  return false;
}

function uncheckall() {
  void(d=document);
  void(el=d.getElementsByTagName('INPUT'));
  for(i=0;i<el.length;i++) {
    void(el[i].checked=0);
  }
}

function checkall() {
  void(d=document);
  void(el=d.getElementsByTagName('INPUT'));
  for(i=0;i<el.length;i++) {
    void(el[i].checked=1);
  }
}

function inserttext(text) {
  text = ' ' + text + ' ';
  if ( opener.document.forms['theform'].message.createTextRange && opener.document.forms['theform'].message.caretPos) {
    var caretPos = opener.document.forms['theform'].message.caretPos;
    caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
  } else {
    opener.document.forms['theform'].message.value  += text;
  }
  opener.document.forms['theform'].message.focus();
}

//]]>
</script>
</head>

<body  class="mod-vizcosh course-2 nocoursepage editing lang-en_utf8" id="mod-vizcosh-view">
    
<div id="page">

    <div id="header" class="clearfix">
        <h1 class="headermain">Mein erster Kurs</h1>
        <div class="headermenu"><div class="navigation">
<ul><li>
<a title="All logs" onclick="this.target='_top';" href="http://localhost/xampp/moodle/course/report/log/index.php?chooselog=1&amp;user=0&amp;date=0&amp;id=2&amp;modid=77"><img class="icon log" src="http://localhost/xampp/moodle/pix/i/log.gif" alt="All logs" /></a>
</li><li>
<form action="http://localhost/xampp/moodle/mod/alviz/view.php" onclick="this.target='_top';">
<div>
<input type="hidden" name="id" value="76" />
<button type="submit" title="Previous activity"><span class="arrow ">&#x25C4;</span><span class="accesshide " >&nbsp;Previous activity</span>
</button>
</div>
</form>
</li>
<li><form action="http://localhost/xampp/moodle/course/jumpto.php" method="get"  id="navmenupopup" class="popupform"><div><select id="navmenupopup_jump" name="jump" onchange="self.location=document.getElementById('navmenupopup').jump.options[document.getElementById('navmenupopup').jump.selectedIndex].value;">
   <option value="http://localhost/xampp/moodle/mod/forum/view.php?id=1" style="background-image: url(http://localhost/xampp/moodle/mod/forum/icon.gif);">News forum</option>
   <option value="http://localhost/xampp/moodle/mod/alviz/view.php?id=76" style="background-image: url(http://localhost/xampp/moodle/mod/alviz/icon.gif);">Mein erstes AlViz</option>
   <option value="http://localhost/xampp/moodle/mod/vizcosh/view.php?id=77" selected="selected" style="background-image: url(http://localhost/xampp/moodle/mod/vizcosh/icon.gif);">Jump to...</option>
   <option value="http://localhost/xampp/moodle/mod/vizcosh/view.php?id=78" style="background-image: url(http://localhost/xampp/moodle/mod/vizcosh/icon.gif);">Mein zweites VizCoSH</option>
   <option value="http://localhost/xampp/moodle/mod/lesson/view.php?id=79" style="background-image: url(http://localhost/xampp/moodle/mod/lesson/icon.gif);">Meine erste Lektion</option>
   <optgroup label="Week 1">   <option value="http://localhost/xampp/moodle/mod/lesson/view.php?id=68" style="background-image: url(http://localhost/xampp/moodle/mod/lesson/icon.gif);">Meine erste Lektion</option>
   <option value="http://localhost/xampp/moodle/mod/quiz/view.php?id=69" style="background-image: url(http://localhost/xampp/moodle/mod/quiz/icon.gif);">Mein erstes Quiz</option>
    </optgroup></select><input type="hidden" name="sesskey" value="lraomqNU6Q" /><div id="noscriptnavmenupopup" style="display: inline;"><input type="submit" value="Go" /></div><script type="text/javascript">
//<![CDATA[
document.getElementById("noscriptnavmenupopup").style.display = "none";
//]]>
</script></div></form></li><li>
<form action="http://localhost/xampp/moodle/mod/vizcosh/view.php"  onclick="this.target='_top';">
<div>
<input type="hidden" name="id" value="78" />
<button type="submit" title="Next activity"><span class="accesshide " >Next activity&nbsp;</span><span class="arrow ">&#x25BA;</span>
</button>
</div>
</form>
</li>
</ul>
</div></div>
    </div>
    <div class="navbar clearfix">
        <div class="breadcrumb"><h2 class="accesshide " >You are here</h2><ul>
<li class="first">
<a  onclick="this.target='_top'" href="http://localhost/xampp/moodle/">moodlestart</a>
</li>
<li class="first"> <span class="accesshide " >/&nbsp;</span><span class="arrow sep">&#x25BA;</span> 
<a  onclick="this.target='_top'" href="../../course/view.php?id=2">MeK</a>
</li>
<li class="first"> <span class="accesshide " >/&nbsp;</span><span class="arrow sep">&#x25BA;</span> 
<a  onclick="this.target='_top'" href="index.php?id=2">VizCoSHs</a>
</li>
<li class="first"> <span class="accesshide " >/&nbsp;</span><span class="arrow sep">&#x25BA;</span>  Mein erstes VizCoSH</li>
</ul>
</div>
        <div class="navbutton"><table cellspacing="0" cellpadding="0"><tr><td><form  method="get" action="http://localhost/xampp/moodle/course/mod.php" onsubmit="this.target='_top'; return true"><div><input type="hidden" name="update" value="77" /><input type="hidden" name="return" value="true" /><input type="hidden" name="sesskey" value="lraomqNU6Q" /><input type="submit" value="Update this VizCoSH" /></div></form></td><td>&nbsp;</td><td><form method="get" action="http://localhost/xampp/moodle/mod/vizcosh/view.php"><div><input type="hidden" name="id" value="77" /><input type="hidden" name="chapterid" value="1" /><input type="hidden" name="edit" value="0" /><input type="submit" value="Turn editing off" /></div></form></td></tr></table></div>
    </div>   
    <div class="clearer">&nbsp;</div>
    <!-- END OF HEADER -->
    <div id="content">
<table border="0" cellspacing="0" width="100%" valign="top" cellpadding="2">

<!-- subchapter title and upper navigation row //-->
<tr>
    <td width="260" valign="bottom">
        Table of Contents<br/>(<a href="import.php?id=77">Import</a>)    </td>
    <td valign="top">
        <table border="0" cellspacing="0" width="100%" valign="top" cellpadding="0">
        <tr>
            <td nowrap="nowrap" align="left"><a title="Print Complete VizCoSH" href="print.php?id=77" onclick="this.target='_blank'"><img src="pix/print_vizcosh.gif" class="bigicon" alt="Print Complete VizCoSH"/></a><a title="Print This Chapter" href="print.php?id=77&amp;chapterid=1" onclick="this.target='_blank'"><img src="pix/print_chapter.gif" class="bigicon" alt="Print This Chapter"/></a><a title="[[generateimscp]]" href="generateimscp.php?id=77"><img class="bigicon" src="pix/generateimscp.gif" height="24" width="24" border="0"></img></a></td>
            <td nowrap ="nowrap" align="right"><img src="pix/nav_prev_dis.gif" class="bigicon" alt="" /><a title="Next" href="view.php?id=77&amp;chapterid=2"><img src="pix/nav_next.gif" class="bigicon" alt="Next" /></a></td>
        </tr>
        </table>
    </td>
</tr>

<!-- toc and chapter row //-->
<tr>
    <td width="260" valign="top" align="left">
        <div class="box generalbox"><div class="vizcosh_toc_none"><font size="-1"><ul><li><strong>Mein erstes VizCoSH-Kapitel</strong>&nbsp;&nbsp; <a title="Down" href="move.php?id=77&amp;chapterid=1&amp;up=0&amp;sesskey=lraomqNU6Q"><img src="http://localhost/xampp/moodle/pix/t/down.gif" height="11" class="iconsmall" alt="Down" /></a> <a title="Edit " href="edit.php?id=77&amp;chapterid=1"><img src="http://localhost/xampp/moodle/pix/t/edit.gif" height="11" class="iconsmall" alt="Edit " /></a> <a title="Delete" href="delete.php?id=77&amp;chapterid=1&amp;sesskey=lraomqNU6Q"><img src="http://localhost/xampp/moodle/pix/t/delete.gif" height="11" class="iconsmall" alt="Delete" /></a> <a title="Hide" href="show.php?id=77&amp;chapterid=1&amp;sesskey=lraomqNU6Q"><img src="http://localhost/xampp/moodle/pix/t/hide.gif" height="11" class="iconsmall" alt="Hide" /></a> <a title="Add new chapter" href="edit.php?id=77&amp;pagenum=1&amp;subchapter=0"><img src="pix/add.gif" height="11" class="iconsmall" alt="Add new chapter" /></a></li><li><a title="Nächstes Kapitel" href="view.php?id=77&amp;chapterid=2">Nächstes Kapitel</a>&nbsp;&nbsp; <a title="Up" href="move.php?id=77&amp;chapterid=2&amp;up=1&amp;sesskey=lraomqNU6Q"><img src="http://localhost/xampp/moodle/pix/t/up.gif" height="11" class="iconsmall" alt="Up" /></a> <a title="Edit " href="edit.php?id=77&amp;chapterid=2"><img src="http://localhost/xampp/moodle/pix/t/edit.gif" height="11" class="iconsmall" alt="Edit " /></a> <a title="Delete" href="delete.php?id=77&amp;chapterid=2&amp;sesskey=lraomqNU6Q"><img src="http://localhost/xampp/moodle/pix/t/delete.gif" height="11" class="iconsmall" alt="Delete" /></a> <a title="Hide" href="show.php?id=77&amp;chapterid=2&amp;sesskey=lraomqNU6Q"><img src="http://localhost/xampp/moodle/pix/t/hide.gif" height="11" class="iconsmall" alt="Hide" /></a> <a title="Add new chapter" href="edit.php?id=77&amp;pagenum=2&amp;subchapter=0"><img src="pix/add.gif" height="11" class="iconsmall" alt="Add new chapter" /></a></li></ul></font></div></div><font size="1"><br /><span class="helplink"><a title="Help with this (new window)" href="http://localhost/xampp/moodle/help.php?module=vizcosh&amp;file=faq.html&amp;forcelang=" onclick="this.target='popup'; return openpopup('/help.php?module=vizcosh&amp;file=faq.html&amp;forcelang=', 'popup', 'menubar=0,location=0,scrollbars,resizable,width=500,height=400', 0);">VizCoSH FAQ&nbsp;<img class="iconhelp" alt="Help with this" src="http://localhost/xampp/moodle/pix/help.gif" /></a></span></font>    </td>
    <td valign="top" align="right">
        <div class="box generalbox"><div class="vizcosh_content"><p class="vizcosh_chapter_title">Mein erstes VizCoSH-Kapitel</p><p>HHHHH</p></div></div><p><img src="pix/nav_prev_dis.gif" class="bigicon" alt="" /><a title="Next" href="view.php?id=77&amp;chapterid=2"><img src="pix/nav_next.gif" class="bigicon" alt="Next" /></a></p>    </td>
</tr>
</table>


</div> <!-- end div content -->

<div id="footer">
<hr />

<p class="helplink"><a href="http://docs.moodle.org/en/mod/vizcosh/view"><img class="iconhelp" src="http://localhost/xampp/moodle/pix/docs.gif" alt="" />Moodle Docs for this page</a></p>
<div class="logininfo">You are logged in as <a  href="http://localhost/xampp/moodle/user/view.php?id=2&amp;course=2">Teena Admin</a>: Teacher (<a 
                      href="http://localhost/xampp/moodle/course/view.php?id=2&amp;switchrole=0&amp;sesskey=lraomqNU6Q">Return to my normal role</a>)</div><div class="homelink"><a  href="http://localhost/xampp/moodle/course/view.php?id=2">MeK</a></div>

        <div class="validators"><ul>
          <li><a href="http://validator.w3.org/check?verbose=1&amp;ss=1&amp;uri=http%3A%2F%2Flocalhost%2Fxampp%2Fmoodle%2Fmod%2Fvizcosh%2Fview.php%3Fid%3D77">Validate HTML</a></li>
          <li><a href="http://www.contentquality.com/mynewtester/cynthia.exe?rptmode=-1&amp;url1=http%3A%2F%2Flocalhost%2Fxampp%2Fmoodle%2Fmod%2Fvizcosh%2Fview.php%3Fid%3D77">Section 508 Check</a></li>
          <li><a href="http://www.contentquality.com/mynewtester/cynthia.exe?rptmode=0&amp;warnp2n3e=1&amp;url1=http%3A%2F%2Flocalhost%2Fxampp%2Fmoodle%2Fmod%2Fvizcosh%2Fview.php%3Fid%3D77">WCAG 1 (2,3) Check</a></li>
        </ul></div>


</div>
</div>
</body>
</html>
