try {
	
	// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// THIS IS THE SECTION WHERE PROCODING INJECTS THE CANVAS SCRIPT
	//
	//
	
	// Multitouch Example
// 
// Created 2012-01-10 for Procoding 
// updated 2012-04-24 for canvas
// updated 2013-04-24 for Procoding OSX (mouse events)
// (c) by by Michael Markert
//
// Note to iPad Users:
// If you have enabled "Multitasking Gestures"
// in Settings » General you can use only up to 3 touches.
// Otherwise it'll work with up to 11 touches
//
// for a detailed API see reference and:
// http://www.w3.org/TR/touch-events/



var debug = false;
var diameter = width/6;
var ctx = canvas.getContext("2d");

// Array to store active Touches
var touches = [];

function setup() {
   if(debug) { console.log("Setup..."); }
   // continuously draw
   setInterval(draw,20);
   // register event handlers
   canvas.addEventListener("touchstart", touchStart, false);
   canvas.addEventListener("touchmove", touchMove, false);
   canvas.addEventListener("touchend", touchEnd, false);
   canvas.addEventListener("touchcancel", touchCancel, false);
   // add mouse events (to see what's going on)
   canvas.addEventListener("mousedown", mouseDown, false);
   canvas.addEventListener("mouseup", mouseUp, false);
   canvas.addEventListener("mousemove", mouseMove, false);
}

setup();

function draw() {
   ctx.fillStyle = "black";
   ctx.fillRect(0,0,width,height);
   
   var touch;
   var i;
   for(i=0; i<touches.length; i++) {
      touch = touches[i];
      ctx.fillStyle = touch.c;
      ctx.strokeStyle = touch.c;
      ctx.lineWidth = 0;
      circle(touch.x, touch.y, diameter);
   }
}

// convenience drawing methods
function circle(x, y, r) {
   ctx.beginPath();
   ctx.arc(x, y, r, 0, Math.PI * 2, true);
   ctx.fill();
   ctx.stroke();
   //console.log("circle x"+x+" y"+y+" c:"+ctx.fillStyle);
}

function random(max) {
   return Math.floor(Math.random()*max);
}



// Touch Event Handlers

function touchStart(t) {
   if(debug) { console.log("TochStart:" + t.touches.length); }
   // save touch object
   var i;
   var id;
   var touch;
   for (i=0; i<t.touches.length; i++) {
      id = t.touches[i].identifier;
      // see if there's already a touch with this id
      var index = indexOfTouchWithID(id);
      if(index < 0) {
         // create
         touch = new Touch(id);
         touches.push(touch);
         if(debug) { console.log("Added touch "+id); }
      } else {
         // already there
         touch = touchWithID(id);
         if(debug) { console.log("Found touch "+id); }
      }
      touch.x = t.touches[i].pageX;
      touch.y = t.touches[i].pageY;
   }
}

function touchMove(t) {
   //console.log("TouchMoved:" + t);
   var touch;
   var i;
   for(i=0; i<t.touches.length; i++) {
      var id = t.touches[i].identifier;
      touch = touchWithID(id);
      touch.x = t.touches[i].pageX;
      touch.y = t.touches[i].pageY;
   }
}


function touchEnd(t) {
   if(debug) { console.log("TouchEnd:" + t.touches.length + " " + t.changedTouches.length); }
   
   // Remember: you'll find all touches not in 'touches' nor 'targetTouches' but in 'changedTouches'. 
   
   // remove touches obj
   var index;
   var i;
   for(i=0; i<t.changedTouches.length; i++) {
      var id = t.changedTouches[i].identifier;
      index = indexOfTouchWithID(id);
      touches.splice(index,1);
      if(debug) { console.log("Removed touch "+id); }
   }
}

function touchCancel(t) {
   if(debug) { console.log("TouchCancelled:" + t); }
   // touchCancel occurs if the application unexpectedly loses focus, e.g. if a call comes in
   // so we're removing all touches
   var i;
   for(i=touches.length-1; i>=0; i--) {
      touches.splice(i,1);
      if(debug) { console.log("Removed touch "+i); }
   }
}

function touchWithID(id) {
   var touch;
   var i;
   for(i=0; i<touches.length; i++) {
      touch = touches[i];
      if(touch.id == id) {
         return touch;
      }
   }
   return;
}

function indexOfTouchWithID(id) {
   var touch;
   var i;
   for(i=0; i<touches.length; i++) {
      touch = touches[i];
      if(touch.id == id) {
         return i;
      }
   }
   return -1;
}


function Touch(identifier) {
   // each touch has an ID, a position and a color
   this.id = identifier;
   this.x = 0;
   this.y = 0;
   this.c = "rgba("+random(255)+","+random(255)+","+random(255)+",0.75)";
   if(debug) { console.log("TouchCreated: " + this.id); }
}



// "Fake" mouseDown functions
// to make this sketch desktop compatible

function mouseDown(e) {
	// create "fake" touch
	var touch = touchWithID(9999);
	if(indexOfTouchWithID(9999) < 0) {
		touch = new Touch(9999);
		touch.x = e.pageX;
		touch.y = e.pageY;
		touches.push(touch);
	}
}

function mouseMove(e) {
	var touch = touchWithID(9999);
	touch.x = e.pageX;
	touch.y = e.pageY;
}

function mouseUp() {
	var index = indexOfTouchWithID(9999);
	if(index >= 0) {
		touches.splice(index,1);
	}
}


	
	//
	//
	// THIS WAS THE SECTION WHERE PROCODING INJECTED THE CANVAS SCRIPT
	// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
} catch (e) {
	_procoding_throwError(e);
}
