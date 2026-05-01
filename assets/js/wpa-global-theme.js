/**
 * WPA Global Theme Logic
 * Handles Sticky Header, Mobile Menu, Progress Bar, Animations, Slider.
 */
document.addEventListener("DOMContentLoaded", function() {
    
    // --- Mobile Menu ---
    const toggle = document.querySelector('.wpa-mobile-toggle');
    const overlay = document.querySelector('.wpa-mobile-menu-overlay');
    const close = document.querySelector('.wpa-mobile-close');
    const stickySocial = document.querySelector('.wpa-social-floating-bottom');
    
    if (toggle && overlay) {
        const menuIcon = `<span class="wpa-icon wpa-icon-menu" aria-hidden="true"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 4h16v2H2V4zm0 5h16v2H2V9zm0 5h16v2H2v-2z"/></svg></span>`;
        const crossIcon = `<span class="wpa-icon wpa-icon-cross" aria-hidden="true"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 8.5l5-5 1.5 1.5-5 5 5 5-1.5 1.5-5-5-5 5-1.5-1.5 5-5-5-5 1.5-1.5 5 5z"/></svg></span>`;

        // Clone desktop menu to mobile early to save DOM nodes
        const desktopNav = document.querySelector('.wpa-header-menu');
        const mobileContainer = document.getElementById('wpa-mobile-menu-container');
        if (desktopNav && mobileContainer && !mobileContainer.hasChildNodes()) {
            const clonedNav = desktopNav.cloneNode(true);
            clonedNav.classList.remove('wpa-header-menu');
            clonedNav.classList.add('wpa-mobile-menu-list');
            
            // Clean up IDs to avoid duplicates
            const allNodes = clonedNav.querySelectorAll('*[id]');
            allNodes.forEach(node => {
                node.id = 'mobile-' + node.id;
            });
            
            mobileContainer.appendChild(clonedNav);
        }

        function openMenu() {
            overlay.classList.add('active');
            toggle.classList.add('active');
            toggle.innerHTML = crossIcon;
            document.body.style.overflow = 'hidden';
            if (stickySocial) stickySocial.style.display = 'none'; // Hide sticky bar
        }

        function closeMenu() {
            overlay.classList.remove('active');
            toggle.classList.remove('active');
            toggle.innerHTML = menuIcon;
            document.body.style.overflow = '';
            if (stickySocial) stickySocial.style.display = ''; // Restore sticky bar
        }

        toggle.addEventListener('click', function(e) {
            if (overlay.classList.contains('active')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        if (close) close.addEventListener('click', closeMenu);
        
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeMenu();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && overlay.classList.contains('active')) {
                closeMenu();
            }
        });
    }

    // --- Dark Mode Logic ---
    const darkModeToggles = document.querySelectorAll('#wpa-dark-mode-toggle, #wpa-dark-mode-toggle-mobile');
    const body = document.body;
    const htmlElement = document.documentElement;
    
    // Check local storage or system preference
    const savedMode = localStorage.getItem('wpa_dark_mode');
    const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // SVG Templates for replacement
    const sunIcon = `<span class="wpa-icon wpa-icon-sun" aria-hidden="true"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" /></svg></span>`;
    const moonIcon = `<span class="wpa-icon wpa-icon-moon" aria-hidden="true"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg></span>`;

    function updateToggles(isDark) {
        darkModeToggles.forEach(btn => {
            btn.innerHTML = isDark ? sunIcon : moonIcon;
        });
    }

    // Initial Toggle State (Class is already applied by head script if needed)
    const isCurrentlyDark = htmlElement.classList.contains('wpa-dark-mode');
    if (isCurrentlyDark) {
        body.classList.add('wpa-dark-mode');
        updateToggles(true);
    } else {
        updateToggles(false);
    }

    darkModeToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            body.classList.toggle('wpa-dark-mode');
            htmlElement.classList.toggle('wpa-dark-mode');
            
            const isDark = body.classList.contains('wpa-dark-mode');
            localStorage.setItem('wpa_dark_mode', isDark ? 'dark' : 'light');
            
            updateToggles(isDark);
        });
    });

    // --- Unified Scroll Handler (Header, Progress, Social) ---
    const header = document.querySelector('.wpa-news-header, .wpa-header');
    const progressBar = document.querySelector('.wpa-header-progress-bar');
    
    let lastScrollY = window.pageYOffset || window.scrollY;
    let ticking = false;

    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                const scrollY = window.pageYOffset || window.scrollY;
                if (scrollY < 0) { ticking = false; return; } // Ignore iOS bounce

                const threshold = 15;
                const hideDistance = 150;
                const diff = scrollY - lastScrollY;
                const absDiff = Math.abs(diff);

                // 1. Shadow logic
                if (header) {
                    if (scrollY > 10) header.classList.add('is-scrolled');
                    else header.classList.remove('is-scrolled');
                }

                // 2. Progress Bar
                if (progressBar) {
                    const totalHeight = document.documentElement.scrollHeight - window.innerHeight;
                    const scrolled = (scrollY / totalHeight) * 100;
                    progressBar.style.width = scrolled + "%";
                }

                // 3. Hiding/Showing Logic (Header & Social synced)
                if (absDiff > threshold || scrollY < 10) {
                    const isScrollingDown = diff > 0;
                    const isScrollingUp = diff < 0;

                    if (scrollY > hideDistance) {
                        if (isScrollingDown) {
                            if (header) header.classList.add('wpa-header-hidden');
                            if (stickySocial) stickySocial.classList.add('wpa-bar-collapsed');
                        } else if (isScrollingUp) {
                            if (header) header.classList.remove('wpa-header-hidden');
                            if (stickySocial) stickySocial.classList.remove('wpa-bar-collapsed');
                        }
                    } else {
                        // Near top -> Show both
                        if (header) header.classList.remove('wpa-header-hidden');
                        if (stickySocial) stickySocial.classList.remove('wpa-bar-collapsed');
                    }

                    // Special case: Bottom of page always shows social
                    if (stickySocial) {
                        const isAtBottom = (window.innerHeight + scrollY) >= (document.documentElement.scrollHeight - 100);
                        if (isAtBottom) stickySocial.classList.remove('wpa-bar-collapsed');
                    }

                    lastScrollY = scrollY;
                }

                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });

    // --- Scroll to Top Logic ---
    const scrollTopBtn = document.getElementById('wpa-scroll-top');
    if (scrollTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 400) {
                scrollTopBtn.classList.add('active');
            } else {
                scrollTopBtn.classList.remove('active');
            }
        }, { passive: true });

        scrollTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // --- Universal Homepage Slider Engine ---
    const sliders = document.querySelectorAll('.wpa-slider-container');
    if (sliders.length > 0) {
        sliders.forEach(slider => {
            const track = slider.querySelector('.wpa-slider-track');
            const slides = slider.querySelectorAll('.wpa-slide');
            const nextBtn = slider.querySelector('.wpa-slider-next');
            const prevBtn = slider.querySelector('.wpa-slider-prev');
            const dotsContainer = slider.querySelector('.wpa-slider-dots');
            
            if (!track || slides.length === 0) return;

            let currentIndex = 0;
            const slideCount = slides.length;
            let autoPlayTimer;

            // Create Dots
            if (dotsContainer) {
                for (let i = 0; i < slideCount; i++) {
                    const dot = document.createElement('button');
                    dot.classList.add('wpa-slider-dot');
                    dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
                    if (i === 0) dot.classList.add('active');
                    dot.addEventListener('click', () => goToSlide(i));
                    dotsContainer.appendChild(dot);
                }
            }

            const dots = slider.querySelectorAll('.wpa-slider-dot');

            const sliderStyle = slider.getAttribute('data-style') || 'classic';
            const isSlideEffect = ['classic', 'card-carousel', 'carousel', 'compact'].includes(sliderStyle);

            function goToSlide(index) {
                if (index < 0) index = slideCount - 1;
                if (index >= slideCount) index = 0;

                currentIndex = index;
                
                if (isSlideEffect) {
                    const width = slider.offsetWidth || slides[0].offsetWidth;
                    track.style.transform = `translateX(-${currentIndex * width}px)`;
                } else {
                    // For Fade/Absolute styles, we don't move the track
                    track.style.transform = 'none';
                }

                // Update Dots
                dots.forEach((dot, idx) => {
                    dot.classList.toggle('active', idx === currentIndex);
                });
                
                // Update Classes for animation
                slides.forEach((slide, idx) => {
                    slide.classList.toggle('active', idx === currentIndex);
                });
            }

            function nextSlide() { goToSlide(currentIndex + 1); }
            function prevSlide() { goToSlide(currentIndex - 1); }

            if (nextBtn) nextBtn.addEventListener('click', () => {
                nextSlide();
                resetTimer();
            });
            
            if (prevBtn) prevBtn.addEventListener('click', () => {
                prevSlide();
                resetTimer();
            });

            // Auto Play
            function startTimer() {
                autoPlayTimer = setInterval(nextSlide, 5000);
            }
            
            function resetTimer() {
                clearInterval(autoPlayTimer);
                startTimer();
            }

            // Init
            startTimer();
            setTimeout(() => goToSlide(0), 10); // Faster init

            // Resize Handler
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => goToSlide(currentIndex), 100);
            });
            
            // Swipe Support (Basic)
            let touchStartX = 0;
            let touchEndX = 0;
            
            slider.addEventListener('touchstart', e => {
                touchStartX = e.changedTouches[0].screenX;
            }, {passive: true});
            
            slider.addEventListener('touchend', e => {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            }, {passive: true});
            
            function handleSwipe() {
                if (touchEndX < touchStartX - 50) nextSlide();
                if (touchEndX > touchStartX + 50) prevSlide();
                resetTimer();
            }
        });
    }

    // --- UX Enhancements: Smooth Scroll ---
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#' || this.classList.contains('nav-tab')) return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});