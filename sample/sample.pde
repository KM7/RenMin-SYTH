int vel=127;

void setup(){
  size(400,200);
}

void draw(){
  fill(255);
  for (int j=0;j<2;j++){
  // width 
  for (int i=0;i<4;i++){
  rect (50+i*50,50+50*j,50,50);
  }
  }
  
  if (mousePressed&&mouseX>50&&mouseX<100&&mouseY>50&&mouseY<100){
    fill(122,33,111);
    rect(50,50,50,map(vel,0,127,0,50));
  }
  
  }

void mousePressed(){
  

  
}
