$(function(){

	// Holy fuck this needs heavy refactoring. What a mess..
	$(document).on('click','video, .bigPlay, .btnPlay', function() {
		if ($(this).parents('.Video').length) {
			var container = $(this).parents('.Video').eq(0);
			console.log('has .Video parent', container);

			var video = container.find('video'),
			bigPlay = container.find('.bigPlay'),
			controls = container.find('.Video__controls');
			
			bigPlay.toggleClass('show');
			controls.toggleClass('show');
			playpause(video);
			//display current video play time
			video.on('timeupdate', function() {
				var currentPos = $(this)[0].currentTime;
				var maxduration = $(this)[0].duration;
				var perc = 100 * currentPos / maxduration;
				$(this).next().find('.timeBar').css('width', perc + '%');
				// $(this).closest('.current').text(timeFormat(currentPos));
			});
		}
	});

	//fullscreen button clicked
	$(document).on('click','.btnFS', function() {
		var container = $(this).closest('.Video');
		var video = container.find('video')[0];
		// go full-screen
		if (video.requestFullscreen) {
			video.requestFullscreen();
		} else if (video.webkitRequestFullscreen) {
			video.webkitRequestFullscreen();
		} else if (video.webkitEnterFullscreen) {
			video.webkitEnterFullscreen();
		} else if (video.mozRequestFullScreen) {
			video.mozRequestFullScreen();
		} else if (video.msRequestFullscreen) {
			video.msRequestFullscreen();
		}else{
			alert('Your browsers doesn\'t support fullscreen');
		}
	});
	//sound button clicked
	$(document).on('click','.sound', function() {
		var container = $(this).closest('.Video');
		var video = container.find('video');
		video[0].muted = !video[0].muted;
		$(this).toggleClass('muted');
	});
	//VIDEO PROGRESS BAR
	//when video timebar clicked
	var timeDrag = false; /* check for drag event */
	$(document).on('mousedown touchstart','.progress-bar', function(e) {
		timeDrag = true;
		updatebar(e.pageX, e.target);
	});
	$(document).on('mouseup touchend', '.progress-bar', function(e) {
		if (timeDrag) {
			timeDrag = false;
			updatebar(e.pageX, e.target);
		}
	});
	$(document).on('mousemove touchmove', '.progress-bar', function(e) {
		if (timeDrag) {
			updatebar(e.pageX, e.target);
		}
	});
	var updatebar = function(e, t) {
		var progress = $(t).find('.progress')[0];
		var video = $(t).closest('.Video').find('video')[0];
		var timebar = $(t).find('.timeBar')
		//calculate drag position
		//and update video currenttime
		//as well as progress bar
		var maxduration = video.duration;
		var position = e - $(progress).offset().left;
		var percentage = 100 * position / $(progress).width();
		if (percentage > 100) {
			percentage = 100;
		}
		if (percentage < 0) {
			percentage = 0;
		}
		//prevent things before video is playing.
		if(video.readyState > 0){
			$(timebar).css('width', percentage + '%');
			video.currentTime = maxduration * percentage / 100;
		}
	};
	var playpause = function(videoel) {
		var container = $(videoel).closest('.Video');
		if (videoel[0].paused || videoel[0].ended) {
			container.find('.btnPlay').addClass('paused');
			container.find('.btnPlay').find('.icon-play').addClass('icon-pause').removeClass('icon-play');
			videoel[0].play();
		} else {
			container.find('.btnPlay').removeClass('paused');
			container.find('.btnPlay').find('.icon-pause').removeClass('icon-pause').addClass('icon-play');
			videoel[0].pause();
		}
	};
});