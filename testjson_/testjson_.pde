JSONObject json;
//According to my server, the maximum is 10 for now
int imageNumber=10;
PImage img[]=new PImage[imageNumber];


void setup() {
  size(1000,1000);

  json = loadJSONObject("http://www.kennilun.com/shanshanliu/test.php");
  JSONArray values = json.getJSONArray("random_pics");
 for (int i = 0; i < imageNumber; i++) {
    
    JSONObject item = values.getJSONObject(i); 
    img[i]=loadImage("http://www.kennilun.com/shanshanliu/pics/"+item.getString("media_id")+".jpg","jpg");
    String name = item.getString("media_id");
    println("loadding "+i+"/"+10);
  }
    println("image loading finished");
}

void draw(){
  for(int i=0;i<imageNumber;i++){
    image(img[i],random(0,1000),random(0,1000));
  }
}
