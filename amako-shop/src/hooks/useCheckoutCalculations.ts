import { useMemo } from 'react';
import { Money } from '../types';

/**
 * Hook to handle tax and total amount calculations.
 * 
 * TODO: Replace hardcoded 13% tax with API call to fetch branch tax rate.
 */
export const useCheckoutCalculations = (
    subtotal: Money,
    appliedOffer: any,
    totalAfterDiscount: number
) => {
    const calculateTax = (amount: number): Money => {
        const taxRate = 13; // 13% tax rate
        return { currency: 'NPR', amount: amount * (taxRate / 100) };
    };

    // Memoize tax calculation
    const tax = useMemo(() => calculateTax(subtotal.amount), [subtotal.amount]);

    // Base total without offer discounts (but including tax)
    const total: Money = useMemo(() => ({
        currency: 'NPR',
        amount: subtotal.amount + tax.amount
    }), [subtotal.amount, tax.amount]);

    // Final total after applying offers if any
    const finalTotalAmount = useMemo(() => {
        return appliedOffer ? totalAfterDiscount + tax.amount : total.amount;
    }, [appliedOffer, totalAfterDiscount, tax.amount, total.amount]);

    return {
        tax,
        total,
        finalTotalAmount,
        calculateTax
    };
};
