// Enhanced Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Real-time clock
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', {
            hour12: true,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        const dateString = now.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        const clockElement = document.getElementById('live-clock');
        if (clockElement) {
            clockElement.innerHTML = `
                <div class="text-sm font-medium text-gray-600 dark:text-gray-400">${dateString}</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">${timeString}</div>
            `;
        }
    }

    // Update clock every second
    updateClock();
    setInterval(updateClock, 1000);

    // Portfolio performance animation
    function animateCounters() {
        const counters = document.querySelectorAll('[data-count]');
        counters.forEach(counter => {
            const target = parseFloat(counter.getAttribute('data-count'));
            const increment = target / 100;
            let current = 0;

            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                counter.textContent = current.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }, 30);
        });
    }

    // Trigger animations when elements come into view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-slide-up');
                if (entry.target.hasAttribute('data-count')) {
                    animateCounters();
                }
            }
        });
    });

    // Observe all animated elements
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });

    // Real-time notifications simulation
    function showNotification(title, message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 max-w-sm w-full bg-white dark:bg-gray-800 shadow-lg rounded-lg border border-gray-200 dark:border-gray-700 transform transition-all duration-300 translate-x-full`;

        const iconColors = {
            success: 'text-green-600',
            error: 'text-red-600',
            warning: 'text-yellow-600',
            info: 'text-blue-600'
        };

        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };

        notification.innerHTML = `
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="${icons[type]} ${iconColors[type]} text-xl"></i>
                    </div>
                    <div class="ml-3 w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">${title}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">${message}</p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button class="inline-flex text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }

    // Trading signals simulation
    const tradingPairs = ['EUR/USD', 'GBP/USD', 'USD/JPY', 'BTC/USD', 'ETH/USD'];
    function simulateTradingSignal() {
        const pair = tradingPairs[Math.floor(Math.random() * tradingPairs.length)];
        const action = Math.random() > 0.5 ? 'BUY' : 'SELL';
        const profit = (Math.random() * 200 + 50).toFixed(2);

        showNotification(
            `Trading Signal: ${action} ${pair}`,
            `Potential profit: $${profit}`,
            'success'
        );
    }

    // Simulate trading signals every 30 seconds
    setInterval(simulateTradingSignal, 30000);

    // Market data updates
    function updateMarketData() {
        const marketElements = document.querySelectorAll('[data-market]');
        marketElements.forEach(element => {
            const currentValue = parseFloat(element.textContent.replace(/[^0-9.-]+/g, ''));
            const change = (Math.random() - 0.5) * 0.1;
            const newValue = currentValue + change;

            element.textContent = newValue.toFixed(2);

            // Add color based on change
            if (change > 0) {
                element.classList.remove('text-red-600');
                element.classList.add('text-green-600');
            } else {
                element.classList.remove('text-green-600');
                element.classList.add('text-red-600');
            }
        });
    }

    // Update market data every 5 seconds
    setInterval(updateMarketData, 5000);

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Enhanced tooltips
    const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
    tooltipTriggers.forEach(trigger => {
        let tooltip;

        trigger.addEventListener('mouseenter', function() {
            const text = this.getAttribute('data-tooltip');
            tooltip = document.createElement('div');
            tooltip.className = 'absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded shadow-lg whitespace-nowrap';
            tooltip.textContent = text;

            document.body.appendChild(tooltip);

            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
        });

        trigger.addEventListener('mouseleave', function() {
            if (tooltip) {
                tooltip.remove();
            }
        });
    });

    // Progressive enhancement for forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            }
        });
    });

    // Charts enhancement for better mobile experience
    function optimizeChartsForMobile() {
        const isMobile = window.innerWidth < 768;
        const charts = document.querySelectorAll('.tradingview-widget-container');

        charts.forEach(chart => {
            if (isMobile) {
                chart.style.height = '250px';
            } else {
                chart.style.height = '400px';
            }
        });
    }

    // Optimize on load and resize
    optimizeChartsForMobile();
    window.addEventListener('resize', optimizeChartsForMobile);

    // Copy to clipboard functionality
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            showNotification('Copied!', 'Text copied to clipboard', 'success');
        }).catch(function() {
            showNotification('Error', 'Failed to copy text', 'error');
        });
    }

    // Add copy functionality to elements with data-copy attribute
    document.querySelectorAll('[data-copy]').forEach(element => {
        element.addEventListener('click', function() {
            const text = this.getAttribute('data-copy') || this.textContent;
            copyToClipboard(text);
        });
    });

    // Performance monitoring
    function trackPerformance() {
        if ('performance' in window) {
            window.addEventListener('load', function() {
                const navigation = performance.getEntriesByType('navigation')[0];
                const loadTime = navigation.loadEventEnd - navigation.loadEventStart;

                if (loadTime > 3000) {
                    console.warn('Dashboard load time is slow:', loadTime + 'ms');
                }
            });
        }
    }

    trackPerformance();
});

// Utility functions
window.DashboardUtils = {
    formatCurrency: function(amount, currency = '$') {
        return currency + parseFloat(amount).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },

    formatPercentage: function(value) {
        return (value >= 0 ? '+' : '') + value.toFixed(2) + '%';
    },

    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};
