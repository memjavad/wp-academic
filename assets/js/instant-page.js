(function() {
    let urlToPreload;
    let mouseoverTimer;

    const prefetcher = document.createElement('link');
    const isSupported = prefetcher.relList && prefetcher.relList.supports && prefetcher.relList.supports('prefetch');
    
    if (!isSupported) return;

    document.addEventListener('mouseover', function(event) {
        const link = event.target.closest('a');
        if (!link) return;
        
        if (mouseoverTimer) {
            clearTimeout(mouseoverTimer);
            mouseoverTimer = undefined;
        }

        urlToPreload = link.href;
        
        // Ignore bad URLs, hashes, and external links
        if (!urlToPreload || urlToPreload.startsWith('#') || link.origin !== location.origin) return;

        mouseoverTimer = setTimeout(() => {
            preload(urlToPreload);
            mouseoverTimer = undefined;
        }, 65); // 65ms delay to prevent prefetching on fast scroll
    }, { capture: true, passive: true });

    document.addEventListener('touchstart', function(event) {
        const link = event.target.closest('a');
        if (!link) return;
        
        urlToPreload = link.href;
        if (!urlToPreload || urlToPreload.startsWith('#') || link.origin !== location.origin) return;
        
        preload(urlToPreload);
    }, { capture: true, passive: true });

    function preload(url) {
        // Prevent duplicate prefetching
        if (document.querySelector(`link[rel="prefetch"][href="${url}"]`)) return;

        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        document.head.appendChild(link);
    }
})();
