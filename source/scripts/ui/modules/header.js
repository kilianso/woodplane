const body = document.querySelector('body');
const header = document.querySelector('.MainHeader');

body.classList.remove('preload');

// on auth pages, we don't have a header like this.
if (header) {
	const header_nav = header.querySelector('.MainHeader__nav');
	const header_navtrigger = header.querySelector('.MainHeader__navtrigger');
	const header_search = header.querySelector('.MainHeader__search');
	const header_searchtrigger = header.querySelector('.MainHeader__searchtrigger');

	if (header_searchtrigger) {
		header_searchtrigger.addEventListener('click', function (event) {
			event.preventDefault();
			showAndHide(event, header_search, header_nav);
		});
	}

	if (header_navtrigger) {
		header_navtrigger.addEventListener('click', function (event) {
			event.preventDefault();
			showAndHide(event, header_nav, header_search);
		});
	}

	function showAndHide(e, currentContext, otherContext) {
		if (!currentContext || !otherContext) return;

		// if header is not open and search is not open
		if (!header.classList.contains('is--open') && !currentContext.classList.contains('is--open')) {
			header.classList.add('is--open');
			currentContext.classList.add('is--open');
			body.classList.add('header--open');
			(e.currentTarget.getAttribute('data-trigger') === 'nav--open') ? header.classList.add('nav--open') : header.classList.add('search--open');
		}
		// if header is already open but clicked context not, show clicked, hide other, keep header
		else if (header.classList.contains('is--open') && !currentContext.classList.contains('is--open')) {
			if (header.classList.contains('nav--open')) {
				header.classList.remove('nav--open');
				header.classList.add('search--open');
			} else {
				header.classList.remove('search--open');
				header.classList.add('nav--open');
			}
			currentContext.classList.add('is--open');
			otherContext.classList.remove('is--open');
		}
		// if header is already open and clicked context, close everything
		else {
			closeAll();
		}
	}

	function closeAll() {
		header.classList.remove('nav--open', 'search--open');
		if (header_search) header_search.classList.remove('is--open');
		if (header_nav) header_nav.classList.remove('is--open');
		header.classList.remove('is--open');
		body.classList.remove('header--open');
	}

	window.addEventListener('resize', function () {
		closeAll();
	});
}
