function loadSample(){

		var request = new XMLHttpRequest();
			request.open('GET','audio/sample.mp3',true);
			request.responseType = "arraybuffer";
			request.onload = function(){
				context.decodeAudioData(request.response,function(b){
					buffer = b; //set the buffer
					data = buffer.getChannelData(0);
					isloaded = true;
					var canvas1 = document.getElementById('canvas');
					//initialize the processing draw when the buffer is ready
					var processing = new Processing(canvas1,waveformdisplay);
					load();

				},function(){
					console.log('loading failed');
					alert('loading failed');
					
				});
			};
		request.send();
}