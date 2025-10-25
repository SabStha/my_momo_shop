/**
 * Test utility for delivery notifications
 * Use this to test different notification styles
 */

import { upsertOrderNotification } from '../notifications/delivery-notifications';

/**
 * Test: Order just placed
 */
export async function testOrderConfirmed() {
  await upsertOrderNotification(1001, 'confirmed', {
    orderNumber: 'ORD-TEST001',
    percent: 20
  });
}

/**
 * Test: Kitchen preparing order
 */
export async function testOrderPreparing() {
  await upsertOrderNotification(1001, 'preparing', {
    orderNumber: 'ORD-TEST001',
    percent: 40
  });
}

/**
 * Test: Order ready, rider about to pick up
 */
export async function testOrderReady() {
  await upsertOrderNotification(1001, 'ready', {
    orderNumber: 'ORD-TEST001',
    riderName: 'Suman',
    percent: 60
  });
}

/**
 * Test: Delivery started (like image) - with ETA and rider
 */
export async function testDeliveryStarted() {
  await upsertOrderNotification(1001, 'out_for_delivery', {
    orderNumber: 'ORD-68F69EC',
    riderName: 'Suman',
    riderPhone: '+977-9841234567',
    etaMin: [18, 22],
    percent: 80
  });
}

/**
 * Test: Rider arriving soon
 */
export async function testDeliveryArriving() {
  await upsertOrderNotification(1001, 'arriving', {
    orderNumber: 'ORD-68F69EC',
    riderName: 'Suman',
    riderPhone: '+977-9841234567',
    etaMin: [2, 4],
    percent: 95
  });
}

/**
 * Test: Order delivered
 */
export async function testOrderDelivered() {
  await upsertOrderNotification(1001, 'delivered', {
    orderNumber: 'ORD-68F69EC',
    percent: 100
  });
}

/**
 * Test: Full delivery cycle (all stages)
 */
export async function testFullDeliveryCycle() {
  console.log('🧪 [TEST] Starting full delivery cycle test...');
  
  // 1. Order confirmed
  await testOrderConfirmed();
  await wait(3000);
  
  // 2. Preparing
  await testOrderPreparing();
  await wait(3000);
  
  // 3. Ready
  await testOrderReady();
  await wait(3000);
  
  // 4. Delivery started
  await testDeliveryStarted();
  await wait(3000);
  
  // 5. Arriving
  await testDeliveryArriving();
  await wait(3000);
  
  // 6. Delivered
  await testOrderDelivered();
  
  console.log('✅ [TEST] Full delivery cycle complete!');
}

/**
 * Wait utility
 */
function wait(ms: number): Promise<void> {
  return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * Export all tests
 */
export const NotificationTests = {
  orderConfirmed: testOrderConfirmed,
  orderPreparing: testOrderPreparing,
  orderReady: testOrderReady,
  deliveryStarted: testDeliveryStarted,
  deliveryArriving: testDeliveryArriving,
  orderDelivered: testOrderDelivered,
  fullCycle: testFullDeliveryCycle,
};

// Usage in your app:
// import { NotificationTests } from './src/services/test-notifications';
// 
// // Test delivery started notification (like in the image)
// await NotificationTests.deliveryStarted();
//
// // Or test full cycle
// await NotificationTests.fullCycle();

