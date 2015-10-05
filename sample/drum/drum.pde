

color currentColor, rectHighlight, rectColor;
boolean[] rectOver;


void setup() {
  size(640,360);
  rectOver = new boolean[4];
  for(int i=0; i<4; i++) {
    rectOver[i] = false;
  }
  rectColor = color(0);
  rectHighlight = color(51);
  currentColor = color(255);
}

void draw() {
  update(mouseX, mouseY);
  background(currentColor);
  for(int i=0; i<4; i++) {
    if(rectOver[i]) {
      fill(rectHighlight);
    } else {
      fill(rectColor);
    }
    stroke(255);
    rect(100+i*70, 50, 50, 50);
  }
}

void update(int x, int y) {
  for(int i=0; i<4; i++) {
    if(overRect(100+i*70, 50, 50, 50)) {
      rectOver[i] = true;
    } else {
      rectOver[i] = false;
    }
  }
}

void mouseClicked() {
  for(int i=0; i<4; i++) {
    if(rectOver[i]) {
      currentColor = rectColor;
    }
  }
}

boolean overRect(int x, int y, int width, int height) {
  if (mouseX >= x && mouseX <= x+width && 
      mouseY >= y && mouseY <= y+height) {
    return true;
  } else {
    return false;
  }
}
