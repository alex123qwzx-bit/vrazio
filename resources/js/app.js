import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

/* =============================================
   VRAZIO — Client-Side JavaScript
   ============================================= */

// Cart drawer toggle
document.addEventListener('alpine:init', () => {
    Alpine.store('cart', {
        open: false,
        items: JSON.parse(localStorage.getItem('vrazio_cart') || '[]'),

        toggle() {
            this.open = !this.open;
        },

        get count() {
            return this.items.reduce((sum, item) => sum + item.quantity, 0);
        },

        get total() {
            return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        addItem(product) {
            const existing = this.items.find(i => i.id === product.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.items.push({ ...product, quantity: 1 });
            }
            this.persist();
        },

        removeItem(productId) {
            this.items = this.items.filter(i => i.id !== productId);
            this.persist();
        },

        updateQuantity(productId, qty) {
            const item = this.items.find(i => i.id === productId);
            if (item) {
                item.quantity = Math.max(1, qty);
                this.persist();
            }
        },

        clear() {
            this.items = [];
            this.persist();
        },

        persist() {
            localStorage.setItem('vrazio_cart', JSON.stringify(this.items));
        },
    });

    Alpine.store('otp', {
        show: false,
        phone: '',
        otp: '',
        sending: false,
        verifying: false,
        sent: false,
        verified: false,
        error: '',
        cooldown: 0,
        timer: null,

        open(phone) {
            this.phone = phone;
            this.show = true;
            this.error = '';
            this.otp = '';
            this.sent = false;
            this.verified = false;
        },

        close() {
            this.show = false;
            if (this.timer) clearInterval(this.timer);
        },

        async sendOtp() {
            this.sending = true;
            this.error = '';
            try {
                const res = await fetch('/api/checkout/otp/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ phone: this.phone }),
                });
                const data = await res.json();
                if (data.success) {
                    this.sent = true;
                    this.startCooldown(30);
                } else {
                    this.error = data.message || 'Failed to send OTP';
                }
            } catch (e) {
                this.error = 'Network error. Please try again.';
            }
            this.sending = false;
        },

        async verifyOtp() {
            this.verifying = true;
            this.error = '';
            try {
                const res = await fetch('/api/checkout/otp/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ phone: this.phone, otp: this.otp }),
                });
                const data = await res.json();
                if (data.success) {
                    this.verified = true;
                    this.show = false;
                    // Dispatch custom event for Livewire/checkout
                    window.dispatchEvent(new CustomEvent('otp-verified', { detail: { phone: this.phone } }));
                } else {
                    this.error = data.message || 'Invalid OTP';
                }
            } catch (e) {
                this.error = 'Network error. Please try again.';
            }
            this.verifying = false;
        },

        startCooldown(seconds) {
            this.cooldown = seconds;
            if (this.timer) clearInterval(this.timer);
            this.timer = setInterval(() => {
                this.cooldown--;
                if (this.cooldown <= 0) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            }, 1000);
        },
    });
});

// Format Indian currency
window.formatINR = function(amount) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};
