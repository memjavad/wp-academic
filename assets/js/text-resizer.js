(function() {
    function initTextResizer() {
        const resizerContainer = document.querySelector('.wpa-text-resizer');
        if (!resizerContainer) return;

        // Try to find the content area using common selectors
        const selectors = [
            resizerContainer.dataset.contentSelector,
            '.entry-content',
            '.post-content',
            '.article-content',
            'article',
            'main'
        ];

        let content = null;
        for (let i = 0; i < selectors.length; i++) {
            if (selectors[i]) {
                const found = document.querySelector(selectors[i]);
                if (found) {
                    content = found;
                    break;
                }
            }
        }

        if (!content) {
            return;
        }

        const increaseBtn = resizerContainer.querySelector('.wpa-resizer-increase');
        const decreaseBtn = resizerContainer.querySelector('.wpa-resizer-decrease');
        const resetBtn = resizerContainer.querySelector('.wpa-resizer-reset');

        // Text elements to resize
        const targetTags = 'p, li, h1, h2, h3, h4, h5, h6, td, th, blockquote, dd, dt, span';
        
        let currentMultiplier = parseFloat(localStorage.getItem('wpa-text-multiplier')) || 1;
        const step = 0.1;
        const minMultiplier = 0.7;
        const maxMultiplier = 2.0;

        function initOriginalSizes() {
            const elements = content.querySelectorAll(targetTags);
            elements.forEach(el => {
                // Avoid resizing the controls themselves if they are inside the content
                if (el.closest('.wpa-text-resizer')) return;

                if (!el.dataset.wpaOriginalSize) {
                    const style = window.getComputedStyle(el);
                    const fontSize = parseFloat(style.fontSize);
                    if (fontSize) {
                        el.dataset.wpaOriginalSize = fontSize;
                    }
                }
            });
        }

        function applyMultiplier(multiplier) {
            const elements = content.querySelectorAll(targetTags);
            elements.forEach(el => {
                if (el.closest('.wpa-text-resizer')) return;

                // Ensure we have the original size
                if (!el.dataset.wpaOriginalSize) {
                    const style = window.getComputedStyle(el);
                    const fontSize = parseFloat(style.fontSize);
                    if (fontSize) {
                        el.dataset.wpaOriginalSize = fontSize;
                    }
                }

                const original = parseFloat(el.dataset.wpaOriginalSize);
                if (original) {
                    const newSize = original * multiplier;
                    el.style.fontSize = newSize + 'px';
                }
            });

            localStorage.setItem('wpa-text-multiplier', multiplier);
            currentMultiplier = multiplier;
        }

        // Initialize logic
        // We delay slightly to ensure styles are fully applied
        setTimeout(() => {
            initOriginalSizes();
            if (currentMultiplier !== 1) {
                applyMultiplier(currentMultiplier);
            }
        }, 100);

        if (increaseBtn) {
            increaseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (currentMultiplier < maxMultiplier) {
                    applyMultiplier(currentMultiplier + step);
                }
            });
        }

        if (decreaseBtn) {
            decreaseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (currentMultiplier > minMultiplier) {
                    applyMultiplier(currentMultiplier - step);
                }
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                applyMultiplier(1);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTextResizer);
    } else {
        initTextResizer();
    }
})();
