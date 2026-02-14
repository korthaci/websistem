/* ============================================
    DOCUMENTATION SYSTEM JAVASCRIPT
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {
    const currentNav = window.PAGE_NAV || autoGenerateNavStructure();

    initNavigation(currentNav);
    initGlobalHeader();
    initLanguageSwitcher();
    initCopyButtons();
    initSearch();
    initScrollSpy();
    initSmoothScroll();
    initEventDelegation();
    initSmartHeader();
});

/* ============================================
    COPY BUTTON FOR CODE BLOCKS
   ============================================ */

function initCopyButtons() {
    const codeHeaders = document.querySelectorAll('.code-header');

    codeHeaders.forEach(header => {
        const copyBtn = document.createElement('button');
        copyBtn.className = 'copy-btn';
        copyBtn.textContent = '⧉';

        copyBtn.addEventListener('click', () => {
            const pre = header.nextElementSibling;
            if (pre && pre.tagName === 'PRE') {
                const code = pre.querySelector('code');
                const textToCopy = code ? code.textContent : pre.textContent;

                navigator.clipboard.writeText(textToCopy).then(() => {
                    copyBtn.textContent = '✓';
                    copyBtn.classList.add('copied');

                    setTimeout(() => {
                        copyBtn.textContent = '⧉';
                        copyBtn.classList.remove('copied');
                    }, 6000);
                });
            }
        });

        header.appendChild(copyBtn);
    });
}

/* ============================================
    GLOBAL HEADER NAVIGATION
   ============================================ */

function initGlobalHeader() {
    const headerNav = document.querySelector('.header-nav');
    if (!headerNav) return;

    const isEnglish = /\/en\//i.test(window.location.pathname);

    const menuItems = isEnglish
        ? [
            { title: 'Getting Started', href: 'getting-started.html' },
            { title: 'Installation', href: 'installation.html' },
            { title: 'Usage', href: 'usage.html' },
            { title: 'Advanced', href: 'advanced.html' },
            { title: 'Capabilities', href: 'capabilities.html' },
            { title: 'Changelog', href: 'changelog.html' },
            { title: 'Roadmap', href: 'roadmap.html' },
            { title: 'FAQ', href: 'faq.html' }
        ]
        : [
            { title: 'Başlangıç', href: 'getting-started.html' },
            { title: 'Kurulum', href: 'installation.html' },
            { title: 'Kullanım', href: 'usage.html' },
            { title: 'Gelişmiş', href: 'advanced.html' },
            { title: 'Neler Yapılabilir?', href: 'capabilities.html' },
            { title: 'Değişiklikler', href: 'changelog.html' },
            { title: 'Roadmap', href: 'roadmap.html' },
            { title: 'SSS', href: 'faq.html' }
        ];

    const path = window.location.pathname;
    const page = path.split('/').pop() || 'index.html';

    headerNav.innerHTML = '';

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'header-menu-close';
    closeBtn.dataset.onclickFn = 'closeHeaderMenu';
    closeBtn.textContent = isEnglish ? 'Close menu' : 'Menüyü kapat';
    headerNav.appendChild(closeBtn);

    menuItems.forEach(item => {
        const a = document.createElement('a');
        a.href = item.href;
        a.className = 'header-nav-link';
        if (page === item.href) {
            a.classList.add('active');
        }
        a.textContent = item.title;
        headerNav.appendChild(a);
    });
}

function autoGenerateNavStructure() {
    const content = document.getElementById('content') || document.getElementById('mainContent');
    if (!content) return [];

    const headings = content.querySelectorAll('h1, h2, h3');
    const structure = [];
    let currentH1 = null;
    let currentH2 = null;

    headings.forEach(h => {
        const id = h.id || h.textContent.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-');
        if (!h.id) h.id = id;

        const item = { title: h.textContent.trim(), id: id, children: [] };

        if (h.tagName === 'H1') {
            currentH1 = item;
            structure.push(item);
            currentH2 = null;
        } else if (h.tagName === 'H2') {
            if (currentH1) {
                currentH1.children.push(item);
                currentH2 = item;
            } else {
                structure.push(item);
            }
        } else if (h.tagName === 'H3') {
            if (currentH2) {
                currentH2.children.push(item);
            } else if (currentH1) {
                currentH1.children.push(item);
            } else {
                structure.push(item);
            }
        }
    });

    return structure;
}

/* ============================================
    SMART HEADER (Show/Hide on Scroll)
   ============================================ */

function initSmartHeader() {
    let lastScrollTop = 0;
    const header = document.querySelector('.main-header');
    const threshold = 10;

    window.addEventListener('scroll', () => {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (Math.abs(lastScrollTop - scrollTop) <= threshold) return;

        if (scrollTop > lastScrollTop && scrollTop > 100) {
            header.classList.add('header-hidden');
        } else {
            header.classList.remove('header-hidden');
        }

        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    }, { passive: true });
}

/* ============================================
    EVENT DELEGATION
   ============================================ */

function initEventDelegation() {
    document.addEventListener('click', (e) => {
        const target = e.target.closest('[data-onclick-fn]');
        if (target) {
            const fnName = target.dataset.onclickFn;
            if (typeof window[fnName] === 'function') {
                window[fnName](e, target);
            }
        }
    });
}

/* ============================================
    NAVIGATION SYSTEM
   ============================================ */

function initNavigation(structure) {
    const nav = document.getElementById('nav');
    if (!nav) return;
    nav.innerHTML = '';

    structure.forEach(item => {
        if (!item.id && item.children && item.children.length > 0) {
            const sectionTitle = document.createElement('div');
            sectionTitle.className = 'nav-section-title';
            sectionTitle.textContent = item.title;
            nav.appendChild(sectionTitle);

            item.children.forEach(child => {
                createNavLink(child, nav, 0);
            });
        } else {
            createNavLink(item, nav, 0);
        }
    });
}

function createNavLink(item, parent, depth) {
    const link = document.createElement('a');
    link.href = `#${item.id}`;
    link.className = 'nav-link';
    link.textContent = item.title;
    link.dataset.target = item.id;
    link.dataset.depth = depth;

    link.addEventListener('click', (e) => {
        e.preventDefault();
        scrollToSection(item.id);
        if (window.innerWidth <= 768) {
            toggleSidebar();
        }
    });

    parent.appendChild(link);

    if (item.children && item.children.length > 0) {
        const childrenContainer = document.createElement('div');
        childrenContainer.className = 'nav-children';
        childrenContainer.dataset.parent = item.id;

        item.children.forEach(child => {
            createNavLink(child, childrenContainer, depth + 1);
        });

        parent.appendChild(childrenContainer);

        link.style.fontWeight = '900';
        link.addEventListener('click', (e) => {
            if (item.children) {
                e.preventDefault();
                const isExpanded = childrenContainer.style.display !== 'none';
                childrenContainer.style.display = isExpanded ? 'none' : 'block';
                link.style.opacity = isExpanded ? '1' : '0.8';
            }
        });
    }
}

/* ============================================
    SEARCH SYSTEM
   ============================================ */

let searchIndex = [];

function initSearch() {
    buildSearchIndex();

    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');
    const searchResults = document.getElementById('searchResults');
    if (!searchInput || !clearBtn || !searchResults) return;

    let searchTimeout;
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();

        if (query.length > 0) {
            clearBtn.classList.add('visible');
            searchTimeout = setTimeout(() => performSearch(query), 150);
        } else {
            clearBtn.classList.remove('visible');
            hideSearchResults();
            clearHighlights();
        }
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.search-container')) {
            hideSearchResults();
        }
    });

    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            searchInput.focus();
        }
        if (e.key === 'Escape') {
            hideSearchResults();
            searchInput.blur();
        }
    });
}

function buildSearchIndex() {
    const content = document.getElementById('content');
    if (!content) return;

    const headings = content.querySelectorAll('h1, h2, h3, h4, p, li, td');

    headings.forEach((element, index) => {
        const text = element.textContent.trim();
        if (text.length > 3) {
            searchIndex.push({
                id: element.id || `result-${index}`,
                text: text.toLowerCase(),
                title: text.substring(0, 60),
                element: element,
                type: element.tagName.toLowerCase()
            });

            if (!element.id) {
                element.id = `result-${index}`;
            }
        }
    });
}

function performSearch(query) {
    const results = searchIndex.filter(item => item.text.includes(query.toLowerCase())).slice(0, 8);
    displaySearchResults(results, query);
    highlightMatches(query);
}

function displaySearchResults(results, query) {
    const container = document.getElementById('searchResults');
    if (!container) return;
    container.innerHTML = '';

    if (results.length === 0) {
        container.innerHTML = '<div class="no-results">No results found</div>';
    } else {
        results.forEach(result => {
            const div = document.createElement('div');
            div.className = 'search-result-item';

            const preview = result.text.length > 60
                ? result.text.substring(0, 60) + '...'
                : result.text;

            const highlightedPreview = preview.replace(
                new RegExp(`(${query})`, 'gi'),
                '<mark style="background: var(--search-highlight); padding: 0 2px;">$1</mark>'
            );

            div.innerHTML = `
                <div class="search-result-title">${result.title}</div>
                <div class="search-result-preview">${highlightedPreview}</div>
            `;

            div.addEventListener('click', () => {
                scrollToSection(result.id);
                hideSearchResults();
                document.getElementById('searchInput').value = '';
                clearHighlights();
            });

            container.appendChild(div);
        });
    }

    container.classList.add('active');
}

function hideSearchResults() {
    const results = document.getElementById('searchResults');
    if (results) results.classList.remove('active');
}

function clearSearch() {
    const input = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');
    if (input) input.value = '';
    if (clearBtn) clearBtn.classList.remove('visible');
    hideSearchResults();
    clearHighlights();
}

function highlightMatches(query) {
    clearHighlights();
    if (!query) return;

    const content = document.getElementById('content');
    const walker = document.createTreeWalker(content, NodeFilter.SHOW_TEXT, null, false);

    const textNodes = [];
    let node;
    while ((node = walker.nextNode())) {
        if (node.nodeValue.toLowerCase().includes(query.toLowerCase())) {
            textNodes.push(node);
        }
    }

    textNodes.forEach(textNode => {
        const span = document.createElement('span');
        const regex = new RegExp(`(${query})`, 'gi');
        span.innerHTML = textNode.nodeValue.replace(
            regex,
            '<mark class="search-highlight" style="background: var(--search-highlight); padding: 0 2px; border-radius: 2px;">$1</mark>'
        );
        textNode.parentNode.replaceChild(span, textNode);
    });
}

function clearHighlights() {
    const marks = document.querySelectorAll('mark.search-highlight');
    marks.forEach(mark => {
        const parent = mark.parentNode;
        parent.replaceChild(document.createTextNode(mark.textContent), mark);
        parent.normalize();
    });
}

/* ============================================
    SCROLL & NAVIGATION BEHAVIOR
   ============================================ */

function initScrollSpy() {
    const headings = document.querySelectorAll('h1[id], h2[id], h3[id]');

    const observerOptions = {
        root: null,
        rootMargin: '-20% 0px -80% 0px',
        threshold: 0
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                updateActiveNav(entry.target.id);
            }
        });
    }, observerOptions);

    headings.forEach(heading => observer.observe(heading));
}

function updateActiveNav(id) {
    const links = document.querySelectorAll('.nav-link');
    links.forEach(link => {
        link.classList.remove('active');
        if (link.dataset.target === id) {
            link.classList.add('active');
            const parent = link.closest('.nav-children');
            if (parent) {
                parent.style.display = 'block';
            }
        }
    });
}

function initSmoothScroll() {
    if (window.location.hash) {
        setTimeout(() => {
            scrollToSection(window.location.hash.substring(1));
        }, 100);
    }
}

function scrollToSection(id) {
    const element = document.getElementById(id);
    if (element) {
        const offset = window.innerWidth <= 768 ? 80 : 20;
        const top = element.getBoundingClientRect().top + window.pageYOffset - offset;

        window.scrollTo({
            top: top,
            behavior: 'smooth'
        });

        history.pushState(null, null, `#${id}`);
        updateActiveNav(id);
    }
}

/* ============================================
    LANGUAGE SWITCHER
   ============================================ */

function initLanguageSwitcher() {
    const path = window.location.pathname;
    const isTr = /\/tr\//i.test(path);
    const isEn = /\/en\//i.test(path);
    if (!isTr && !isEn) return;

    const currentLang = isTr ? 'tr' : 'en';
    localStorage.setItem('docsLang', currentLang);

    const article = document.getElementById('content');
    if (!article) return;

    const page = path.split('/').pop() || 'index.html';
    const hash = window.location.hash || '';
    const targetTr = `../tr/${page}${hash}`;
    const targetEn = `../en/${page}${hash}`;

    const switcher = document.createElement('div');
    switcher.className = 'lang-switcher';
    switcher.innerHTML = currentLang === 'tr'
        ? `<span class="lang-current">TR</span> · <a href="${targetEn}" data-lang="en">EN</a>`
        : `<a href="${targetTr}" data-lang="tr">TR</a> · <span class="lang-current">EN</span>`;

    switcher.querySelectorAll('a[data-lang]').forEach(link => {
        link.addEventListener('click', () => {
            localStorage.setItem('docsLang', link.dataset.lang);
        });
    });

    article.appendChild(switcher);
}

/* ============================================
    MOBILE MENU
   ============================================ */

function closeHeaderMenu() {
    const header = document.querySelector('.main-header');
    if (header) header.classList.remove('mobile-menu-open');
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.overlay');
    const header = document.querySelector('.main-header');
    const willOpen = !sidebar.classList.contains('open');

    sidebar.classList.toggle('open', willOpen);
    overlay.classList.toggle('active', willOpen);
    if (header) header.classList.toggle('mobile-menu-open', willOpen);
    document.body.style.overflow = willOpen ? 'hidden' : '';
}

window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.overlay');
        const header = document.querySelector('.main-header');

        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('active');
        if (header) header.classList.remove('mobile-menu-open');
        document.body.style.overflow = '';
    }
});
