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
		if (!$(this).hasClass('fixed')) {
			$('.ws_sidebar .ws_nav-item.ws_has-submenu').removeClass('open');
			$('.ws_sidebar .ws_submenu').removeClass('open');
		}
	});
});
