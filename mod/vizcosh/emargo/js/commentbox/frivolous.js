/*
 * this file was part of the "Commentpress" wordpress plugin and has 
 * been modified slightly
 * @see http://www.futureofthebook.org/commentpress/
 */

 jQuery.noConflict ()

// when page loads succesfully
jQuery(document).ready(function(){ 
//	initializeCommentbox();
});

/**
 * when they click the read bubble, we flip through the comments and hide all the 
 * ones that are not connected to the paragraph.. unhighlight all the paragraphs
 * and highlight just the selected one.
 *
 * id attribute is "para-nr_id-nr" where para-nr is the position in the chapter
 * and id-nr is the internal id of the paragraph
 */
function initializeBubbles() {
  jQuery(".paragraph_read").click(function(){
  
  //Todo In Funktion auslagern
   var parameterarray = jQuery(this).attr('id').split("_");
   var paragraph = parseInt(parameterarray[0].substr(5));	 
   var id = parseInt(parameterarray[1].substr(3));	 
//   SwitchCommentFilter(1);

   paragraph_read_event(paragraph, id);	 
   jQuery('.cp_goToText').hide();
  });
}

//function SplitParagraphAndID(IdString) {
 //  var parameterarray = IDString.split("_");
 //  var paragraph = parseInt(parameterarray[0].substr(5));	 
 //  var id = parseInt(parameterarray[1].substr(3));	 
   //return
 // });
//}

function highlightParagraphBorder(paragraphId) {
	if (paragraphId >= 0) {
		if (jQuery('.cbcontent')[paragraphId] !== undefined) {
			jQuery('.cbcontent')[paragraphId].style.background = '#F5F5F5';
			jQuery('.cbcontent')[paragraphId].style.border = '1px dotted #D0D0D0';
		}
	}
}


function initializeCommentbox() {
  //show all the comments
  jQuery("#cp_newComment").hide(); 	//alert of new comment
  jQuery("#no_comments_yet").hide();	//this is used only when there are no comments in a paragraph

  jQuery("#comment_index").show();		//DELETE: we hide comment area and determine wether it should be shown by cookie below
  jQuery("#comment_help").hide();		//this should only appear on first load. determine by cookie request below
  jQuery("#comment_read").hide();		//we hide comment area and determine wether it should be shown by cookie below

  //is this the first time we are loading the page? let's add a cookie to hide the help.
  /*
  if(readCookie('commentpress_hide_help')){
   jQuery("#comment_index").show();
   jQuery("#comment_help").hide();	 
   jQuery("#comment_read").hide();	 
  }
  else{
   jQuery("#comment_read").hide();	 
   jQuery("#comment_index").hide(); 	
   jQuery("#comment_help").show(); 
  }
  */

  //TODO: resize comment 	area
  //var height = client_coords().height - 40;
  //jQuery("#cp_comments").css("height", height + "px")


  
  var emptyBox = true;
  var clickingLock = false;
  //var active_paragraph = readCookie(cookie_name()); //remmebers which para you were reading last
  var active_paragraph;
  var active_paragraphID;
  
  /**
   * are we returning from the comment?
   */

  if(return_comment()){
   //id=contentblock_id, title=par   <- orderposition
   active_paragraphID = parseInt(get_hash());
   // gets the paragraph number from the title element 
   if(document.getElementById("contentblock_"+active_paragraphID)!=null){
		active_paragraph = parseInt(document.getElementById("contentblock_"+active_paragraphID).title);
	}
	else{
		delete(active_paragraph);
	}
  }

  /** 
   * if a paragraph is selected by the time the page loads, either by 
   * a cookie that saved their last position, or a #location URL 
   * marking the paragraph, then we highlight the paragraph and show
   * its comments
   */
  if(active_paragraph){
	paragraph_read_event(active_paragraph, active_paragraphID);
	window.location.hash = '#' + active_paragraph;
	window.scrollBy(0, -110);	   
	jQuery('.cp_goToText').hide();
	jQuery(".author_on_paragraph").hide();		 
  }

	initializeBubbles();

  jQuery(".paragraph_read_index").click(function(){
 
   var parameterarray = jQuery(this).attr('id').split("_");
   var paragraph = parseInt(parameterarray[0].substr(5));	 
   var id = parseInt(parameterarray[1].substr(3));	 

   paragraph_read_event(paragraph, id);	   
   jQuery('.cp_goToText').hide();
  });
  

	  
  /**
   * 
   */
  jQuery("#page_comments_action").click(function(){		
   jQuery("#comment_reply").hide();
   jQuery("#comment_read").show();
   paragraph_read_event(-1);		
  });


  /**
   * 	SHOW HELP when they click on the HELP link
   */
  jQuery("#show_help_action").click(function(){		
   jQuery("#show_all_comments_action").text(strOverview);
   jQuery("#comment_list_heading").text('');

   jQuery(".cbcontent").each(function(i){
	this.style.background = '#fff';
	this.style.border = '1px solid #fff';
   });
   
   jQuery("#comment_contentIndex").val(-2);	   
   jQuery("#comment_index").hide();
   jQuery("#comment_read").hide();
   jQuery("#comment_help").show();	   
  });

  /**
  * 	SHOW ALL COMMENTS when they click on the 'overview' link
  */
  jQuery("#show_all_comments_action").click(function(){		
   if(jQuery("#comment_contentIndex").val() != -1){
	jQuery("#show_all_comments_action").text(strHelp);
	jQuery("#comment_list_heading").text('');
	jQuery("#comment_index").show();
	jQuery("#comment_read").hide();
	jQuery("#comment_help").hide();
	jQuery("#comment_contentIndex").val(-1);
	jQuery('.cp_goToText').show();
	jQuery(".author_on_paragraph").hide();

	if(jQuery("#comment_total_count").val() > 0){
	 jQuery("#no_comments_yet").show();
	}
	else{
	 jQuery("#no_comments_yet").hide();
	}
	
	jQuery(".cbcontent").each(function(i){
	 this.style.background = '#fff';
	 this.style.border = '1px solid #fff';
	});

	jQuery(".cp_commentBody").each(function(i){			 
			this.style.display = 'block';
	});
   }
   else{
	jQuery("#show_all_comments_action").text(strOverview);
	jQuery("#comment_list_heading").text('');

	jQuery(".cbcontent").each(function(i){
	 this.style.background = '#fff';
	 this.style.border = '1px solid #fff';
	});
	
	jQuery("#comment_contentIndex").val(-2);	   
	jQuery("#comment_index").hide();
	jQuery("#comment_read").hide();
	jQuery("#comment_help").show();	   
   
   
   }
   /*
   else {
	jQuery("#show_all_comments_action").text('collapse all [--]');
	jQuery("#active_paragraph").text('for all paragraphs');
	jQuery("#comment_read").show();
	jQuery("#comment_index").hide();
	jQuery("#comment_help").hide();		
	jQuery("#addcomment").hide();
	jQuery("#comment_contentIndex").val(-2);
	jQuery(".reply_link").hide();
	jQuery('.cp_goToText').show();
	jQuery(".author_on_paragraph").show();
		

	if(jQuery("#comment_total_count").val() > 0){
	 jQuery("#no_comments_yet").show();
	}
	else{
	 jQuery("#no_comments_yet").hide();
	}
	
	jQuery(".cbcontent").each(function(i){
	 this.style.background = '#fff';
	 this.style.border = '1px solid #fff';
	});

	jQuery(".cp_commentBody").each(function(i){			 
	 this.style.display = 'block';
	});	  
	//jQuery("#active_paragraph").text("");
   }
   */
  });


  
  /**
   * 
   */
  jQuery("#searchbox").focus(function(){
   jQuery("#searchbox").val("");
  });

  /**
   * 
   */
  jQuery(".cp_goToText").click(function(fn){
   var id = parseInt(jQuery(this).attr('id').substr(12));	 
   paragraph_read_event(id);
   jQuery('.cp_goToText').hide();
  });


  /**
   * 
   */
  jQuery(".cp_replyLink").click(function(){

  });
  

  /**
   * 
   */
  jQuery(".cp_closeReplyBox").click(function(){
	  jQuery("#comment_reply").hide();
	  //jQuery("#comment_read").fadeIn("fast");
  });	
  
}

//http://www.mail-archive.com/discuss@jquery.com/msg02537.html
function client_coords() {
  var dimensions = {width: 0, height: 0};
  
  if (document.documentElement) {
   dimensions.width = document.documentElement.offsetWidth;
   dimensions.height = document.documentElement.offsetHeight;
  } 
  else if (window.innerWidth && window.innerHeight) {
   dimensions.width = window.innerWidth;
   dimensions.height = window.innerHeight;
  }
  return dimensions;
}


function check_comments(){
 $.get(siteurl + "/?function=getAllCommentCount", function(data){		
   if(data > readCookie('commentpress_comment_count')){					
	createCookie('commentpress_comment_count', data,7);
	jQuery("#cp_newComment").fadeIn("slow");
	 $.get(siteurl + "/?function=getAllCommentCount", function(data){		
	  jQuery("#cp_count").fadeOut("slow");		 
	  var count = jQuery("#cp_count").text();
	  jQuery("#cp_count").text( count );
	  jQuery("#cp_count").fadeIn("slow");
	 });		
   }
 });
}

// TODO: future ajax calls
//setInterval("check_comments()",6000); //query the database for new comments every 6 seconds


//this function is called when someone wants to read a specific paragraph and clicks the bubble
function paragraph_read_event(paragraph, id){

  //alert("Funktion bekam " + arguments.length + " Argumente");
  //for (var i = 0; i < arguments.length; ++i)
  //  alert(arguments[i]);

  //R
  //alert(paragraph + " - " + id);
  currentParagraph = paragraph;

  reRoot(); 
  emptyBox = true;
  
  if(!readCookie('commentpress_hide_help')){
   createCookie('commentpress_hide_help', 1,7);
  }
  
  jQuery("#comment_help").hide();
  jQuery(".author_on_paragraph").hide();
  
  if(jQuery('#comment_contentIndex').val() == paragraph){	
   jQuery("#comment_read").hide();	 
   jQuery("#comment_index").show();
   //jQuery("#show_all_comments_action").text('expand all [++]');
   
   jQuery(".cbcontent").each(function(i){
	  this.style.background = '#fff';
	  this.style.border = '1px solid #fff';
   });
   
   jQuery("#comment_contentIndex").val(-1);
   jQuery("#show_all_comments_action").text(strHelp);
   eraseCookie(cookie_name());
  }
  
  else {       
   jQuery("#comment_index").hide();
   jQuery("#comment_help").hide();
   jQuery("#comment_read").show();
   
   jQuery(".cbcontent").each(function(i){
	 this.style.background = '#fff';
	 this.style.border = '1px solid #fff';
	 //this.addClass('cp_nohighlight');	 
   });
   
   if(jQuery('.cbcontent')[paragraph].style.background == 'rbg(255,255,255) none repeat scroll 0% 0%' ){
	jQuery('.cbcontent')[paragraph].style.background = '#fff';
	jQuery('.cbcontent')[paragraph].style.border = '1px solid #fff';
	//jQuery('.cbcontent')[id].addClass('cp_nohighlight');	 
	
	jQuery("#comment_contentIndex").val(0);
	jQuery("#comment_index").show();
   }
   else {
	jQuery("#show_all_comments_action").text(strOverview);
	jQuery('.cbcontent')[paragraph].style.background = '#F5F5F5';
	jQuery('.cbcontent')[paragraph].style.border = '1px dotted #D0D0D0';
	//jQuery('.cbcontent')[id].addClass('cp_highlight');	 
	
	jQuery("#comment_contentIndex").val(id);
	jQuery('.cp_goToText').show();
 
	jQuery(".cp_commentBody").each(function(i){		 
	  if(this.id.substr(14) == id){
	   emptyBox = false;
	   this.style.display = 'block';
	  }
	  else{
	   this.style.display = 'none';
	  }			 
	});
   }
   
   //createCookie(cookie_name(),id,7);	
   //id = readCookie(cookie_name());
   var active_paragraph;
   if(paragraph > 0){
	active_paragraph = strOnParagraph + " " + paragraph ;
   }
   else {
	active_paragraph = strOnWholePage;
   }
   
   jQuery("#active_paragraph").text(active_paragraph);   
 
   // save paragraph read state via AJAX to DB
   markParagraphRead(id); // @see: ajax.js

  }
 

  //TODO: figure out how to scroll the div to the top
  //jQuery("#cp_comments").scrollTop = 0;
 
  if(emptyBox){
   jQuery("#no_comments_yet").show();
  }
  else{
   jQuery("#no_comments_yet").hide();
  }
 
  jQuery("#addcomment").show();	
		
}

function get_path(){
 return window.location.pathname.toString().substr(0, (window.location.pathname.toString().length - 1) );
}


function get_hash(){ 
 //alert(window.location.hash.toString().indexOf('comment'));
 if(window.location.hash.toString().indexOf('comment') == 1){	  	
  var comment_id = window.location.hash.toString().substr(9);
  var id =  '#comment-link-'+comment_id;  
  var para = jQuery(id).text();
  //window.location.hash = '#' + para;
  return para;
 }	  
 else if(window.location.hash.toString().indexOf("respond") == 1){
  return false;
 }
 else{
  return window.location.hash.toString().substr(1);
 }
}

function return_comment(){
 if(window.location.hash.toString().length){
  return true;
 }
 else{
  return false;
 }
}

function cookie_name(){
 return 'cp' + get_path().replaceAll("/","-");
}

