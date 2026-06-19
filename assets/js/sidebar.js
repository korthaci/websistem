function setActiveSidebarLink() {
	var currentPath = window.location.pathname.replace(/^\//, '');
	$('.ws_sidebar .ws_nav-item').removeClass('active');
	$('.ws_sidebar .ws_nav-item.ws_has-submenu').removeClass('open');
	$('.ws_sidebar .ws_submenu').removeClass('open');

	$('.ws_sidebar .ws_nav-item[href]').each(function() {
		var linkPath = $(this).attr('href').replace(/^\//, '');
		if (linkPath && currentPath.indexOf(linkPath) !== -1) {
			$(this).addClass('active');
			var submenu = $(this).closest('.ws_submenu');
			if (submenu.length) {
				submenu.addClass('open');
				var parentMenuItem = submenu.prev('.ws_nav-item.ws_has-submenu');
				parentMenuItem.addClass('open');
				
				var grandParentSubmenu = parentMenuItem.closest('.ws_submenu');
				if (grandParentSubmenu.length) {
					grandParentSubmenu.addClass('open');
					grandParentSubmenu.prev('.ws_nav-item.ws_has-submenu').addClass('open');
				}
			}
		}
	});
}

function filterSidebarMenu(filterText) {
	if (!filterText) {
		$('.ws_sidebar .ws_nav-item, .ws_sidebar .ws_submenu').show();
		return;
	}

	$('.ws_sidebar .ws_nav-item').hide();
	$('.ws_sidebar .ws_submenu').hide();

	$('.ws_sidebar .ws_nav-item').each(function() {
		var $item = $(this);
		var text = $item.text().toLowerCase();
		
		if (text.indexOf(filterText) !== -1) {
			$item.show();
			
			var $parentSubmenu = $item.closest('.ws_submenu');
			if ($parentSubmenu.length) {
				$parentSubmenu.show();
				var $parentMenuItem = $parentSubmenu.prev('.ws_nav-item.ws_has-submenu');
				$parentMenuItem.show();
				
				var $grandParentSubmenu = $parentMenuItem.closest('.ws_submenu');
				if ($grandParentSubmenu.length) {
					$grandParentSubmenu.show();
					$grandParentSubmenu.prev('.ws_nav-item.ws_has-submenu').show();
				}
			}
			
			if ($item.hasClass('ws_has-submenu')) {
				var $childSubmenu = $item.next('.ws_submenu');
				if ($childSubmenu.length) {
					$childSubmenu.show();
					$childSubmenu.find('.ws_nav-item').each(function() {
						var $childItem = $(this);
						var childText = $childItem.text().toLowerCase();
						if (childText.indexOf(filterText) !== -1) {
							$childItem.show();
						}
					});
				}
			}
		}
	});
}

function toggleResetButton(filterText) {
	if (filterText && filterText.length > 0) {
		$('.ws_sidebar_filtre_reset').addClass('show');
	} else {
		$('.ws_sidebar_filtre_reset').removeClass('show');
	}
}

$(document).ready(function() {
	setActiveSidebarLink();
	toggleResetButton($('.ws_sidebar_menu_filtre').val());

	const isPinned = localStorage.getItem('sidebarPinned') === 'true';
	if (isPinned) {
		$('.ws_pin-sidebar').addClass('active');
		$('.ws_sidebar').addClass('fixed');
		$('.main-content').css('margin-left', '220px');
	}

	$(document).on('click', '.ws_pin-sidebar', function() {
		/* Mobil pin kontrolü başlangıcı */
		if (window.matchMedia('(max-width: 768px)').matches) return;
		/* Mobil pin kontrolü sonu */
		$(this).toggleClass('active');
		$('.ws_sidebar').toggleClass('fixed');

		const isFixed = $('.ws_sidebar').hasClass('fixed');
		localStorage.setItem('sidebarPinned', isFixed);

		if (isFixed) {
			$('.main-content').css('margin-left', '220px');
		} else {
			$('.main-content').css('margin-left', '50px');
			$('.ws_sidebar .ws_nav-item.ws_has-submenu').removeClass('open');
			$('.ws_sidebar .ws_submenu').removeClass('open');
		}
	});

	$(document).on('click', '.ws_sidebar .ws_nav-item.ws_has-submenu', function(e) {
		e.stopPropagation(); 
		const currentSubmenu = $(this).next('.ws_submenu');
		const wasOpen = $(this).hasClass('open');

		if ($(this).hasClass('level-2')) {
			$(this).closest('.ws_submenu').find('.level-2.ws_nav-item').not(this).removeClass('open');
			$(this).closest('.ws_submenu').find('.level-2.ws_submenu').not(currentSubmenu).removeClass('open');
		} else {
			$('.ws_sidebar .ws_nav-item.ws_has-submenu').removeClass('open');
			$('.ws_sidebar .ws_submenu').removeClass('open');
		}

		if (!wasOpen) {
			$(this).addClass('open');
			currentSubmenu.addClass('open');

			if (!$(this).hasClass('level-2')) {
				$(this).closest('.ws_submenu').addClass('open');
				$(this).closest('.ws_submenu').prev('.ws_nav-item.ws_has-submenu').addClass('open');
			}
		}
	});

	$(document).on('input', '.ws_sidebar_menu_filtre', function(e){
		var filterText = $(this).val().toLowerCase().trim();
		filterSidebarMenu(filterText);
		toggleResetButton(filterText);
	});

	$(document).on('click', '.ws_sidebar_filtre_reset', function(e) {
		e.preventDefault();
		$('.ws_sidebar_menu_filtre').val('');
		filterSidebarMenu('');
		toggleResetButton('');
		$('.ws_sidebar_menu_filtre').focus();
	});

	$(document).on('click', '.ws_sidebar .ws_nav-item:not(.ws_has-submenu)', function(e) {
		$('.ws_sidebar .ws_nav-item').removeClass('active');
		$(this).addClass('active');
	});

	$('.ws_sidebar').on('mouseleave', function() {
		/* Mobil mouseleave kontrolü başlangıcı */
		if (window.matchMedia('(max-width: 768px)').matches) return;
		/* Mobil mouseleave kontrolü sonu */
		if (!$(this).hasClass('fixed')) {
			$('.ws_sidebar .ws_nav-item.ws_has-submenu').removeClass('open');
			$('.ws_sidebar .ws_submenu').removeClass('open');
		}
	});
	/*Mobil touch başlangıcı*/
	(function() {
		'use strict';

		const mobileQuery = window.matchMedia('(max-width: 768px)');
		let initialized = false;
		let bodyLocked = false;
		let scrollOffset = 0;
		let previousBodyStyles = null;
		let $sidebar;
		let $overlay;
		let $edgeZone;

		function lockBodyScroll() {
			if (bodyLocked) return;
			scrollOffset = window.scrollY || window.pageYOffset;
			previousBodyStyles = {
				position: document.body.style.position,
				top: document.body.style.top,
				width: document.body.style.width
			};
			document.body.style.position = 'fixed';
			document.body.style.top = '-' + scrollOffset + 'px';
			document.body.style.width = '100%';
			$('body').addClass('ws_sidebar-mobile-open');
			bodyLocked = true;
		}

		function unlockBodyScroll() {
			if (!bodyLocked) return;
			document.body.style.position = previousBodyStyles.position;
			document.body.style.top = previousBodyStyles.top;
			document.body.style.width = previousBodyStyles.width;
			$('body').removeClass('ws_sidebar-mobile-open');
			bodyLocked = false;
			window.scrollTo(0, scrollOffset);
		}

		function openMobileSidebar() {
			if (!initialized || !mobileQuery.matches) return;
			$sidebar.addClass('open').attr('aria-hidden', 'false');
			$overlay.addClass('active').attr('aria-hidden', 'false');
			lockBodyScroll();
		}

		function closeMobileSidebar() {
			if (!$sidebar || !$sidebar.length) return;
			if ($sidebar[0].contains(document.activeElement)) document.activeElement.blur();
			$sidebar.removeClass('open').attr('aria-hidden', 'true');
			if ($overlay && $overlay.length) $overlay.removeClass('active').attr('aria-hidden', 'true');
			unlockBodyScroll();
		}

		function disablePinnedState() {
			$('.ws_pin-sidebar').removeClass('active');
			$sidebar.removeClass('fixed');
			$('.main-content').css('margin-left', '');
		}

		function restorePinnedState() {
			const isPinned = localStorage.getItem('sidebarPinned') === 'true';
			$('.ws_pin-sidebar').toggleClass('active', isPinned);
			$sidebar.toggleClass('fixed', isPinned).removeAttr('aria-hidden');
			$('.main-content').css('margin-left', isPinned ? '220px' : '50px');
		}

		function init() {
			if (initialized || !mobileQuery.matches) return;
			$sidebar = $('.ws_sidebar').first();
			if (!$sidebar.length) return;

			initialized = true;
			disablePinnedState();
			$sidebar.attr('aria-hidden', 'true');
			$overlay = $('<div class="ws_overlay" aria-hidden="true"></div>').insertAfter($sidebar);
			$edgeZone = $('<div class="ws_sidebar_edge_zone" aria-hidden="true"></div>').appendTo('body');

			let startX = 0;
			let startY = 0;
			let currentX = 0;
			let currentY = 0;
			let tracking = false;
			let suppressClickUntil = 0;

			$edgeZone.on('click.mobileSidebar', function(e) {
				if (Date.now() < suppressClickUntil) return;
				e.preventDefault();
				openMobileSidebar();
			});

			$edgeZone.on('touchstart.mobileSidebar', function(e) {
				const touch = e.originalEvent.touches[0];
				if (!touch || $sidebar.hasClass('open')) return;
				startX = currentX = touch.clientX;
				startY = currentY = touch.clientY;
				tracking = true;
			});

			$edgeZone.on('touchmove.mobileSidebar', function(e) {
				if (!tracking) return;
				const touch = e.originalEvent.touches[0];
				if (!touch) return;
				currentX = touch.clientX;
				currentY = touch.clientY;
				if (Math.abs(currentY - startY) > Math.abs(currentX - startX) && Math.abs(currentY - startY) > 10) tracking = false;
			});

			$edgeZone.on('touchend.mobileSidebar', function(e) {
				if (!tracking) return;
				const touch = e.originalEvent.changedTouches[0];
				if (touch) {
					currentX = touch.clientX;
					currentY = touch.clientY;
				}
				const deltaX = currentX - startX;
				const deltaY = currentY - startY;
				tracking = false;
				if (deltaX >= 50 && Math.abs(deltaX) > Math.abs(deltaY)) {
					suppressClickUntil = Date.now() + 500;
					openMobileSidebar();
				}
			});

			$edgeZone.on('touchcancel.mobileSidebar', function() { tracking = false; });
			$overlay.on('click.mobileSidebar', closeMobileSidebar);
			$sidebar.on('click.mobileSidebar', 'a.ws_nav-item[href]', closeMobileSidebar);
			$(document).on('keydown.mobileSidebar', function(e) {
				if (e.key === 'Escape' && $sidebar.hasClass('open')) closeMobileSidebar();
			});
		}

		function destroy() {
			if (!initialized) return;
			closeMobileSidebar();
			$(document).off('.mobileSidebar');
			$sidebar.off('.mobileSidebar');
			$overlay.off('.mobileSidebar').remove();
			$edgeZone.off('.mobileSidebar').remove();
			restorePinnedState();
			initialized = false;
		}

		function handleBreakpointChange(e) {
			if (e.matches) init();
			else destroy();
		}

		if (mobileQuery.addEventListener) mobileQuery.addEventListener('change', handleBreakpointChange);
		else mobileQuery.addListener(handleBreakpointChange);
		init();
	})();
	/* Mobil touch sonu*/
});
