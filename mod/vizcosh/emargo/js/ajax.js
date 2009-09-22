
/*
 * this javascript is responsible for sending all AJAX-requests to the server and 
 * for handling the AJAX-replies
 */

 var callbackFunctionName = "";
 var loadingImageDivId = "";

 /* debugging only:
var handleSuccess = function(o) {
	if(o.responseText !== undefined){
		contentDiv.innerHTML = "<li>Transaction id: " + o.tId + "</li>";
		contentDiv.innerHTML += "<li>HTTP status: " + o.status + "</li>";
		contentDiv.innerHTML += "<li>Status code message: " + o.statusText + "</li>";
		contentDiv.innerHTML += "<li>HTTP headers received: <ul>" + o.getAllResponseHeaders + "</ul></li>";
		contentDiv.innerHTML += "<li>Argument object: Array ([0] => " + o.argument[0] +
						 " [1] => " + o.argument[1] + " )</li>";
		contentDiv.innerHTML += "<li>PHP response: " + o.responseText + "</li>";
	}
	removeLoaderImage('ajaxloader', statusDivName);
};
*/

	/**
 * handles a successfull AJAX response (depending on "callbackFunctionName")
 */
 var handleSuccess = function(o) {
     switch (callbackFunctionName) {
     case 'loadMarkedText':
	 if(o.responseText !== undefined){
	     var responseObject = json_parse(o.responseText); 
	     contentDiv.innerHTML = stripSlashes(responseObject.content);
	     markingsHidden = false;
	     markingsOfOthersHidden = true;
	 }
	 if (commentBoxIsInitialized == false) {
	     initializeCommentbox();
	     commentBoxIsInitialized	= true;
	 } else {
	     initializeBubbles();
	 }
	 highlightParagraphBorder(currentParagraph);
	 loadJsxaalAnims ()
	 break;
     case 'loadUnmarkedText':
	 if(o.responseText !== undefined){
	     var responseObject = json_parse(o.responseText); 
	     contentDiv.innerHTML = stripSlashes(responseObject.content);
	     markingsHidden = true;
	     markingsOfOthersHidden = true;
	 }
	 initializeBubbles();
	 highlightParagraphBorder(currentParagraph);
	 loadJsxaalAnims ()
	 break;
     case 'loadJsxaalAnims':
	 if(o.responseText !== undefined){
	     var responseObject = json_parse(o.responseText); 
	     eval (stripSlashes(responseObject.content));
	 }
	 break;
     case 'deleteMarking':
	 hideMarkerContextmenu();
	 loadMarkedText();
	 break;
     case 'markParagraphRead':
     case 'markText':
     case 'deleteAllMarkings':
     case 'saveQuestionmark':
     case 'deleteQuestionmark':
     case 'saveBookmark':
     case 'deleteBookmark':
	 reloadTextDependingOnOptions();
	 break;
     case 'switchComments':
     case 'saveComment':
     case 'updateComment':
     case 'deleteComment':
	 commentBoxDiv = document.getElementById("comments");
	 //		alert("commentBoxDiv:" +commentBoxDiv +" / "+commentBoxDiv.value +", o:" +o +",=" +o.responseText);
	 if(o.responseText !== undefined){
	     var responseObject = json_parse(o.responseText); 
	     commentBoxDiv.innerHTML = stripSlashes(responseObject.content);
	 }
	 initializeCommentbox();
	 break;
     case 'getSingleComment':
	 var responseObject = json_parse(o.responseText); 
	 var comment = responseObject.content;
	 removeLoaderImage('ajaxloader', loadingImageDivId);
	 var	commentDiv = document.getElementById(loadingImageDivId);
	 commentDiv.innerHTML = '<strong>' + strEditComment + ':</strong><br /><div style="width: 100%;"><form><input type="hidden" id="editcomment_id" name="editcomment_id" value="' + comment.id + '" /><input style="width: 100%;" type="text" id="editcomment_subject" name="editcomment_subject" class="textarea" value="' + comment.subject + '" size="28" tabindex="1" /><br /><textarea style="width: 100%;" name="editcomment_message" id="editcomment_message" cols="30" rows="9" tabindex="4">' + comment.message + '</textarea></div><input name="makeeditingnotpublic" type="checkbox" id="makeeditingnotpublic" value="makeeditingnotpublic" />'+strMakeEditingNotPublic+'<br><input onclick="if(typeof(editComment) == \'function\') { editComment(); };" name="editcommentbutton" type="button" id="editcommentbutton" value="' + strSaveComment + '" /> <input onclick="if(typeof(cancelEditingComment) == \'function\') { cancelEditingComment(' + comment.id + '); };" name="canceleditingbutton" type="button" id="canceleditingbutton" value="' + strCancelEditing + '" /></form>';
	 break;
     default:
	 // do nothing (optional TODO: throw error message)
     }
     removeLoaderImage('ajaxloader', statusDivName);
 };


 /**
 * handles an unsuccessfull AJAX response
 */
 var handleFailure = function(o){
     if(o.responseText !== undefined){
	 statusDivName.innerHTML = "<li>Transaction id: " + o.tId + "</li>";
	 statusDivName.innerHTML += "<li>HTTP status: " + o.status + "</li>";
	 statusDivName.innerHTML += "<li>Status code message: " + o.statusText + "</li>";
     }
     removeLoaderImage('ajaxloader', loadingImageDivId);
 };


 /**
 * callback object for YUI AJAX requests
 */ 
 var callback = {
     success:handleSuccess,
     failure:handleFailure,
     argument:[callbackFunctionName,loadingImageDivId]
 };


 /**
 * sends a YUI AJAX-request to the server (using HTTP POST)
 */
 function makeRequest(postData, pLoadingImageDivId, loadingImageTextlabel, pCallbackFunctionName) {
     callbackFunctionName = pCallbackFunctionName;
     loadingImageDivId = pLoadingImageDivId;
     postData = postData + "&moduleName=" + moduleName + "&courseModuleId=" + courseModuleId + "&chapterid=" + chapterid;
     createLoaderImage('ajaxloader', loadingImageDivId, wwwroot, loadingImageTextlabel);
     //	alert("@makeRequest; ajaxURL: " +ajaxUrl +", postData: " + postData);
     var request = YAHOO.util.Connect.asyncRequest('POST', ajaxUrl, callback, postData);
 }

 /**
 * load unmarked text using AJAX
 */
 function loadUnmarkedText() {
     hideMarkerContextmenu();
     var postData = "ajaxObj=Marker&ajaxAction=loadUnmarkedText";
     makeRequest(postData, statusDivName, strLoadingChapterContent, 'loadUnmarkedText');
 }

 /**
 * load marked text for the current user using AJAX
 */
 function loadMarkedText() {
     hideMarkerContextmenu();
     var postData = "ajaxObj=Marker&ajaxAction=loadMarkedText";
     makeRequest(postData, statusDivName, strLoadingChapterContent, 'loadMarkedText');
 }

 /**
 * Load JSXAAL viewers for the current user.
 */
 function loadJsxaalAnims () {
     var postData = "ajaxObj=Marker&ajaxAction=loadJsxaalAnims";
     makeRequest(postData, statusDivName, strLoadingChapterContent, 'loadJsxaalAnims');
 }


 /**
 * helper function for handleSuccess (because this code is needed twice)
 */
 function reloadTextDependingOnOptions() {
     if (markingsHidden) {
	 loadUnmarkedText();
     } else {
	 loadMarkedText();
     }
 }

 /**
 * deletes all markings for the current user using AJAX
 */
 function deleteAllMarkings() {
     if (confirm(strDeleteMarkings)) {
	 var postData = "ajaxObj=Marker&ajaxAction=deleteAllMarkings";
	 makeRequest(postData, statusDivName, strLoadingChapterContent, 'deleteAllMarkings');
     }
 }

 /**
 * deletes a single marking using AJAX
 */
 function deleteMarking() {
     if (markingToDelete != 0) {
	 var postData = "ajaxObj=Marker&ajaxAction=deleteMarking&markingId=" + markingToDelete;
	 makeRequest(postData, statusDivName, strDeletingMarking, 'deleteMarking');
     } else {
	 alert("Test");
	 //TODO: Fehlerbehandlung
     }
 }

 /**
 * sends the text currently marked by the user to the server using AJAX
 */
 function markText(paragraphId) {
     if (Marker.isEnabled()) {
	 var selectedText = getSelectedText();
	 if (selectedText.length >= 5) {
	     var postData = "ajaxObj=Marker&ajaxAction=markText&paragraphId=" + paragraphId + "&markedText=" + encodeURIComponent(selectedText);
	     makeRequest(postData, statusDivName, strSavingMarking, 'markText');
	 }
	 else{
	     alert(strMarkingToShort);
	 }
     } else {
	 //		alert(strMarkertoolAlert);
     }
 }

 function markParagraphRead(paragraphId) {
     var postData = "ajaxObj=Commentbox&ajaxAction=markParagraphRead&paragraphId=" + paragraphId;
     makeRequest(postData, statusDivName, '', 'markParagraphRead');
 }

 function switchComments(paragraphId, switchstate) {
     var postData = "ajaxObj=Commentbox&ajaxAction=switchComments&paragraphId=" + paragraphId + "&switchstate=" + switchstate;
     commentBoxDiv = document.getElementById("comments");
     commentBoxDiv.innerHTML = "";
     makeRequest(postData, 'comments', "Wechsle Ansicht...", 'switchComments');
 }

 function saveComment(paragraphId, subject, message, parentId, messageType) {
     var postData = "ajaxObj=Commentbox&ajaxAction=saveComment&paragraphId=" + paragraphId + "&subject=" + subject + "&message=" + message + "&parentId=" + parentId + "&messageType=" + messageType;
     commentBoxDiv = document.getElementById("comments");
     commentBoxDiv.innerHTML = "";
     //	alert("saveComment:" + paragraphId +", subject=" +subject +", msg=" +message +", parentID=" +parentId +", msgType=" +messageType +", data: " +postData +", commentBoxDiv: " +commentBoxDiv);
     makeRequest(postData, 'comments', strSavingComment, 'saveComment');
 }

 function updateComment(commentId, subject, message, makenotpublic) {
     if (makenotpublic == true)
	 makenotpublic = 1;
     else
	 makenotpublic = 0;
     var postData = "ajaxObj=Commentbox&ajaxAction=updateComment&commentId=" + commentId + "&subject=" + subject + "&message=" + message + "&makenotpublic=" + makenotpublic;
     var commentContentDivId = "comment-content-" + commentId;
     var commentContentDiv = document.getElementById(commentContentDivId);
     makeRequest(postData, 'commentContentDivId', strSavingComment, 'updateComment');
 }

 function getSingleComment(commentId) {
     var postData = "ajaxObj=Commentbox&ajaxAction=getSingleComment&commentId=" + commentId;
     var commentContentDivId = "comment-content-" + commentId;
     var commentContentDiv = document.getElementById(commentContentDivId);
     commentContentDiv.innerHTML = "";
     makeRequest(postData, commentContentDivId, 'Loading...', 'getSingleComment');
 }

 function deleteComment(commentId) {
     var confirmation = confirm(strDeleteCommentConfirmation);
     if (confirmation) {
	 var postData = "ajaxObj=Commentbox&ajaxAction=deleteComment&commentId=" + commentId;
	 commentBoxDiv = document.getElementById("comments");
	 commentBoxDiv.innerHTML = "";
	 makeRequest(postData, 'comments', strDeletingComment, 'deleteComment');
     }
 }

 function saveBookmark(paragraphId) {
     var postData = "ajaxObj=Commentbox&ajaxAction=saveBookmark&paragraphId=" + paragraphId;
     makeRequest(postData, statusDivName, strSavingBookmark, 'saveBookmark');
 }

 function deleteBookmark(paragraphId) {
     var postData = "ajaxObj=Commentbox&ajaxAction=deleteBookmark&paragraphId=" + paragraphId;
     makeRequest(postData, statusDivName, strDeletingBookmark, 'deleteBookmark');
 }

 function saveQuestionmark(paragraphId) {
     var postData = "ajaxObj=Commentbox&ajaxAction=saveQuestionmark&paragraphId=" + paragraphId;
     makeRequest(postData, statusDivName, strSavingQuestionmark, 'saveQuestionmark');
 }

 function deleteQuestionmark(paragraphId) {
     var postData = "ajaxObj=Commentbox&ajaxAction=deleteQuestionmark&paragraphId=" + paragraphId;
     makeRequest(postData, statusDivName, strDeletingQuestionmark, 'deleteQuestionmark');
 }

 /**
 * toggles between "show marked text" and "show unmarked text" 
 * (if user has clicked the toggle-button)
 */
 function toggleMarkings() {
     if (markingsHidden == true) {
	 loadMarkedText();
     } else {
	 loadUnmarkedText();
     }
 }

 /**
 *	displays an animated loader image
 */
 var createLoaderImage = function (elClass, parentId, wwwRoot, textLabel) {
     var parentEl = document.getElementById(parentId);
     if (!parentEl) {
	 return false;
     }
     if (document.getElementById("loaderImg")) {
	 // a loader image already exists.
	 return false;
     }
     var loadingImg = document.createElement("img");

     loadingImg.setAttribute("src", wwwRoot+"/mod/vizcosh/emargo/pix/buttons/ajax-loader.gif");
     loadingImg.setAttribute("class", elClass);
     loadingImg.setAttribute("alt", "Loading");
     loadingImg.setAttribute("id", "loaderImg");
     parentEl.appendChild(loadingImg);

     var loadingText = document.createElement("div");
     loadingText.setAttribute("id", "loaderText");
     loadingText.setAttribute("style", "display:inline; padding-left:5px; font-size: .8em;");
     loadingText.innerHTML = textLabel;
     parentEl.appendChild(loadingText);

     return true;
 };


 /**
 *	removes animated loader image
 */
 var removeLoaderImage = function (elClass, parentId) {
     var parentEl = document.getElementById(parentId);
     if (parentEl) {
	 var loaderImg = document.getElementById("loaderImg");
	 var loaderText = document.getElementById("loaderText");
	 if (parentEl.hasChildNodes()) {
	     parentEl.removeChild(loaderImg);
	     parentEl.removeChild(loaderText);
	 }
     }
 };

 /**
 * removes all slashes from a string (exactly like in PHP)
 */ 
 function stripSlashes(str) {
     return (str+'').replace('/\0/g', '0').replace('/\(.)/g', '$1');
 }
