$(function(){
	var
			body = $('body'),
			header = $('.main-header'),
			header_nav = header.find('.main-header__nav'),
			header_navtrigger = header.find('.main-header__navtrigger'),
			header_search = header.find('.main-header__search'),
			header_searchtrigger = header.find('.main-header__searchtrigger'),
			shop_link = header_nav.find('a[href="https://teespring.com/stores/Woodplane"]');
	body.removeClass('preload');
	$(header_searchtrigger).on('click', function(event){
		event.preventDefault();
		showAndHide(event, header_search, header_nav)
	});
	$(header_navtrigger).on('click', function(event){
		event.preventDefault();
		showAndHide(event, header_nav, header_search)
	});

	$(shop_link).on('click', function(){
		console.log('conversion tracking');
		gtag_report_conversion();
	});

	function showAndHide(e, currentContext, otherContext){
		// if header is not open and search is not open
		if(!header.hasClass('is--open') && !currentContext.hasClass('is--open')){
			// console.log('open all');
			header.addClass('is--open');
			currentContext.addClass('is--open');
			body.addClass('header--open');
			($(e.currentTarget).attr('data-trigger') === 'nav--open') ? header.addClass('nav--open') : header.addClass('search--open');
		}
		// if header is already open but clicked context not, show clicked, hide other, keep header
		else if(header.hasClass('is--open') && !currentContext.hasClass('is--open')){
			// console.log('open this, close other, keep header');
			header.hasClass('nav--open') ? header.removeClass('nav--open').addClass('search--open') : header.removeClass('search--open').addClass('nav--open')
			currentContext.addClass('is--open');
			otherContext.removeClass('is--open');
		}
		// if header is already open and clicked context, close everything
		else {
			// console.log('close all');
			header.removeClass('nav--open search--open');
			currentContext.removeClass('is--open');
			otherContext.removeClass('is--open');
			header.removeClass('is--open');
			body.removeClass('header--open')
		}
	}

	$(window).on('resize', function(){
		$([body, header, header_nav, header_search]).removeClass('header--open is--open nav--open search--open');
	});

	// 1. Exchange content when clicking between menu & search and menu is already opened.
	// 2. Make sure, only one of the two (menu or search) is shown.
	// $(document).on('click', function(e){
	// 	if(!$(e.target).is(header_navtrigger,header_searchtrigger) && $(e.target).closest(header_nav, header_search).length == 0){
	// 		$([body, header, header_nav, header_search]).removeClass('is--open');
	// 	}
	// 	if($(e.target).is('.main-header__nav a')){
	// 		$('.main-header__nav a').removeClass('active');
	// 		$(e.target).addClass('active');
	// 		$([body, header, header_nav, header_search]).removeClass('is--open');
	// 	}
	// });
	// // $(document).on('scroll', function(){
	// // 	if ($(document).scrollTop() > 50) {
	// // 		header.addClass('is-white');
	// // 	}else {
	// // 		header.removeClass('is-white');
	// // 	}
	// // })
});
