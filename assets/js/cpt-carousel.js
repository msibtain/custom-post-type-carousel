/* global tns */
(function() {
	function initCarousels() {
		console.log('CPT Carousel: Initializing carousels');
		var carousels = document.querySelectorAll('.cpt-carousel');
		if (!carousels || carousels.length === 0) return;

		carousels.forEach(function(wrapper){
			var optionsRaw = wrapper.getAttribute('data-tns-options');
			var options = {};
			try {
				options = optionsRaw ? JSON.parse(optionsRaw) : {};
			} catch (e) {
				options = {};
			}

			var track = wrapper.querySelector('.cpt-carousel-track');
			if (!track) return;

			console.log('CPT Carousel: Options:', options);

			// Start with default options, then merge with PHP options
			var merged = Object.assign({
				container: track,
				controlsText: ['\u2039', '\u203A'],
				autoplayButtonOutput: false,
				responsive: {
					0: { items: Math.min(options.items || 1, 1) },
					480: { items: Math.max(1, Math.min(2, options.items || 2)) },
					768: { items: Math.max(1, options.items || 3) }
				}
			}, options);

			// Ensure container is always the DOM element
			merged.container = track;

			console.log('CPT Carousel: Container element:', track);
			console.log('CPT Carousel: Container children count:', track.children.length);
			console.log('CPT Carousel: Final merged options:', merged);

			try {
				var slider = tns(merged);
				console.log('CPT Carousel: Successfully initialized slider:', slider);
			} catch (err) {
				console.error('CPT Carousel: Error initializing carousel:', err);
			}
		});
	}

	// Wait for Tiny Slider to load
	function waitForTinySlider() {
		if (typeof tns !== 'undefined' && typeof tns === 'function') {
			console.log('CPT Carousel: Tiny Slider loaded, initializing carousels');
			initCarousels();
		} else {
			console.log('CPT Carousel: Waiting for Tiny Slider to load...');
			setTimeout(waitForTinySlider, 100);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', waitForTinySlider);
	} else {
		waitForTinySlider();
	}
})();


