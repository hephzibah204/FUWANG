import Swal from 'sweetalert2';

// Basic Error Mappings (Supports Localization in future if we use a window.locale object)
const errorMappings = {
    'insufficient_balance': {
        title: 'Insufficient Balance',
        text: 'You do not have enough funds to complete this transaction. Please fund your wallet.',
        icon: 'warning',
        confirmButtonText: 'Add Funds',
        actionUrl: '/wallet/fund' // Example URL
    },
    'unauthorized': {
        title: 'Unauthorized',
        text: 'Please log in to continue.',
        icon: 'error',
        confirmButtonText: 'Log In',
        actionUrl: '/login'
    },
    'default': {
        title: 'An Error Occurred',
        text: 'Something went wrong. Please try again later.',
        icon: 'error',
        confirmButtonText: 'Close'
    }
};

/**
 * Matches error messages/codes to predefined configurations
 */
function getErrorConfig(response) {
    const msg = (response?.data?.message || '').toLowerCase();
    const status = response?.status;

    if (msg.includes('insufficient balance') || response?.data?.code === 'INSUFFICIENT_BALANCE') {
        return errorMappings['insufficient_balance'];
    }

    if (status === 401 || msg.includes('unauthenticated') || msg.includes('unauthorized')) {
        return errorMappings['unauthorized'];
    }

    // Default configuration with dynamic message if provided
    const config = { ...errorMappings['default'] };
    if (response?.data?.message) {
        config.text = response.data.message;
    }

    return config;
}

/**
 * Shows the Error Modal
 */
export function showErrorModal(response) {
    const config = getErrorConfig(response);

    Swal.fire({
        title: config.title,
        text: config.text,
        icon: config.icon,
        confirmButtonText: config.confirmButtonText,
        showCancelButton: !!config.actionUrl,
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#0056b3',
        cancelButtonColor: '#6c757d',
        // Accessibility attributes
        customClass: {
            popup: 'accessible-error-modal'
        },
        didOpen: () => {
            const popup = Swal.getPopup();
            if (popup) {
                popup.setAttribute('role', 'alertdialog');
                popup.setAttribute('aria-modal', 'true');
                popup.setAttribute('aria-labelledby', Swal.getTitle().id);
                popup.setAttribute('aria-describedby', Swal.getHtmlContainer().id);
            }
        }
    }).then((result) => {
        if (result.isConfirmed && config.actionUrl) {
            window.location.href = config.actionUrl;
        }
    });
}

/**
 * Axios Interceptor Setup
 */
export function setupErrorInterceptor(axiosInstance) {
    axiosInstance.interceptors.response.use(
        (response) => {
            // Intercept API responses that return 200 OK but contain a logical error
            if (response.data && response.data.status === false && response.data.message) {
                if (response.config && response.config.hideError) return Promise.reject(response.data);
                showErrorModal(response);
                return Promise.reject(response.data);
            }
            return response;
        },
        (error) => {
            // Handle actual HTTP errors (4xx, 5xx)
            if (error.config && error.config.hideError) return Promise.reject(error);
            showErrorModal(error.response || error);
            return Promise.reject(error);
        }
    );
}
