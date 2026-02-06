function setActiveSidebarLink() {
	var currentPath = window.location.pathname.replace(/^\//, '');
	$('.sidebar .nav-item').removeClass('active');
	$('.sidebar .nav-item.has-submenu').removeClass('open');
	$('.sidebar .submenu').removeClass('open');

	$('.sidebar .nav-item[href]').each(function() {
		var linkPath = $(this).attr('href').replace(/^\//, '');
		if (linkPath && currentPath.indexOf(linkPath) !== -1) {
			$(this).addClass('active');
			var submenu = $(this).closest('.submenu');
			if (submenu.length) {
				submenu.addClass('open');
				var parentMenuItem = submenu.prev('.nav-item.has-submenu');
				parentMenuItem.addClass('open');
				
				var grandParentSubmenu = parentMenuItem.closest('.submenu');
				if (grandParentSubmenu.length) {
					grandParentSubmenu.addClass('open');
					grandParentSubmenu.prev('.nav-item.has-submenu').addClass('open');
				}
			}
		}
	});
}

function filterSidebarMenu(filterText) {
	if (!filterText) {
		$('.sidebar .nav-item, .sidebar .submenu').show();
		return;
	}

	$('.sidebar .nav-item').hide();
	$('.sidebar .submenu').hide();

	$('.sidebar .nav-item').each(function() {
		var $item = $(this);
		var text = $item.text().toLowerCase();
		
		if (text.indexOf(filterText) !== -1) {
			$item.show();
			
			var $parentSubmenu = $item.closest('.submenu');
			if ($parentSubmenu.length) {
				$parentSubmenu.show();
				var $parentMenuItem = $parentSubmenu.prev('.nav-item.has-submenu');
				$parentMenuItem.show();
				
				var $grandParentSubmenu = $parentMenuItem.closest('.submenu');
				if ($grandParentSubmenu.length) {
					$grandParentSubmenu.show();
					$grandParentSubmenu.prev('.nav-item.has-submenu').show();
				}
			}
			
			if ($item.hasClass('has-submenu')) {
				var $childSubmenu = $item.next('.submenu');
				if ($childSubmenu.length) {
					$childSubmenu.show();
					$childSubmenu.find('.nav-item').each(function() {
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
		$('.sidebar_filtre_reset').addClass('show');
	} else {
		$('.sidebar_filtre_reset').removeClass('show');
	}
}

$(document).ready(function() {
	setActiveSidebarLink();
	toggleResetButton($('.sidebar_menu_filtre').val());

	const isPinned = localStorage.getItem('sidebarPinned') === 'true';
	if (isPinned) {
		$('.pin-sidebar').addClass('active');
		$('.sidebar').addClass('fixed');
		$('.main-content').css('margin-left', '220px');
	}

	$(document).on('click', '.pin-sidebar', function() {
		$(this).toggleClass('active');
		$('.sidebar').toggleClass('fixed');

		const isFixed = $('.sidebar').hasClass('fixed');
		localStorage.setItem('sidebarPinned', isFixed);

		if (isFixed) {
			$('.main-content').css('margin-left', '220px');
		} else {
			$('.main-content').css('margin-left', '50px');
			$('.sidebar .nav-item.has-submenu').removeClass('open');
			$('.sidebar .submenu').removeClass('open');
		}
	});

	$(document).on('click', '.sidebar .nav-item.has-submenu', function(e) {
		e.stopPropagation(); 
		const currentSubmenu = $(this).next('.submenu');
		const wasOpen = $(this).hasClass('open');

		if ($(this).hasClass('level-2')) {
			$(this).closest('.submenu').find('.level-2.nav-item').not(this).removeClass('open');
			$(this).closest('.submenu').find('.level-2.submenu').not(currentSubmenu).removeClass('open');
		} else {
			$('.sidebar .nav-item.has-submenu').removeClass('open');
			$('.sidebar .submenu').removeClass('open');
		}

		if (!wasOpen) {
			$(this).addClass('open');
			currentSubmenu.addClass('open');

			if (!$(this).hasClass('level-2')) {
				$(this).closest('.submenu').addClass('open');
				$(this).closest('.submenu').prev('.nav-item.has-submenu').addClass('open');
			}
		}
	});

	$(document).on('input', '.sidebar_menu_filtre', function(e){
		var filterText = $(this).val().toLowerCase().trim();
		filterSidebarMenu(filterText);
		toggleResetButton(filterText);
	});

	$(document).on('click', '.sidebar_filtre_reset', function(e) {
		e.preventDefault();
		$('.sidebar_menu_filtre').val('');
		filterSidebarMenu('');
		toggleResetButton('');
		$('.sidebar_menu_filtre').focus();
	});

	$(document).on('click', '.sidebar .nav-item:not(.has-submenu)', function(e) {
		$('.sidebar .nav-item').removeClass('active');
		$(this).addClass('active');
	});

	$('.sidebar').on('mouseleave', function() {
		if (!$(this).hasClass('fixed')) {
			$('.sidebar .nav-item.has-submenu').removeClass('open');
			$('.sidebar .submenu').removeClass('open');
		}
	});
});