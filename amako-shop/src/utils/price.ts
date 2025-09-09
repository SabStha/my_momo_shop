import { Money } from '../types';

/**
 * Sum multiple Money objects
 * All Money objects must have the same currency (NPR)
 */
export function sumMoney(...amounts: Money[]): Money {
  if (amounts.length === 0) {
    return { currency: 'NPR', amount: 0 };
  }

  // Validate all amounts have the same currency
  const currency = amounts[0].currency;
  if (!amounts.every(amount => amount.currency === currency)) {
    throw new Error('All amounts must have the same currency');
  }

  const totalAmount = amounts.reduce((sum, amount) => sum + amount.amount, 0);
  
  return {
    currency,
    amount: totalAmount,
  };
}

/**
 * Add two Money objects
 * Both Money objects must have the same currency (NPR)
 */
export function addMoney(a: Money, b: Money): Money {
  if (a.currency !== b.currency) {
    throw new Error('Cannot add amounts with different currencies');
  }

  return {
    currency: a.currency,
    amount: a.amount + b.amount,
  };
}

/**
 * Multiply a Money object by a quantity
 */
export function multiplyMoney(money: Money, quantity: number): Money {
  return {
    currency: money.currency,
    amount: money.amount * quantity,
  };
}

/**
 * Calculate the total price for an item with variants and add-ons
 */
export function calculateItemTotal(
  basePrice: Money,
  variantPriceDiff?: Money,
  addOns: Money[] = [],
  quantity: number = 1
): Money {
  let total = basePrice;
  
  // Add variant price difference if selected
  if (variantPriceDiff) {
    total = addMoney(total, variantPriceDiff);
  }
  
  // Add add-ons
  if (addOns.length > 0) {
    const addOnsTotal = sumMoney(...addOns);
    total = addMoney(total, addOnsTotal);
  }
  
  // Multiply by quantity
  return multiplyMoney(total, quantity);
}

/**
 * Format Money object to display string
 */
export function formatMoney(money: Money, showCurrency: boolean = true): string {
  const formattedAmount = money.amount.toLocaleString('en-IN');
  return showCurrency ? `Rs. ${formattedAmount}` : formattedAmount;
}

/**
 * Check if two Money objects are equal
 */
export function isMoneyEqual(a: Money, b: Money): boolean {
  return a.currency === b.currency && a.amount === b.amount;
}

/**
 * Check if a Money amount is zero
 */
export function isMoneyZero(money: Money): boolean {
  return money.amount === 0;
}
