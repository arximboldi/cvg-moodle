
/**
 * Marker class
 *
 * tracks the current mouse position and changes the mouse cursor image 
 * if the user has enabled the Marker
 * ## Wilfried: var Eraser is copy of var Marker
 */
 
 var Eraser = {

	enabled: false,
	cursorIsHidden : true,
	
	/** saves the x-position of the mouse pointer */
	globalPosX : 0,
	/** saves the y-position of the mouse pointer */
	globalPosY : 0,

	init: function () {
		var cursorDiv = document.getElementById('emargo_cursor');
		if (!cursorDiv) {
			// create the eraser mouse cursor
			div = document.createElement('div');
			div.id = 'emargo_cursor';			
			div.innerHTML = '<img src="' + wwwroot + '/mod/vizcosh/emargo/pix/buttons/eraser.gif" />';
			document.body.appendChild(div);
			
			// change the background of the YUI-markertool-button to blue (using CSS-sprites)
			var markerButton = document.getElementById("deletebutton");
			markerButton.style.backgroundPosition = "0 -1300px";
		} else {
			// remove the eraser mouse cursor
			document.body.removeChild(cursorDiv);
			this.enabled = false;

			// change the background of the YUI-markertool-button to white (using CSS-sprites)
			var markerButton = document.getElementById("deletebutton");
			markerButton.style.backgroundPosition = "0 0";
		}
	},
	
	/**
	 * tracks current mouse position and moves the position of the deleting mouse cursor image
	 */
	moveCursor: function (e) {
		// cross-browser way of getting the current mouse position
		if (document.captureEvents) {
			Marker.globalPosX = e.pageX;
			Marker.globalPosY = e.pageY;
		} else if (window.event.clientX) {
			Marker.globalPosX = e.pageX;
			Marker.globalPosY = e.pageY;
		} else if (window.event.clientX) {
			Marker.globalPosX = window.event.clientX + document.documentElement.scrollLeft;
			Marker.globalPosY = window.event.clientY + document.documentElement.scrollTop;
		}
		var div = document.getElementById('emargo_cursor');
		if (div) {
			var divStyle = div.style;
		}
		var xPos = (parseInt(Marker.globalPosX) + 3)
		var yPos = (parseInt(Marker.globalPosY) + 2);
		if (divStyle && xPos > 3 && yPos > 2) {
			divStyle.left = xPos + "px";
			divStyle.top = yPos + "px";
		}
		return true;
	},
	
	isEnabled: function () {
		return this.enabled;
	},
	
	changeEnabled: function () {
		//var cursor = document.getElementById("emargo_cursor");
		//if (cursor != undefined) {
			if (!this.isEnabled()) {
				this.enable();
			} else {
				this.disable();
			}
		//}
		return false;
	},

	enable: function () {
		var cursor = document.getElementById("emargo_cursor");
		if (cursor != undefined) {
			if (!this.isEnabled()) {
				cursor.style.visibility = "visible";
				this.enabled = true;
				hideMarkerContextmenu();
			}
		}
	},
	
	disable: function() {
		var cursor = document.getElementById("emargo_cursor");
		if (cursor != undefined) {
			cursor.style.visibility = "hidden";
			this.enabled = false;
		}
	}
}
  
var Marker = {

	enabled: false,
	cursorIsHidden : true,
	
	/** saves the x-position of the mouse pointer */
	globalPosX : 0,
	/** saves the y-position of the mouse pointer */
	globalPosY : 0,

	init: function () {
		var cursorDiv = document.getElementById('emargo_cursor');
		if (!cursorDiv) {
			//if markings are hidden -> show 'em
			if (markingsHidden) {
				loadMarkedText();
			}
			// create the highlighting mouse cursor
			div = document.createElement('div');
			div.id = 'emargo_cursor';			
			div.innerHTML = '<img src="' + wwwroot + '/mod/vizcosh/emargo/pix/buttons/highlight.png" />';
			document.body.appendChild(div);
			
			// change the background of the YUI-markertool-button to blue (using CSS-sprites)
			var markerButton = document.getElementById("markerbutton");
			markerButton.style.backgroundPosition = "0 -1300px";
		} else {
			// remove the highlighting mouse cursor
			document.body.removeChild(cursorDiv);
			this.enabled = false;

			// change the background of the YUI-markertool-button to white (using CSS-sprites)
			var markerButton = document.getElementById("markerbutton");
			markerButton.style.backgroundPosition = "0 0";
		}
	},
	
	/**
	 * tracks current mouse position and moves the position of the highlighting mouse cursor image
	 */
	moveCursor: function (e) {
		// cross-browser way of getting the current mouse position
		if (document.captureEvents) {
			Marker.globalPosX = e.pageX;
			Marker.globalPosY = e.pageY;
		} else if (window.event.clientX) {
			Marker.globalPosX = window.event.clientX + document.documentElement.scrollLeft;
			Marker.globalPosY = window.event.clientY + document.documentElement.scrollTop;
		}
		var div = document.getElementById('emargo_cursor');
		if (div) {
			var divStyle = div.style;
		}
		var xPos = (parseInt(Marker.globalPosX) + 3)
		var yPos = (parseInt(Marker.globalPosY) + 2);
		if (divStyle && xPos > 3 && yPos > 2) {
			divStyle.left = xPos + "px";
			divStyle.top = yPos + "px";
		}
		return true;
	},
	
	isEnabled: function () {
		return this.enabled;
	},
	
	changeEnabled: function () {
		//var cursor = document.getElementById("emargo_cursor");
		//if (cursor != undefined) {
			if (!this.isEnabled()) {
				this.enable();
			} else {
				this.disable();
			}
		//}
		return false;
	},

	enable: function () {
		var cursor = document.getElementById("emargo_cursor");
		if (cursor != undefined) {
			if (!this.isEnabled()) {
				cursor.style.visibility = "visible";
				this.enabled = true;
				hideMarkerContextmenu();
			}
		}
	},
	
	disable: function() {
		var cursor = document.getElementById("emargo_cursor");
		if (cursor != undefined) {
			cursor.style.visibility = "hidden";
			this.enabled = false;
		}
	}
}


/**
 * cross browser compatible way to get the selected text
 */
function getSelectedText() {
	var selectedText = '';
	if (document.getSelection) {
		selectedText = document.getSelection();
	} else if (window.getSelection) {
		selectedText = window.getSelection();
	} else if (document.selection) {
		selectedText = document.selection.createRange().text;
	}
	// whitespace fix
	//selectedText = selectedText.replace(new RegExp('([\\f\\n\\r\\t\\v ])+', 'g')," ");

	return selectedText;
}

/**
 * starts the capturing of mouse actions. See Marker.moveCursor() for details
 */
function captureMouseActions() {
	document.onmousemove = Marker.moveCursor;
	if (document.Event) {
		document.captureEvents(Event.MOUSEMOVE);
	}
}

/**
 * shows or hides the bookmark- and questionmark-icons next to the paragraphs
 */
function toggleParagraphIcons(paragraphId) {
	var flagDiv = document.getElementById("bookmark-" + paragraphId);
	var questionmarkDiv = document.getElementById("questionmark-hidden" + paragraphId);
	// show/hide bookmarks
	if (flagDiv != undefined) {
		if (flagDiv.style.visibility == "visible") {
			flagDiv.style.visibility = "hidden";
		} else {
			flagDiv.style.visibility = "visible";
		}
	}

	// show/hide questionmarks
	if (questionmarkDiv != undefined) {
		if (questionmarkDiv.style.visibility == "visible") {
			questionmarkDiv.style.visibility = "hidden";
		} else {
			questionmarkDiv.style.visibility = "visible";
		}
	} /* else {
		questionmarkDiv = document.getElementById("questionmark-" + paragraphId);
	} */

	// change font size of questionmarks (not needed here, handled via PHP now)
	/*
	if (questionmarkDiv != undefined) {
		if (questionmarkCount != 0) {
			if (questionmarkCount > 9) {
				questionmarkCount = 9;
			}
			questionmarkDiv.style.fontSize = "1." + questionmarkCount + "em";
		}
	}
	*/
}



/**
 * displays a contextmenu that is used to show a link that enables the user to 
 * delete a certain marking
 */
function showMarkerContextmenu(annotationId) {
	// display only if marker-tool is disabled
	if (!Marker.isEnabled() && markingsOfOthersHidden) {
		var markerContextmenu = document.getElementById('markercontextmenu');
		markerContextmenu.style.top = (Marker.globalPosY+10)+"px";
		markerContextmenu.style.left = (Marker.globalPosX-20)+"px";
		markerContextmenu.style.visibility = 'visible';
		markingToDelete = strReplace("annotation", "", annotationId);
	}
}

/**
 * hides the contextmenu displayed with showMarkerContextmenu()
 */
function hideMarkerContextmenu() {
	var markerContextmenu = document.getElementById('markercontextmenu');
	if (markerContextmenu) {
		markerContextmenu.style.visibility = 'hidden';
		markingToDelete = 0;
	}
}


/**
 * displays a mouseover-tooltip. This function is called using YUI button methods 
 * if the user moves his mouse over a certain button. 
 */
function showButtonTooltip(tooltipButtonLabel) {
	var tooltipDiv = document.getElementById('buttontooltip');
	tooltipDiv.style.top = (Marker.globalPosY+10)+"px";
	tooltipDiv.style.left = (Marker.globalPosX-20)+"px";
	tooltipDiv.style.visibility = 'visible';
	tooltipDiv.innerHTML = tooltipButtonLabel;
}


/**
 * hides the tooltip displayed with showButtonTooltip()
 */
function hideButtonTooltip() {
	var tooltipDiv = document.getElementById('buttontooltip');
	tooltipDiv.style.visibility = 'hidden';
}

/**
 * opens a new popup that contains the help page for all eMargo annotation-features
 */
function showEmargoHelp() {
	openpopup('/help.php?module=emargo&file=index.html', 'popup', 'menubar=0,location=0,scrollbars,resizable,width=500,height=400', 0);
}


/**
 * Replaces all occurrences of the search string with the replacement string
 * works like str_replace() in PHP 
 */
function strReplace(searchString, replaceString, subject) {
	return subject.split(searchString).join(replaceString);
}