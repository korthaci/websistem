/* ============================================
    DOCUMENTATION SYSTEM JAVASCRIPT
    ============================================ */

// Navigation logic will be determined on runtime (Auto-discovery or manual override via window.PAGE_NAV)

// Initialize Documentation System
document.addEventListener('DOMContentLoaded', function () {
    // Check if there is a manual override, if not, auto-generate based on headings
    const currentNav = window.PAGE_NAV || autoGenerateNavStructure();

    initNavigation(currentNav);
    initGlobalHeader();
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

    const menuItems = [
        { title: 'Getting Started', href: 'getting-started.html' },
        { title: 'Installation', href: 'installation.html' },
        { title: 'Usage', href: 'usage.html' },
        { title: 'Advanced', href: 'advanced.html' },
        { title: 'Changelog', href: 'changelog.html' },
        { title: 'Roadmap', href: 'roadmap.html' },
        { title: 'FAQ', href: 'faq.html' }
    ];

    const path = window.location.pathname;
    const page = path.split("/").pop() || 'index.html';

    headerNav.innerHTML = '';
    menuItems.forEach(item => {
        const a = document.createElement('a');
        a.href = item.href;
        a.className = 'header-nav-link';
        // Simple match for active state
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
    const threshold = 10; // Minimum scroll miktarı

    window.addEventListener('scroll', () => {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        // Çok küçük oynamalarda tepki verme
        if (Math.abs(lastScrollTop - scrollTop) <= threshold) return;

        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Aşağı kaydırıyor - Gizle
            header.classList.add('header-hidden');
        } else {
            // Yukarı kaydırıyor - Göster
            header.classList.remove('header-hidden');
        }

        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // Negatif değerleri önle
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
    nav.innerHTML = ''; // Clear existing

    structure.forEach(item => {
        // Handle section titles (items with children but no ID)
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

    // Add click handler for smooth scroll
    link.addEventListener('click', (e) => {
        e.preventDefault();
        scrollToSection(item.id);
        if (window.innerWidth <= 768) {
            toggleSidebar();
        }
    });

    parent.appendChild(link);

    // Handle nested children
    if (item.children && item.children.length > 0) {
        const childrenContainer = document.createElement('div');
        childrenContainer.className = 'nav-children';
        childrenContainer.dataset.parent = item.id;

        item.children.forEach(child => {
            createNavLink(child, childrenContainer, depth + 1);
        });

        parent.appendChild(childrenContainer);

        // Make parent expandable
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
    const searchResults = document.getElementById('searchResults');
    const clearBtn = document.getElementById('clearSearch');

    // Debounce search input
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

    // Close search when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.search-container')) {
            hideSearchResults();
        }
    });

    // Keyboard shortcut (Cmd/Ctrl + K)
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

            // Ensure element has ID for linking
            if (!element.id) {
                element.id = `result-${index}`;
            }
        }
    });
}

function performSearch(query) {
    const results = searchIndex.filter(item =>
        item.text.includes(query.toLowerCase())
    ).slice(0, 8); // Limit to 8 results

    displaySearchResults(results, query);
    highlightMatches(query);
}

function displaySearchResults(results, query) {
    const container = document.getElementById('searchResults');
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

            // Highlight query in preview
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
    document.getElementById('searchResults').classList.remove('active');
}

function clearSearch() {
    document.getElementById('searchInput').value = '';
    document.getElementById('clearSearch').classList.remove('visible');
    hideSearchResults();
    clearHighlights();
}

function highlightMatches(query) {
    clearHighlights();

    if (!query) return;

    const content = document.getElementById('content');
    const walker = document.createTreeWalker(
        content,
        NodeFilter.SHOW_TEXT,
        null,
        false
    );

    const textNodes = [];
    let node;
    while (node = walker.nextNode()) {
        if (node.nodeValue.toLowerCase().includes(query.toLowerCase())) {
            textNodes.push(node);
        }
    }

    textNodes.forEach(node => {
        const span = document.createElement('span');
        const regex = new RegExp(`(${query})`, 'gi');
        span.innerHTML = node.nodeValue.replace(regex,
            '<mark class="search-highlight" style="background: var(--search-highlight); padding: 0 2px; border-radius: 2px;">$1</mark>'
        );
        node.parentNode.replaceChild(span, node);
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
    const navLinks = document.querySelectorAll('.nav-link');

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
            // Expand parent if exists
            const parent = link.closest('.nav-children');
            if (parent) {
                parent.style.display = 'block';
            }
        }
    });
}

function initSmoothScroll() {
    // Handled by CSS scroll-behavior, but ensure hash links work
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

        // Update URL without jumping
        history.pushState(null, null, `#${id}`);
        updateActiveNav(id);
    }
}

/* ============================================
    MOBILE MENU
    ============================================ */

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.overlay');

    sidebar.classList.toggle('open');
    overlay.classList.toggle('active');

    // Prevent body scroll when menu is open
    document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
}

// Close sidebar on window resize if moving to desktop
window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
        document.getElementById('sidebar').classList.remove('open');
        document.querySelector('.overlay').classList.remove('active');
        document.body.style.overflow = '';
    }
});