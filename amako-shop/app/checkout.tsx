import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, Alert, TouchableOpacity, Modal, ScrollView, Image } from 'react-native';
import { router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { WebView } from 'react-native-webview';
import { ScrollableScreen } from '../src/components';
import { useCartItems, useCartSubtotal, useClearCart } from '../src/state/cart';
import { useCreateOrder } from '../src/state/orders';
import { Button, Price, Card } from '../src/ui';
import { spacing, fontSizes, fontWeights, colors, radius } from '../src/ui';
import { addMoney, multiplyMoney } from '../src/utils/price';
import { Money } from '../src/types';
import { useProductImages } from '../src/hooks/useProductImages';

type PaymentMethod = 'cash' | 'esewa';

export default function CheckoutScreen() {
  const items = useCartItems();
  const subtotal = useCartSubtotal();
  const clearCart = useClearCart();
  const createOrder = useCreateOrder();
  const { getImageUrl, isLoading: imagesLoading } = useProductImages();
  const [paymentMethod, setPaymentMethod] = useState<PaymentMethod>('cash');
  const [showESewaPayment, setShowESewaPayment] = useState(false);
  const [paymentStatus, setPaymentStatus] = useState<'pending' | 'success' | 'failed' | null>(null);
  const [productImages, setProductImages] = useState<Map<string, any>>(new Map());
  
  // Debug API configuration
  useEffect(() => {
    if (__DEV__) {
      console.log('🔧 Checkout: API Configuration Debug');
      console.log('🔧 Checkout: getImageUrl function:', typeof getImageUrl);
      console.log('🔧 Checkout: imagesLoading:', imagesLoading);
    }
  }, [getImageUrl, imagesLoading]);
  
  // Calculate fees and totals
  const deliveryFee: Money = { currency: 'NPR', amount: 0 }; // Free delivery
  const tax: Money = { currency: 'NPR', amount: 0 }; // No tax for now
  const total: Money = { currency: 'NPR', amount: subtotal.amount + deliveryFee.amount + tax.amount };

  const handlePlaceOrder = () => {
    if (__DEV__) {
      console.log('🛒 Checkout: Starting order creation...');
      console.log('🛒 Checkout: Cart items:', items);
      console.log('🛒 Checkout: Subtotal:', subtotal);
      console.log('🛒 Checkout: Total:', total);
      console.log('🛒 Checkout: Payment method:', paymentMethod);
    }

    if (paymentMethod === 'esewa') {
      // For eSewa, show payment modal first
      setShowESewaPayment(true);
      setPaymentStatus('pending');
      return;
    }

    // For cash payments, create order directly
    createOrderDirectly();
  };

  const createOrderDirectly = () => {
    // Create the order
    createOrder({
      items: [...items], // Copy items to avoid reference issues
      subtotal,
      deliveryFee,
      tax,
      total,
      status: 'pending',
      paymentMethod,
      deliveryAddress: '123 Main Street, Kathmandu, Nepal', // Sample address for now
      notes: '',
    });

    if (__DEV__) {
      console.log('🛒 Checkout: Order creation completed');
    }

    Alert.alert(
      'Order Placed!',
      'Your order has been placed successfully! You can track it in the Orders tab.',
      [
        {
          text: 'View Orders',
          onPress: () => {
            if (__DEV__) {
              console.log('🛒 Checkout: User chose to view orders');
            }
            clearCart();
            router.push('/(tabs)/orders');
          }
        },
        {
          text: 'Continue Shopping',
          onPress: () => {
            if (__DEV__) {
              console.log('🛒 Checkout: User chose to continue shopping');
            }
            clearCart();
            router.push('/(tabs)/');
          }
        }
      ]
    );
  };

  const handleAddAddress = () => {
    Alert.alert(
      'Add Address',
      'Address management will be implemented in a future update.',
      [{ text: 'OK' }]
    );
  };

  // Helper function to get food images from database or fallback to emojis
  const getFoodImage = (itemName: string) => {
    // Check if we have a cached image
    if (productImages.has(itemName)) {
      return productImages.get(itemName);
    }
    
    // Fallback to emoji placeholder
    const name = itemName.toLowerCase();
    if (name.includes('chicken')) {
      return { uri: 'https://via.placeholder.com/60x60/FF6B6B/FFFFFF?text=🍗' };
    } else if (name.includes('veg') || name.includes('vegetable')) {
      return { uri: 'https://via.placeholder.com/60x60/51CF66/FFFFFF?text=🥬' };
    } else if (name.includes('paneer')) {
      return { uri: 'https://via.placeholder.com/60x60/FFD43B/FFFFFF?text=🧀' };
    } else if (name.includes('cheese')) {
      return { uri: 'https://via.placeholder.com/60x60/FFD43B/FFFFFF?text=🧀' };
    } else if (name.includes('spicy')) {
      return { uri: 'https://via.placeholder.com/60x60/FF6B6B/FFFFFF?text=🌶️' };
    } else if (name.includes('tandoori')) {
      return { uri: 'https://via.placeholder.com/60x60/FF6B6B/FFFFFF?text=🔥' };
    } else if (name.includes('fried')) {
      return { uri: 'https://via.placeholder.com/60x60/FFD43B/FFFFFF?text=🍳' };
    } else if (name.includes('chilli') || name.includes('garlic')) {
      return { uri: 'https://via.placeholder.com/60x60/FF6B6B/FFFFFF?text=🧄' };
    } else {
      // Default to dumpling emoji for momos
      return { uri: 'https://via.placeholder.com/60x60/74C0FC/FFFFFF?text=🥟' };
    }
  };

  // Load product images from database
  useEffect(() => {
    const loadProductImages = async () => {
      const newImages = new Map();
      
      for (const item of items) {
        try {
          const imageUrl = await getImageUrl(item.name);
          if (imageUrl) {
            newImages.set(item.name, { uri: imageUrl });
          }
        } catch (error) {
          console.log('Failed to load image for:', item.name);
        }
      }
      
      setProductImages(newImages);
    };
    
    if (items.length > 0 && !imagesLoading) {
      loadProductImages();
    }
  }, [items, getImageUrl, imagesLoading]);

  return (
    <ScrollableScreen
      contentContainerStyle={{ paddingBottom: spacing.xl }}
      scrollViewProps={{
        showsVerticalScrollIndicator: true,
        bounces: true,
      }}
    >
             <View style={styles.backgroundPattern} />
      <View style={styles.container}>
        {/* Header with back button */}
        <View style={styles.header}>
          <TouchableOpacity
            style={styles.backButton}
            onPress={() => router.back()}
            accessibilityRole="button"
            accessibilityLabel="Go back"
          >
            <Ionicons name="arrow-back" size={24} color={colors.gray[700]} />
          </TouchableOpacity>
          <Text style={styles.title}>Checkout</Text>
          <View style={styles.headerSpacer} />
        </View>

                 {/* Header Banner */}
         <View style={styles.headerBanner}>
           <Text style={styles.headerImageTitle}>Almost There!</Text>
           <Text style={styles.headerImageSubtitle}>Complete your order</Text>
         </View>

        {/* Address Section */}
        <Card style={styles.section} padding="lg" radius="md" shadow="light">
          <View style={styles.addressHeader}>
            <View style={styles.addressIconContainer}>
              <Ionicons name="location" size={24} color={colors.primary[600]} />
            </View>
            <Text style={styles.sectionTitle}>Delivery Address</Text>
          </View>
          <View style={styles.addressPlaceholder}>
            <Text style={styles.addressText}>
              📍 123 Main Street, Kathmandu, Nepal
            </Text>
            <Text style={styles.addressSubtext}>
              (Sample address - will be configurable)
            </Text>
          </View>
          <Button
            title="Add New Address"
            onPress={handleAddAddress}
            variant="outline"
            size="md"
            style={styles.addAddressButton}
          />
        </Card>

        {/* Payment Method Section */}
        <Card style={styles.section} padding="lg" radius="md" shadow="light">
          <View style={styles.addressHeader}>
            <View style={styles.addressIconContainer}>
              <Ionicons name="card" size={24} color={colors.primary[600]} />
            </View>
            <Text style={styles.sectionTitle}>Payment Method</Text>
          </View>
          
          {/* Cash on Delivery */}
          <TouchableOpacity 
            style={[
              styles.paymentOption,
              paymentMethod === 'cash' && styles.paymentOptionSelected
            ]} 
            onPress={() => {
              console.log('🛒 Checkout: Cash payment method selected');
              setPaymentMethod('cash');
            }}
            activeOpacity={0.7}
          >
            <View style={styles.paymentOptionContent}>
              <View style={styles.paymentIconContainer}>
                <View style={styles.cashIcon}>
                  <Ionicons name="cash-outline" size={24} color={colors.gray[600]} />
                </View>
              </View>
              <View style={styles.paymentTextContainer}>
                <View style={styles.radioContainer}>
                  <View style={[
                    styles.radioButton,
                    paymentMethod === 'cash' && styles.radioButtonSelected
                  ]}>
                    {paymentMethod === 'cash' && <View style={styles.radioButtonInner} />}
                  </View>
                  <Text style={styles.paymentLabel}>Cash on Delivery</Text>
                </View>
                <Text style={styles.paymentDescription}>Pay when you receive your order</Text>
              </View>
            </View>
          </TouchableOpacity>

          {/* eSewa (Enabled) */}
          <TouchableOpacity 
            style={[
              styles.paymentOption,
              paymentMethod === 'esewa' && styles.paymentOptionSelected
            ]} 
            onPress={() => {
              console.log('🛒 Checkout: eSewa payment method selected');
              setPaymentMethod('esewa');
            }}
            activeOpacity={0.7}
          >
            <View style={styles.paymentOptionContent}>
              <View style={styles.paymentIconContainer}>
                <View style={styles.esewaIcon}>
                  <Text style={styles.esewaIconText}>e</Text>
                </View>
              </View>
              <View style={styles.paymentTextContainer}>
                <View style={styles.radioContainer}>
                  <View style={[
                    styles.radioButton,
                    paymentMethod === 'esewa' && styles.radioButtonSelected
                  ]}>
                    {paymentMethod === 'esewa' && <View style={styles.radioButtonInner} />}
                  </View>
                  <Text style={styles.paymentLabel}>eSewa</Text>
                </View>
                <Text style={styles.paymentDescription}>Secure online payment</Text>
              </View>
            </View>
          </TouchableOpacity>
        </Card>

        {/* Order Summary Section */}
        <Card style={styles.section} padding="lg" radius="md" shadow="light">
          <View style={styles.addressHeader}>
            <View style={styles.addressIconContainer}>
              <Ionicons name="receipt" size={24} color={colors.primary[600]} />
            </View>
            <Text style={styles.sectionTitle}>Order Summary</Text>
          </View>
          
          {/* Order Items */}
          <View style={styles.orderItems}>
            {items.map((item, index) => {
              // Calculate unit price: base + add-ons
              let unitPrice = item.unitBasePrice;
              if (item.addOns && item.addOns.length > 0) {
                const addOnsTotal = item.addOns.reduce((sum, addon) => 
                  addMoney(sum, addon.price), { currency: 'NPR' as const, amount: 0 }
                );
                unitPrice = addMoney(unitPrice, addOnsTotal);
              }

              // Calculate line total
              const lineTotal = multiplyMoney(unitPrice, item.qty);

              return (
                <View key={`${item.itemId}-${item.variantId || 'base'}-${index}`} style={styles.orderItem}>
                  <View style={styles.orderItemLeft}>
                                         <View style={styles.orderItemHeader}>
                       <Image 
                         source={getFoodImage(item.name)}
                         style={styles.orderItemImage}
                         resizeMode="cover"
                       />
                       <View style={styles.orderItemInfo}>
                        <Text style={styles.orderItemName} numberOfLines={1}>
                          {item.name}
                        </Text>
                        {item.variantName && (
                          <Text style={styles.orderItemVariant}>
                            {item.variantName}
                          </Text>
                        )}
                        {item.addOns && item.addOns.length > 0 && (
                          <Text style={styles.orderItemAddOns}>
                            + {item.addOns.map(a => a.name).join(', ')}
                          </Text>
                        )}
                      </View>
                    </View>
                    <Text style={styles.orderItemQuantity}>
                      Qty: {item.qty} × Rs. {unitPrice.amount}
                    </Text>
                  </View>
                  <Price 
                    value={lineTotal} 
                    size="md" 
                    weight="semibold" 
                    color={colors.primary[600]}
                  />
                </View>
              );
            })}
          </View>

          {/* Order Totals */}
          <View style={styles.orderTotals}>
            <View style={styles.totalRow}>
              <Text style={styles.totalLabel}>Subtotal:</Text>
              <Price 
                value={subtotal} 
                size="md" 
                weight="medium"
              />
            </View>
            
            <View style={styles.totalRow}>
              <Text style={styles.totalLabel}>Delivery Fee:</Text>
              <Price 
                value={deliveryFee} 
                size="md" 
                weight="medium"
                color={deliveryFee.amount === 0 ? colors.success[600] : colors.gray[600]}
              />
            </View>
            
            <View style={styles.totalRow}>
              <Text style={styles.totalLabel}>Tax:</Text>
              <Price 
                value={tax} 
                size="md" 
                weight="medium"
              />
            </View>
            
            <View style={styles.divider} />
            
            <View style={styles.totalRow}>
              <Text style={styles.grandTotalLabel}>Total:</Text>
              <Price 
                value={total} 
                size="lg" 
                weight="bold" 
                color={colors.primary[600]}
              />
            </View>
          </View>
        </Card>

        {/* Place Order Button */}
        <View style={styles.placeOrderSection}>
          <View style={styles.placeOrderCard}>
            <View style={styles.placeOrderHeader}>
              <Ionicons name="checkmark-circle" size={24} color={colors.success[600]} />
              <Text style={styles.placeOrderTitle}>Ready to Order</Text>
            </View>
            <Text style={styles.placeOrderSubtitle}>
              Review your order details above and click the button below to complete your purchase
            </Text>
            <Button
              title="Place Order"
              onPress={handlePlaceOrder}
              variant="solid"
              size="lg"
              style={styles.placeOrderButton}
            />
          </View>
        </View>

        {/* Debug: Extra content to test scrolling */}
        <View style={styles.debugSection}>
          <View style={styles.debugHeader}>
            <Ionicons name="information-circle" size={20} color={colors.gray[500]} />
            <Text style={styles.debugTitle}>Order Details</Text>
          </View>
          <Text style={styles.debugText}>Current payment method: {paymentMethod}</Text>
          <Text style={styles.debugText}>Total items: {items.length}</Text>
          <Text style={styles.debugText}>Total amount: Rs. {total.amount}</Text>
        </View>

        {/* eSewa Payment Modal */}
        <Modal
          visible={showESewaPayment}
          animationType="slide"
          presentationStyle="pageSheet"
        >
          <View style={styles.paymentModal}>
            {/* Header */}
            <View style={styles.paymentModalHeader}>
              <TouchableOpacity
                style={styles.closeButton}
                onPress={() => setShowESewaPayment(false)}
              >
                <Ionicons name="close" size={24} color={colors.gray[700]} />
              </TouchableOpacity>
              <Text style={styles.paymentModalTitle}>eSewa Payment</Text>
              <View style={styles.headerSpacer} />
            </View>

            {/* Payment Status */}
            {paymentStatus === 'pending' && (
              <View style={styles.paymentStatusContainer}>
                <Ionicons name="card-outline" size={48} color={colors.primary[500]} />
                <Text style={styles.paymentStatusTitle}>Processing Payment</Text>
                <Text style={styles.paymentStatusMessage}>
                  Please complete your payment through eSewa
                </Text>
                <Price 
                  value={total} 
                  size="xl" 
                  weight="bold" 
                  color="primary"
                  style={styles.paymentAmount}
                />
              </View>
            )}

            {/* WebView for eSewa */}
            <WebView
              source={{
                html: `
                  <!DOCTYPE html>
                  <html>
                    <head>
                      <meta name="viewport" content="width=device-width, initial-scale=1.0">
                      <title>eSewa Payment</title>
                      <style>
                        body { 
                          font-family: -apple-system, BlinkMacSystemFont, sans-serif; 
                          margin: 0; 
                          padding: 20px; 
                          background: #f5f5f5;
                          text-align: center;
                        }
                        .payment-form {
                          background: white;
                          padding: 30px;
                          border-radius: 12px;
                          box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                          max-width: 400px;
                          margin: 0 auto;
                        }
                        .esewa-logo {
                          width: 80px;
                          height: 80px;
                          background: #60bb46;
                          border-radius: 50%;
                          margin: 0 auto 20px;
                          display: flex;
                          align-items: center;
                          justify-content: center;
                          color: white;
                          font-size: 24px;
                          font-weight: bold;
                        }
                        .amount {
                          font-size: 32px;
                          font-weight: bold;
                          color: #60bb46;
                          margin: 20px 0;
                        }
                        .payment-button {
                          background: #60bb46;
                          color: white;
                          border: none;
                          padding: 15px 30px;
                          border-radius: 8px;
                          font-size: 16px;
                          font-weight: bold;
                          cursor: pointer;
                          width: 100%;
                          margin: 10px 0;
                        }
                        .payment-button:hover {
                          background: #4a9c3a;
                        }
                        .test-info {
                          background: #fff3cd;
                          border: 1px solid #ffeaa7;
                          padding: 15px;
                          border-radius: 8px;
                          margin: 20px 0;
                          font-size: 14px;
                          color: #856404;
                        }
                      </style>
                    </head>
                    <body>
                      <div class="payment-form">
                        <div class="esewa-logo">e</div>
                        <h2>eSewa Payment Gateway</h2>
                        <div class="amount">Rs. ${total.amount}</div>
                        <p>Order Total</p>
                        
                        <div class="test-info">
                          <strong>Test Mode:</strong><br>
                          This is a demo payment gateway.<br>
                          Click "Pay with eSewa" to simulate payment.
                        </div>
                        
                        <button class="payment-button" onclick="processPayment()">
                          Pay with eSewa
                        </button>
                        
                        <button class="payment-button" style="background: #dc3545;" onclick="cancelPayment()">
                          Cancel Payment
                        </button>
                      </div>
                      
                      <script>
                        function processPayment() {
                          // Simulate payment processing
                          setTimeout(() => {
                            window.ReactNativeWebView.postMessage(JSON.stringify({
                              type: 'payment_success',
                              amount: ${total.amount},
                              transactionId: 'TXN_' + Date.now()
                            }));
                          }, 2000);
                        }
                        
                        function cancelPayment() {
                          window.ReactNativeWebView.postMessage(JSON.stringify({
                            type: 'payment_cancelled'
                          }));
                        }
                      </script>
                    </body>
                  </html>
                `
              }}
              style={styles.webview}
              onMessage={(event) => {
                try {
                  const data = JSON.parse(event.nativeEvent.data);
                  if (data.type === 'payment_success') {
                    setPaymentStatus('success');
                    setTimeout(() => {
                      setShowESewaPayment(false);
                      createOrderDirectly();
                    }, 1500);
                  } else if (data.type === 'payment_cancelled') {
                    setPaymentStatus('failed');
                    setTimeout(() => {
                      setShowESewaPayment(false);
                      setPaymentStatus(null);
                    }, 1500);
                  }
                } catch (error) {
                  console.error('Payment message error:', error);
                }
              }}
            />
          </View>
        </Modal>
      </View>
    </ScrollableScreen>
  );
}

const styles = StyleSheet.create({
  backgroundPattern: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: colors.gray[50],
    opacity: 0.3,
  },
  container: {
    flex: 1,
    paddingHorizontal: spacing.lg,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: spacing.lg,
    paddingTop: spacing.sm,
  },
  backButton: {
    padding: spacing.sm,
    borderRadius: radius.sm,
    backgroundColor: colors.gray[100],
  },
  headerSpacer: {
    width: 40, // Same width as back button for centering
  },
  title: {
    fontSize: fontSizes.xxl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    textAlign: 'center',
    flex: 1,
  },
  headerBanner: {
    marginBottom: spacing.lg,
    borderRadius: radius.lg,
    height: 120,
    backgroundColor: colors.primary[600],
    justifyContent: 'center',
    alignItems: 'center',
    padding: spacing.lg,
  },
  headerImageTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.white,
    marginBottom: spacing.xs,
  },
  headerImageSubtitle: {
    fontSize: fontSizes.md,
    color: colors.white,
    opacity: 0.9,
  },
  section: {
    marginBottom: spacing.lg,
  },
  addressHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  addressIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: colors.primary[100],
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.sm,
  },
  sectionTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[800],
    marginBottom: spacing.md,
  },
  addressPlaceholder: {
    marginBottom: spacing.md,
  },
  addressText: {
    fontSize: fontSizes.md,
    color: colors.gray[700],
    marginBottom: spacing.xs,
  },
  addressSubtext: {
    fontSize: fontSizes.sm,
    color: colors.gray[500],
    fontStyle: 'italic',
  },
  addAddressButton: {
    alignSelf: 'flex-start',
  },
  paymentOption: {
    marginBottom: spacing.md,
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.sm,
    borderRadius: radius.sm,
    backgroundColor: colors.white,
    borderWidth: 1,
    borderColor: colors.gray[200],
  },
  paymentOptionSelected: {
    borderColor: colors.primary[600],
    backgroundColor: colors.primary[50],
  },
  paymentOptionContent: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  paymentIconContainer: {
    marginRight: spacing.md,
  },
  cashIcon: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: colors.gray[100],
    justifyContent: 'center',
    alignItems: 'center',
  },
  esewaIcon: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: colors.success[500],
    justifyContent: 'center',
    alignItems: 'center',
  },
  esewaIconText: {
    fontSize: 24,
    fontWeight: 'bold',
    color: colors.white,
  },
  paymentTextContainer: {
    flex: 1,
  },
  radioContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.xs,
  },
  radioButton: {
    width: 20,
    height: 20,
    borderRadius: 10,
    borderWidth: 2,
    borderColor: colors.gray[300],
    marginRight: spacing.sm,
    justifyContent: 'center',
    alignItems: 'center',
  },
  radioButtonSelected: {
    borderColor: colors.primary[600],
  },
  radioButtonDisabled: {
    borderColor: colors.gray[200],
    backgroundColor: colors.gray[100],
  },
  radioButtonInner: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: colors.primary[600],
  },
  paymentLabel: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: colors.gray[800],
  },
  paymentLabelDisabled: {
    color: colors.gray[400],
  },
  paymentDescription: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginLeft: 30,
  },
  paymentDescriptionDisabled: {
    fontSize: fontSizes.sm,
    color: colors.gray[400],
    marginLeft: 30,
  },
  orderItems: {
    marginBottom: spacing.lg,
  },
  orderItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    paddingVertical: spacing.sm,
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[200],
  },
  orderItemLeft: {
    flex: 1,
    marginRight: spacing.md,
  },
  orderItemHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.xs,
  },
  orderItemImage: {
    width: 60,
    height: 60,
    borderRadius: radius.sm,
    marginRight: spacing.sm,
  },
  orderItemEmojiContainer: {
    width: 60,
    height: 60,
    borderRadius: radius.sm,
    marginRight: spacing.sm,
    backgroundColor: colors.gray[100],
    justifyContent: 'center',
    alignItems: 'center',
  },
  orderItemEmoji: {
    fontSize: 32,
  },
  orderItemInfo: {
    flex: 1,
  },
  orderItemName: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: colors.gray[800],
    marginBottom: spacing.xs,
  },
  orderItemVariant: {
    fontSize: fontSizes.sm,
    color: colors.primary[600],
    marginBottom: spacing.xs,
  },
  orderItemAddOns: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginBottom: spacing.xs,
    fontStyle: 'italic',
  },
  orderItemQuantity: {
    fontSize: fontSizes.sm,
    color: colors.gray[500],
  },
  orderTotals: {
    gap: spacing.sm,
  },
  totalRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  totalLabel: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
  },
  deliveryFee: {
    fontSize: fontSizes.md,
    color: colors.success[600],
    fontWeight: fontWeights.medium,
  },
  taxAmount: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
  },
  divider: {
    height: 1,
    backgroundColor: colors.gray[200],
    marginVertical: spacing.sm,
  },
  grandTotalLabel: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[800],
  },
  placeOrderSection: {
    marginTop: spacing.lg,
    marginBottom: spacing.xl,
  },
  placeOrderCard: {
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    padding: spacing.lg,
    borderWidth: 1,
    borderColor: colors.gray[200],
    shadowColor: colors.gray[900],
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  placeOrderHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  placeOrderTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: colors.gray[800],
    marginLeft: spacing.sm,
  },
  placeOrderSubtitle: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    marginBottom: spacing.lg,
    lineHeight: 20,
  },
  placeOrderButton: {
    width: '100%',
  },
  paymentModal: {
    flex: 1,
    backgroundColor: colors.white,
    paddingTop: spacing.lg,
  },
  paymentModalHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.lg,
    paddingBottom: spacing.md,
  },
  closeButton: {
    padding: spacing.sm,
    borderRadius: radius.sm,
    backgroundColor: colors.gray[100],
  },
  paymentModalTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    textAlign: 'center',
    flex: 1,
  },
  paymentStatusContainer: {
    padding: spacing.lg,
    alignItems: 'center',
    marginTop: spacing.lg,
  },
  paymentStatusTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.primary[600],
    marginTop: spacing.md,
    marginBottom: spacing.xs,
  },
  paymentStatusMessage: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    marginBottom: spacing.md,
  },
  paymentAmount: {
    fontSize: fontSizes.xxl,
    fontWeight: fontWeights.bold,
    color: colors.primary[600],
  },
  webview: {
    flex: 1,
    backgroundColor: colors.white,
  },
  debugSection: {
    marginTop: spacing.lg,
    padding: spacing.md,
    backgroundColor: colors.gray[100],
    borderRadius: radius.sm,
    borderWidth: 1,
    borderColor: colors.gray[300],
  },
  debugHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  debugTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: colors.gray[700],
    marginLeft: spacing.xs,
  },
  debugText: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginBottom: spacing.xs,
  },
});
