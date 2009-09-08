/*
 * this file was part of the "Commentpress" wordpress plugin and has 
 * been modified for Moodle an AJAX use
 * @see http://www.futureofthebook.org/commentpress/
 */

var commentContentBackup;
var editFormIsVisible = false;

 function collapseThread( theId ) {
	 var comment = document.getElementById(theId);
	 if(!comment)
	 {
		 alert("ERROR:\nThe document structure is different\nfrom what Threaded Comments expects.\nYou are missing the element '"+theId+"'");
		 return;
	 }
	 var theBody = findBody(comment);
	 if(comment.className.indexOf("collapsed") > -1) {
		 comment.className = comment.className.replace(" collapsed", "");;
	 } else {
		 comment.className += " collapsed";
	 }
 }

 function expandThread( theId ) {
	 var comment = document.getElementById(theId);
	 if(!comment)
	 {
		 alert("ERROR:\nThe document structure is different\nfrom what Threaded Comments expects.\nYou are missing the element '"+theId+"'");
		 return;
	 }
	 var theBody = findBody(comment);
	 if(comment.className.indexOf("collapsed") > -1) {
		 comment.className = comment.className.replace(" collapsed", "");;
	 } 
 }
 
 function findBody(el)
 {
	 var divs = el.getElementsByTagName("div");
	 var ret;
	 for(var i = 0; i < divs.length; ++i) {
		 if(divs.item(i).className.indexOf("body") > -1)
			 return divs.item(i);
	 }
	 return false;
 }

 function showEditForm(commentId) {
	var commentContentDiv = document.getElementById("comment-content-" + commentId);
	if (!editFormIsVisible) {
		commentContentBackup = commentContentDiv.innerHTML;
		getSingleComment(commentId); // @see:ajax.js
	} 
	editFormIsVisible = true;
 }

 function editComment() {
	var commentId = document.getElementById("editcomment_id");
	var subject = document.getElementById("editcomment_subject");
	var message = document.getElementById("editcomment_message");
	updateComment(commentId.value, subject.value, message.value, document.getElementById("makeeditingnotpublic").checked); // @see:ajax.js
	editFormIsVisible = false;
 }
 
 function cancelEditingComment(commentId) {
	var	commentDiv = document.getElementById("comment-content-" + commentId);
	if (commentContentBackup != '') {
		commentDiv.innerHTML = commentContentBackup;
	}
	editFormIsVisible = false;
 }

 function SwitchCommentFilter(switchState) {
	 var paragraph = document.getElementById("comment_contentIndex");

	 switchComments(paragraph.value, switchState); // @see ajax.js
 } 
 
  function onAddPrivateComment() {
	 //checkDocumentIntegrity();
 
	 var paragraph = document.getElementById("comment_contentIndex");
	 var subject = document.getElementById("comment_subject");
	 var message = document.getElementById("comment_message");
	 var parentId = document.getElementById("comment_parent");
   
   if (message.value == "") {
   	alert(strPleaseFillInAllRequiredFields);
   } else {
	saveComment(paragraph.value, subject.value, message.value, parentId.value, 1); // @see ajax.js
   }

	 // old (non-AJAX) code:
	 //var el = document.getElementById("commentform");
	 //el.submit();
 }

 function onAddComment() {
	 //checkDocumentIntegrity();
	 var paragraph = document.getElementById("comment_contentIndex");
	 var subject = document.getElementById("comment_subject");
	 var message = document.getElementById("comment_message");
	 var parentId = document.getElementById("comment_parent");
   if (message.value == "") {
   	alert(strPleaseFillInAllRequiredFields);
   } else {
	saveComment(paragraph.value, subject.value, message.value, parentId.value, 0); // @see ajax.js
   }
 }
 
 function moveAddCommentBelow(theId, threadId, collapse, titleText)
 {
	 expandThread( theId );
	 var addComment = document.getElementById("addcomment");
	 if(!addComment)
	 {
			 alert("ERROR:\nThreaded Comments can't find the 'addcomment' div.\nThis is probably because you have changed\nthe comments.php file.\nMake sure there is a tag around the form\nthat has the id 'addcomment'"); 
		 return
	 }
	 var comment = document.getElementById(theId);
	 if(collapse)
	 {
		 for(var i = 0; i < comment.childNodes.length; ++i) {
			 var c = comment.childNodes.item(i);
			 if(typeof(c.className) == "string" && c.className.indexOf("collapsed")<0) {
				 c.className += " collapsed";
			 }
		 }
	 }
	 addComment.parentNode.removeChild(addComment);

	 comment.appendChild(addComment);
	 if(comment.className.indexOf("alt")>-1) {
		 addComment.className = addComment.className.replace(" alt", "");					
	 } else {
		 addComment.className += " alt";
	 }
	 var replyId = document.getElementById("comment_reply_ID");
	 if(replyId == null)
	 {
		 alert("Brians Threaded Comments Error:\nThere is no hidden form field called\n'comment_reply_ID'. This is probably because you\nchanged the comments.php file and forgot\nto include the field. Please take a look\nat the original comments.php and copy the\nform field over.");
	 }
	 replyId.value = threadId;
	 document.getElementById("comment_parent").value = threadId;
	 //alert(document.getElementById("comment_parent").value);
	 var reRootElement = document.getElementById("reroot");
	 if(reRootElement == null)
	 {
		 alert("Brians Threaded Comments Error:\nThere is no anchor tag called 'reroot' where\nthe comment form starts.\nPlease compare your comments.php to the original\ncomments.php and copy the reroot anchor tag over.");
	 }
	 reRootElement.style.display = "block";
	 var aTags = comment.getElementsByTagName("A");
	 var anc = aTags.item(0).id;

	 var subjectElement = document.getElementById("comment_subject");
	 if(titleText.substr(0,3) == "AW:") {
		subjectElement.value = titleText;
	 }
	 else{
		subjectElement.value = "AW: " + titleText;
	}
	
	//Scroll to comment window
	//document.getElementById("addcommentbutton").focus();
	document.getElementById("comment_message").focus();
 }

 function checkDocumentIntegrity()
 {
	 str = "";
	 
	 str += checkElement("reroot","div tag");
	 str += checkElement("addcomment", "div tag");
	 str += checkElement("comment_reply_ID", "hidden form field");
	 str += checkElement("cbcontent", "div tag");
	 str += checkElement("comment", "textfield");
	 str += checkElement("addcommentanchor", "anchor tag");
	 
	 if(str != "")
	 {
		 str = "Brian's Threaded Comments are missing some of the elements that are required for it to function correctly.\nThis is probably the because you have changed the original comments.php that was included with the plugin.\n\nThese are the errors:\n" + str;
		 str += "\nYou should compare your comments.php with the original comments.php and make sure the required elements have not been removed.";

		 alert(str);
	 }
 }
						
 function checkElement(theId, elDesc)
 {
	 var el = document.getElementById(theId);
	 if(!el)
	 {
		 if(elDesc == null)
			 elDesc = "element";
		 return "- The "+elDesc+" with the ID '" +theId + "' is missing\n"; 
	 }
	 else 
		 return "";
 }
 
 function reRoot()
 {
	 var addComment = document.getElementById("addcomment");			
	 var reRootElement = document.getElementById("reroot");
	 if (reRootElement != null)
  	   reRootElement.style.display = "none";
	 var content = document.getElementById("base_comment_box");
         if (addComment != null) {
  	   addComment.parentNode.removeChild(addComment);
	   content.appendChild(addComment);
	   addComment.className = addComment.className.replace(" odd", "");
	 }
	 //document.location.hash = "#addcommentanchor";
	 //document.getElementById("comment").focus();				
	 var replyID = document.getElementById("comment_reply_ID");
	 if (replyID != null)
	     replyID.value = "0";
 	 var subjectElement = document.getElementById("comment_subject");
	 if (subjectElement != null)
	     subjectElement.value = '';
 }

 function changeCommentSize(d)
 {
	 var el = document.getElementById("comment");
	 var height = parseInt(el.style.height);
	 if(!height && el.offsetHeight)
		 height = el.offsetHeight;
	 height += d;
	 if(height < 20) 
		 height = 20;
	 el.style.height = height+"px";
 }		
 
