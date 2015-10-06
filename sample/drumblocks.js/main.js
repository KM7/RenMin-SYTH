

var currentColor, rectHighlight, rectColor;
var rectOver = new Array(4);
// Simple way to attach js code to the canvas is by using a function
function sketchProc(processing) {
  processing.setup = function() {
    processing.size(640, 360);
    for(var i=0; i<4; i++) {
      rectOver[i] = false;
    }
    rectColor = processing.color(0);
    rectHighlight = processing.color(51);
    currentColor = processing.color(255);
  };

  processing.draw = function() {
    update(processing.mouseX, processing.mouseY);
    processing.background(currentColor);
    for(var i=0; i<4; i++) {
      if(rectOver[i]) {
        processing.fill(rectHighlight);
      } else {
        processing.fill(rectColor);
      }
      processing.stroke(255);
      processing.rect(100+i*70, 50, 50, 50);
    }
  };

  function update(x, y) {
    for(var i=0; i<4; i++) {
      if(overRect(100+i*70, 50, 50, 50)) {
        rectOver[i] = true;
      } else {
        rectOver[i] = false;
      }
    }
  }

  processing.mouseClicked = function() {
    if(rectOver[0]) {
      Sample.play(BUFFERS.kick, 0);
    } else if(rectOver[1]) {
      Sample.play(BUFFERS.snare, 0);
    } else if(rectOver[2]) {
      Sample.play(BUFFERS.hihat, 0);
    } else if(rectOver[3]) {
      Sample.toggle(BUFFERS.beat, 0);
    }
  };
  
  function overRect(x, y, width, height)  {
    if (processing.mouseX >= x && processing.mouseX <= x+50 && 
        processing.mouseY >= y && processing.mouseY <= y+50) {
      return true;
    } else {
      return false;
    }
  }
}
  
var canvas = document.getElementById("canvas1");
// attaching the sketchProc function to the canvas
var p = new Processing(canvas, sketchProc);
// p.exit(); to detach it
