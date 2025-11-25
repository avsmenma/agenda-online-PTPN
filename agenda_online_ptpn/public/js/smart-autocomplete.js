/**
 * Smart Autocomplete Library
 * Handles autocomplete functionality for dynamically added input fields
 * Supports event delegation for dynamic content
 */
class SmartAutocomplete {
    constructor(options = {}) {
        this.options = {
            minLength: 2,
            debounceDelay: 300,
            maxSuggestions: 10,
            ...options
        };

        this.cache = new Map(); // Cache for API responses
        this.activeRequests = new Map(); // Track active requests
        this.activeDropdown = null; // Currently active dropdown

        this.init();
    }

    init() {
        // Use event delegation for dynamically added elements
        document.addEventListener('focusin', this.handleFocusIn.bind(this));
        document.addEventListener('input', this.handleInput.bind(this));
        document.addEventListener('keydown', this.handleKeydown.bind(this));
        document.addEventListener('click', this.handleOutsideClick.bind(this));

        console.log('Smart Autocomplete initialized');
    }

    handleFocusIn(e) {
        const input = e.target;
        if (this.isAutocompleteInput(input)) {
            this.showInitialSuggestions(input);
        }
    }

    handleInput(e) {
        const input = e.target;
        if (!this.isAutocompleteInput(input)) return;

        const query = input.value.trim();
        const autocompleteType = input.dataset.autocomplete;

        // Debounce the input
        this.debounce(() => {
            if (query.length >= this.options.minLength) {
                this.fetchSuggestions(input, query, autocompleteType);
            } else {
                this.hideDropdown(input);
            }
        }, this.options.debounceDelay)();
    }

    handleKeydown(e) {
        const input = e.target;
        if (!this.isAutocompleteInput(input)) return;

        const dropdown = this.getDropdown(input);
        if (!dropdown) return;

        const items = dropdown.querySelectorAll('.autocomplete-item');
        const activeItem = dropdown.querySelector('.autocomplete-item.active');

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.selectNextItem(items, activeItem);
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.selectPreviousItem(items, activeItem);
                break;
            case 'Enter':
                e.preventDefault();
                if (activeItem) {
                    this.selectItem(activeItem, input);
                } else {
                    this.hideDropdown(input);
                }
                break;
            case 'Escape':
                e.preventDefault();
                this.hideDropdown(input);
                break;
            case 'Tab':
                this.hideDropdown(input);
                break;
        }
    }

    handleOutsideClick(e) {
        if (!e.target.closest('.autocomplete-container') &&
            !e.target.closest('.autocomplete-dropdown')) {
            this.hideAllDropdowns();
        }
    }

    isAutocompleteInput(element) {
        return element.tagName === 'INPUT' && element.dataset.autocomplete;
    }

    getDropdown(input) {
        return input.parentElement.querySelector('.autocomplete-dropdown');
    }

    createDropdown(input) {
        let dropdown = this.getDropdown(input);

        if (!dropdown) {
            dropdown = document.createElement('div');
            dropdown.className = 'autocomplete-dropdown';
            input.parentElement.appendChild(dropdown);
        }

        return dropdown;
    }

    showInitialSuggestions(input) {
        const autocompleteType = input.dataset.autocomplete;
        const cacheKey = `${autocompleteType}:recent`;

        if (this.cache.has(cacheKey)) {
            this.renderDropdown(input, this.cache.get(cacheKey));
        }
    }

    async fetchSuggestions(input, query, autocompleteType) {
        const cacheKey = `${autocompleteType}:${query}`;

        // Check cache first
        if (this.cache.has(cacheKey)) {
            this.renderDropdown(input, this.cache.get(cacheKey));
            return;
        }

        // Cancel previous request for this input
        const previousRequestId = `${autocompleteType}_request`;
        if (this.activeRequests.has(previousRequestId)) {
            this.activeRequests.get(previousRequestId).abort();
        }

        // Show loading state
        this.showLoading(input);

        try {
            const controller = new AbortController();
            this.activeRequests.set(previousRequestId, controller);

            const url = `/api/autocomplete/${autocompleteType}?q=${encodeURIComponent(query)}&limit=${this.options.maxSuggestions}`;

            const response = await fetch(url, {
                signal: controller.signal,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const suggestions = await response.json();

            // Cache the results
            this.cache.set(cacheKey, suggestions);

            // Also cache as recent for initial suggestions
            if (suggestions.length > 0) {
                this.cache.set(`${autocompleteType}:recent`, suggestions.slice(0, 5));
            }

            this.renderDropdown(input, suggestions);

        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Autocomplete error:', error);
                this.showError(input, 'Gagal memuat saran. Silakan coba lagi.');
            }
        } finally {
            this.activeRequests.delete(previousRequestId);
        }
    }

    renderDropdown(input, suggestions) {
        const dropdown = this.createDropdown(input);

        if (suggestions.length === 0) {
            dropdown.innerHTML = `
                <div class="autocomplete-item disabled">
                    <i class="fas fa-info-circle"></i>
                    Tidak ada saran ditemukan
                </div>
            `;
        } else {
            dropdown.innerHTML = suggestions.map((suggestion, index) => `
                <div class="autocomplete-item ${index === 0 ? 'active' : ''}" data-value="${this.escapeHtml(suggestion)}">
                    <i class="fas fa-check-circle"></i>
                    ${this.escapeHtml(suggestion)}
                </div>
            `).join('');

            // Add click handlers
            dropdown.querySelectorAll('.autocomplete-item:not(.disabled)').forEach(item => {
                item.addEventListener('click', () => this.selectItem(item, input));
            });
        }

        dropdown.classList.add('show');
        this.activeDropdown = dropdown;
    }

    selectItem(item, input) {
        const value = item.dataset.value;
        input.value = value;

        // Trigger change event for form validation
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));

        this.hideDropdown(input);

        // Move to next input if available
        this.moveToNextInput(input);
    }

    moveToNextInput(currentInput) {
        const form = currentInput.closest('form');
        if (!form) return;

        const inputs = Array.from(form.querySelectorAll('input:not([type="hidden"]), select, textarea'));
        const currentIndex = inputs.indexOf(currentInput);

        if (currentIndex < inputs.length - 1) {
            const nextInput = inputs[currentIndex + 1];
            nextInput.focus();
        }
    }

    selectNextItem(items, activeItem) {
        const currentIndex = activeItem ? Array.from(items).indexOf(activeItem) : -1;
        const nextIndex = (currentIndex + 1) % items.length;

        if (activeItem) activeItem.classList.remove('active');
        items[nextIndex].classList.add('active');

        // Scroll into view if needed
        items[nextIndex].scrollIntoView({ block: 'nearest' });
    }

    selectPreviousItem(items, activeItem) {
        const currentIndex = activeItem ? Array.from(items).indexOf(activeItem) : items.length;
        const prevIndex = currentIndex > 0 ? currentIndex - 1 : items.length - 1;

        if (activeItem) activeItem.classList.remove('active');
        items[prevIndex].classList.add('active');

        // Scroll into view if needed
        items[prevIndex].scrollIntoView({ block: 'nearest' });
    }

    showLoading(input) {
        const dropdown = this.createDropdown(input);
        dropdown.innerHTML = `
            <div class="autocomplete-item disabled">
                <i class="fas fa-spinner fa-spin"></i>
                Memuat saran...
            </div>
        `;
        dropdown.classList.add('show');
    }

    showError(input, message) {
        const dropdown = this.createDropdown(input);
        dropdown.innerHTML = `
            <div class="autocomplete-item disabled error">
                <i class="fas fa-exclamation-triangle"></i>
                ${this.escapeHtml(message)}
            </div>
        `;
        dropdown.classList.add('show');

        // Auto-hide after 3 seconds
        setTimeout(() => this.hideDropdown(input), 3000);
    }

    hideDropdown(input) {
        const dropdown = this.getDropdown(input);
        if (dropdown) {
            dropdown.classList.remove('show');
        }
        if (this.activeDropdown === dropdown) {
            this.activeDropdown = null;
        }
    }

    hideAllDropdowns() {
        document.querySelectorAll('.autocomplete-dropdown.show').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
        this.activeDropdown = null;
    }

    debounce(func, wait) {
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

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Public methods
    clearCache() {
        this.cache.clear();
    }

    destroy() {
        document.removeEventListener('focusin', this.handleFocusIn);
        document.removeEventListener('input', this.handleInput);
        document.removeEventListener('keydown', this.handleKeydown);
        document.removeEventListener('click', this.handleOutsideClick);

        // Cancel all active requests
        this.activeRequests.forEach(controller => controller.abort());
        this.activeRequests.clear();

        // Remove all dropdowns
        document.querySelectorAll('.autocomplete-dropdown').forEach(dropdown => dropdown.remove());
    }
}

// Initialize SmartAutocomplete when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.smartAutocomplete = new SmartAutocomplete({
        minLength: 2,
        debounceDelay: 300,
        maxSuggestions: 10
    });
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SmartAutocomplete;
}