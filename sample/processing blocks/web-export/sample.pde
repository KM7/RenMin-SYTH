int vel = 100;

Box[][] boxes;

void setup() {
  size(400,200);
  boxes = new Box[2][4];
  for (int j=0;j<2;j++){
    for (int i=0;i<4;i++){
      boxes[j][i] = new Box(i,j,40);
    }
  }  
}

void draw() {
  background(255);
  
  for (int j=0;j<2;j++){
    for (int i=0;i<4;i++){
      boxes[j][i].display();
    }
  }
}

class Box {
  int a,b;
  int size = 40;
  
  Box(int i,int j,int s) { 
    a = 50+i*50;
    b = 50+j*50;
    size = s;
  }
  
  void display() {
    fill(233);
    rect (a,b,size,size);
    if(overSqu(a,b,size,size) && mousePressed) {
      fill(122,33,111);
      rect(a,b+size-map(vel,0,127,0,size),size,map(vel,0,127,0,size));
    }
  }
}

boolean overSqu(int x, int y, int width, int height) {
  if (mouseX >= x && mouseX <= x+width && 
      mouseY >= y && mouseY <= y+height) {
    return true;
  } else {
    return false;
  }
}

