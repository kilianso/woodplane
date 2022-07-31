// Holy fuck this needs heavy refactoring. What a mess..
const VideoElements = document.querySelectorAll('.Video');

let container;
let video;
let controls;
let bigPlay;
let btnPlay;
let btnFs;
let progress;

if (VideoElements.length) {
    const playpause = function (videoel) {
        const icon = btnPlay.querySelector('span');
        if (videoel.paused || videoel.ended) {
            btnPlay.classList.add('paused');
            icon.classList.remove('icon-play');
            icon.classList.add('icon-pause');
            videoel.play();
        } else {
            btnPlay.classList.remove('paused');
            icon.classList.remove('icon-pause');
            icon.classList.add('icon-play');
            videoel.pause();
        }
    };


    const updatebar = (e, t) => {
        const timebar = container.querySelector('.timeBar');
        const progressInner = progress.querySelector('.progress');
        let maxduration = video.duration;
        let position = e - progressInner.getBoundingClientRect().left;
        let percentage = 100 * position / progressInner.offsetWidth;
        if (percentage > 100) {
            percentage = 100;
        }
        if (percentage < 0) {
            percentage = 0;
        }
        //prevent things before video is playing.
        if (video.readyState > 0) {
            timebar.style.width = percentage + '%';
            video.currentTime = maxduration * percentage / 100;
        }
    };

    const registerListeners = () => {
        //fullscreen button clicked

        // Click on small play button
        btnPlay.addEventListener('click', function() {
            playpause(video)
        });

        btnFs.addEventListener('click', function () {
            if(video.requestFullScreen){
                video.requestFullScreen();
            } else if(video.webkitRequestFullScreen){
                video.webkitRequestFullScreen();
            } else if(video.mozRequestFullScreen){
                video.mozRequestFullScreen();
            }
        })

        //sound button clicked
        container.querySelector('.sound').addEventListener('click', function (event) {
            video.muted = !video.muted;
            event.target.classList.toggle('muted');
        });

        //VIDEO PROGRESS BAR
        //when video timebar clicked
		progress.addEventListener('touchend', (e) => progressHandler(e));
		progress.addEventListener('mouseup', (e) => progressHandler(e));

        //display current video play time
        video.addEventListener('timeupdate', function () {
            let currentPos = video.currentTime;
            let maxduration = video.duration;
            let perc = 100 * currentPos / maxduration;
            controls.querySelector('.timeBar').style.width = perc + '%';
        });
    }

	const progressHandler = (e) => {
		updatebar(e.pageX, e.target);
		// if (timeDrag) {
		// 	timeDrag = false;
		// }
		// timeDrag = false;
	}

    // init
    document.addEventListener('click', function (event) {
        if (
            event.target.matches('video') ||
            event.target.matches('.bigPlay') ||
            event.target.matches('.btnPlay')
        ) {
            event.preventDefault();
            if (event.target.parentNode.matches('.Video')) {

                container = event.target.parentNode;
                video = container.querySelector('video');
                bigPlay = container.querySelector('.bigPlay');
                btnPlay = container.querySelector('.btnPlay');
                btnFs = container.querySelector('.btnFS');
                controls = container.querySelector('.Video__controls');
                progress = controls.querySelector('.progress-bar');

                bigPlay.classList.toggle('show');
                controls.classList.toggle('show');
                playpause(video);
                registerListeners();
            }
        }
    })

}
