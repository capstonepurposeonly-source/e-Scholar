/**
 * Fix for layout issues when using browser back button
 * This script handles page restoration from browser cache (bfcache)
 */

(function () {
    'use strict';

    // Flag to track if we've already run the fix
    let hasFixedLayout = false;

    /**
     * Reset all transform and transition states
     */
    function resetLayoutStates() {
        console.log('Resetting layout states...');

        // Remove ALL inline styles from elements that might have stuck transforms
        const allElements = document.querySelectorAll('*');
        allElements.forEach(element => {
            // Clear inline transform styles
            if (element.style.transform) {
                element.style.transform = '';
            }

            // Remove stuck hover/focus/active classes
            element.classList.remove('hover', 'active', 'focus');
        });

        // Reset specific problematic elements with transforms in the stylesheet
        const transformElements = document.querySelectorAll(
            '.btn, .btn-yellow, .quick-link-item, .video-container, .process-step, ' +
            '.certificate-item, .btn-adlog, .btn-studportal, .btn-prog, .btn-acscho, ' +
            '.btn-track, .quick-link-logo, .contact-card, .faq-item'
        );

        transformElements.forEach(element => {
            // Remove transform temporarily
            const originalTransform = window.getComputedStyle(element).transform;
            element.style.transform = 'none !important';

            // Force a reflow
            void element.offsetHeight;

            // Clear the inline style
            element.style.transform = '';
        });

        // Reset grid layouts that might be broken
        const gridContainers = document.querySelectorAll(
            '.quick-links-container, .video-content, .about-content, ' +
            '.campus-content, .process-grid, .certificate-list, .contact-grid'
        );

        gridContainers.forEach(grid => {
            const display = grid.style.display;
            grid.style.display = 'none';
            void grid.offsetHeight;
            grid.style.display = display || '';
        });

        // Force reflow on all major containers
        const containers = document.querySelectorAll('.hero, .video-section, .process-section, .quick-links, .contact-section, .faq-section, .about-section, .campus-section, .navbar');
        containers.forEach(container => {
            if (container) {
                void container.offsetHeight;
            }
        });
    }

    /**
     * Force a complete page reflow
     */
    function forceReflow() {
        console.log('Forcing complete reflow...');

        // Temporarily hide and show the body
        const originalDisplay = document.body.style.display;
        document.body.style.display = 'none';

        // Force a reflow
        void document.body.offsetHeight;

        // Restore display
        document.body.style.display = originalDisplay || '';

        // Trigger another reflow
        void document.body.offsetHeight;
    }

    /**
     * Main fix function
     */
    function fixBackButtonLayout() {
        if (hasFixedLayout) {
            return;
        }

        hasFixedLayout = true;

        console.log('Fixing back button layout issues...');

        // Reset layout states
        resetLayoutStates();

        // Force reflow
        forceReflow();

        // Reset scroll position if needed
        if (window.scrollY === 0 && sessionStorage.getItem('scrollPos')) {
            const scrollPos = parseInt(sessionStorage.getItem('scrollPos'), 10);
            window.scrollTo(0, scrollPos);
            sessionStorage.removeItem('scrollPos');
        }

        // Dispatch a custom event for other scripts to listen to
        window.dispatchEvent(new CustomEvent('layoutFixed', {
            detail: { timestamp: Date.now() }
        }));

        console.log('Layout fix complete');

        // Reset the flag after a short delay
        setTimeout(() => {
            hasFixedLayout = false;
        }, 1000);
    }

    /**
     * Handle pageshow event (fires when navigating back to cached page)
     */
    window.addEventListener('pageshow', function (event) {
        // Check if the page was restored from bfcache
        if (event.persisted) {
            console.log('Page restored from bfcache - applying fixes');

            // Apply fix after a brief delay to ensure DOM is ready
            setTimeout(fixBackButtonLayout, 50);
        }
    });

    /**
     * Handle page visibility changes
     */
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            // Page became visible - check for layout issues
            setTimeout(() => {
                const body = document.body;
                const computedStyle = window.getComputedStyle(body);

                // Check if body has any unexpected transforms
                if (computedStyle.transform !== 'none' && computedStyle.transform !== 'matrix(1, 0, 0, 1, 0, 0)') {
                    console.log('Detected layout issue on visibility change');
                    fixBackButtonLayout();
                }
            }, 100);
        }
    });

    /**
     * Save scroll position before navigating away
     */
    window.addEventListener('beforeunload', function () {
        sessionStorage.setItem('scrollPos', window.scrollY.toString());
    });

    /**
     * Handle popstate event (browser back/forward buttons)
     */
    window.addEventListener('popstate', function () {
        console.log('Popstate event detected');
        setTimeout(fixBackButtonLayout, 100);
    });

    /**
     * Initialize on DOMContentLoaded
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            console.log('Back button fix script loaded');
        });
    } else {
        console.log('Back button fix script loaded');
    }

})();
