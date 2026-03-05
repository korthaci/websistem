$(document).ready(function () {

	document.addEventListener('mousedown', function (e) {
		const editor = e.target.closest('.jodit_editor');
		if (!editor) return;

		const a = e.target.closest('a[href^="#"]');
		if (!a) return;

		if (e.ctrlKey || e.metaKey) return;

		e.preventDefault();
		e.stopPropagation();
	}, true);


	let pasteModalEnabled = false;

	Jodit.defaultOptions.controls.pasteModalToggle = {
		icon: '<svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M19,20H5V4H7V7H17V4H19M12,2A1,1 0 0,1 13,3A1,1 0 0,1 12,4A1,1 0 0,1 11,3A1,1 0 0,1 12,2M19,2H14.82C14.4,0.84 13.3,0 12,0C10.7,0 9.6,0.84 9.18,2H5A2,2 0 0,0 3,4V20A2,2 0 0,0 5,22H19A2,2 0 0,0 21,20V4A2,2 0 0,0 19,2Z"/></svg>',
		tooltip: 'Yapıştırma Modal Kapalı',
		exec: function (editor) {
			pasteModalEnabled = !pasteModalEnabled;
			editor.o.processPasteHTML = pasteModalEnabled;
		},
		isActive: function () {
			return pasteModalEnabled;
		}
	};

	const getDefaultJoditOptions = function (isInline) {
		const pageHasNoToolbar = !!document.querySelector('.jodit_no_toolbar');

		return {
			autofocus: true,
			language: "tr",
			enter: "BR",
			showCharsCounter: false,
			showWordsCounter: false,
			showXPathInStatusbar: false,
			saveHeightInStorage: false,
			saveModeInStorage: true,
			askBeforePasteHTML: false,
			defaultActionOnPaste: Jodit.constants.INSERT_ONLY_TEXT,
			processPasteHTML: false,
			nl2brInPlainText: true,
			disablePlugins: "about,print,file,speech-recognize,spellcheck,powered-by-jodit,ai-assistant",
			height: 'auto',
			minHeight: "600px",
			minWidth: 400,
			inline: isInline,
			toolbar: !pageHasNoToolbar,
			toolbarInlineForSelection: !pageHasNoToolbar,
			showPlaceholder: false,
			toolbarAdaptive: true,
			colorPickerDefaultTab: 'color',
			replaceNBSP: false,
			addNewLine: false,
			removeEmptyTags: false,
			allowedContent: "*[*]",
			invalidElements: "script,embed,object,base",
			cleanHTML: false,
			toolbarSticky: !pageHasNoToolbar,
			toolbarStickyOffset: 0,
			toolbarDisableStickyForMobile: false,
			statusbar: !pageHasNoToolbar,
			sanitizeHTML: false,
			link: {
				follow: false
			},

			buttons: [
				'bold', 'italic', 'underline', 'brush', 'font', 'fontsize',
				'align', 'outdent', 'indent', 'ul', 'ol',
				'link', 'image', 'table', 'hr',
				'lineHeight', 'preview', 'paragraph',
				'find', 'pasteModalToggle', 'source'
			],

			popup: {
				selection: Jodit.atom([
					'bold', 'italic', 'brush', 'font', 'fontsize', 'paragraph', 'align'
				])
			},

			events: {
				afterInit: function (editor) {
					window.unsavedChanges = false;

					const parentForm = editor.element.closest('form');
					if (parentForm) {
						parentForm.setAttribute('novalidate', 'novalidate');
					}

					const doc = editor.iframe
						? editor.iframe.contentDocument
						: editor.editor.ownerDocument;

					doc.addEventListener('click', function (e) {
						const a = e.target.closest('a[href^="#"]');
						if (!a) return;

						if (e.ctrlKey || e.metaKey) return;

						e.preventDefault();
						e.stopPropagation();
					}, true);

					if (editor.element.classList.contains('format_html')) {
						let html = editor.value;
						editor.value = formatHTML(html);
					}

					if (editor.element.classList.contains('jodit_code')) {
						editor.setMode(Jodit.constants.MODE_SOURCE);
					}

					editor.editor.addEventListener('keydown', function (e) {
						if (/^[a-zA-Z0-9\s\p{P}]$/u.test(e.key)) {
							window.unsavedChanges = true;
						}
					});
				}
			}

		};
	};

	window.joditEkle = function (selector, customOptions = {}) {
		const elements = document.querySelectorAll(selector);
		if (elements.length === 0) return null;

		const editors = [];
		elements.forEach(function (el) {
			if (el.classList.contains('jodit') || el.classList.contains('jodit_initialized')) {
				return;
			}

			const isInline = el.getAttribute('data-inline');
			const defaultOptions = getDefaultJoditOptions(isInline);

			if (el.classList.contains('no_toolbar')) {
				defaultOptions.toolbar = false;
			}

			const jodit_options = { ...defaultOptions, ...customOptions };
			const editor = new Jodit(el, jodit_options);

			el.classList.add('jodit_initialized');
			editors.push(editor);
		});

		return editors.length === 1 ? editors[0] : editors;
	};

	document.querySelectorAll('.jodit_editor').forEach(function (el) {
		$('.jodit_editor').removeClass('dyok');

		if (!el.classList.contains('jodit_initialized')) {
			const isInline = el.getAttribute('data-inline');
			new Jodit(el, getDefaultJoditOptions(isInline));
			el.classList.add('jodit_initialized');
		}
	});

	let askUnsavedChanges = localStorage.getItem('askUnsavedChanges') === 'true';

	window.addEventListener('beforeunload', function (e) {
		if (window.unsavedChanges && askUnsavedChanges) {
			e.preventDefault();
			e.returnValue = '';
		}
	});

	$(document).on('click', '[data-ask-unsaved-changes]', function () {
		askUnsavedChanges = $(this).data('ask-unsaved-changes');
		localStorage.setItem('askUnsavedChanges', askUnsavedChanges);
	});

	$(document).on('click', '[data-jodit_ekle]', function () {
		let element = $(this).attr('data-jodit_ekle');
		let $element = $(element);

		if ($element.length > 0) {
			if (!$element[0].__jodit && !$element.hasClass('jodit_initialized')) {
				window.joditEkle(element, {
					width: $element.width() || '100%',
					inline: $element.attr('data-inline') !== undefined
				});
			}
		}
	});

});
