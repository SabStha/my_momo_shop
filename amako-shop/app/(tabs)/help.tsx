import React, { useState } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  ScrollView, 
  TouchableOpacity,
  Linking,
  Animated,
  Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius } from '../../src/ui/tokens';

type Section = 
  | 'getting-started' 
  | 'ordering' 
  | 'gps' 
  | 'badges' 
  | 'payment' 
  | 'troubleshooting' 
  | null;

export default function HelpScreen() {
  const [expandedSection, setExpandedSection] = useState<Section>(null);

  const toggleSection = (section: Section) => {
    setExpandedSection(expandedSection === section ? null : section);
  };

  const handleCallSupport = () => {
    Linking.openURL('tel:+977-1-1234567');
  };

  const renderQuickNavCard = (
    title: string,
    description: string,
    icon: keyof typeof Ionicons.glyphMap,
    color: string,
    section: Section
  ) => (
    <TouchableOpacity
      style={[styles.quickNavCard, { borderLeftColor: color, borderLeftWidth: 4 }]}
      onPress={() => toggleSection(section)}
      activeOpacity={0.7}
    >
      <View style={[styles.quickNavIcon, { backgroundColor: color + '20' }]}>
        <Ionicons name={icon} size={28} color={color} />
      </View>
      <View style={styles.quickNavContent}>
        <Text style={styles.quickNavTitle}>{title}</Text>
        <Text style={styles.quickNavDescription}>{description}</Text>
      </View>
      <Ionicons 
        name={expandedSection === section ? "chevron-up" : "chevron-down"} 
        size={24} 
        color={color} 
      />
    </TouchableOpacity>
  );

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
        {/* Header */}
        <View style={styles.header}>
          <Image 
            source={require('../../assets/animations/welcome.gif')} 
            style={styles.headerMascot}
            resizeMode="contain"
          />
          <Text style={styles.headerTitle}>Help Center</Text>
          <Text style={styles.headerSubtitle}>
            Everything you need to know about Amako Momo
          </Text>
        </View>

        {/* Quick Navigation */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>📋 Quick Navigation</Text>
          
          {renderQuickNavCard(
            'Getting Started',
            'First time here? Start here!',
            'rocket',
            '#3B82F6',
            'getting-started'
          )}
          
          {expandedSection === 'getting-started' && (
            <View style={styles.expandedContent}>
              <View style={styles.stepCard}>
                <View style={styles.stepNumber}>
                  <Text style={styles.stepNumberText}>1</Text>
                </View>
                <View style={styles.stepContent}>
                  <Text style={styles.stepTitle}>Welcome to Amako!</Text>
                  <Text style={styles.stepText}>
                    You're all set! Your account is active and you can start exploring our delicious menu of momos and more. We're here to make ordering easy and fun!
                  </Text>
                </View>
              </View>

              <View style={styles.stepCard}>
                <View style={styles.stepNumber}>
                  <Text style={styles.stepNumberText}>2</Text>
                </View>
                <View style={styles.stepContent}>
                  <Text style={styles.stepTitle}>Complete Your Profile</Text>
                  <Text style={styles.stepText}>
                    Add your delivery address and contact information in your Profile to make checkout faster and easier.
                  </Text>
                </View>
              </View>

              <View style={styles.stepCard}>
                <View style={styles.stepNumber}>
                  <Text style={styles.stepNumberText}>3</Text>
                </View>
                <View style={styles.stepContent}>
                  <Text style={styles.stepTitle}>Enable GPS (Optional)</Text>
                  <Text style={styles.stepText}>
                    Enable GPS to automatically find the nearest branch, get accurate delivery times, and faster service.
                  </Text>
                </View>
              </View>

              <View style={styles.stepCard}>
                <View style={styles.stepNumber}>
                  <Text style={styles.stepNumberText}>4</Text>
                </View>
                <View style={styles.stepContent}>
                  <Text style={styles.stepTitle}>Start Ordering!</Text>
                  <Text style={styles.stepText}>
                    Browse the menu, add items to cart, and place your first order. Earn badges and rewards with every purchase!
                  </Text>
                </View>
              </View>
            </View>
          )}

          {renderQuickNavCard(
            'How to Order',
            'Step-by-step ordering guide',
            'cart',
            '#10B981',
            'ordering'
          )}

          {expandedSection === 'ordering' && (
            <View style={styles.expandedContent}>
              <View style={styles.orderingSteps}>
                <View style={styles.orderStep}>
                  <View style={[styles.orderStepNumber, { backgroundColor: '#10B981' }]}>
                    <Text style={styles.orderStepNumberText}>1</Text>
                  </View>
                  <View style={styles.orderStepContent}>
                    <Text style={styles.orderStepTitle}>Browse Menu</Text>
                    <Text style={styles.orderStepText}>
                      Explore our delicious menu with momos, combos, drinks, and more.
                    </Text>
                  </View>
                </View>

                <View style={styles.orderStep}>
                  <View style={[styles.orderStepNumber, { backgroundColor: '#10B981' }]}>
                    <Text style={styles.orderStepNumberText}>2</Text>
                  </View>
                  <View style={styles.orderStepContent}>
                    <Text style={styles.orderStepTitle}>Add to Cart</Text>
                    <Text style={styles.orderStepText}>
                      Click "Add to Cart" on your favorite items. Customize quantities as needed.
                    </Text>
                  </View>
                </View>

                <View style={styles.orderStep}>
                  <View style={[styles.orderStepNumber, { backgroundColor: '#10B981' }]}>
                    <Text style={styles.orderStepNumberText}>3</Text>
                  </View>
                  <View style={styles.orderStepContent}>
                    <Text style={styles.orderStepTitle}>Review Cart</Text>
                    <Text style={styles.orderStepText}>
                      Check your order, apply any available discounts or promo codes.
                    </Text>
                  </View>
                </View>

                <View style={styles.orderStep}>
                  <View style={[styles.orderStepNumber, { backgroundColor: '#059669' }]}>
                    <Text style={styles.orderStepNumberText}>4</Text>
                  </View>
                  <View style={styles.orderStepContent}>
                    <Text style={styles.orderStepTitle}>Enter Details</Text>
                    <Text style={styles.orderStepText}>
                      Fill in your delivery address and contact information.
                    </Text>
                  </View>
                </View>

                <View style={styles.orderStep}>
                  <View style={[styles.orderStepNumber, { backgroundColor: '#059669' }]}>
                    <Text style={styles.orderStepNumberText}>5</Text>
                  </View>
                  <View style={styles.orderStepContent}>
                    <Text style={styles.orderStepTitle}>Choose Branch</Text>
                    <Text style={styles.orderStepText}>
                      Select the nearest branch for faster delivery to your location.
                    </Text>
                  </View>
                </View>

                <View style={styles.orderStep}>
                  <View style={[styles.orderStepNumber, { backgroundColor: '#059669' }]}>
                    <Text style={styles.orderStepNumberText}>6</Text>
                  </View>
                  <View style={styles.orderStepContent}>
                    <Text style={styles.orderStepTitle}>Payment</Text>
                    <Text style={styles.orderStepText}>
                      Pay securely with cash on delivery, eSewa, Khalti, or other digital wallets.
                    </Text>
                  </View>
                </View>

                <View style={styles.orderStep}>
                  <View style={[styles.orderStepNumber, { backgroundColor: '#059669' }]}>
                    <Text style={styles.orderStepNumberText}>7</Text>
                  </View>
                  <View style={styles.orderStepContent}>
                    <Text style={styles.orderStepTitle}>Track Order</Text>
                    <Text style={styles.orderStepText}>
                      View your order status in the Orders page and enjoy your momos!
                    </Text>
                  </View>
                </View>
              </View>

              <View style={styles.proTipsBox}>
                <Text style={styles.proTipsTitle}>🎯 Pro Tips</Text>
                <Text style={styles.proTip}>• Use GPS for accurate delivery times</Text>
                <Text style={styles.proTip}>• Check for daily offers and discounts</Text>
                <Text style={styles.proTip}>• Earn badges and rewards with every order</Text>
                <Text style={styles.proTip}>• Save your delivery address for quick reordering</Text>
              </View>
            </View>
          )}

          {renderQuickNavCard(
            'GPS Location',
            'Location & delivery setup',
            'location',
            '#8B5CF6',
            'gps'
          )}

          {expandedSection === 'gps' && (
            <View style={styles.expandedContent}>
              <View style={styles.infoBox}>
                <Text style={styles.infoBoxTitle}>📍 Why Use GPS?</Text>
                <Text style={styles.infoBoxItem}>• Get accurate delivery times</Text>
                <Text style={styles.infoBoxItem}>• Find the nearest branch automatically</Text>
                <Text style={styles.infoBoxItem}>• Ensure delivery to the correct address</Text>
                <Text style={styles.infoBoxItem}>• Save time with automatic location detection</Text>
              </View>

              <View style={styles.guideBox}>
                <Text style={styles.guideBoxTitle}>📱 Enable GPS</Text>
                <Text style={styles.guideStep}>1. Go to your device Settings</Text>
                <Text style={styles.guideStep}>2. Navigate to Privacy or Privacy & Security</Text>
                <Text style={styles.guideStep}>3. Find Location Services</Text>
                <Text style={styles.guideStep}>4. Enable location access for Amako app</Text>
                <Text style={styles.guideStep}>5. Return to app and allow GPS permission</Text>
              </View>

              <View style={styles.alternativeBox}>
                <Text style={styles.alternativeBoxTitle}>✅ Alternative Options</Text>
                <Text style={styles.alternativeBoxText}>
                  If GPS doesn't work, you can always:
                </Text>
                <Text style={styles.alternativeBoxItem}>• Enter your address manually in checkout</Text>
                <Text style={styles.alternativeBoxItem}>• Select a branch from the list</Text>
                <Text style={styles.alternativeBoxItem}>• Contact support for assistance</Text>
              </View>
            </View>
          )}

          {renderQuickNavCard(
            'Badge System',
            'Earn rewards & points',
            'medal',
            '#60A5FA',
            'badges'
          )}

          {expandedSection === 'badges' && (
            <View style={styles.expandedContent}>
              <View style={styles.badgeInfoBox}>
                <Text style={styles.badgeInfoTitle}>🎯 How It Works</Text>
                <Text style={styles.badgeInfoText}>
                  Earn points and badges by ordering regularly, trying new items, and engaging with our community. Unlock exclusive rewards and privileges!
                </Text>
              </View>

              <View style={styles.badgeTiersBox}>
                <Text style={styles.badgeTiersTitle}>🥟 Loyalty Badges</Text>
                <View style={styles.badgeTier}>
                  <View style={[styles.badgeTierIcon, { backgroundColor: '#CD7F32' }]}>
                    <Ionicons name="medal" size={20} color="#FFF" />
                  </View>
                  <Text style={styles.badgeTierText}>Bronze: 100-500 points</Text>
                </View>
                <View style={styles.badgeTier}>
                  <View style={[styles.badgeTierIcon, { backgroundColor: '#C0C0C0' }]}>
                    <Ionicons name="medal" size={20} color="#FFF" />
                  </View>
                  <Text style={styles.badgeTierText}>Silver: 300-1500 points</Text>
                </View>
                <View style={styles.badgeTier}>
                  <View style={[styles.badgeTierIcon, { backgroundColor: '#FFD700' }]}>
                    <Ionicons name="medal" size={20} color="#FFF" />
                  </View>
                  <Text style={styles.badgeTierText}>Gold: 600-3000 points</Text>
                </View>
                <View style={styles.badgeTier}>
                  <View style={[styles.badgeTierIcon, { backgroundColor: '#8B5CF6' }]}>
                    <Ionicons name="trophy" size={20} color="#FFF" />
                  </View>
                  <Text style={styles.badgeTierText}>Prestige: 1200+ points</Text>
                </View>
              </View>

              <View style={styles.engagementBox}>
                <Text style={styles.engagementTitle}>🎯 Engagement Badges</Text>
                <Text style={styles.engagementItem}>• Try new menu items</Text>
                <Text style={styles.engagementItem}>• Refer friends and family</Text>
                <Text style={styles.engagementItem}>• Share on social media</Text>
                <Text style={styles.engagementItem}>• Participate in community events</Text>
              </View>

              <View style={styles.rewardsBox}>
                <Text style={styles.rewardsTitle}>🎁 Available Rewards</Text>
                <View style={styles.rewardsList}>
                  <View style={styles.rewardCategory}>
                    <Text style={styles.rewardCategoryTitle}>Free Items</Text>
                    <Text style={styles.rewardItem}>• Free Momo (any variety)</Text>
                    <Text style={styles.rewardItem}>• Free Drink</Text>
                    <Text style={styles.rewardItem}>• Tasting Kit</Text>
                  </View>
                  <View style={styles.rewardCategory}>
                    <Text style={styles.rewardCategoryTitle}>Privileges</Text>
                    <Text style={styles.rewardItem}>• Skip the Line</Text>
                    <Text style={styles.rewardItem}>• Loyalty Discounts</Text>
                    <Text style={styles.rewardItem}>• Event Passes</Text>
                  </View>
                </View>
              </View>
            </View>
          )}

          {renderQuickNavCard(
            'Payment Methods',
            'Payment options & security',
            'card',
            '#EF4444',
            'payment'
          )}

          {expandedSection === 'payment' && (
            <View style={styles.expandedContent}>
              <View style={styles.paymentMethodsGrid}>
                <View style={styles.paymentMethodCard}>
                  <Ionicons name="wallet" size={32} color="#60A5FA" />
                  <Text style={styles.paymentMethodTitle}>Amako Credits</Text>
                  <Text style={styles.paymentMethodText}>
                    Use your Amako wallet credits for instant payment. Earn credits through rewards!
                  </Text>
                </View>

                <View style={styles.paymentMethodCard}>
                  <Ionicons name="cash" size={32} color="#10B981" />
                  <Text style={styles.paymentMethodTitle}>Cash on Delivery</Text>
                  <Text style={styles.paymentMethodText}>
                    Pay with cash upon delivery. Exact change preferred.
                  </Text>
                </View>

                <View style={styles.paymentMethodCard}>
                  <Ionicons name="phone-portrait" size={32} color="#8B5CF6" />
                  <Text style={styles.paymentMethodTitle}>eSewa</Text>
                  <Text style={styles.paymentMethodText}>
                    Pay securely with your eSewa digital wallet.
                  </Text>
                </View>

                <View style={styles.paymentMethodCard}>
                  <Ionicons name="wallet" size={32} color="#3B82F6" />
                  <Text style={styles.paymentMethodTitle}>Khalti</Text>
                  <Text style={styles.paymentMethodText}>
                    Use Khalti for quick and easy payments.
                  </Text>
                </View>

                <View style={styles.paymentMethodCard}>
                  <Ionicons name="card" size={32} color="#60A5FA" />
                  <Text style={styles.paymentMethodTitle}>FonePay</Text>
                  <Text style={styles.paymentMethodText}>
                    Pay with FonePay digital wallet service.
                  </Text>
                </View>
              </View>

              <View style={styles.securityBox}>
                <Text style={styles.securityTitle}>🔒 Security</Text>
                <Text style={styles.securityItem}>• All payments are processed securely</Text>
                <Text style={styles.securityItem}>• Your payment information is encrypted</Text>
                <Text style={styles.securityItem}>• We never store your card details</Text>
                <Text style={styles.securityItem}>• Multiple payment options for your convenience</Text>
              </View>
            </View>
          )}

          {renderQuickNavCard(
            'Troubleshooting',
            'Common issues & solutions',
            'settings',
            '#6B7280',
            'troubleshooting'
          )}

          {expandedSection === 'troubleshooting' && (
            <View style={styles.expandedContent}>
              <View style={styles.troubleshootCard}>
                <Text style={styles.troubleshootTitle}>📍 Can't access GPS location?</Text>
                <Text style={styles.troubleshootText}>
                  This is usually a permission issue.
                </Text>
                <Text style={styles.troubleshootItem}>• Check app location permissions</Text>
                <Text style={styles.troubleshootItem}>• Try refreshing the app</Text>
                <Text style={styles.troubleshootItem}>• Use manual address entry as alternative</Text>
              </View>

              <View style={styles.troubleshootCard}>
                <Text style={styles.troubleshootTitle}>🛒 Order not showing up?</Text>
                <Text style={styles.troubleshootText}>
                  Check your order status and contact support if needed.
                </Text>
                <Text style={styles.troubleshootItem}>• Check Orders page in bottom navigation</Text>
                <Text style={styles.troubleshootItem}>• Look for order confirmation</Text>
                <Text style={styles.troubleshootItem}>• Pull down to refresh orders list</Text>
              </View>

              <View style={styles.troubleshootCard}>
                <Text style={styles.troubleshootTitle}>💳 Payment issues?</Text>
                <Text style={styles.troubleshootText}>
                  We're here to help with any payment problems.
                </Text>
                <Text style={styles.troubleshootItem}>• Try a different payment method</Text>
                <Text style={styles.troubleshootItem}>• Check your payment details</Text>
                <Text style={styles.troubleshootItem}>• Contact support if issue persists</Text>
              </View>

              <View style={styles.troubleshootCard}>
                <Text style={styles.troubleshootTitle}>🚚 Delivery taking too long?</Text>
                <Text style={styles.troubleshootText}>
                  Normal delivery time is 30-45 minutes.
                </Text>
                <Text style={styles.troubleshootItem}>• Check order status in Orders page</Text>
                <Text style={styles.troubleshootItem}>• Weather and traffic may cause delays</Text>
                <Text style={styles.troubleshootItem}>• Call the restaurant if order is very late</Text>
              </View>
            </View>
          )}
        </View>

        {/* Contact Support Section */}
        <View style={styles.contactSection}>
          <Ionicons name="headset" size={48} color={colors.white} />
          <Text style={styles.contactTitle}>Still need help?</Text>
          <Text style={styles.contactDescription}>
            Our support team is here to help you 24/7
          </Text>
          <TouchableOpacity style={styles.contactButton} onPress={handleCallSupport}>
            <Ionicons name="call" size={20} color={colors.white} />
            <Text style={styles.contactButtonText}>Call Support</Text>
          </TouchableOpacity>
        </View>

        {/* FAQ Section */}
        <View style={styles.faqSection}>
          <Text style={styles.faqTitle}>❓ Frequently Asked Questions</Text>
          
          <View style={styles.faqItem}>
            <Text style={styles.faqQuestion}>Q: How long does delivery take?</Text>
            <Text style={styles.faqAnswer}>
              A: Typically 30-45 minutes depending on your location and the selected branch.
            </Text>
          </View>

          <View style={styles.faqItem}>
            <Text style={styles.faqQuestion}>Q: Do you deliver to all areas?</Text>
            <Text style={styles.faqAnswer}>
              A: We deliver within 5km of each branch. Use GPS to check if your area is covered.
            </Text>
          </View>

          <View style={styles.faqItem}>
            <Text style={styles.faqQuestion}>Q: Can I cancel my order?</Text>
            <Text style={styles.faqAnswer}>
              A: Orders can be cancelled within 5 minutes of placing. Contact support immediately.
            </Text>
          </View>

          <View style={styles.faqItem}>
            <Text style={styles.faqQuestion}>Q: How do I earn loyalty points?</Text>
            <Text style={styles.faqAnswer}>
              A: You earn points automatically with every order. Check your profile to see your points and badges.
            </Text>
          </View>
        </View>
      </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.gray[50],
  },
  header: {
    backgroundColor: colors.white,
    padding: spacing.xl,
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[200],
  },
  headerMascot: {
    width: 80,
    height: 80,
    marginBottom: spacing.sm,
  },
  headerTitle: {
    fontSize: fontSizes['2xl'],
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    marginBottom: spacing.xs,
  },
  headerSubtitle: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    textAlign: 'center',
  },
  section: {
    padding: spacing.lg,
  },
  sectionTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    marginBottom: spacing.md,
  },
  quickNavCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.white,
    padding: spacing.md,
    borderRadius: radius.lg,
    marginBottom: spacing.md,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
  },
  quickNavIcon: {
    width: 56,
    height: 56,
    borderRadius: radius.lg,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.md,
  },
  quickNavContent: {
    flex: 1,
  },
  quickNavTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.xs,
  },
  quickNavDescription: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
  },
  expandedContent: {
    backgroundColor: colors.gray[50],
    padding: spacing.md,
    borderRadius: radius.lg,
    marginBottom: spacing.md,
    marginTop: -spacing.sm,
  },
  stepCard: {
    flexDirection: 'row',
    backgroundColor: colors.white,
    padding: spacing.md,
    borderRadius: radius.lg,
    marginBottom: spacing.md,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 2,
    elevation: 1,
  },
  stepNumber: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: colors.brand.primary,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.md,
  },
  stepNumberText: {
    color: colors.white,
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
  },
  stepContent: {
    flex: 1,
  },
  stepTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.xs,
  },
  stepText: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    lineHeight: 20,
  },
  orderingSteps: {
    marginBottom: spacing.md,
  },
  orderStep: {
    flexDirection: 'row',
    marginBottom: spacing.md,
  },
  orderStepNumber: {
    width: 32,
    height: 32,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.md,
  },
  orderStepNumberText: {
    color: colors.white,
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.bold,
  },
  orderStepContent: {
    flex: 1,
  },
  orderStepTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.xs,
  },
  orderStepText: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    lineHeight: 18,
  },
  proTipsBox: {
    backgroundColor: '#DBEAFE',
    padding: spacing.md,
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: '#60A5FA',
  },
  proTipsTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#1E40AF',
    marginBottom: spacing.sm,
  },
  proTip: {
    fontSize: fontSizes.sm,
    color: '#1E40AF',
    marginBottom: spacing.xs,
  },
  infoBox: {
    backgroundColor: '#DBEAFE',
    padding: spacing.md,
    borderRadius: radius.lg,
    marginBottom: spacing.md,
  },
  infoBoxTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#1E40AF',
    marginBottom: spacing.sm,
  },
  infoBoxItem: {
    fontSize: fontSizes.sm,
    color: '#1E40AF',
    marginBottom: spacing.xs,
  },
  guideBox: {
    backgroundColor: colors.white,
    padding: spacing.md,
    borderRadius: radius.lg,
    marginBottom: spacing.md,
  },
  guideBoxTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.sm,
  },
  guideStep: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginBottom: spacing.xs,
    paddingLeft: spacing.sm,
  },
  alternativeBox: {
    backgroundColor: '#D1FAE5',
    padding: spacing.md,
    borderRadius: radius.lg,
  },
  alternativeBoxTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#065F46',
    marginBottom: spacing.sm,
  },
  alternativeBoxText: {
    fontSize: fontSizes.sm,
    color: '#065F46',
    marginBottom: spacing.xs,
  },
  alternativeBoxItem: {
    fontSize: fontSizes.sm,
    color: '#065F46',
    marginBottom: spacing.xs,
  },
  badgeInfoBox: {
    backgroundColor: '#DBEAFE',
    padding: spacing.md,
    borderRadius: radius.lg,
    marginBottom: spacing.md,
  },
  badgeInfoTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#1E40AF',
    marginBottom: spacing.sm,
  },
  badgeInfoText: {
    fontSize: fontSizes.sm,
    color: '#1E40AF',
    lineHeight: 20,
  },
  badgeTiersBox: {
    backgroundColor: colors.white,
    padding: spacing.md,
    borderRadius: radius.lg,
    marginBottom: spacing.md,
  },
  badgeTiersTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.md,
  },
  badgeTier: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  badgeTierIcon: {
    width: 32,
    height: 32,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.sm,
  },
  badgeTierText: {
    fontSize: fontSizes.sm,
    color: colors.gray[700],
  },
  engagementBox: {
    backgroundColor: '#EFF6FF',
    padding: spacing.md,
    borderRadius: radius.lg,
    marginBottom: spacing.md,
  },
  engagementTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#1E40AF',
    marginBottom: spacing.sm,
  },
  engagementItem: {
    fontSize: fontSizes.sm,
    color: '#1E40AF',
    marginBottom: spacing.xs,
  },
  rewardsBox: {
    backgroundColor: '#F3E8FF',
    padding: spacing.md,
    borderRadius: radius.lg,
  },
  rewardsTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#6B21A8',
    marginBottom: spacing.md,
  },
  rewardsList: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  rewardCategory: {
    flex: 1,
  },
  rewardCategoryTitle: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    color: '#6B21A8',
    marginBottom: spacing.xs,
  },
  rewardItem: {
    fontSize: fontSizes.xs,
    color: '#6B21A8',
    marginBottom: spacing.xs,
  },
  paymentMethodsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
    marginBottom: spacing.md,
  },
  paymentMethodCard: {
    flex: 1,
    minWidth: '48%',
    backgroundColor: colors.white,
    padding: spacing.md,
    borderRadius: radius.lg,
    alignItems: 'center',
  },
  paymentMethodTitle: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginTop: spacing.sm,
    marginBottom: spacing.xs,
    textAlign: 'center',
  },
  paymentMethodText: {
    fontSize: fontSizes.xs,
    color: colors.gray[600],
    textAlign: 'center',
    lineHeight: 16,
  },
  securityBox: {
    backgroundColor: '#DBEAFE',
    padding: spacing.md,
    borderRadius: radius.lg,
  },
  securityTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#1E40AF',
    marginBottom: spacing.sm,
  },
  securityItem: {
    fontSize: fontSizes.sm,
    color: '#1E40AF',
    marginBottom: spacing.xs,
  },
  troubleshootCard: {
    backgroundColor: colors.white,
    padding: spacing.md,
    borderRadius: radius.lg,
    marginBottom: spacing.md,
    borderLeftWidth: 4,
    borderLeftColor: colors.error[500],
  },
  troubleshootTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.xs,
  },
  troubleshootText: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginBottom: spacing.sm,
  },
  troubleshootItem: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    marginBottom: spacing.xs,
  },
  contactSection: {
    backgroundColor: colors.brand.primary,
    margin: spacing.lg,
    padding: spacing.xl,
    borderRadius: radius.xl,
    alignItems: 'center',
  },
  contactTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.white,
    marginTop: spacing.md,
    marginBottom: spacing.sm,
  },
  contactDescription: {
    fontSize: fontSizes.md,
    color: colors.white,
    textAlign: 'center',
    marginBottom: spacing.lg,
  },
  contactButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.white,
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
    gap: spacing.sm,
  },
  contactButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.brand.primary,
  },
  faqSection: {
    backgroundColor: colors.white,
    margin: spacing.lg,
    marginTop: 0,
    padding: spacing.lg,
    borderRadius: radius.xl,
    marginBottom: spacing.xl,
  },
  faqTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    marginBottom: spacing.lg,
  },
  faqItem: {
    marginBottom: spacing.lg,
    paddingBottom: spacing.md,
    borderBottomWidth: 1,
    borderBottomColor: colors.gray[200],
  },
  faqQuestion: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.gray[900],
    marginBottom: spacing.sm,
  },
  faqAnswer: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    lineHeight: 20,
  },
});
