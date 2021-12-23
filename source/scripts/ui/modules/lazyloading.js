document.addEventListener("DOMContentLoaded", function() {
  var lazyImages = [].slice.call(document.querySelectorAll("img.lazy"));


	if (!window.IntersectionObserver) {
		lazyImages.forEach(function(image) {
			image.setAttribute("src", image.getAttribute("data-src"));
			image.setAttribute("srcset", image.getAttribute("data-srcset"));
		})
	}
	else {
		// Do regular loading with IntersectionObserver
			var lazyImageObserver = new IntersectionObserver(function(entries) {
				entries.forEach(function(entry) {
					if (entry.isIntersecting) {
						var lazyImage = entry.target;
						lazyImage.src = lazyImage.dataset.src;
						lazyImage.srcset = lazyImage.dataset.srcset;
						lazyImage.classList.remove("lazy");
						lazyImageObserver.unobserve(lazyImage);
					}
				});
			});

			lazyImages.forEach(function(lazyImage) {
				lazyImageObserver.observe(lazyImage);
			});
	}
});
