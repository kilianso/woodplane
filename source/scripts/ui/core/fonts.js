// This makes sure Text is visible and Webfonts are just replaced when finished loading.
import FontFaceObserver from './fontfaceobserver';

var font = new FontFaceObserver('wdpln_bold', 'wdpln_regular');

font.load(null, 10000).then(function () {
	document.body.className += " fontsloaded";
	// console.log('fonts loaded');
});
