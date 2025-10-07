// Server-side cart calculation client
class ServerCartCalculator {
    constructor() {
        this.baseUrl = '/api/cart';
    }

    /**
     * Calculate cart totals server-side
     */
    async calculateTotals(cartItems) {
        try {
            const response = await fetch(`${this.baseUrl}/calculate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ items: cartItems })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to calculate totals');
            }

            return data;
        } catch (error) {
            console.error('Server calculation error:', error);
            throw error;
        }
    }

    /**
     * Validate cart items server-side
     */
    async validateItems(cartItems) {
        try {
            const response = await fetch(`${this.baseUrl}/validate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ items: cartItems })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Cart validation failed');
            }

            return data;
        } catch (error) {
            console.error('Server validation error:', error);
            throw error;
        }
    }

    /**
     * Get server-calculated totals for display
     */
    async getDisplayTotals(cartItems) {
        try {
            const result = await this.calculateTotals(cartItems);
            
            if (!result.success) {
                return {
                    success: false,
                    errors: result.errors,
                    message: result.message
                };
            }

            return {
                success: true,
                subtotal: result.cart.subtotal,
                taxAmount: result.cart.tax_amount,
                grandTotal: result.cart.grand_total,
                items: result.cart.items
            };
        } catch (error) {
            return {
                success: false,
                errors: [error.message],
                message: 'Failed to calculate totals'
            };
        }
    }
}

// Global instance
window.serverCartCalculator = new ServerCartCalculator();

