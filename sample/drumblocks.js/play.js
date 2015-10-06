var Sample = {
  FADE_TIME: 0.5
};

Sample.gainNode = null;
Sample.convolver = null;
Sample.revlevel = null;

Sample.play = function(buffer, time) {
  if (!context.createGain)
    context.createGain = context.createGainNode;
  this.gainNode = context.createGain();
  var fra = document.getElementById("volume").value / 100;
  this.gainNode.gain.value = fra * fra;
  
  this.convolver = context.createConvolver();
  this.revlevel = context.createGain();
  this.revlevel.gain.value = document.getElementById("rev").value / 100;
  this.convolver.connect(this.revlevel);
  this.revlevel.connect(context.destination);
  
  var source = context.createBufferSource();
  source.buffer = buffer;
  this.convolver.buffer = BUFFERS.cvbuffer;
  source.connect(this.gainNode);
  this.gainNode.connect(this.convolver);
  this.gainNode.connect(context.destination);
  
  source.start(time);
  this.source = source;
};

Sample.changeVolume = function(element) {
  var volume = element.value;
  var fraction = parseInt(element.value) / parseInt(element.max);
  // Let's use an x*x curve (x-squared) since simple linear (x) does not
  // sound as good.
  this.gainNode.gain.value = fraction * fraction;
};

Sample.changeRev = function(element) {
  var reverb = element.value;
  this.revlevel.gain.value = parseInt(reverb) / 100;
};

Sample.stop = function() {
  var ctx = this;
  this.gainNode.gain.linearRampToValueAtTime(1, context.currentTime);
  this.gainNode.gain.linearRampToValueAtTime(0, context.currentTime + ctx.FADE_TIME);
  clearTimeout(this.timer);
};

Sample.toggle = function(buffer, time) {
  if(this.playing) {
    this.stop(); 
  } else {
    this.play(buffer, time);}
  this.playing = !this.playing;
};