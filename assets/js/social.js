/**
 * Social Sharing JS
 */
document.addEventListener('DOMContentLoaded', function() {
    // Copy Link functionality
    const copyLinkBtns = document.querySelectorAll('.wpa-copy-link-btn');
    
    copyLinkBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const link = this.getAttribute('data-link');
            
            if (!link) return;

            // Use Clipboard API if available
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(link).then(() => {
                    showCopyFeedback(this);
                }).catch(err => {
                    console.error('Could not copy text: ', err);
                    fallbackCopyTextToClipboard(link, this);
                });
            } else {
                fallbackCopyTextToClipboard(link, this);
            }
        });
    });

    function fallbackCopyTextToClipboard(text, btn) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        
        // Avoid scrolling to bottom
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopyFeedback(btn);
            }
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
        }

        document.body.removeChild(textArea);
    }

    // Native Share functionality
    const nativeShareBtns = document.querySelectorAll('.wpa-native-share-btn');
    
    if (navigator.share) {
        nativeShareBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('data-url');
                const title = this.getAttribute('data-title');

                navigator.share({
                    title: title,
                    url: url
                }).catch(err => console.error('Error sharing:', err));
            });
        });
    } else {
        // Hide native share buttons if not supported
        nativeShareBtns.forEach(btn => btn.style.display = 'none');
    }

    // Mobile Download Row Toggle
    document.addEventListener('click', function(e) {
        const toggle = e.target.closest('.wpa-mobile-download-toggle');
        if (toggle) {
            const bar = document.querySelector('.wpa-social-floating-bottom');
            if (bar) {
                const isCollapsed = bar.classList.toggle('wpa-bar-collapsed');
                toggle.classList.toggle('is-collapsed', isCollapsed);
            }
        }
    });

    function showCopyFeedback(btn) {
        const originalText = btn.innerHTML;
        const successMsg = wpa_social_vars.copy_success || 'Link copied!';
        
        // Find text span if it exists
        const textSpan = btn.querySelector('.wpa-social-text');
        if (textSpan) {
            const originalSpanText = textSpan.innerText;
            textSpan.innerText = successMsg;
            btn.classList.add('wpa-copy-success');
            
            setTimeout(() => {
                textSpan.innerText = originalSpanText;
                btn.classList.remove('wpa-copy-success');
            }, 2000);
        } else {
            // Icon only mode or similar
            const tooltip = document.createElement('span');
            tooltip.className = 'wpa-copy-tooltip';
            tooltip.innerText = successMsg;
            btn.appendChild(tooltip);
            btn.classList.add('wpa-copy-success');

            setTimeout(() => {
                tooltip.remove();
                btn.classList.remove('wpa-copy-success');
            }, 2000);
        }
    }
});
