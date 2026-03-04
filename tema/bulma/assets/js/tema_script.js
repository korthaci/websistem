// Data layer - All content data in arrays/objects
        const navigationData = [
            { "label": "Features", "href": "#features", "active": false },
            { "label": "Use Cases", "href": "#use-cases", "active": false },
            { "label": "Pricing", "href": "#pricing", "active": false },
            { "label": "Testimonials", "href": "#testimonials", "active": false },
            { "label": "Documentation", "href": "#", "active": false }
        ];
        
        const featuresData = [
            { 
                "id": 1, 
                "icon": "fas fa-chart-line", 
                "title": "Advanced Analytics", 
                "description": "Get real-time insights and predictive analytics to make data-driven decisions with confidence.",
                "color": "has-text-primary"
            },
            { 
                "id": 2, 
                "icon": "fas fa-robot", 
                "title": "AI Automation", 
                "description": "Automate repetitive tasks and workflows with intelligent bots that learn and adapt over time.",
                "color": "has-text-info"
            },
            { 
                "id": 3, 
                "icon": "fas fa-users", 
                "title": "Team Collaboration", 
                "description": "Seamlessly collaborate with your team in real-time with shared workspaces and task management.",
                "color": "has-text-success"
            },
            { 
                "id": 4, 
                "icon": "fas fa-shield-alt", 
                "title": "Enterprise Security", 
                "description": "Bank-level security with end-to-end encryption, role-based access, and compliance certifications.",
                "color": "has-text-warning"
            },
            { 
                "id": 5, 
                "icon": "fas fa-plug", 
                "title": "300+ Integrations", 
                "description": "Connect with your favorite tools like Slack, Salesforce, Google Workspace, and many more.",
                "color": "has-text-danger"
            },
            { 
                "id": 6, 
                "icon": "fas fa-mobile-alt", 
                "title": "Mobile Apps", 
                "description": "Access your workspace on the go with native iOS and Android apps with full functionality.",
                "color": "has-text-link"
            }
        ];
        
        const useCasesData = [
            {
                "id": 1,
                "title": "Marketing Teams",
                "description": "Automate campaign tracking, analyze customer sentiment, and optimize ad spend with predictive algorithms.",
                "icon": "fas fa-bullhorn",
                "image": "https://picsum.photos/400/250?random=201",
                "alt": "Marketing team using analytics dashboard"
            },
            {
                "id": 2,
                "title": "Sales Professionals",
                "description": "Predict lead conversion, automate follow-ups, and gain insights into customer behavior patterns.",
                "icon": "fas fa-chart-bar",
                "image": "https://picsum.photos/400/250?random=202",
                "alt": "Sales dashboard with performance metrics"
            },
            {
                "id": 3,
                "title": "Software Developers",
                "description": "Automate code reviews, predict bugs, and optimize development workflows with AI assistance.",
                "icon": "fas fa-code",
                "image": "https://picsum.photos/400/250?random=203",
                "alt": "Developer working with code and AI tools"
            },
            {
                "id": 4,
                "title": "HR Departments",
                "description": "Streamline recruitment with AI screening, analyze employee engagement, and predict retention risks.",
                "icon": "fas fa-users-cog",
                "image": "https://picsum.photos/400/250?random=204",
                "alt": "HR professionals reviewing candidate profiles"
            }
        ];
        
        const pricingData = [
            {
                "id": 1,
                "title": "Starter",
                "price": "$19",
                "period": "per user/month",
                "description": "Perfect for individuals and small teams",
                "features": ["Up to 5 users", "10 GB storage", "Basic analytics", "Email support", "10 integrations"],
                "buttonText": "Get Started",
                "buttonClass": "is-outlined is-primary",
                "popular": false
            },
            {
                "id": 2,
                "title": "Professional",
                "price": "$49",
                "period": "per user/month",
                "description": "Best for growing teams and businesses",
                "features": ["Up to 50 users", "100 GB storage", "Advanced analytics", "Priority support", "Unlimited integrations", "Custom workflows", "API access"],
                "buttonText": "Try Free for 14 Days",
                "buttonClass": "is-primary",
                "popular": true
            },
            {
                "id": 3,
                "title": "Enterprise",
                "price": "Custom",
                "period": "tailored pricing",
                "description": "For large organizations with specific needs",
                "features": ["Unlimited users", "1 TB+ storage", "Custom analytics", "24/7 dedicated support", "White-label solution", "On-premise deployment", "SLA 99.9%"],
                "buttonText": "Contact Sales",
                "buttonClass": "is-outlined is-primary",
                "popular": false
            }
        ];
        
        const testimonialsData = [
            {
                "id": 1,
                "name": "Sarah Johnson",
                "role": "CTO, TechFlow Inc.",
                "text": "NexusAI has transformed how our engineering team works. We've reduced development time by 40% and improved code quality significantly.",
                "avatar": "https://picsum.photos/100?random=301",
                "rating": 5
            },
            {
                "id": 2,
                "name": "Michael Chen",
                "role": "Marketing Director, GrowthLab",
                "text": "The analytics and automation features have helped us increase campaign ROI by 300%. The platform is intuitive yet powerful.",
                "avatar": "https://picsum.photos/100?random=302",
                "rating": 5
            },
            {
                "id": 3,
                "name": "Emma Rodriguez",
                "role": "Product Manager, InnovateCo",
                "text": "We tried several AI tools before settling on NexusAI. The team collaboration features and seamless integrations made it the clear winner.",
                "avatar": "https://picsum.photos/100?random=303",
                "rating": 4
            }
        ];
        
        const productLinksData = [
            { "label": "Features", "href": "#features" },
            { "label": "Pricing", "href": "#pricing" },
            { "label": "Use Cases", "href": "#use-cases" },
            { "label": "API Documentation", "href": "#" },
            { "label": "Mobile Apps", "href": "#" }
        ];
        
        const companyLinksData = [
            { "label": "About Us", "href": "#" },
            { "label": "Careers", "href": "#" },
            { "label": "Blog", "href": "#" },
            { "label": "Press", "href": "#" },
            { "label": "Contact", "href": "#" }
        ];
        
        const legalLinksData = [
            { "label": "Privacy Policy", "href": "#" },
            { "label": "Terms of Service", "href": "#" },
            { "label": "Cookie Policy", "href": "#" },
            { "label": "GDPR Compliance", "href": "#" }
        ];
        
        // Reusable render functions
        function renderNavigation(items) {
            return items.map(item => `
                <a class="navbar-item has-text-white ${item.active ? 'nav-active' : ''}" href="${item.href}">
                    ${item.label}
                </a>
            `).join("");
        }
        
        function renderFeatures(items) {
            return items.map(item => `
                <div class="column is-4">
                    <div class="box has-text-centered p-5">
                        <div class="feature-icon ${item.color}">
                            <i class="${item.icon} fa-2x"></i>
                        </div>
                        <h3 class="title is-4 mb-3">${item.title}</h3>
                        <p class="has-text-grey">${item.description}</p>
                    </div>
                </div>
            `).join("");
        }
        
        function renderUseCases(items) {
            return items.map(item => `
                <div class="column is-6">
                    <div class="use-case-card box p-0">
                        <div class="card-image">
                            <figure class="image is-4by3">
                                <img src="${item.image}" alt="${item.alt}" loading="lazy">
                            </figure>
                        </div>
                        <div class="p-5">
                            <div class="media">
                                <div class="media-left">
                                    <span class="icon is-medium ${item.id === 1 ? 'has-text-primary' : item.id === 2 ? 'has-text-info' : item.id === 3 ? 'has-text-success' : 'has-text-warning'}">
                                        <i class="${item.icon} fa-2x"></i>
                                    </span>
                                </div>
                                <div class="media-content">
                                    <h4 class="title is-4">${item.title}</h4>
                                </div>
                            </div>
                            <p class="mt-3">${item.description}</p>
                            <a href="#" class="button is-text mt-4">Learn more <i class="fas fa-arrow-right ml-2"></i></a>
                        </div>
                    </div>
                </div>
            `).join("");
        }
        
        function renderPricing(items) {
            return items.map(item => `
                <div class="column is-4">
                    <div class="pricing-card box p-5 ${item.popular ? 'popular' : ''}">
                        ${item.popular ? '<div class="popular-badge">Most Popular</div>' : ''}
                        <h3 class="title is-3 has-text-centered">${item.title}</h3>
                        <div class="has-text-centered my-4">
                            <span class="title is-1">${item.price}</span>
                            <p class="has-text-grey">${item.period}</p>
                        </div>
                        <p class="has-text-centered mb-5">${item.description}</p>
                        <ul class="mb-5">
                            ${item.features.map(feature => `<li class="mb-2"><i class="fas fa-check has-text-success mr-2"></i> ${feature}</li>`).join("")}
                        </ul>
                        <a href="#" class="button is-fullwidth is-medium ${item.buttonClass}">${item.buttonText}</a>
                    </div>
                </div>
            `).join("");
        }
        
        function renderTestimonials(items) {
            return items.map(item => `
                <div class="column is-4">
                    <div class="testimonial-card box p-5">
                        <div class="media">
                            <div class="media-left">
                                <figure class="image is-64x64">
                                    <img class="testimonial-avatar" src="${item.avatar}" alt="${item.name}" loading="lazy">
                                </figure>
                            </div>
                            <div class="media-content">
                                <h4 class="title is-5 mb-1">${item.name}</h4>
                                <p class="subtitle is-6 has-text-grey">${item.role}</p>
                            </div>
                        </div>
                        <div class="content mt-4">
                            <p>"${item.text}"</p>
                            <div class="stars mt-3">
                                ${Array(item.rating).fill().map(() => '<i class="fas fa-star has-text-warning"></i>').join("")}
                                ${Array(5 - item.rating).fill().map(() => '<i class="far fa-star has-text-warning"></i>').join("")}
                            </div>
                        </div>
                    </div>
                </div>
            `).join("");
        }
        
        function renderFooterLinks(items, containerId) {
            return items.map(item => `
                <li class="mb-2">
                    <a href="${item.href}" class="has-text-white-ter hover-white">${item.label}</a>
                </li>
            `).join("");
        }
        
        function renderLegalLinks(items) {
            return items.map((item, index) => `
                <a href="${item.href}" class="has-text-white-ter hover-white ${index > 0 ? 'ml-4' : ''}">${item.label}</a>
            `).join("");
        }
        
        // Initialize page content
        document.addEventListener('DOMContentLoaded', function() {
            // Render navigation
            document.getElementById('nav-links').innerHTML = `
                <a class="navbar-link has-text-white">
                    Menu
                </a>
                <div class="navbar-dropdown has-background-dark">
                    ${renderNavigation(navigationData)}
                </div>
            `;
            
            // Render features
            document.getElementById('features-container').innerHTML = renderFeatures(featuresData);
            
            // Render use cases
            document.getElementById('use-cases-container').innerHTML = renderUseCases(useCasesData);
            
            // Render pricing
            document.getElementById('pricing-container').innerHTML = renderPricing(pricingData);
            
            // Render testimonials
            document.getElementById('testimonials-container').innerHTML = renderTestimonials(testimonialsData);
            
            // Render footer links
            document.getElementById('product-links').innerHTML = renderFooterLinks(productLinksData, 'product-links');
            document.getElementById('company-links').innerHTML = renderFooterLinks(companyLinksData, 'company-links');
            document.getElementById('footer-legal-links').innerHTML = renderLegalLinks(legalLinksData);
            
            // Mobile menu toggle
            const burger = document.querySelector('.navbar-burger');
            const menu = document.getElementById('navbarMenu');
            
            if (burger) {
                burger.addEventListener('click', function() {
                    burger.classList.toggle('is-active');
                    menu.classList.toggle('is-active');
                });
            }
            
            // Smooth scroll for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    
                    // Skip if it's just "#"
                    if (href === '#') return;
                    
                    e.preventDefault();
                    
                    // Close mobile menu if open
                    if (menu.classList.contains('is-active')) {
                        burger.classList.remove('is-active');
                        menu.classList.remove('is-active');
                    }
                    
                    const targetElement = document.querySelector(href);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 80,
                            behavior: 'smooth'
                        });
                    }
                });
            });
            
            // CTA button handlers
            const ctaButtons = ['start-free-btn', 'demo-btn', 'login-btn', 'signup-btn', 'cta-btn'];
            ctaButtons.forEach(buttonId => {
                const button = document.getElementById(buttonId);
                if (button) {
                    button.addEventListener('click', function() {
                        if (buttonId === 'demo-btn' || buttonId === 'login-btn') {
                            alert("This would open a demo/login modal in a real implementation.");
                        } else {
                            alert("Thank you for your interest! You would be redirected to the signup page.");
                        }
                    });
                }
            });
            
            // Update active navigation based on scroll position
            function updateActiveNav() {
                const scrollPosition = window.scrollY + 100;
                
                // Reset all nav items
                navigationData.forEach(item => item.active = false);
                
                // Find which section is currently in view
                const sections = ['features', 'use-cases', 'pricing', 'testimonials'];
                for (const section of sections) {
                    const element = document.getElementById(section);
                    if (element) {
                        const offsetTop = element.offsetTop;
                        const offsetBottom = offsetTop + element.offsetHeight;
                        
                        if (scrollPosition >= offsetTop && scrollPosition < offsetBottom) {
                            const navItem = navigationData.find(item => item.href === `#${section}`);
                            if (navItem) navItem.active = true;
                        }
                    }
                }
                
                // Update navigation UI
                const navLinks = document.querySelectorAll('.navbar-item[href^="#"]');
                navLinks.forEach(link => {
                    const href = link.getAttribute('href');
                    const navItem = navigationData.find(item => item.href === href);
                    if (navItem && navItem.active) {
                        link.classList.add('nav-active');
                    } else {
                        link.classList.remove('nav-active');
                    }
                });
            }
            
            // Add scroll event listener
            window.addEventListener('scroll', updateActiveNav);
            
            // Initialize active nav on load
            updateActiveNav();
        });