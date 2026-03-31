import axios from 'axios';

class WhatsAppWidget {
    constructor() {
        this.container = null;
        this.config = null;
        this.syncInterval = null;
        this.init();
    }

    async init() {
        // Create container if not exists
        this.container = document.createElement('div');
        this.container.id = 'dynamic-whatsapp-widget';
        document.body.appendChild(this.container);

        await this.fetchConfig();

        // Setup real-time polling every 60 seconds
        this.syncInterval = setInterval(() => this.fetchConfig(), 60000);
    }

    async fetchConfig() {
        try {
            const response = await axios.get('/api/whatsapp-widget/config', { hideError: true });
            if (response.data.status) {
                const newConfig = response.data.data;
                
                // Only re-render if config changed or it's the first load
                if (JSON.stringify(this.config) !== JSON.stringify(newConfig)) {
                    this.config = newConfig;
                    this.render();
                }
            }
        } catch (error) {
            console.error('Failed to load WhatsApp widget config:', error);
            // Fallback gracefully (hide widget if config fails)
            if (!this.config) {
                this.container.innerHTML = '';
            }
        }
    }

    shouldDisplay() {
        if (!this.config || !this.config.enabled || !this.config.number) {
            return false;
        }

        // Check Display Pages Rules
        const isAuth = document.querySelector('meta[name="auth-check"]')?.getAttribute('content') === 'true';
        if (this.config.display_pages === 'auth' && !isAuth) return false;
        if (this.config.display_pages === 'guest' && isAuth) return false;

        // Check Operating Hours
        if (this.config.hours_start !== '00:00' || this.config.hours_end !== '23:59') {
            const serverTime = this.config.server_time; // 'HH:mm'
            if (serverTime < this.config.hours_start || serverTime > this.config.hours_end) {
                return false;
            }
        }

        return true;
    }

    render() {
        if (!this.shouldDisplay()) {
            this.container.innerHTML = '';
            return;
        }

        // Parse config
        const { position, size, color, hover_color, x_offset, y_offset, animation, number, prefilled_message } = this.config;

        // Position styles
        let positionStyles = `position: fixed; z-index: 9999; width: ${size}px; height: ${size}px;`;
        if (position.includes('bottom')) positionStyles += ` bottom: ${y_offset}px;`;
        else positionStyles += ` top: ${y_offset}px;`;

        if (position.includes('right')) positionStyles += ` right: ${x_offset}px;`;
        else positionStyles += ` left: ${x_offset}px;`;

        // Animation classes
        let animClass = '';
        if (animation === 'bounce') animClass = 'animate-bounce';
        else if (animation === 'pulse') animClass = 'animate-pulse';
        else if (animation === 'fade') animClass = 'animate-fade-in';

        // URL Generation
        const cleanNumber = number.replace(/[^0-9]/g, '');
        const message = encodeURIComponent(prefilled_message || '');
        const whatsappUrl = `https://wa.me/${cleanNumber}?text=${message}`;

        this.container.innerHTML = `
            <a href="${whatsappUrl}" 
               target="_blank" 
               rel="noopener noreferrer"
               id="wa-widget-btn"
               class="${animClass}"
               style="${positionStyles} background-color: ${color}; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.3); transition: all 0.3s ease; text-decoration: none;"
               aria-label="Chat with us on WhatsApp"
               role="button"
               tabindex="0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width: 50%; height: 50%; fill: white;">
                    <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7 .9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
                </svg>
            </a>
        `;

        const btn = document.getElementById('wa-widget-btn');
        
        // Hover effects
        btn.addEventListener('mouseenter', () => {
            btn.style.backgroundColor = hover_color;
            btn.style.transform = 'scale(1.1)';
        });
        
        btn.addEventListener('mouseleave', () => {
            btn.style.backgroundColor = color;
            btn.style.transform = 'scale(1)';
        });

        // Click Tracking Analytics
        btn.addEventListener('click', () => {
            axios.post('/api/whatsapp-widget/click', {
                page_url: window.location.href
            }, { hideError: true }).catch(e => console.error('Analytics log failed', e));
        });
    }
}

// Global Custom Animations (if not using Tailwind/Animate.css natively)
const style = document.createElement('style');
style.innerHTML = `
    @keyframes waBounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-25%); }
    }
    @keyframes waPulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: .8; }
    }
    @keyframes waFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .animate-bounce { animation: waBounce 2s infinite; }
    .animate-pulse { animation: waPulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    .animate-fade-in { animation: waFadeIn 0.5s ease-in-out; }
`;
document.head.appendChild(style);

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    new WhatsAppWidget();
});
