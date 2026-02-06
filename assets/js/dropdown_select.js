(function () {
	'use strict';
	class WSDropdown {
		constructor(element) {
			this.dropdown = element;
			this.toggle = element.querySelector('.ws_dropdown_toggle');
			this.menu = element.querySelector('.ws_dropdown_menu');
			this.items = element.querySelectorAll('.ws_dropdown_item:not(.ws_disabled)');
			this.isOpen = false;

			this.init();
		}

		init() {
			this.toggle.addEventListener('click', (e) => {
				e.stopPropagation();
				this.toggleDropdown();
			});
			this.items.forEach(item => {
				item.addEventListener('click', (e) => {
					this.selectItem(item);
				});
			});
			document.addEventListener('click', (e) => {
				if (this.isOpen && !this.dropdown.contains(e.target)) {
					this.closeDropdown();
				}
			});
			document.addEventListener('keydown', (e) => {
				if (e.key === 'Escape' && this.isOpen) {
					this.closeDropdown();
					this.toggle.focus();
				}
			});
		}

		toggleDropdown() {
			this.isOpen ? this.closeDropdown() : this.openDropdown();
		}

		openDropdown() {
			document.querySelectorAll('.ws_dropdown.ws_active').forEach(dd => {
				if (dd !== this.dropdown) {
					dd.classList.remove('ws_active');
				}
			});

			this.dropdown.classList.add('ws_active');
			this.isOpen = true;
			this.toggle.setAttribute('aria-expanded', 'true');
		}

		closeDropdown() {
			this.dropdown.classList.remove('ws_active');
			this.isOpen = false;
			this.toggle.setAttribute('aria-expanded', 'false');
		}

		selectItem(item) {
			this.items.forEach(i => i.classList.remove('ws_selected'));
			item.classList.add('ws_selected');
			this.closeDropdown();
			const event = new CustomEvent('ws-dropdown-select', {
				detail: {
					value: item.getAttribute('data-value'),
					text: item.textContent.trim(),
					element: item
				}
			});
			this.dropdown.dispatchEvent(event);
		}
	}

	function initDropdowns() {
		const dropdowns = document.querySelectorAll('[data-ws-dropdown]');
		dropdowns.forEach(dropdown => {
			new WSDropdown(dropdown);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initDropdowns);
	} else {
		initDropdowns();
	}

	/*document.addEventListener('ws-dropdown-select', function (e) {
		console.log('Dropdown selected:', {
			value: e.detail.value,
			text: e.detail.text
		});
	});*/

})();