import React from 'react';
import { View, Text, StyleSheet, Pressable, Linking, Alert } from 'react-native';
import MapView, { Marker, PROVIDER_GOOGLE } from 'react-native-maps';
import { MaterialCommunityIcons as MCI } from '@expo/vector-icons';
import { colors, spacing, fontSizes, fontWeights, radius, shadows } from '../../ui/tokens';
import { typography, fonts } from '../../theme';

interface BusinessHours {
  day: string;
  open: string;
  close: string;
  isOpen: boolean;
}

interface StoreInfo {
  address: string;
  latitude?: number;
  longitude?: number;
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

// Business Hours Component
export function BusinessHours({ storeInfo = defaultStoreInfo }: VisitUsProps) {
  const isCurrentlyOpen = () => {
    const now = new Date();
    const currentDay = now.toLocaleDateString('en-US', { weekday: 'long' });
    const currentTime = now.toLocaleTimeString('en-US', { 
      hour12: false, 
      hour: '2-digit', 
      minute: '2-digit' 
    });
    
    if (!storeInfo.businessHours || !Array.isArray(storeInfo.businessHours)) {
      return false;
    }
    
    const todayHours = storeInfo.businessHours.find(
      hours => hours.day === currentDay
    );
    
    if (!todayHours) return false;
    
    return currentTime >= todayHours.open && currentTime <= todayHours.close;
  };

  return (
    <View style={styles.container}>
      <View style={styles.subSection}>
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
    </View>
  );
}

// Visit Us Map Component
export function VisitUsMap({ storeInfo = defaultStoreInfo }: VisitUsProps) {
  // Use coordinates from database or default to Kathmandu, Nepal
  const storeLocation = {
    latitude: storeInfo?.latitude ?? 27.7172,
    longitude: storeInfo?.longitude ?? 85.3240,
    latitudeDelta: 0.0015, // More zoomed in for better view (smaller = more zoom)
    longitudeDelta: 0.0015, // More zoomed in
  };

  const handleGetDirections = () => {
    const url = `https://maps.google.com/maps?q=${storeLocation.latitude},${storeLocation.longitude}`;
    
    Linking.openURL(url).catch(() => {
      Alert.alert('Error', 'Could not open maps app');
    });
  };

  const handleCopyAddress = () => {
    Alert.alert('Address Copied', storeInfo.address);
  };

  return (
    <View style={styles.container}>
      <View style={styles.subSection}>
        <View style={styles.mapContainer}>
          {/* Real Map View */}
          <MapView
            provider={PROVIDER_GOOGLE}
            style={styles.map}
            initialRegion={storeLocation}
            scrollEnabled={false}
            zoomEnabled={false}
            pitchEnabled={false}
            rotateEnabled={false}
            mapType="standard"
            showsBuildings={true}
            showsPointsOfInterest={false}
            showsTraffic={false}
            showsCompass={false}
            showsScale={false}
            showsMyLocationButton={false}
            toolbarEnabled={false}
            customMapStyle={[
              {
                featureType: 'poi',
                elementType: 'labels',
                stylers: [{ visibility: 'off' }],
              },
              {
                featureType: 'transit',
                elementType: 'labels',
                stylers: [{ visibility: 'off' }],
              },
            ]}
          >
            <Marker
              coordinate={{
                latitude: storeLocation.latitude,
                longitude: storeLocation.longitude,
              }}
              title="Ama Ko Shop"
              description={storeInfo.address}
            >
              <View style={styles.customMarker}>
                <MCI name="store" size={28} color={colors.white} />
              </View>
            </Marker>
          </MapView>
          
          {/* Address Display */}
          <View style={styles.addressContainer}>
            <MCI name="map-marker" size={16} color={colors.brand.primary} />
            <Text style={styles.addressText}>{storeInfo.address}</Text>
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
    </View>
  );
}

// Contact Us Component
export function ContactUs({ storeInfo = defaultStoreInfo }: VisitUsProps) {
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

  return (
    <View style={styles.container}>
      <View style={styles.subSection}>
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
    </View>
  );
}

// Follow Us Component
export function FollowUs({ storeInfo = defaultStoreInfo }: VisitUsProps) {
  const handleSocialMedia = (platform: string, url?: string) => {
    if (url) {
      Linking.openURL(url).catch(() => {
        Alert.alert('Error', `Could not open ${platform}`);
      });
    }
  };

  return (
    <View style={styles.container}>
      <View style={styles.subSection}>
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

// Combined Contact & Follow Us Component - Optimized for space efficiency
export function ContactFollowUs({ storeInfo = defaultStoreInfo }: VisitUsProps) {
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
    <View style={styles.combinedContainer}>
      {/* Main Header */}
      <View style={styles.combinedHeader}>
        <MCI name="phone" size={20} color={colors.brand.primary} />
        <Text style={styles.combinedTitle}>Contact & Follow Us</Text>
      </View>
      
      {/* Combined Content */}
      <View style={styles.combinedContent}>
        {/* Contact Section - Left Side */}
        <View style={styles.contactSection}>
          <View style={styles.contactItems}>
            <Pressable style={styles.compactContactItem} onPress={handleCall}>
              <MCI name="phone" size={16} color={colors.brand.primary} />
              <Text style={styles.compactContactText}>{storeInfo.phone}</Text>
            </Pressable>
            
            <Pressable style={styles.compactContactItem} onPress={handleEmail}>
              <MCI name="email" size={16} color={colors.brand.primary} />
              <Text style={styles.compactContactText}>{storeInfo.email}</Text>
            </Pressable>
          </View>
        </View>

        {/* Divider */}
        <View style={styles.divider} />

        {/* Social Media Section - Right Side */}
        <View style={styles.socialSection}>
          <View style={styles.compactSocialContainer}>
            {storeInfo.socialMedia.facebook && (
              <Pressable 
                style={styles.compactSocialButton}
                onPress={() => handleSocialMedia('Facebook', storeInfo.socialMedia.facebook)}
              >
                <MCI name="facebook" size={20} color={colors.brand.primary} />
              </Pressable>
            )}
            
            {storeInfo.socialMedia.instagram && (
              <Pressable 
                style={styles.compactSocialButton}
                onPress={() => handleSocialMedia('Instagram', storeInfo.socialMedia.instagram)}
              >
                <MCI name="instagram" size={20} color={colors.brand.primary} />
              </Pressable>
            )}
            
            {storeInfo.socialMedia.twitter && (
              <Pressable 
                style={styles.compactSocialButton}
                onPress={() => handleSocialMedia('Twitter', storeInfo.socialMedia.twitter)}
              >
                <MCI name="twitter" size={20} color={colors.brand.primary} />
              </Pressable>
            )}
          </View>
        </View>
      </View>
    </View>
  );
}

// Original VisitUs component (keeping for backward compatibility)
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
    // Use coordinates if available, otherwise use address
    const url = (storeInfo?.latitude && storeInfo?.longitude)
      ? `https://maps.google.com/maps?q=${storeInfo.latitude},${storeInfo.longitude}`
      : `https://maps.google.com/maps?q=${encodeURIComponent(storeInfo.address)}`;
    
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
      <View style={styles.subSection}>
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
      <View style={styles.subSection}>
        <View style={styles.sectionHeader}>
          <MCI name="map-marker" size={20} color={colors.brand.primary} />
          <Text style={styles.sectionTitle}>Visit Us</Text>
        </View>
        
        <View style={styles.mapContainer}>
          {/* Real Map View - Using database coordinates */}
          <MapView
            provider={PROVIDER_GOOGLE}
            style={styles.map}
            initialRegion={{
              latitude: storeInfo?.latitude ?? 27.7172,
              longitude: storeInfo?.longitude ?? 85.3240,
              latitudeDelta: 0.0015, // More zoomed in
              longitudeDelta: 0.0015,
            }}
            scrollEnabled={false}
            zoomEnabled={false}
            pitchEnabled={false}
            rotateEnabled={false}
            mapType="standard"
            showsBuildings={true}
            showsPointsOfInterest={false}
            showsTraffic={false}
            showsCompass={false}
            showsScale={false}
            showsMyLocationButton={false}
            toolbarEnabled={false}
            customMapStyle={[
              {
                featureType: 'poi',
                elementType: 'labels',
                stylers: [{ visibility: 'off' }],
              },
              {
                featureType: 'transit',
                elementType: 'labels',
                stylers: [{ visibility: 'off' }],
              },
            ]}
          >
            <Marker
              coordinate={{
                latitude: storeInfo?.latitude ?? 27.7172,
                longitude: storeInfo?.longitude ?? 85.3240,
              }}
              title="Ama Ko Shop"
              description={storeInfo.address}
            >
              <View style={styles.customMarker}>
                <MCI name="store" size={28} color={colors.white} />
              </View>
            </Marker>
          </MapView>
          
          {/* Address Display */}
          <View style={styles.addressContainer}>
            <MCI name="map-marker" size={16} color={colors.brand.primary} />
            <Text style={styles.addressText}>{storeInfo.address}</Text>
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
      <View style={styles.subSection}>
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
      <View style={styles.subSection}>
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
    paddingHorizontal: 0,
    paddingVertical: 0,
  },
  subSection: {
    marginBottom: spacing.lg,
    backgroundColor: colors.white,
    borderRadius: radius.md,
    padding: spacing.md,
    ...shadows.light,
  },
  socialSubSection: {
    marginBottom: spacing.sm,
    backgroundColor: colors.white,
    borderRadius: radius.md,
    padding: spacing.sm,
    ...shadows.light,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  socialSectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.xs,
  },
  sectionTitle: {
    fontFamily: fonts.section,
    fontSize: fontSizes.xs,
    fontWeight: '700' as const,
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
    fontFamily: fonts.body,
    color: colors.white,
    fontSize: fontSizes.xs,
    fontWeight: '700' as const,
  },
  hoursContainer: {
    paddingTop: spacing.xs,
  },
  hoursRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: spacing.xs,
  },
  dayText: {
    fontFamily: fonts.body,
    fontSize: fontSizes.xs,
    color: colors.brand.primary,
    fontWeight: '500' as const,
  },
  timeText: {
    fontFamily: fonts.body,
    fontSize: fontSizes.xs,
    color: colors.momo.mocha,
  },
  mapContainer: {
    paddingTop: spacing.xs,
  },
  map: {
    height: 280, // Taller for better visibility
    borderRadius: radius.lg,
    marginBottom: spacing.sm,
    overflow: 'hidden',
    borderWidth: 2,
    borderColor: colors.brand.primary,
    shadowColor: colors.brand.primary,
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.15,
    shadowRadius: 6,
    elevation: 5,
  },
  addressContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.md,
    backgroundColor: colors.gray[50],
    borderRadius: radius.md,
    marginBottom: spacing.md,
    borderWidth: 1,
    borderColor: colors.gray[200],
  },
  addressText: {
    fontFamily: fonts.body,
    fontSize: fontSizes.sm,
    color: colors.brand.primary,
    marginLeft: spacing.xs,
    fontWeight: '600' as const,
    flex: 1,
  },
  customMarker: {
    backgroundColor: colors.brand.primary,
    padding: spacing.md,
    borderRadius: 30,
    borderWidth: 3,
    borderColor: colors.white,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.3,
    shadowRadius: 4.65,
    elevation: 8,
    alignItems: 'center',
    justifyContent: 'center',
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
    fontFamily: fonts.body,
    fontSize: fontSizes.xs,
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
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
    marginHorizontal: spacing.xs,
    shadowColor: colors.brand.primary,
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
    elevation: 5,
  },
  actionText: {
    fontFamily: fonts.body,
    color: colors.white,
    fontSize: fontSizes.xs,
    fontWeight: '500' as const,
    marginLeft: spacing.xs,
  },
  contactContainer: {
    paddingTop: spacing.xs,
  },
  contactItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: spacing.sm,
  },
  contactText: {
    fontFamily: fonts.body,
    fontSize: fontSizes.xs,
    color: colors.brand.primary,
    marginLeft: spacing.sm,
    flex: 1,
  },
  contactLabel: {
    fontFamily: fonts.caption,
    fontSize: fontSizes.xs,
    color: colors.gray[500],
  },
  socialContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    paddingTop: spacing.xs,
  },
  socialButton: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: colors.white,
    justifyContent: 'center',
    alignItems: 'center',
    marginHorizontal: spacing.sm,
  },
  // New styles for optimized combined component
  combinedContainer: {
    backgroundColor: colors.white,
    borderRadius: radius.md,
    padding: spacing.md,
    ...shadows.light,
  },
  combinedHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  combinedTitle: {
    fontFamily: fonts.section,
    fontSize: fontSizes.xs,
    fontWeight: '700' as const,
    color: colors.brand.primary,
    marginLeft: spacing.sm,
    flex: 1,
  },
  combinedContent: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    minHeight: 60, // Ensure minimum height for consistency
  },
  contactSection: {
    flex: 1,
    paddingRight: spacing.sm,
  },
  contactItems: {
    gap: spacing.xs,
  },
  compactContactItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: spacing.xs,
  },
  compactContactText: {
    fontFamily: fonts.body,
    fontSize: fontSizes.xs,
    color: colors.brand.primary,
    marginLeft: spacing.xs,
    flex: 1,
  },
  divider: {
    width: 1,
    backgroundColor: colors.gray[200],
    marginHorizontal: spacing.sm,
    alignSelf: 'stretch',
  },
  socialSection: {
    flex: 1,
    paddingLeft: spacing.sm,
  },
  compactSocialContainer: {
    flexDirection: 'row',
    justifyContent: 'flex-start',
    alignItems: 'center',
    gap: spacing.sm,
  },
  compactSocialButton: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: colors.gray[50],
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: colors.gray[200],
    shadowColor: colors.gray[300],
    shadowOffset: {
      width: 0,
      height: 1,
    },
    shadowOpacity: 0.2,
    shadowRadius: 2,
    elevation: 1,
  },
});
