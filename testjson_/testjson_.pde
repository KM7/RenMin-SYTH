JSONObject json;
//According to my server, the maximum is 10 for now
int imageNumber=10;
PImage img[]=new PImage[imageNumber];
PImage ren;
PImage mask;
boolean isRemote=false;
import codeanticode.syphon.*;

SyphonServer server;



void setup() {
  size(1000,500,P2D);
      ren=loadImage("ren.png");
if (isRemote){
  json = loadJSONObject("http://www.kennilun.com/shanshanliu/test.php");
  JSONArray values = json.getJSONArray("random_pics");
 for (int i = 0; i < imageNumber; i++) {
    
    JSONObject item = values.getJSONObject(i); 
    img[i]=loadImage("http://www.kennilun.com/shanshanliu/pics/"+item.getString("media_id")+".jpg","jpg");
    String name = item.getString("media_id");
    println("loadding "+i+"/"+imageNumber);
  }
}else{
   for (int i = 0; i < imageNumber; i++) {
    img[i]=loadImage("temp_image/"+i+".jpg");
   }
}
     server = new SyphonServer(this, "Processing Syphon");

    println("image loading finished");
}

void draw(){
  server.sendScreen();

  for(int i=0;i<imageNumber;i++){
    image(img[i],random(0,width),random(0,height));
  }
  mask=get();
  background(214,6,6);
  mask.mask(ren);
  image(mask,0,0);
}
