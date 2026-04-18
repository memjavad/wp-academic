/**
 * Enhanced TOC Logic: Collapsible + Scroll Spy + Smooth Scroll
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Collapsible Toggle
    const tocToggles = document.querySelectorAll('.wpa-toc-toggle');
    tocToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const container = this.closest('.wpa-toc-container');
            const nav = container.querySelector('.wpa-toc-nav');
            container.classList.toggle('wpa-toc-collapsed');
            
            if (container.classList.contains('wpa-toc-collapsed')) {
                nav.style.display = 'none';
            } else {
                nav.style.display = 'block';
            }
        });
    });

    // 2. Smooth Scroll with Offset
    const tocLinks = document.querySelectorAll('.wpa-toc-nav a, .wpa-toc-list a');
    const headerOffset = 100; // Adjust based on your sticky header height

    tocLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href.startsWith('#')) {
                e.preventDefault();
                const targetId = href.substring(1);
                const targetElement = document.getElementById(targetId);

                if (targetElement) {
                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: "smooth"
                    });

                    // Update URL without jump
                    history.pushState(null, null, href);
                }
            }
        });
    });

    // 3. Scroll Spy (Highlight Active Item)
    const anchors = document.querySelectorAll('.wpa-toc-anchor');
    const tocItems = document.querySelectorAll('.wpa-toc-item');

    if (anchors.length > 0 && tocItems.length > 0 && 'IntersectionObserver' in window) {
        const observerOptions = {
            root: null,
            rootMargin: '-10% 0px -70% 0px', // Trigger when item is in the top 30% of viewport
            threshold: 0
        };

        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute('id');
                    
                    // Remove all active classes
                    tocItems.forEach(item => item.classList.remove('wpa-active-section'));
                    
                    // Add to current
                    const activeItem = document.querySelector(`.wpa-toc-item[data-anchor="${id}"]`);
                    if (activeItem) {
                        activeItem.classList.add('wpa-active-section');
                        
                        // If in a sub-menu, ensure parent is highlighted or visible (optional)
                        let parent = activeItem.parentElement.closest('.wpa-toc-item');
                        if (parent) parent.classList.add('wpa-active-section-parent');
                    }
                }
            });
        }, observerOptions);

        anchors.forEach(anchor => observer.observe(anchor));
    }
});
