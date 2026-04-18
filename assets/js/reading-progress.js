document.addEventListener('DOMContentLoaded', function() {
    const progressBar = document.getElementById('wpa-reading-progress-bar');
    const container = document.getElementById('wpa-reading-progress-container');

    if (!progressBar) return;

    // Check if we are on a single post (body class usually has single-post or similar, but the PHP check handles this mostly)
    
    function updateProgress() {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;
        
        progressBar.style.width = scrollPercent + '%';
    }

    window.addEventListener('scroll', updateProgress);
    window.addEventListener('resize', updateProgress);
    
    // Initial call
    updateProgress();
});
