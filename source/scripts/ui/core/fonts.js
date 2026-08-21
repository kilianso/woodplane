// This makes sure Text is visible and Webfonts are just replaced when finished loading.
import * as FontFaceObserverModule from './fontfaceobserver';
const FontFaceObserver = FontFaceObserverModule.default || FontFaceObserverModule;

var font = new FontFaceObserver('wdpln_bold', 'wdpln_regular');

font.load(null, 10000).then(function () {
	document.body.className += " fontsloaded";
	// console.log('fonts loaded');
});
