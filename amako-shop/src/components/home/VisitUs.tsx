import React from 'react';
import { View, Text, StyleSheet, Pressable, Linking, Alert } from 'react-native';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius, shadows } from '../../ui/tokens';

interface BusinessHours {
  day: string;
  open: string;
  close: string;
  isOpen: boolean;
}

interface StoreInfo {
  address: string;
  phone: string;
  email: string;
  businessHours: BusinessHours[];
  socialMedia: {
    facebook?: string;
    instagram?: string;
    twitter?: string;
  };
}

interface VisitUsProps {
  storeInfo?: StoreInfo;
}

const defaultStoreInfo: StoreInfo = {
  address: '123 Momo Street, Kathmandu, Nepal',
  phone: '+977-1-2345678',
  email: 'info@amakoshop.com',
  businessHours: [
    { day: 'Monday', open: '10:00', close: '22:00', isOpen: true },
    { day: 'Tuesday', open: '10:00', close: '22:00', isOpen: true },
    { day: 'Wednesday', open: '10:00', close: '22:00', isOpen: true },
    { day: 'Thursday', open: '10:00', close: '22:00', isOpen: true },
    { day: 'Friday', open: '10:00', close: '23:00', isOpen: true },
    { day: 'Saturday', open: '10:00', close: '23:00', isOpen: true },
    { day: 'Sunday', open: '11:00', close: '21:00', isOpen: true },
  ],
  socialMedia: {
    facebook: 'https://facebook.com/amakoshop',
    instagram: 'https://instagram.com/amakoshop',
    twitter: 'https://twitter.com/amakoshop',
  },
};

export default function VisitUs({ storeInfo = defaultStoreInfo }: VisitUsProps) {
  const isCurrentlyOpen = () => {
    const now = new Date();
    const currentDay = now.toLocaleDateString('en-US', { weekday: 'long' });
    const currentTime = now.toLocaleTimeString('en-US', { 
      hour12: false, 
      hour: '2-digit', 
      minute: '2-digit' 
    });
    
    // Safety check for businessHours
    if (!storeInfo.businessHours || !Array.isArray(storeInfo.businessHours)) {
      return false;
    }
    
    const todayHours = storeInfo.businessHours.find(
      hours => hours.day === currentDay
    );
    
    if (!todayHours) return false;
    
    return currentTime >= todayHours.open && currentTime <= todayHours.close;
  };

  const handleGetDirections = () => {
    const encodedAddress = encodeURIComponent(storeInfo.address);
    const url = `https://maps.google.com/maps?q=${encodedAddress}`;
    
    Linking.openURL(url).catch(() => {
      Alert.alert('Error', 'Could not open maps app');
    });
  };

  const handleCopyAddress = () => {
    // In a real app, you'd use Clipboard API
    Alert.alert('Address Copied', storeInfo.address);
  };

  const handleCall = () => {
    const url = `tel:${storeInfo.phone}`;
    Linking.openURL(url).catch(() => {
      Alert.alert('Error', 'Could not open phone app');
    });
  };

  const handleEmail = () => {
    const url = `mailto:${storeInfo.email}`;
    Linking.openURL(url).catch(() => {
      Alert.alert('Error', 'Could not open email app');
    });
  };

  const handleSocialMedia = (platform: string, url?: string) => {
    if (url) {
      Linking.openURL(url).catch(() => {
        Alert.alert('Error', `Could not open ${platform}`);
      });
    }
  };

  return (
    <View style={styles.container}>
      {/* Business Hours */}
      <View style={styles.section}>
        <View style={styles.sectionHeader}>
          <MCI name="clock-outline" size={20} color={colors.brand.primary} />
          <Text style={styles.sectionTitle}>Business Hours</Text>
          <View style={[
            styles.statusBadge,
            { backgroundColor: isCurrentlyOpen() ? colors.momo.green : colors.error }
          ]}>
            <Text style={styles.statusText}>
              {isCurrentlyOpen() ? 'Open' : 'Closed'}
            </Text>
          </View>
        </View>
        
        <View style={styles.hoursContainer}>
          {storeInfo.businessHours.map((hours, index) => (
            <View key={index} style={styles.hoursRow}>
              <Text style={styles.dayText}>{hours.day}</Text>
              <Text style={styles.timeText}>
                {hours.open} - {hours.close}
              </Text>
            </View>
          ))}
        </View>
      </View>

      {/* Map Preview */}
      <View style={styles.section}>
        <View style={styles.sectionHeader}>
          <MCI name="map-marker" size={20} color={colors.brand.primary} />
          <Text style={styles.sectionTitle}>Visit Us</Text>
        </View>
        
        <View style={styles.mapContainer}>
          <View style={styles.mapPlaceholder}>
            <MCI name="map" size={48} color={colors.gray[400]} />
            <Text style={styles.mapText}>Map Preview</Text>
          </View>
          
          <View style={styles.mapActions}>
            <Pressable style={styles.actionButton} onPress={handleGetDirections}>
              <MCI name="directions" size={16} color={colors.white} />
              <Text style={styles.actionText}>Get Directions</Text>
            </Pressable>
            
            <Pressable style={styles.actionButton} onPress={handleCopyAddress}>
              <MCI name="content-copy" size={16} color={colors.white} />
              <Text style={styles.actionText}>Copy Address</Text>
            </Pressable>
          </View>
        </View>
      </View>

      {/* Contact Information */}
      <View style={styles.section}>
        <View style={styles.sectionHeader}>
          <MCI name="phone" size={20} color={colors.brand.primary} />
          <Text style={styles.sectionTitle}>Contact Us</Text>
        </View>
        
        <View style={styles.contactContainer}>
          <Pressable style={styles.contactItem} onPress={handleCall}>
            <MCI name="phone" size={20} color={colors.brand.primary} />
            <Text style={styles.contactText}>{storeInfo.phone}</Text>
            <Text style={styles.contactLabel}>Call Now</Text>
          </Pressable>
          
          <Pressable style={styles.contactItem} onPress={handleEmail}>
            <MCI name="email" size={20} color={colors.brand.primary} />
            <Text style={styles.contactText}>{storeInfo.email}</Text>
            <Text style={styles.contactLabel}>Email</Text>
          </Pressable>
        </View>
      </View>

      {/* Social Media */}
      <View style={styles.section}>
        <View style={styles.sectionHeader}>
          <MCI name="share" size={20} color={colors.brand.primary} />
          <Text style={styles.sectionTitle}>Follow Us</Text>
        </View>
        
        <View style={styles.socialContainer}>
          {storeInfo.socialMedia.facebook && (
            <Pressable 
              style={styles.socialButton}
              onPress={() => handleSocialMedia('Facebook', storeInfo.socialMedia.facebook)}
            >
              <MCI name="facebook" size={24} color={colors.brand.primary} />
            </Pressable>
          )}
          
          {storeInfo.socialMedia.instagram && (
            <Pressable 
              style={styles.socialButton}
              onPress={() => handleSocialMedia('Instagram', storeInfo.socialMedia.instagram)}
            >
              <MCI name="instagram" size={24} color={colors.brand.primary} />
            </Pressable>
          )}
          
          {storeInfo.socialMedia.twitter && (
            <Pressable 
              style={styles.socialButton}
              onPress={() => handleSocialMedia('Twitter', storeInfo.socialMedia.twitter)}
            >
              <MCI name="twitter" size={24} color={colors.brand.primary} />
            </Pressable>
          )}
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
  },
  section: {
    marginBottom: spacing.xl,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  sectionTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    marginLeft: spacing.sm,
    flex: 1,
  },
  statusBadge: {
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: radius.sm,
  },
  statusText: {
    color: colors.white,
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.bold,
  },
  hoursContainer: {
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    padding: spacing.md,
    ...shadows.light,
  },
  hoursRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: spacing.xs,
  },
  dayText: {
    fontSize: fontSizes.sm,
    color: colors.brand.primary,
    fontWeight: fontWeights.medium,
  },
  timeText: {
    fontSize: fontSizes.sm,
    color: colors.momo.mocha,
  },
  mapContainer: {
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    padding: spacing.md,
    ...shadows.light,
  },
  mapPlaceholder: {
    height: 120,
    backgroundColor: colors.gray[100],
    borderRadius: radius.md,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  mapText: {
    fontSize: fontSizes.sm,
    color: colors.gray[500],
    marginTop: spacing.xs,
  },
  mapActions: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  actionButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.brand.primary,
    paddingVertical: spacing.sm,
    borderRadius: radius.md,
    marginHorizontal: spacing.xs,
  },
  actionText: {
    color: colors.white,
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    marginLeft: spacing.xs,
  },
  contactContainer: {
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    padding: spacing.md,
    ...shadows.light,
  },
  contactItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: spacing.sm,
  },
  contactText: {
    fontSize: fontSizes.sm,
    color: colors.brand.primary,
    marginLeft: spacing.sm,
    flex: 1,
  },
  contactLabel: {
    fontSize: fontSizes.xs,
    color: colors.gray[500],
  },
  socialContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
  },
  socialButton: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: colors.momo.cream,
    justifyContent: 'center',
    alignItems: 'center',
    marginHorizontal: spacing.sm,
  },
});
