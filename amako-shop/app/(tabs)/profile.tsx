import React, { useState, useEffect, useRef } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert, Image, Modal, TextInput, Dimensions, Animated } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { CameraView, useCameraPermissions } from 'expo-camera';
import QRCode from 'react-native-qrcode-svg';
import { useSession } from '../../src/session/SessionProvider';
import { getToken } from '../../src/session/token';
import { ENV_CONFIG } from '../../src/config/environment';

import * as ImagePicker from 'expo-image-picker';
import { useProfile, useLogout, useChangePassword, useUploadProfilePicture } from '../../src/api/auth-hooks';
import { useLoyalty } from '../../src/api/loyalty';
import { Card, Button, Chip, spacing, fontSizes, fontWeights, colors, radius } from '../../src/ui';
import { client } from '../../src/api/client';
import { SkeletonCard } from '../../src/components';

const { width: screenWidth } = Dimensions.get('window');

type TabType = 'credits' | 'badges' | 'address-book' | 'security' | 'referrals' | 'account';

export default function ProfileScreen() {
  const { user, clearToken } = useSession();
  const { data: profile, isLoading, refetch } = useProfile();
  const { data: loyalty, isLoading: loyaltyLoading, error: loyaltyError, refetch: refetchLoyalty } = useLoyalty();
  const logoutMutation = useLogout();

  // Helper function to get full profile image URL
  const getProfileImageUrl = (imagePath: string): string => {
    if (!imagePath) return '';
    
    // If it's already a full URL, return as is
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
      return imagePath;
    }
    
    // If it's a relative path, prepend the API base URL
    const baseURL = client.defaults.baseURL?.replace('/api', '') || 'http://localhost:8000';
    return `${baseURL}/${imagePath}`;
  };
  const changePasswordMutation = useChangePassword();
  const uploadProfilePictureMutation = useUploadProfilePicture();
  
  const [activeTab, setActiveTab] = useState<TabType>('credits');
  const [showHamburgerMenu, setShowHamburgerMenu] = useState(false);
  const [showTopUpModal, setShowTopUpModal] = useState(false);
  const [topUpTab, setTopUpTab] = useState<'show' | 'scan'>('show');
  const [barcodeInput, setBarcodeInput] = useState('');
  const [showManualEntry, setShowManualEntry] = useState(false);
  const [showScanner, setShowScanner] = useState(false);
  const [permission, requestPermission] = useCameraPermissions();
  const [scanned, setScanned] = useState(false);
  const [showPasswordSuccessModal, setShowPasswordSuccessModal] = useState(false);
  
  // Password form state
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [isUpdatingPassword, setIsUpdatingPassword] = useState(false);
  
  // Password visibility state
  const [showCurrentPassword, setShowCurrentPassword] = useState(false);
  const [showNewPassword, setShowNewPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  
  // Profile picture upload state
  const [isUploadingPicture, setIsUploadingPicture] = useState(false);
  
  const slideAnim = useRef(new Animated.Value(screenWidth)).current;

  // Load profile when component mounts
  useEffect(() => {
    if (user) {
      refetch();
    }
  }, [user, refetch]);

  // Handle hamburger menu animation
  useEffect(() => {
    if (showHamburgerMenu) {
      Animated.timing(slideAnim, {
        toValue: 0,
        duration: 300,
        useNativeDriver: true,
      }).start();
    } else {
      Animated.timing(slideAnim, {
        toValue: screenWidth,
        duration: 300,
        useNativeDriver: true,
      }).start();
    }
  }, [showHamburgerMenu, slideAnim]);

  const openHamburgerMenu = () => {
    setShowHamburgerMenu(true);
  };

  const closeHamburgerMenu = () => {
    setShowHamburgerMenu(false);
  };

  const isProcessingQR = useRef(false);
  const lastScannedQR = useRef('');

  const handleBarCodeScanned = async ({ type, data }: { type: string; data: string }) => {
    // Prevent multiple scans of the same QR code
    if (scanned || isProcessingQR.current || lastScannedQR.current === data) {
      console.log('🚫 Already processing this QR code, ignoring...');
      return;
    }
    
    setScanned(true);
    isProcessingQR.current = true;
    lastScannedQR.current = data;
    setBarcodeInput(data);
    setShowScanner(false);
    
    console.log('📸 QR Code scanned:', data);
    
    // Try to process the QR code
    try {
      // Get token first
      const tokenData = await getToken();
      const token = tokenData?.token;
      
      if (!token) {
        Alert.alert('Error', 'Please log in again to use this feature');
        isProcessingQR.current = false;
        setScanned(false);
        return;
      }

      console.log('📡 Sending QR code to API:', {
        url: `${ENV_CONFIG.API_URL}/wallet/process-qr`,
        code_preview: data.substring(0, 50) + '...'
      });

      // Call API to process QR code
      const response = await fetch(`${ENV_CONFIG.API_URL}/wallet/process-qr`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify({
          code: data,
        }),
      });

      console.log('📡 Response status:', response.status, response.statusText);

      if (!response.ok) {
        const errorText = await response.text();
        console.error('❌ API Error Response:', errorText);
        Alert.alert(
          'Processing Failed',
          `Server returned error (${response.status}): ${errorText.substring(0, 100)}`,
          [{ 
            text: 'OK',
            onPress: () => {
              isProcessingQR.current = false;
              setScanned(false);
            }
          }]
        );
        return;
      }

      const result = await response.json();
      console.log('💳 QR code processing result:', result);

      if (result.success) {
        // Success! Refresh loyalty data
        await refetchLoyalty();
        
        // Show success modal with better UX
        Alert.alert(
          '✅ Credits Added Successfully!',
          `Rs. ${result.amount_added} has been added to your account.\n\nNew Balance: Rs. ${result.new_balance}`,
          [
            {
              text: 'View Wallet',
              onPress: () => {
                setActiveTab('credits');
                isProcessingQR.current = false;
              },
            },
            { 
              text: 'OK', 
              style: 'default',
              onPress: () => {
                isProcessingQR.current = false;
              }
            },
          ]
        );
      } else {
        Alert.alert(
          '❌ Processing Failed',
          result.message || 'Unable to process QR code. Please try again or contact support.',
          [{ 
            text: 'OK',
            onPress: () => {
              isProcessingQR.current = false;
              setScanned(false);
            }
          }]
        );
      }
    } catch (error) {
      console.error('❌ Error processing QR code:', error);
      Alert.alert(
        'Error',
        'Failed to process QR code. Please check your connection and try again.',
        [{ 
          text: 'OK',
          onPress: () => {
            isProcessingQR.current = false;
            setScanned(false);
          }
        }]
      );
    }
  };

  const openScanner = async () => {
    if (!permission?.granted) {
      const result = await requestPermission();
      if (!result.granted) {
        Alert.alert('Permission Required', 'Camera permission is needed to scan barcodes.');
        return;
      }
    }
    // Reset scan state when opening scanner
    setScanned(false);
    isProcessingQR.current = false;
    lastScannedQR.current = ''; // Clear last scanned QR for new scan
    setShowScanner(true);
  };

  // Generate QR code data for Laravel system
  const generateQRCodeData = () => {
    const qrData = {
      type: 'credit_transfer',
      user_id: profile?.id || user?.id,
      user_name: profile?.name || user?.name,
      user_email: profile?.email || user?.email,
      current_balance: loyalty?.credits || 0,
      timestamp: new Date().toISOString(),
      app_version: '1.0.0',
      transfer_endpoint: '/api/credits/transfer' // Your Laravel API endpoint
    };
    
    return JSON.stringify(qrData);
  };

  const handleUpdatePassword = async () => {
    // Validation
    if (!currentPassword.trim()) {
      Alert.alert('Error', 'Please enter your current password');
      return;
    }
    
    if (!newPassword.trim()) {
      Alert.alert('Error', 'Please enter a new password');
      return;
    }
    
    if (newPassword.length < 6) {
      Alert.alert('Error', 'New password must be at least 6 characters long');
      return;
    }
    
    if (newPassword !== confirmPassword) {
      Alert.alert('Error', 'New password and confirm password do not match');
      return;
    }
    
    if (currentPassword === newPassword) {
      Alert.alert('Error', 'New password must be different from current password');
      return;
    }

    setIsUpdatingPassword(true);
    
    try {
      console.log('🔐 Profile: Starting password change with:', {
        currentPasswordLength: currentPassword.length,
        newPasswordLength: newPassword.length,
        confirmPasswordLength: confirmPassword.length
      });
      
      // Call the real API
      const result = await changePasswordMutation.mutateAsync({
        current_password: currentPassword,
        new_password: newPassword,
        new_password_confirmation: confirmPassword
      });
      
      console.log('🔐 Profile: Password change API response:', result);
      
      // Clear form immediately
      setCurrentPassword('');
      setNewPassword('');
      setConfirmPassword('');
      setShowCurrentPassword(false);
      setShowNewPassword(false);
      setShowConfirmPassword(false);
      
      // Show beautiful success modal
      setShowPasswordSuccessModal(true);
      
    } catch (error: any) {
      console.error('🔐 Profile: Password update error:', error);
      console.error('🔐 Profile: Error details:', {
        status: error?.response?.status,
        statusText: error?.response?.statusText,
        data: error?.response?.data,
        message: error?.message
      });
      
      // Extract error message from API response
      let errorMessage = 'Failed to update password. Please try again.';
      if (error?.response?.data?.message) {
        errorMessage = error.response.data.message;
      } else if (error?.message) {
        errorMessage = error.message;
      }
      
      Alert.alert('Error', errorMessage);
    } finally {
      setIsUpdatingPassword(false);
    }
  };

  const handleUploadProfilePicture = async () => {
    try {
      // Request permission to access media library
      const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
      
      if (status !== 'granted') {
        Alert.alert(
          'Permission Required',
          'Please allow access to your photos to upload a profile picture.'
        );
        return;
      }

      // Show options: Camera or Gallery
      Alert.alert(
        'Upload Profile Picture',
        'Choose an option',
        [
          {
            text: 'Take Photo',
            onPress: () => pickImageFromCamera(),
          },
          {
            text: 'Choose from Gallery',
            onPress: () => pickImageFromGallery(),
          },
          {
            text: 'Cancel',
            style: 'cancel',
          },
        ]
      );
    } catch (error) {
      console.error('Error requesting permissions:', error);
      Alert.alert('Error', 'Failed to request permissions');
    }
  };

  const pickImageFromCamera = async () => {
    try {
      const { status } = await ImagePicker.requestCameraPermissionsAsync();
      
      if (status !== 'granted') {
        Alert.alert(
          'Permission Required',
          'Please allow access to your camera to take a photo.'
        );
        return;
      }

      const result = await ImagePicker.launchCameraAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        allowsEditing: true,
        aspect: [1, 1],
        quality: 0.8,
      });

      if (!result.canceled && result.assets[0]) {
        await uploadImage(result.assets[0].uri);
      }
    } catch (error) {
      console.error('Error taking photo:', error);
      Alert.alert('Error', 'Failed to take photo');
    }
  };

  const pickImageFromGallery = async () => {
    try {
      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        allowsEditing: true,
        aspect: [1, 1],
        quality: 0.8,
      });

      if (!result.canceled && result.assets[0]) {
        await uploadImage(result.assets[0].uri);
      }
    } catch (error) {
      console.error('Error picking image:', error);
      Alert.alert('Error', 'Failed to pick image');
    }
  };

  const uploadImage = async (imageUri: string) => {
    setIsUploadingPicture(true);
    
    try {
      console.log('📸 Uploading profile picture:', imageUri);
      
      const result = await uploadProfilePictureMutation.mutateAsync(imageUri);
      
      Alert.alert('Success', 'Profile picture updated successfully!');
      
      // Refetch profile to show new picture
      await refetch();
    } catch (error: any) {
      console.error('Error uploading profile picture:', error);
      
      let errorMessage = 'Failed to upload profile picture. Please try again.';
      if (error?.message) {
        errorMessage = error.message;
      }
      
      Alert.alert('Error', errorMessage);
    } finally {
      setIsUploadingPicture(false);
    }
  };

  const handleLogout = () => {
    console.log('🔐 Profile: Logout button pressed');
    Alert.alert(
      'Logout',
      'Are you sure you want to logout?',
      [
        { text: 'Cancel', style: 'cancel' },
        { 
          text: 'Logout', 
          style: 'destructive',
          onPress: () => {
            console.log('🔐 Profile: Confirmed logout, calling mutation');
            logoutMutation.mutate();
          }
        }
      ]
    );
  };

  const handleRefreshProfile = () => {
    refetch();
  };

  // Helper function to get badge variant based on tier
  const getBadgeVariant = (tier: string) => {
    switch (tier.toLowerCase()) {
      case 'gold':
        return 'warning';
      case 'silver':
        return 'primary';
      case 'bronze':
        return 'default';
      case 'platinum':
        return 'success';
      default:
        return 'default';
    }
  };

  const tabs = [
    { id: 'credits' as TabType, label: 'Profile Info', icon: 'person-outline', color: '#3B82F6' },
    { id: 'badges' as TabType, label: 'Badges', icon: 'medal-outline', color: '#8B5CF6' },
    { id: 'address-book' as TabType, label: 'Address Book', icon: 'location-outline', color: '#F59E0B' },
    { id: 'security' as TabType, label: 'Security', icon: 'shield-outline', color: '#EF4444' },
    { id: 'referrals' as TabType, label: 'Referrals', icon: 'people-outline', color: '#6366F1' },
    { id: 'logout' as TabType, label: 'Logout', icon: 'log-out-outline', color: '#EF4444' },
  ];

  const renderTabContent = () => {
    switch (activeTab) {
      case 'credits':
        return renderCreditsTab();
      case 'badges':
        return renderBadgesTab();
      case 'address-book':
        return renderAddressBookTab();
      case 'security':
        return renderSecurityTab();
      case 'referrals':
        return renderReferralsTab();
      case 'account':
        return renderAccountTab();
      default:
        return renderCreditsTab();
    }
  };

  const renderCreditsTab = () => (
    <View style={styles.tabContent}>
      {/* Profile Section - Simplified */}
      <View style={styles.profileSection}>
        <LinearGradient
          colors={['#FEF3C7', '#FED7AA']}
          style={styles.profileGradient}
        >
          {/* Top Row - Profile Picture Left, Achievement Right */}
          <View style={styles.topRow}>
            {/* Profile Picture - Left Side */}
            <View style={styles.profilePictureContainer}>
              <TouchableOpacity 
                style={styles.profilePicture}
                onPress={handleUploadProfilePicture}
                disabled={isUploadingPicture}
              >
                {(profile as any)?.profile_picture ? (
                  <Image 
                    source={{ uri: getProfileImageUrl((profile as any).profile_picture) }} 
                    style={styles.profileImage}
                    resizeMode="cover"
                  />
                ) : (
                  <View style={styles.profileImagePlaceholder}>
                    <Ionicons name="person" size={64} color="#F59E0B" />
                  </View>
                )}
                
                {/* Camera/Upload Button Overlay */}
                <View style={styles.cameraButtonOverlay}>
                  {isUploadingPicture ? (
                    <View style={styles.cameraButton}>
                      <Ionicons name="hourglass" size={20} color="#FFFFFF" />
                    </View>
                  ) : (
                    <View style={styles.cameraButton}>
                      <Ionicons name="camera" size={20} color="#FFFFFF" />
                    </View>
                  )}
                </View>
              </TouchableOpacity>
            </View>
            
            {/* Achievement Badge - Right Side */}
            <View style={styles.achievementSection}>
              <Text style={styles.achievementTitle}>Achievement</Text>
              {loyalty?.badges && loyalty.badges.length > 0 ? (
                <View style={styles.achievementContent}>
                  <View style={styles.badgeIconContainer}>
                    <Ionicons name="trophy" size={24} color="#FFFFFF" />
                  </View>
                  <View style={styles.badgeInfo}>
                    <Text style={styles.badgeName}>
                      {loyalty.badges[0].name || 'Achievement'}
                    </Text>
                    <View style={styles.badgeTierContainer}>
                      <Ionicons name="star" size={10} color="#F59E0B" />
                      <Text style={styles.badgeTier}>
                        {loyalty.badges[0].tier || 'Bronze'}
                      </Text>
                    </View>
                  </View>
                </View>
              ) : (
                <View style={styles.achievementContent}>
                  <View style={styles.noBadgeIconContainer}>
                    <Ionicons name="trophy-outline" size={24} color="#9CA3AF" />
                  </View>
                  <View style={styles.badgeInfo}>
                    <Text style={styles.badgeName}>No Badge</Text>
                    <View style={styles.badgeTierContainer}>
                      <Text style={styles.badgeTier}>Start earning!</Text>
                    </View>
                  </View>
                </View>
              )}
            </View>
          </View>
          
          {/* User Details - Below */}
          <View style={styles.userDetailsSection}>
            <Text style={styles.userName}>
              {profile?.name || user?.name || 'User'}
            </Text>
            <View style={styles.userEmailContainer}>
              <Ionicons name="mail-outline" size={12} color="#F59E0B" />
              <Text style={styles.userEmail}>
                {profile?.email || user?.email || 'No email'}
              </Text>
            </View>
            <View style={styles.memberSinceContainer}>
              <Ionicons name="calendar-outline" size={12} color="#6B7280" />
              <Text style={styles.memberSince}>
                Member since {profile?.created_at ? new Date(profile.created_at).toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) : 'Unknown'}
              </Text>
            </View>
            
            {/* Credit Balance */}
            <View style={styles.creditBalanceSection}>
              <View style={styles.creditBalanceContainer}>
                <Ionicons name="wallet-outline" size={16} color="#F59E0B" />
                <Text style={styles.creditBalanceLabel}>Credits:</Text>
                <Text style={styles.creditBalanceAmount}>
                  Rs. {loyalty?.credits || '0.00'}
                </Text>
              </View>
            </View>
          </View>
        </LinearGradient>
      </View>

      {/* Credit Top-up Section */}
      <View style={styles.topUpSection}>
        <View style={styles.topUpHeader}>
          <View style={styles.topUpIconContainer}>
            <Ionicons name="add-circle-outline" size={20} color="#FFFFFF" />
          </View>
          <Text style={styles.topUpTitle}>Add Credits</Text>
        </View>
        
        <View style={styles.topUpContent}>
          {/* Tab Navigation */}
          <View style={styles.topUpTabs}>
            <TouchableOpacity 
              style={[styles.topUpTab, topUpTab === 'show' && styles.activeTopUpTab]}
              onPress={() => setTopUpTab('show')}
            >
              <Ionicons name="qr-code-outline" size={16} color={topUpTab === 'show' ? '#10B981' : '#6B7280'} />
              <Text style={[styles.topUpTabText, topUpTab === 'show' && styles.activeTopUpTabText]}>
                Show QR Code
              </Text>
            </TouchableOpacity>
            <TouchableOpacity 
              style={[styles.topUpTab, topUpTab === 'scan' && styles.activeTopUpTab]}
              onPress={() => setTopUpTab('scan')}
            >
              <Ionicons name="scan-outline" size={16} color={topUpTab === 'scan' ? '#10B981' : '#6B7280'} />
              <Text style={[styles.topUpTabText, topUpTab === 'scan' && styles.activeTopUpTabText]}>
                Scan QR Code
              </Text>
            </TouchableOpacity>
          </View>

          {/* Show QR Code Tab Content */}
          {topUpTab === 'show' && (
            <View style={styles.topUpTabContent}>
              <View style={styles.qrCodeInfo}>
                <View style={styles.qrCodeIconContainer}>
                  <Ionicons name="qr-code-outline" size={20} color="#FFFFFF" />
                </View>
                <Text style={styles.qrCodeInfoText}>
                  Generate a QR code to receive credit top-ups from other users
                </Text>
              </View>
              
              <TouchableOpacity 
                style={styles.showQRButton}
                onPress={() => setShowTopUpModal(true)}
              >
                <Ionicons name="qr-code-outline" size={20} color="#FFFFFF" />
                <Text style={styles.showQRButtonText}>Generate My QR Code</Text>
              </TouchableOpacity>
              
              <View style={styles.qrCodeFeatures}>
                <View style={styles.featureItem}>
                  <Ionicons name="checkmark-circle" size={16} color="#10B981" />
                  <Text style={styles.featureText}>Share with friends to receive credits</Text>
                </View>
                <View style={styles.featureItem}>
                  <Ionicons name="checkmark-circle" size={16} color="#10B981" />
                  <Text style={styles.featureText}>Instant credit transfer</Text>
                </View>
                <View style={styles.featureItem}>
                  <Ionicons name="checkmark-circle" size={16} color="#10B981" />
                  <Text style={styles.featureText}>Secure and encrypted</Text>
                </View>
              </View>
            </View>
          )}

          {/* Scan QR Code Tab Content */}
          {topUpTab === 'scan' && (
            <View style={styles.topUpTabContent}>
              <View style={styles.scanInstructions}>
                <View style={styles.scanIconContainer}>
                  <Ionicons name="camera-outline" size={16} color="#FFFFFF" />
                </View>
                <Text style={styles.scanInstructionsText}>
                  Scan a QR code or enter barcode to top up your account
                </Text>
              </View>
              
              <View style={styles.scanActions}>
                <TouchableOpacity 
                  style={styles.scanButton}
                  onPress={openScanner}
                >
                  <Ionicons name="scan-outline" size={20} color="#FFFFFF" />
                  <Text style={styles.scanButtonText}>Scan Barcode</Text>
                </TouchableOpacity>
                
                <TouchableOpacity 
                  style={styles.manualEntryButton}
                  onPress={() => setShowManualEntry(!showManualEntry)}
                >
                  <Text style={styles.manualEntryText}>Or enter code manually</Text>
                </TouchableOpacity>

                {showManualEntry && (
                  <View style={styles.manualEntryForm}>
                    <Text style={styles.manualEntryLabel}>QR Code or Barcode</Text>
                    <TextInput
                      style={styles.barcodeInput}
                      placeholder="Enter QR code data or 12-digit barcode"
                      value={barcodeInput}
                      onChangeText={setBarcodeInput}
                      multiline={false}
                    />
                    <Text style={styles.barcodeHint}>Enter QR code data (JSON) or 12-digit barcode</Text>
                    
                    <View style={styles.manualEntryActions}>
                      <TouchableOpacity style={styles.processButton}>
                        <Ionicons name="checkmark-circle-outline" size={20} color="#FFFFFF" />
                        <Text style={styles.processButtonText}>Process Card</Text>
                      </TouchableOpacity>
                      
                      <TouchableOpacity 
                        style={styles.cancelButton}
                        onPress={() => setShowManualEntry(false)}
                      >
                        <Text style={styles.cancelButtonText}>Cancel</Text>
                      </TouchableOpacity>
                    </View>
                  </View>
                )}
              </View>
            </View>
          )}
        </View>
      </View>
    </View>
  );

  const renderBadgesTab = () => {
    // Get real badge data from API
    const badgesEarned = loyalty?.badges?.length || 0;
    const totalBadges = 9; // Total possible badges (could be dynamic later)
    const progressPercentage = totalBadges > 0 ? (badgesEarned / totalBadges) * 100 : 0;
    
    return (
    <View style={styles.tabContent}>
      {/* Hero Header Section */}
      <View style={styles.badgeHeroSection}>
        <View style={styles.badgeHeroContent}>
          <Text style={styles.badgeHeroIcon}>🏆</Text>
          <Text style={styles.badgeHeroTitle}>Your Achievement Collection</Text>
          <Text style={styles.badgeHeroSubtitle}>Unlock badges, climb ranks, and become a Momo legend!</Text>
        </View>
        <View style={styles.badgeFloatingElements}>
          <Text style={styles.badgeFloatingIcon1}>🥇</Text>
          <Text style={styles.badgeFloatingIcon2}>⭐</Text>
          <Text style={styles.badgeFloatingIcon3}>🎖️</Text>
        </View>
      </View>

      {/* Stats Dashboard - Real Data */}
      <View style={styles.badgeStatsGrid}>
        <View style={styles.badgeStatCard}>
          <Text style={styles.badgeStatIcon}>🏆</Text>
          <Text style={styles.badgeStatNumber}>{badgesEarned}</Text>
          <Text style={styles.badgeStatLabel}>Badges Earned</Text>
        </View>
        <View style={styles.badgeStatCard}>
          <Text style={styles.badgeStatIcon}>👑</Text>
          <Text style={styles.badgeStatNumber}>{loyalty?.tier || 'Bronze'}</Text>
          <Text style={styles.badgeStatLabel}>Current Tier</Text>
        </View>
        <View style={styles.badgeStatCard}>
          <Text style={styles.badgeStatIcon}>💰</Text>
          <Text style={styles.badgeStatNumber}>{loyalty?.credits?.toLocaleString() || 0}</Text>
          <Text style={styles.badgeStatLabel}>Total Credits</Text>
        </View>
        <View style={styles.badgeStatCard}>
          <Text style={styles.badgeStatIcon}>🎯</Text>
          <Text style={styles.badgeStatNumber}>{badgesEarned > 0 ? 'Active' : 'Start'}</Text>
          <Text style={styles.badgeStatLabel}>Status</Text>
        </View>
      </View>

      {/* Progress Overview - Real Data */}
      <View style={styles.badgeProgressCard}>
        <View style={styles.badgeProgressHeader}>
          <Text style={styles.badgeProgressTitle}>Collection Progress</Text>
          <Text style={styles.badgeProgressCount}>{badgesEarned} of {totalBadges} badges collected</Text>
        </View>
        <View style={styles.badgeProgressBar}>
          <View style={[styles.badgeProgressFill, { width: `${progressPercentage}%` }]} />
        </View>
      </View>

      {/* Badge Collection Gallery - Real Data */}
      <View style={styles.badgeCollectionSection}>
        <Text style={styles.badgeCollectionTitle}>🎮 Badge Collection</Text>
        
        {loyaltyLoading ? (
          <Text style={styles.loadingText}>Loading badges...</Text>
        ) : badgesEarned > 0 ? (
          <View style={styles.badgeGrid}>
            {loyalty?.badges?.map((badge: any, index: number) => (
              <View key={index} style={styles.badgeCard}>
                <View style={styles.badgeCardContent}>
                  <View style={styles.badgeIconContainer}>
                    <Text style={styles.badgeIcon}>🏆</Text>
                  </View>
                  <View style={styles.badgeStatus}>
                    <View style={styles.badgeStatusUnlocked}>
                      <Text style={styles.badgeStatusText}>✓</Text>
                    </View>
                  </View>
                </View>
                <Text style={styles.badgeName}>{badge.name || 'Badge'}</Text>
                <View style={styles.badgeRankContainer}>
                  <Text style={styles.badgeRank}>{badge.tier || 'Bronze'} Rank</Text>
                </View>
                <TouchableOpacity style={styles.badgeActionButton}>
                  <Text style={styles.badgeActionButtonText}>View Details</Text>
                </TouchableOpacity>
              </View>
            ))}
          </View>
        ) : (
          <View style={styles.emptyStateContainer}>
            <Text style={styles.emptyStateIcon}>🏆</Text>
            <Text style={styles.emptyStateTitle}>No Badges Yet</Text>
            <Text style={styles.emptyStateText}>Start ordering to earn your first badge!</Text>
          </View>
        )}
      </View>

      {/* Achievement History - Real Data */}
      <View style={styles.badgeHistorySection}>
        <Text style={styles.badgeHistoryTitle}>📜 Achievement History</Text>
        {badgesEarned > 0 ? (
          <View style={styles.badgeHistoryList}>
            {loyalty?.badges?.map((badge: any, index: number) => (
              <View key={index} style={styles.badgeHistoryItem}>
                <View style={styles.badgeHistoryIcon}>
                  <Text style={styles.badgeHistoryIconText}>🏆</Text>
                </View>
                <View style={styles.badgeHistoryContent}>
                  <Text style={styles.badgeHistoryName}>{badge.name || 'Badge'}</Text>
                  <Text style={styles.badgeHistoryRank}>{badge.tier || 'Bronze'} Rank</Text>
                  <Text style={styles.badgeHistoryDesc}>Achievement unlocked!</Text>
                </View>
                <View style={styles.badgeHistoryDate}>
                  <Text style={styles.badgeHistoryStatus}>ACHIEVED!</Text>
                </View>
              </View>
            ))}
          </View>
        ) : (
          <View style={styles.emptyStateContainer}>
            <Text style={styles.emptyStateIcon}>📜</Text>
            <Text style={styles.emptyStateTitle}>No Achievements Yet</Text>
            <Text style={styles.emptyStateText}>Your badge history will appear here as you earn them!</Text>
          </View>
        )}
      </View>
    </View>
  );
  };

  const renderAddressBookTab = () => (
    <View style={styles.tabContent}>
      {/* Header Section */}
      <View style={styles.addressHeaderSection}>
        <View style={styles.addressHeaderContent}>
          <Text style={styles.addressHeaderTitle}>Address Book</Text>
          <Text style={styles.addressHeaderSubtitle}>Manage your delivery addresses</Text>
        </View>
        <TouchableOpacity style={styles.addressAddButton}>
          <Ionicons name="add" size={20} color="#FFFFFF" />
          <Text style={styles.addressAddButtonText}>Add New Address</Text>
        </TouchableOpacity>
      </View>

      {/* Address List - Real Data or Empty State */}
      <View style={styles.addressList}>
        {profile?.city || profile?.ward_number || profile?.area_locality ? (
          // Show user's saved address from profile
          <View style={styles.addressCard}>
            <View style={styles.addressDefaultBadge}>
              <Ionicons name="checkmark" size={12} color="#FFFFFF" />
              <Text style={styles.addressDefaultText}>Default</Text>
            </View>
            
            <View style={styles.addressCardContent}>
              <View style={styles.addressCardIcon}>
                <Ionicons name="location" size={24} color="#FFFFFF" />
              </View>
              <View style={styles.addressCardInfo}>
                <View style={styles.addressCardHeader}>
                  <Text style={styles.addressCardName}>{profile?.name || user?.name || 'User'}</Text>
                  <Text style={styles.addressCardType}>• Home</Text>
                </View>
                
                <View style={styles.addressCardDetails}>
                  {profile?.area_locality && (
                    <View style={styles.addressCardDetail}>
                      <Ionicons name="location-outline" size={16} color="#9CA3AF" />
                      <Text style={styles.addressCardDetailText}>{profile.area_locality}</Text>
                    </View>
                  )}
                  {profile?.ward_number && (
                    <View style={styles.addressCardDetail}>
                      <Ionicons name="map-outline" size={16} color="#9CA3AF" />
                      <Text style={styles.addressCardDetailText}>Ward {profile.ward_number}</Text>
                    </View>
                  )}
                  {profile?.city && (
                    <View style={styles.addressCardDetail}>
                      <Ionicons name="business-outline" size={16} color="#9CA3AF" />
                      <Text style={styles.addressCardDetailText}>{profile.city}</Text>
                    </View>
                  )}
                  {profile?.building_name && (
                    <View style={styles.addressCardDetail}>
                      <Ionicons name="home-outline" size={16} color="#9CA3AF" />
                      <Text style={styles.addressCardDetailText}>{profile.building_name}</Text>
                    </View>
                  )}
                  {profile?.detailed_directions && (
                    <View style={styles.addressCardDetail}>
                      <Ionicons name="information-circle-outline" size={16} color="#9CA3AF" />
                      <Text style={styles.addressCardDetailText}>{profile.detailed_directions}</Text>
                    </View>
                  )}
                </View>
              </View>
              <View style={styles.addressCardActions}>
                <TouchableOpacity style={styles.addressEditButton}>
                  <Ionicons name="create-outline" size={16} color="#3B82F6" />
                  <Text style={styles.addressEditButtonText}>Edit</Text>
                </TouchableOpacity>
              </View>
            </View>
          </View>
        ) : (
          // Empty state when no address saved
          <View style={styles.emptyStateContainer}>
            <Ionicons name="location-outline" size={64} color={colors.gray[400]} />
            <Text style={styles.emptyStateTitle}>No Address Saved</Text>
            <Text style={styles.emptyStateText}>
              Add your delivery address to speed up checkout and get accurate delivery estimates.
            </Text>
            <TouchableOpacity style={styles.emptyStateButton}>
              <Ionicons name="add-circle-outline" size={20} color={colors.white} />
              <Text style={styles.emptyStateButtonText}>Add Address</Text>
            </TouchableOpacity>
          </View>
        )}
      </View>
    </View>
  );

  const renderSecurityTab = () => (
    <View style={styles.tabContent}>
      {/* Header Section */}
      <View style={styles.securityHeaderSection}>
        <View style={styles.securityHeaderIcon}>
          <Ionicons name="shield-checkmark" size={24} color="#FFFFFF" />
        </View>
        <View style={styles.securityHeaderContent}>
          <Text style={styles.securityHeaderTitle}>Security Settings</Text>
          <Text style={styles.securityHeaderSubtitle}>Manage your password and account security</Text>
        </View>
      </View>

      {/* Change Password Form */}
      <View style={styles.securityFormCard}>
        <View style={styles.securityFormHeader}>
          <View style={styles.securityFormIcon}>
            <Ionicons name="key" size={20} color="#FFFFFF" />
          </View>
          <Text style={styles.securityFormTitle}>Change Password</Text>
        </View>
        
        <View style={styles.securityForm}>
          <View style={styles.securityInputContainer}>
            <Text style={styles.securityInputLabel}>Current Password</Text>
            <View style={styles.securityInputWrapper}>
              <Ionicons name="lock-closed" size={20} color="#9CA3AF" style={styles.securityInputIcon} />
              <TextInput 
                style={styles.securityInput}
                placeholder="Enter your current password"
                placeholderTextColor="#9CA3AF"
                secureTextEntry={!showCurrentPassword}
                value={currentPassword}
                onChangeText={setCurrentPassword}
              />
              <TouchableOpacity 
                onPress={() => setShowCurrentPassword(!showCurrentPassword)}
                style={styles.passwordToggle}
              >
                <Ionicons 
                  name={showCurrentPassword ? "eye-off" : "eye"} 
                  size={20} 
                  color="#9CA3AF" 
                />
              </TouchableOpacity>
            </View>
          </View>
          
          <View style={styles.securityInputContainer}>
            <Text style={styles.securityInputLabel}>New Password</Text>
            <View style={styles.securityInputWrapper}>
              <Ionicons name="lock-closed" size={20} color="#9CA3AF" style={styles.securityInputIcon} />
              <TextInput 
                style={styles.securityInput}
                placeholder="Enter your new password"
                placeholderTextColor="#9CA3AF"
                secureTextEntry={!showNewPassword}
                value={newPassword}
                onChangeText={setNewPassword}
              />
              <TouchableOpacity 
                onPress={() => setShowNewPassword(!showNewPassword)}
                style={styles.passwordToggle}
              >
                <Ionicons 
                  name={showNewPassword ? "eye-off" : "eye"} 
                  size={20} 
                  color="#9CA3AF" 
                />
              </TouchableOpacity>
            </View>
          </View>
          
          <View style={styles.securityInputContainer}>
            <Text style={styles.securityInputLabel}>Confirm New Password</Text>
            <View style={styles.securityInputWrapper}>
              <Ionicons name="lock-closed" size={20} color="#9CA3AF" style={styles.securityInputIcon} />
              <TextInput 
                style={styles.securityInput}
                placeholder="Confirm your new password"
                placeholderTextColor="#9CA3AF"
                secureTextEntry={!showConfirmPassword}
                value={confirmPassword}
                onChangeText={setConfirmPassword}
              />
              <TouchableOpacity 
                onPress={() => setShowConfirmPassword(!showConfirmPassword)}
                style={styles.passwordToggle}
              >
                <Ionicons 
                  name={showConfirmPassword ? "eye-off" : "eye"} 
                  size={20} 
                  color="#9CA3AF" 
                />
              </TouchableOpacity>
            </View>
          </View>
          
          <TouchableOpacity 
            style={[styles.securityUpdateButton, (isUpdatingPassword || changePasswordMutation.isPending) && { opacity: 0.7 }]} 
            onPress={handleUpdatePassword}
            disabled={isUpdatingPassword || changePasswordMutation.isPending}
          >
            <Ionicons name={(isUpdatingPassword || changePasswordMutation.isPending) ? "hourglass" : "shield-checkmark"} size={20} color="#FFFFFF" />
            <Text style={styles.securityUpdateButtonText}>
              {(isUpdatingPassword || changePasswordMutation.isPending) ? 'Updating...' : 'Update Password'}
            </Text>
          </TouchableOpacity>
        </View>
      </View>

      {/* Security Information */}
      <View style={styles.securityInfoCard}>
        <View style={styles.securityInfoHeader}>
          <View style={styles.securityInfoIcon}>
            <Ionicons name="shield-checkmark" size={20} color="#FFFFFF" />
          </View>
          <Text style={styles.securityInfoTitle}>Security Information</Text>
        </View>
        
        <View style={styles.securityInfoGrid}>
          <View style={styles.securityInfoItem}>
            <View style={styles.securityInfoItemIcon}>
              <Ionicons name="calendar" size={16} color="#3B82F6" />
            </View>
            <View style={styles.securityInfoItemContent}>
              <Text style={styles.securityInfoItemLabel}>Last Password Change</Text>
              <Text style={styles.securityInfoItemValue}>
                {profile?.updated_at 
                  ? new Date(profile.updated_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                  : 'Never'}
              </Text>
            </View>
          </View>
          
          <View style={styles.securityInfoItem}>
            <View style={styles.securityInfoItemIcon}>
              <Ionicons name="time" size={16} color="#10B981" />
            </View>
            <View style={styles.securityInfoItemContent}>
              <Text style={styles.securityInfoItemLabel}>Account Created</Text>
              <Text style={styles.securityInfoItemValue}>
                {profile?.created_at 
                  ? new Date(profile.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                  : 'Unknown'}
              </Text>
            </View>
          </View>
        </View>
      </View>
    </View>
  );

  const renderReferralsTab = () => (
    <View style={styles.tabContent}>
      {/* Header Section */}
      <View style={styles.referralHeaderSection}>
        <View style={styles.referralHeaderIcon}>
          <Ionicons name="people" size={24} color="#FFFFFF" />
        </View>
        <View style={styles.referralHeaderContent}>
          <Text style={styles.referralHeaderTitle}>Referrals</Text>
          <Text style={styles.referralHeaderSubtitle}>Share your referral code and earn rewards</Text>
        </View>
      </View>

      {/* Referral Code Section */}
      <View style={styles.referralCodeCard}>
        <View style={styles.referralCodeHeader}>
          <View style={styles.referralCodeIcon}>
            <Ionicons name="qr-code" size={20} color="#FFFFFF" />
          </View>
          <Text style={styles.referralCodeTitle}>Your Referral Code</Text>
        </View>
        
        <View style={styles.referralCodeContainer}>
          <TextInput 
            style={styles.referralCodeInput}
            value={user?.id ? `AMAKO${user.id}` : "Loading..."}
            editable={false}
          />
          <TouchableOpacity style={styles.referralCodeCopyButton}>
            <Text style={styles.referralCodeCopyText}>Copy</Text>
          </TouchableOpacity>
        </View>
        
        <View style={styles.referralCodeInfo}>
          <Ionicons name="information-circle" size={16} color="#8B5CF6" />
          <Text style={styles.referralCodeInfoText}>
            Share this code with friends to earn rewards when they sign up and make their first order.
          </Text>
        </View>
      </View>

      {/* Referral Stats - Real Data */}
      <View style={styles.referralStatsGrid}>
        <View style={styles.referralStatCard}>
          <View style={styles.referralStatIcon}>
            <Ionicons name="people" size={24} color="#FFFFFF" />
          </View>
          <Text style={styles.referralStatNumber}>0</Text>
          <Text style={styles.referralStatLabel}>Total Referrals</Text>
        </View>
        
        <View style={styles.referralStatCard}>
          <View style={styles.referralStatIcon}>
            <Ionicons name="checkmark-circle" size={24} color="#FFFFFF" />
          </View>
          <Text style={styles.referralStatNumber}>0</Text>
          <Text style={styles.referralStatLabel}>Successful Referrals</Text>
        </View>
        
        <View style={styles.referralStatCard}>
          <View style={styles.referralStatIcon}>
            <Ionicons name="wallet" size={24} color="#FFFFFF" />
          </View>
          <Text style={styles.referralStatNumber}>Rs 0</Text>
          <Text style={styles.referralStatLabel}>Total Earnings</Text>
        </View>
      </View>

      {/* Share Options */}
      <View style={styles.referralShareCard}>
        <View style={styles.referralShareHeader}>
          <View style={styles.referralShareIcon}>
            <Ionicons name="share" size={20} color="#FFFFFF" />
          </View>
          <Text style={styles.referralShareTitle}>Share Your Code</Text>
        </View>
        
        <View style={styles.referralShareButtons}>
          <TouchableOpacity style={styles.referralShareButton}>
            <Ionicons name="logo-whatsapp" size={20} color="#FFFFFF" />
            <Text style={styles.referralShareButtonText}>WhatsApp</Text>
          </TouchableOpacity>
          
          <TouchableOpacity style={styles.referralShareButton}>
            <Ionicons name="mail" size={20} color="#FFFFFF" />
            <Text style={styles.referralShareButtonText}>Email</Text>
          </TouchableOpacity>
          
          <TouchableOpacity style={styles.referralShareButton}>
            <Ionicons name="chatbubble" size={20} color="#FFFFFF" />
            <Text style={styles.referralShareButtonText}>SMS</Text>
          </TouchableOpacity>
        </View>
      </View>
    </View>
  );

  const renderAccountTab = () => (
    <View style={styles.tabContent}>
      <View style={styles.accountActions}>
        <TouchableOpacity style={styles.accountAction} onPress={handleRefreshProfile}>
          <Ionicons name="refresh-outline" size={24} color="#6B7280" />
          <Text style={styles.accountActionText}>Refresh Profile</Text>
          <Ionicons name="chevron-forward" size={20} color="#9CA3AF" />
        </TouchableOpacity>
        
        <TouchableOpacity style={styles.accountAction}>
          <Ionicons name="settings-outline" size={24} color="#6B7280" />
          <Text style={styles.accountActionText}>Settings</Text>
          <Ionicons name="chevron-forward" size={20} color="#9CA3AF" />
        </TouchableOpacity>
        
        <TouchableOpacity style={styles.accountAction}>
          <Ionicons name="help-circle-outline" size={24} color="#6B7280" />
          <Text style={styles.accountActionText}>Help & Support</Text>
          <Ionicons name="chevron-forward" size={20} color="#9CA3AF" />
        </TouchableOpacity>
        
        <TouchableOpacity style={styles.accountAction} onPress={handleLogout}>
          <Ionicons name="log-out-outline" size={24} color="#EF4444" />
          <Text style={[styles.accountActionText, { color: '#EF4444' }]}>Logout</Text>
          <Ionicons name="chevron-forward" size={20} color="#9CA3AF" />
        </TouchableOpacity>
      </View>
    </View>
  );

  if (isLoading) {
    return (
      <ScrollView style={styles.container} contentContainerStyle={styles.content}>
        <View style={styles.header}>
          <SkeletonCard height={80} />
        </View>
        <View style={styles.section}>
          <SkeletonCard height={120} />
        </View>
        <View style={styles.section}>
          <SkeletonCard height={60} />
        </View>
      </ScrollView>
    );
  }

  return (
    <View style={styles.container}>
      {/* Header with Hamburger Menu */}
      <View style={styles.header}>
        <View style={styles.headerContent}>
          <Text style={styles.headerTitle}>Profile</Text>
          <TouchableOpacity 
            style={styles.hamburgerButton}
            onPress={openHamburgerMenu}
          >
            <Ionicons name="menu" size={24} color="#374151" />
          </TouchableOpacity>
            </View>
      </View>

      {/* Tab Content */}
      <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
        {renderTabContent()}
      </ScrollView>

      {/* Top-up Modal */}
      <Modal
        visible={showTopUpModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowTopUpModal(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>My Credit QR Code</Text>
              <TouchableOpacity onPress={() => setShowTopUpModal(false)}>
                <Ionicons name="close" size={24} color="#6B7280" />
              </TouchableOpacity>
            </View>
            
            {/* QR Code Display */}
            <View style={styles.qrCodeContainer}>
              <View style={styles.qrCodeWrapper}>
                <QRCode
                  value={generateQRCodeData()}
                  size={200}
                  color="#000000"
                  backgroundColor="#FFFFFF"
                  logoSize={30}
                  logoMargin={2}
                  logoBorderRadius={15}
                  quietZone={10}
                />
              </View>
              <Text style={styles.qrCodeDescription}>
                Share this QR code with friends to receive credit transfers
              </Text>
              <Text style={styles.qrCodeInfoText}>
                This QR code contains your user information and can be scanned by other users to send you credits
              </Text>
            </View>
            
            {/* User Info */}
            <View style={styles.userInfoSection}>
              <View style={styles.infoItem}>
                <Ionicons name="person-circle-outline" size={20} color="#6B7280" />
                <View style={styles.infoContent}>
                  <Text style={styles.infoLabel}>Your Name</Text>
                  <Text style={styles.infoValue}>{profile?.name || user?.name || 'User'}</Text>
          </View>
      </View>

              <View style={styles.infoItem}>
                <Ionicons name="wallet-outline" size={20} color="#6B7280" />
                <View style={styles.infoContent}>
                  <Text style={styles.infoLabel}>Current Balance</Text>
                  <Text style={styles.infoValue}>Rs. {loyalty?.credits || '0.00'}</Text>
                </View>
              </View>
              
              <View style={styles.infoItem}>
                <Ionicons name="shield-checkmark-outline" size={20} color="#6B7280" />
                <View style={styles.infoContent}>
                  <Text style={styles.infoLabel}>Security</Text>
                  <Text style={styles.infoValue}>Encrypted & Secure</Text>
                </View>
              </View>
            </View>
            
            {/* Action Buttons */}
            <View style={styles.qrCodeActions}>
              <TouchableOpacity style={styles.shareButton}>
                <Ionicons name="share-outline" size={20} color="#FFFFFF" />
                <Text style={styles.shareButtonText}>Share QR Code</Text>
          </TouchableOpacity>
          
              <TouchableOpacity style={styles.saveButton}>
                <Ionicons name="download-outline" size={20} color="#6B7280" />
                <Text style={styles.saveButtonText}>Save to Gallery</Text>
          </TouchableOpacity>
      </View>
          </View>
        </View>
      </Modal>

      {/* Hamburger Menu Modal */}
      <Modal
        visible={showHamburgerMenu}
        animationType="none"
        transparent={true}
        onRequestClose={() => setShowHamburgerMenu(false)}
      >
        <View style={styles.hamburgerOverlay}>
          <Animated.View style={[styles.hamburgerMenu, { transform: [{ translateX: slideAnim }] }]}>
            <View style={styles.hamburgerHeader}>
              <Text style={styles.hamburgerTitle}>Profile Menu</Text>
              <TouchableOpacity onPress={closeHamburgerMenu}>
                <Ionicons name="close" size={24} color="#6B7280" />
              </TouchableOpacity>
          </View>
          
            <ScrollView style={styles.hamburgerContent}>
              {tabs.map((tab) => (
                <TouchableOpacity
                  key={tab.id}
                  style={[
                    styles.hamburgerMenuItem,
                    activeTab === tab.id && styles.activeHamburgerMenuItem
                  ]}
                  onPress={() => {
                    if (tab.id === 'logout') {
                      closeHamburgerMenu();
                      handleLogout();
                    } else {
                      setActiveTab(tab.id);
                      closeHamburgerMenu();
                    }
                  }}
                >
                  <View style={[
                    styles.hamburgerMenuIcon,
                    { backgroundColor: activeTab === tab.id ? tab.color : '#9CA3AF' }
                  ]}>
                    <Ionicons 
                      name={tab.icon as any} 
                      size={20} 
                      color="#FFFFFF" 
                    />
              </View>
                  <Text style={[
                    styles.hamburgerMenuText,
                    activeTab === tab.id && styles.activeHamburgerMenuText
                  ]}>
                    {tab.label}
                  </Text>
                  {activeTab === tab.id && (
                    <View style={styles.activeIndicator}>
                      <View style={styles.activeDot} />
            </View>
                  )}
              </TouchableOpacity>
              ))}
            </ScrollView>
          </Animated.View>
            </View>
      </Modal>

      {/* Scanner Modal */}
      <Modal
        visible={showScanner}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowScanner(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Scan Barcode</Text>
              <TouchableOpacity onPress={() => setShowScanner(false)}>
                <Ionicons name="close" size={24} color="#6B7280" />
              </TouchableOpacity>
            </View>
            
            {permission?.granted ? (
              <View style={styles.cameraContainer}>
                <CameraView
                  style={styles.camera}
                  facing="back"
                  onBarcodeScanned={scanned ? undefined : handleBarCodeScanned}
                  barcodeScannerSettings={{
                    barcodeTypes: ['qr', 'ean13', 'ean8', 'code128', 'code39'],
                  }}
                />
                
                {/* Overlay positioned absolutely outside CameraView */}
                <View style={styles.scannerOverlay}>
                  <View style={styles.scannerFrame}>
                    <View style={[styles.corner, styles.topLeft]} />
                    <View style={[styles.corner, styles.topRight]} />
                    <View style={[styles.corner, styles.bottomLeft]} />
                    <View style={[styles.corner, styles.bottomRight]} />
                  </View>
                  <Text style={styles.scannerInstructions}>
                    Position the QR code or barcode within the frame
                  </Text>
                </View>
                
                <View style={styles.scannerActions}>
                  <TouchableOpacity 
                    style={styles.cancelScannerButton}
                    onPress={() => setShowScanner(false)}
                  >
                    <Ionicons name="close" size={20} color="#FFFFFF" />
                    <Text style={styles.cancelScannerText}>Cancel</Text>
                  </TouchableOpacity>
                </View>
              </View>
            ) : (
              <View style={styles.permissionContainer}>
                <Ionicons name="camera-outline" size={80} color="#F59E0B" />
                <Text style={styles.permissionTitle}>Camera Permission Required</Text>
                <Text style={styles.permissionDescription}>
                  We need camera access to scan QR codes and barcodes
                </Text>
                <TouchableOpacity 
                  style={styles.permissionButton}
                  onPress={requestPermission}
                >
                  <Ionicons name="camera" size={20} color="#FFFFFF" />
                  <Text style={styles.permissionButtonText}>Grant Permission</Text>
                </TouchableOpacity>
                <TouchableOpacity 
                  style={styles.cancelScannerButton}
                  onPress={() => setShowScanner(false)}
                >
                  <Text style={styles.cancelScannerText}>Cancel</Text>
                </TouchableOpacity>
              </View>
            )}
          </View>
        </View>
      </Modal>

      {/* Password Change Success Modal */}
      <Modal
        visible={showPasswordSuccessModal}
        animationType="fade"
        transparent={true}
        onRequestClose={() => {}}
      >
        <View style={styles.successModalOverlay}>
          <View style={styles.successModalContent}>
            {/* Success Animation Icon */}
            <View style={styles.successIconContainer}>
              <View style={styles.successIconCircle}>
                <Ionicons name="checkmark-circle" size={80} color="#10B981" />
              </View>
            </View>
            
            {/* Success Message */}
            <Text style={styles.successModalTitle}>Password Changed!</Text>
            <Text style={styles.successModalMessage}>
              Your password has been updated successfully.{'\n'}
              Please log in again with your new password.
            </Text>
            
            {/* Logout Button */}
            <TouchableOpacity
              style={styles.successModalButton}
              onPress={async () => {
                setShowPasswordSuccessModal(false);
                // Log out user
                await logoutMutation.mutateAsync();
                await clearToken();
                router.replace('/(auth)/login');
              }}
            >
              <Ionicons name="log-out-outline" size={20} color="#FFFFFF" style={{ marginRight: 8 }} />
              <Text style={styles.successModalButtonText}>Log In Again</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F3F4F6',
  },
  content: {
    flex: 1,
    padding: spacing.md,
  },
  headerContainer: {
    marginBottom: spacing.lg,
  },
  section: {
    marginBottom: spacing.lg,
  },
  
  // Header
  header: {
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
    paddingTop: spacing.lg,
    paddingBottom: spacing.md,
  },
  headerContent: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.lg,
  },
  headerTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: '#111827',
  },
  hamburgerButton: {
    padding: spacing.sm,
    borderRadius: radius.md,
  },
  
  // Tab Content
  tabContent: {
    flex: 1,
  },
  
  // Profile Section - Simplified
  profileSection: {
    marginBottom: spacing.lg,
    borderRadius: radius.xl,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 4,
  },
  profileGradient: {
    padding: spacing.lg,
    gap: spacing.lg,
  },
  
  // Top Row - Profile Picture Left, Achievement Right
  topRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.lg,
  },
  
  // Profile Picture Container
  profilePictureContainer: {
    alignItems: 'center',
  },
  
  // Achievement Section
  achievementSection: {
    alignItems: 'center',
    flex: 1,
  },
  
  // User Details Section
  userDetailsSection: {
    gap: spacing.sm,
  },
  profilePicture: {
    width: 128,
    height: 128,
    borderRadius: 64,
    borderWidth: 6,
    borderColor: '#FFFFFF',
    overflow: 'hidden',
    backgroundColor: '#FFFFFF',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.3,
    shadowRadius: 16,
    elevation: 8,
  },
  profileImage: {
    width: '100%',
    height: '100%',
  },
  profileImagePlaceholder: {
    width: '100%',
    height: '100%',
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#FEF3C7',
  },
  cameraButtonOverlay: {
    position: 'absolute',
    bottom: 0,
    right: 0,
    backgroundColor: 'transparent',
  },
  cameraButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#152039',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 3,
    borderColor: '#FFFFFF',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 5,
  },
  userDetails: {
    flex: 1,
    minWidth: 0,
  },
  userName: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: '#111827',
    marginBottom: spacing.xs,
  },
  userEmailContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
    marginBottom: spacing.xs,
  },
  userEmail: {
    fontSize: fontSizes.sm,
    color: '#6B7280',
    flex: 1,
  },
  memberSinceContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
  },
  memberSince: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
  },
  
  // Credit Balance
  creditBalanceSection: {
    marginTop: spacing.sm,
  },
  creditBalanceContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255, 255, 255, 0.8)',
    borderRadius: radius.lg,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    gap: spacing.sm,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  creditBalanceLabel: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: '#6B7280',
  },
  creditBalanceAmount: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: '#F59E0B',
  },
  
  // Achievement Section Styles
  achievementTitle: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: '#6B7280',
    marginBottom: spacing.sm,
    textAlign: 'center',
  },
  achievementContent: {
    flexDirection: 'column',
    alignItems: 'center',
    gap: spacing.sm,
  },
  badgeIconContainer: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#F59E0B',
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 4,
    borderWidth: 3,
    borderColor: '#FFFFFF',
  },
  noBadgeIconContainer: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#F3F4F6',
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 4,
    borderWidth: 3,
    borderColor: '#FFFFFF',
  },
  badgeInfo: {
    alignItems: 'center',
  },
  badgeName: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: '#111827',
    marginBottom: spacing.xs,
    textAlign: 'center',
  },
  badgeTierContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FEF3C7',
    borderRadius: radius.full,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.xs,
    gap: spacing.xs,
    borderWidth: 1,
    borderColor: '#F59E0B',
  },
  badgeTier: {
    fontSize: fontSizes.sm,
    color: '#92400E',
    textAlign: 'center',
    fontWeight: fontWeights.medium,
  },
  
  // Top-up Section
  topUpSection: {
    backgroundColor: 'rgba(255, 255, 255, 0.9)',
    borderRadius: radius.xl,
    padding: spacing.lg,
    marginBottom: spacing.lg,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 4,
  },
  topUpHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    marginBottom: spacing.lg,
  },
  topUpIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#10B981',
    justifyContent: 'center',
    alignItems: 'center',
  },
  topUpTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.semibold,
    color: '#111827',
  },
  topUpContent: {
    gap: spacing.lg,
  },
  
  // Top-up Tabs
  topUpTabs: {
    flexDirection: 'row',
    backgroundColor: '#F3F4F6',
    borderRadius: radius.lg,
    padding: spacing.xs,
  },
  topUpTab: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.md,
    borderRadius: radius.md,
    gap: spacing.sm,
  },
  activeTopUpTab: {
    backgroundColor: '#FFFFFF',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  topUpTabText: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: '#6B7280',
  },
  activeTopUpTabText: {
    color: '#10B981',
  },
  
  // Top-up Tab Content
  topUpTabContent: {
    gap: spacing.lg,
  },
  showQRButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#10B981',
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    borderRadius: radius.lg,
    gap: spacing.sm,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  showQRButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: '#FFFFFF',
  },
  
  // QR Code Info
  qrCodeInfo: {
    alignItems: 'center',
    backgroundColor: '#EFF6FF',
    borderRadius: radius.xl,
    padding: spacing.lg,
    borderWidth: 1,
    borderColor: '#DBEAFE',
    marginBottom: spacing.lg,
  },
  qrCodeIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#3B82F6',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  qrCodeInfoText: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: '#1E40AF',
    textAlign: 'center',
  },
  
  // QR Code Features
  qrCodeFeatures: {
    marginTop: spacing.lg,
    gap: spacing.sm,
  },
  featureItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  featureText: {
    fontSize: fontSizes.sm,
    color: '#374151',
  },
  
  // Scan Instructions
  scanInstructions: {
    alignItems: 'center',
    backgroundColor: '#EFF6FF',
    borderRadius: radius.xl,
    padding: spacing.lg,
    borderWidth: 1,
    borderColor: '#DBEAFE',
  },
  scanIconContainer: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#3B82F6',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  scanInstructionsText: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: '#1E40AF',
    textAlign: 'center',
  },
  
  // Scan Actions
  scanActions: {
    gap: spacing.md,
  },
  scanButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#3B82F6',
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    borderRadius: radius.lg,
    gap: spacing.sm,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  scanButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: '#FFFFFF',
  },
  manualEntryButton: {
    alignItems: 'center',
  },
  manualEntryText: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: '#3B82F6',
    textDecorationLine: 'underline',
  },
  
  // Manual Entry Form
  manualEntryForm: {
    backgroundColor: '#F9FAFB',
    borderRadius: radius.xl,
    padding: spacing.lg,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    gap: spacing.md,
  },
  manualEntryLabel: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: '#374151',
  },
  barcodeInput: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#D1D5DB',
    borderRadius: radius.lg,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.md,
    fontSize: fontSizes.md,
    color: '#111827',
  },
  barcodeHint: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
  },
  manualEntryActions: {
    flexDirection: 'row',
    gap: spacing.md,
  },
  processButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#10B981',
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    borderRadius: radius.lg,
    gap: spacing.sm,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  processButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: '#FFFFFF',
  },
  cancelButton: {
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    backgroundColor: '#6B7280',
    borderRadius: radius.lg,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  cancelButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: '#FFFFFF',
  },
  
  // Account Actions
  accountActions: {
    gap: spacing.sm,
  },
  accountAction: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.md,
    backgroundColor: '#FFFFFF',
    borderRadius: radius.md,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 2,
    elevation: 1,
  },
  accountActionText: {
    flex: 1,
    fontSize: fontSizes.md,
    color: '#374151',
    marginLeft: spacing.sm,
  },
  
  // Coming Soon
  comingSoonText: {
    fontSize: fontSizes.lg,
    color: '#6B7280',
    textAlign: 'center',
    marginTop: spacing.xl,
  },
  
  // Modal
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.75)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: spacing.lg,
  },
  modalContent: {
    backgroundColor: '#FFFFFF',
    borderRadius: radius.xl,
    maxWidth: 400,
    width: '100%',
    maxHeight: '90%',
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.25,
    shadowRadius: 20,
    elevation: 10,
  },
  modalHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  modalTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.semibold,
    color: '#111827',
  },
  qrCodeContainer: {
    padding: spacing.xl,
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: 200,
  },
  qrCodeWrapper: {
    padding: spacing.md,
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  qrCodeText: {
    fontSize: fontSizes.md,
    color: '#6B7280',
    textAlign: 'center',
  },
  
  // Enhanced QR Code Modal
  modalScrollContent: {
    flex: 1,
    padding: spacing.lg,
  },
  qrCodePlaceholder: {
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FEF3C7',
    borderRadius: radius.xl,
    padding: spacing.xl,
    marginBottom: spacing.lg,
    borderWidth: 2,
    borderColor: '#F59E0B',
    borderStyle: 'dashed',
  },
  qrCodePlaceholderText: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: '#92400E',
    marginTop: spacing.sm,
  },
  qrCodeDescription: {
    fontSize: fontSizes.sm,
    color: '#6B7280',
    textAlign: 'center',
    marginBottom: spacing.lg,
  },
  
  // QR Code Info Section
  userInfoSection: {
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    padding: spacing.md,
    marginTop: spacing.md,
  },
  infoItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: spacing.md,
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
    gap: spacing.md,
  },
  infoContent: {
    flex: 1,
  },
  infoLabel: {
    fontSize: fontSizes.sm,
    color: '#6B7280',
    marginBottom: spacing.xs,
  },
  infoValue: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: '#111827',
  },
  
  // QR Code Actions
  qrCodeActions: {
    marginTop: spacing.lg,
    gap: spacing.md,
  },
  shareButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#10B981',
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    borderRadius: radius.lg,
    gap: spacing.sm,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  shareButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: '#FFFFFF',
  },
  saveButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#F3F4F6',
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    borderRadius: radius.lg,
    gap: spacing.sm,
    borderWidth: 1,
    borderColor: '#D1D5DB',
  },
  saveButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: '#6B7280',
  },
  
  // Hamburger Menu
  hamburgerOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-start',
    alignItems: 'flex-end',
  },
  hamburgerMenu: {
    width: screenWidth * 0.8,
    height: '100%',
    backgroundColor: '#FFFFFF',
    shadowColor: '#000',
    shadowOffset: { width: -2, height: 0 },
    shadowOpacity: 0.25,
    shadowRadius: 10,
    elevation: 10,
  },
  hamburgerHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.lg,
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
    paddingTop: spacing.xl,
  },
  hamburgerTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: '#111827',
  },
  hamburgerContent: {
    flex: 1,
    paddingTop: spacing.lg,
  },
  hamburgerMenuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    marginHorizontal: spacing.md,
    marginBottom: spacing.xs,
    borderRadius: radius.md,
  },
  activeHamburgerMenuItem: {
    backgroundColor: '#EFF6FF',
  },
  hamburgerMenuIcon: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.md,
  },
  hamburgerMenuText: {
    flex: 1,
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
    color: '#374151',
  },
  activeHamburgerMenuText: {
    color: '#3B82F6',
    fontWeight: fontWeights.semibold,
  },
  activeIndicator: {
    width: 24,
    alignItems: 'center',
  },
  activeDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: '#3B82F6',
  },
  
  // Scanner Modal Styles
  scannerContainer: {
    padding: spacing.lg,
    alignItems: 'center',
  },
  scannerPlaceholder: {
    alignItems: 'center',
    padding: spacing.xl,
    backgroundColor: '#F9FAFB',
    borderRadius: radius.lg,
    marginBottom: spacing.lg,
    gap: spacing.sm,
  },
  scannerPlaceholderText: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: '#374151',
  },
  scannerDescription: {
    fontSize: fontSizes.sm,
    color: '#6B7280',
    textAlign: 'center',
    lineHeight: 20,
  },
  scannerActions: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    padding: spacing.lg,
    gap: spacing.md,
    backgroundColor: 'rgba(0, 0, 0, 0.7)',
  },
  scannerButton: {
    backgroundColor: '#10B981',
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    borderRadius: radius.lg,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.sm,
  },
  scannerButtonText: {
    color: '#FFFFFF',
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
  },
  cancelScannerButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    borderRadius: radius.lg,
    backgroundColor: 'rgba(255, 255, 255, 0.9)',
    gap: spacing.sm,
  },
  cancelScannerText: {
    color: '#374151',
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
  },
  
  // Camera Scanner Styles
  cameraContainer: {
    width: '100%',
    height: 500,
    position: 'relative',
    backgroundColor: '#000',
    borderRadius: radius.lg,
    overflow: 'hidden',
  },
  camera: {
    width: '100%',
    height: '100%',
  },
  scannerOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 80, // Space for cancel button
    justifyContent: 'center',
    alignItems: 'center',
    pointerEvents: 'none', // Allow camera to receive touch events
  },
  scannerFrame: {
    width: 250,
    height: 250,
    position: 'relative',
  },
  corner: {
    position: 'absolute',
    width: 30,
    height: 30,
    borderColor: '#F59E0B',
    borderWidth: 3,
  },
  topLeft: {
    top: 0,
    left: 0,
    borderRightWidth: 0,
    borderBottomWidth: 0,
  },
  topRight: {
    top: 0,
    right: 0,
    borderLeftWidth: 0,
    borderBottomWidth: 0,
  },
  bottomLeft: {
    bottom: 0,
    left: 0,
    borderRightWidth: 0,
    borderTopWidth: 0,
  },
  bottomRight: {
    bottom: 0,
    right: 0,
    borderLeftWidth: 0,
    borderTopWidth: 0,
  },
  scannerInstructions: {
    position: 'absolute',
    bottom: 100,
    color: '#FFFFFF',
    fontSize: fontSizes.md,
    textAlign: 'center',
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.md,
  },
  permissionContainer: {
    padding: spacing.lg,
    alignItems: 'center',
    gap: spacing.md,
  },
  permissionTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: '#374151',
    textAlign: 'center',
  },
  permissionDescription: {
    fontSize: fontSizes.sm,
    color: '#6B7280',
    textAlign: 'center',
    lineHeight: 20,
  },
  permissionButton: {
    backgroundColor: '#10B981',
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    borderRadius: radius.lg,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.sm,
    marginTop: spacing.sm,
  },
  permissionButtonText: {
    color: '#FFFFFF',
    fontSize: fontSizes.md,
    fontWeight: fontWeights.medium,
  },
  
  // Badge Tab Styles
  badgeHeroSection: {
    backgroundColor: '#FFF9F0',
    borderRadius: 24,
    padding: spacing.lg,
    marginBottom: spacing.lg,
    alignItems: 'center',
    position: 'relative',
    overflow: 'hidden',
  },
  badgeHeroContent: {
    alignItems: 'center',
    zIndex: 2,
  },
  badgeHeroIcon: {
    fontSize: 48,
    marginBottom: spacing.md,
  },
  badgeHeroTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: '#7A1C1E',
    textAlign: 'center',
    marginBottom: spacing.sm,
  },
  badgeHeroSubtitle: {
    fontSize: fontSizes.md,
    color: '#A43E2D',
    textAlign: 'center',
    opacity: 0.8,
  },
  badgeFloatingElements: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    zIndex: 1,
  },
  badgeFloatingIcon1: {
    position: 'absolute',
    top: 16,
    left: 16,
    fontSize: 24,
    opacity: 0.6,
  },
  badgeFloatingIcon2: {
    position: 'absolute',
    top: 32,
    right: 32,
    fontSize: 20,
    opacity: 0.6,
  },
  badgeFloatingIcon3: {
    position: 'absolute',
    bottom: 24,
    left: 48,
    fontSize: 18,
    opacity: 0.6,
  },
  
  badgeStatsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.xs,
    marginBottom: spacing.md,
    justifyContent: 'space-between',
  },
  badgeStatCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 6,
    padding: spacing.sm,
    alignItems: 'center',
    width: '48%',
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  badgeStatIcon: {
    fontSize: 12,
    marginBottom: spacing.xs,
  },
  badgeStatNumber: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.bold,
    color: '#7A1C1E',
    marginBottom: 2,
  },
  badgeStatLabel: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
    textAlign: 'center',
  },
  
  badgeProgressCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 6,
    padding: spacing.sm,
    marginBottom: spacing.md,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  badgeProgressHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.xs,
  },
  badgeProgressTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#7A1C1E',
  },
  badgeProgressCount: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
  },
  badgeProgressBar: {
    width: '100%',
    height: 6,
    backgroundColor: '#E5E7EB',
    borderRadius: 3,
  },
  badgeProgressFill: {
    height: '100%',
    backgroundColor: '#7A1C1E',
    borderRadius: 3,
  },
  
  badgeCollectionSection: {
    marginBottom: spacing.lg,
  },
  badgeCollectionTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
    color: '#7A1C1E',
    marginBottom: spacing.md,
  },
  badgeGrid: {
    gap: spacing.md,
  },
  badgeCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: spacing.md,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    position: 'relative',
  },
  badgeCardLocked: {
    opacity: 0.6,
  },
  badgeCardContent: {
    alignItems: 'center',
    marginBottom: spacing.md,
    position: 'relative',
  },
  badgeIconContainerEngagement: {
    backgroundColor: '#059669',
    borderColor: '#059669',
  },
  badgeIconContainerGold: {
    backgroundColor: '#7C2D12',
    borderColor: '#7C2D12',
  },
  badgeIcon: {
    fontSize: 24,
  },
  badgeStatus: {
    position: 'absolute',
    top: 0,
    right: 0,
  },
  badgeStatusUnlocked: {
    width: 20,
    height: 20,
    backgroundColor: '#10B981',
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 4,
    elevation: 3,
  },
  badgeStatusLocked: {
    width: 20,
    height: 20,
    backgroundColor: '#6B7280',
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 4,
    elevation: 3,
  },
  badgeStatusText: {
    color: '#FFFFFF',
    fontSize: 10,
    fontWeight: fontWeights.bold,
  },
  
  badgeRankContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    gap: spacing.sm,
    marginBottom: spacing.md,
  },
  badgeRank: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    color: '#374151',
    backgroundColor: '#F3F4F6',
    paddingHorizontal: spacing.sm,
    paddingVertical: 4,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D1D5DB',
  },
  
  badgeProgressContainer: {
    marginBottom: spacing.md,
  },
  badgeProgressRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 4,
  },
  badgeProgressLabel: {
    fontSize: fontSizes.sm,
    color: '#6B7280',
  },
  badgeProgressPoints: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    color: '#7A1C1E',
  },
  badgeProgressBarSmall: {
    width: '100%',
    height: 6,
    backgroundColor: '#E5E7EB',
    borderRadius: 3,
    marginBottom: 4,
  },
  badgeProgressFillSmall: {
    height: '100%',
    backgroundColor: '#7A1C1E',
    borderRadius: 4,
  },
  badgeProgressNext: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
    textAlign: 'center',
  },
  
  badgeLockedText: {
    fontSize: fontSizes.sm,
    color: '#6B7280',
    textAlign: 'center',
    fontStyle: 'italic',
    marginBottom: spacing.md,
  },
  
  badgeActionButton: {
    backgroundColor: '#A43E2D',
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.sm,
    borderRadius: 20,
    alignItems: 'center',
  },
  badgeActionButtonLocked: {
    backgroundColor: '#9CA3AF',
  },
  badgeActionButtonText: {
    color: '#FFFFFF',
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
  },
  
  badgeHistorySection: {
    marginBottom: spacing.md,
  },
  badgeHistoryTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#7A1C1E',
    marginBottom: spacing.sm,
  },
  badgeHistoryList: {
    gap: spacing.sm,
  },
  badgeHistoryItem: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: spacing.sm,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    flexDirection: 'row',
    alignItems: 'center',
  },
  badgeHistoryIcon: {
    width: 36,
    height: 36,
    backgroundColor: '#F59E0B',
    borderRadius: 18,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: spacing.sm,
  },
  badgeHistoryIconEngagement: {
    backgroundColor: '#059669',
  },
  badgeHistoryIconText: {
    fontSize: 15,
  },
  badgeHistoryContent: {
    flex: 1,
  },
  badgeHistoryName: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: '#7A1C1E',
    marginBottom: 2,
  },
  badgeHistoryRank: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
    marginBottom: 2,
  },
  badgeHistoryDesc: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
  },
  badgeHistoryDate: {
    alignItems: 'flex-end',
  },
  badgeHistoryDateText: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
    marginBottom: 2,
  },
  badgeHistoryStatus: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.bold,
    color: '#10B981',
  },
  
  // Order History Tab Styles
  orderHeaderSection: {
    backgroundColor: '#F8FAFC',
    borderRadius: 6,
    padding: spacing.sm,
    marginBottom: spacing.sm,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  orderHeaderContent: {
    marginBottom: spacing.xs,
  },
  orderHeaderTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: '#1F2937',
    marginBottom: spacing.xs,
  },
  orderHeaderSubtitle: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
  },
  orderControlsContainer: {
    gap: spacing.xs,
  },
  orderSearchContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    borderRadius: 4,
    borderWidth: 1,
    borderColor: '#D1D5DB',
    paddingHorizontal: spacing.xs,
    paddingVertical: spacing.xs,
  },
  orderSearchIcon: {
    marginRight: spacing.xs,
  },
  orderSearchInput: {
    flex: 1,
    fontSize: fontSizes.xs,
    color: '#374151',
  },
  orderFilterContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    borderRadius: 4,
    borderWidth: 1,
    borderColor: '#D1D5DB',
    paddingHorizontal: spacing.xs,
    paddingVertical: spacing.xs,
    justifyContent: 'space-between',
  },
  orderFilterText: {
    fontSize: fontSizes.xs,
    color: '#374151',
  },
  
  ordersList: {
    gap: spacing.sm,
    marginBottom: spacing.md,
  },
  orderCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 8,
    padding: spacing.md,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    borderWidth: 1,
    borderColor: '#E5E7EB',
  },
  orderCardHeader: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: spacing.sm,
  },
  orderCardIconContainer: {
    width: 32,
    height: 32,
    backgroundColor: '#3B82F6',
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: spacing.sm,
  },
  orderCardInfo: {
    flex: 1,
  },
  orderCardTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#1F2937',
    marginBottom: spacing.xs,
  },
  orderCardDate: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  orderCardDateText: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
    marginLeft: spacing.xs,
  },
  orderCardStatusContainer: {
    alignItems: 'flex-end',
  },
  orderCardStatusCompleted: {
    backgroundColor: '#D1FAE5',
    borderWidth: 1,
    borderColor: '#A7F3D0',
    paddingHorizontal: spacing.xs,
    paddingVertical: 3,
    borderRadius: 8,
    marginBottom: spacing.xs,
  },
  orderCardStatusProcessing: {
    backgroundColor: '#DBEAFE',
    borderWidth: 1,
    borderColor: '#93C5FD',
    paddingHorizontal: spacing.xs,
    paddingVertical: 3,
    borderRadius: 8,
    marginBottom: spacing.xs,
  },
  orderCardStatusCancelled: {
    backgroundColor: '#FEE2E2',
    borderWidth: 1,
    borderColor: '#FCA5A5',
    paddingHorizontal: spacing.xs,
    paddingVertical: 3,
    borderRadius: 8,
    marginBottom: spacing.xs,
  },
  orderCardStatusText: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.semibold,
    color: '#065F46',
  },
  orderCardTotal: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: '#1F2937',
  },
  
  orderItemsSection: {
    borderTopWidth: 1,
    borderTopColor: '#E5E7EB',
    paddingTop: spacing.sm,
    marginBottom: spacing.sm,
  },
  orderItemsHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.xs,
  },
  orderItemsTitle: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.semibold,
    color: '#374151',
    marginLeft: spacing.xs,
  },
  orderItemsList: {
    gap: spacing.xs,
  },
  orderItem: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F9FAFB',
    padding: spacing.xs,
    borderRadius: 6,
  },
  orderItemQuantity: {
    width: 24,
    height: 24,
    backgroundColor: '#F59E0B',
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: spacing.xs,
  },
  orderItemQuantityText: {
    color: '#FFFFFF',
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.bold,
  },
  orderItemName: {
    flex: 1,
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: '#374151',
  },
  orderItemPrice: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    color: '#1F2937',
  },
  
  orderCardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: '#E5E7EB',
    paddingTop: spacing.sm,
  },
  orderCardFooterLeft: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  orderCardType: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
    marginLeft: spacing.xs,
  },
  orderViewDetailsButton: {
    backgroundColor: '#3B82F6',
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: 6,
    gap: spacing.xs,
  },
  orderViewDetailsText: {
    color: '#FFFFFF',
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.medium,
  },
  
  orderLoadMoreContainer: {
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  orderLoadMoreButton: {
    backgroundColor: '#F3F4F6',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: '#D1D5DB',
  },
  orderLoadMoreText: {
    color: '#374151',
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
  },
  
  // Address Book Tab Styles
  addressHeaderSection: {
    backgroundColor: '#F0FDF4',
    borderRadius: 8,
    padding: spacing.md,
    marginBottom: spacing.md,
    borderWidth: 1,
    borderColor: '#BBF7D0',
  },
  addressHeaderContent: {
    marginBottom: spacing.sm,
  },
  addressHeaderTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: '#1F2937',
    marginBottom: spacing.xs,
  },
  addressHeaderSubtitle: {
    fontSize: fontSizes.sm,
    color: '#6B7280',
  },
  addressAddButton: {
    backgroundColor: '#10B981',
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: 6,
    gap: spacing.xs,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  addressAddButtonText: {
    color: '#FFFFFF',
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
  },
  
  addressList: {
    gap: spacing.sm,
    marginBottom: spacing.md,
  },
  addressCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 8,
    padding: spacing.md,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    position: 'relative',
  },
  addressDefaultBadge: {
    position: 'absolute',
    top: -6,
    right: -6,
    backgroundColor: '#10B981',
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: spacing.xs,
    paddingVertical: 3,
    borderRadius: 8,
    gap: 3,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 4,
    elevation: 3,
  },
  addressDefaultText: {
    color: '#FFFFFF',
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.medium,
  },
  
  addressCardContent: {
    flexDirection: 'row',
    alignItems: 'flex-start',
  },
  addressCardIcon: {
    width: 32,
    height: 32,
    backgroundColor: '#10B981',
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: spacing.sm,
  },
  addressCardIconOffice: {
    backgroundColor: '#3B82F6',
  },
  addressCardInfo: {
    flex: 1,
  },
  addressCardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.xs,
  },
  addressCardName: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#1F2937',
  },
  addressCardType: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
  },
  addressCardDetails: {
    gap: spacing.xs,
  },
  addressCardDetail: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  addressCardDetailText: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
    marginLeft: spacing.xs,
  },
  
  addressCardActions: {
    gap: spacing.xs,
    marginLeft: spacing.sm,
  },
  addressEditButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#EBF8FF',
    paddingHorizontal: spacing.xs,
    paddingVertical: spacing.xs,
    borderRadius: 4,
    gap: 3,
  },
  addressEditButtonText: {
    color: '#3B82F6',
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.medium,
  },
  addressSetDefaultButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F0FDF4',
    paddingHorizontal: spacing.xs,
    paddingVertical: spacing.xs,
    borderRadius: 4,
    gap: 3,
  },
  addressSetDefaultButtonText: {
    color: '#10B981',
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.medium,
  },
  addressDeleteButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FEF2F2',
    paddingHorizontal: spacing.xs,
    paddingVertical: spacing.xs,
    borderRadius: 4,
    gap: 3,
  },
  addressDeleteButtonText: {
    color: '#EF4444',
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.medium,
  },
  
  addressAddContainer: {
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  addressAddNewButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: 8,
    borderWidth: 2,
    borderColor: '#10B981',
    borderStyle: 'dashed',
    gap: spacing.xs,
  },
  addressAddNewButtonText: {
    color: '#10B981',
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
  },
  
  // Security Tab Styles
  securityHeaderSection: {
    backgroundColor: '#FEF2F2',
    borderRadius: 8,
    padding: spacing.md,
    marginBottom: spacing.md,
    borderWidth: 1,
    borderColor: '#FECACA',
    flexDirection: 'row',
    alignItems: 'center',
  },
  securityHeaderIcon: {
    width: 32,
    height: 32,
    backgroundColor: '#EF4444',
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: spacing.sm,
  },
  securityHeaderContent: {
    flex: 1,
  },
  securityHeaderTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: '#1F2937',
    marginBottom: spacing.xs,
  },
  securityHeaderSubtitle: {
    fontSize: fontSizes.sm,
    color: '#6B7280',
  },
  
  securityFormCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 8,
    padding: spacing.md,
    marginBottom: spacing.md,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    borderWidth: 1,
    borderColor: '#E5E7EB',
  },
  securityFormHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  securityFormIcon: {
    width: 28,
    height: 28,
    backgroundColor: '#3B82F6',
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: spacing.sm,
  },
  securityFormTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#1F2937',
  },
  securityForm: {
    gap: spacing.md,
  },
  securityInputContainer: {
    gap: spacing.xs,
  },
  securityInputLabel: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.medium,
    color: '#374151',
  },
  securityInputWrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F9FAFB',
    borderRadius: 6,
    borderWidth: 1,
    borderColor: '#D1D5DB',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
  },
  securityInputIcon: {
    marginRight: spacing.xs,
  },
  securityInput: {
    flex: 1,
    fontSize: fontSizes.sm,
    color: '#374151',
  },
  passwordToggle: {
    padding: spacing.xs,
    marginLeft: spacing.xs,
  },
  securityUpdateButton: {
    backgroundColor: '#EF4444',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: 6,
    gap: spacing.xs,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  securityUpdateButtonText: {
    color: '#FFFFFF',
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
  },
  
  securityInfoCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 8,
    padding: spacing.md,
    marginBottom: spacing.md,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    borderWidth: 1,
    borderColor: '#E5E7EB',
  },
  securityInfoHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  securityInfoIcon: {
    width: 28,
    height: 28,
    backgroundColor: '#10B981',
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: spacing.sm,
  },
  securityInfoTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#1F2937',
  },
  securityInfoGrid: {
    gap: spacing.sm,
  },
  securityInfoItem: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F8FAFC',
    padding: spacing.sm,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  securityInfoItemIcon: {
    width: 24,
    height: 24,
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: spacing.xs,
  },
  securityInfoItemContent: {
    flex: 1,
  },
  securityInfoItemLabel: {
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.medium,
    color: '#6B7280',
  },
  securityInfoItemValue: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#1F2937',
  },
  
  // Referrals Tab Styles
  referralHeaderSection: {
    backgroundColor: '#FAF5FF',
    borderRadius: 8,
    padding: spacing.md,
    marginBottom: spacing.md,
    borderWidth: 1,
    borderColor: '#DDD6FE',
    flexDirection: 'row',
    alignItems: 'center',
  },
  referralHeaderIcon: {
    width: 32,
    height: 32,
    backgroundColor: '#EC4899',
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: spacing.sm,
  },
  referralHeaderContent: {
    flex: 1,
  },
  referralHeaderTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: '#1F2937',
    marginBottom: spacing.xs,
  },
  referralHeaderSubtitle: {
    fontSize: fontSizes.sm,
    color: '#6B7280',
  },
  
  referralCodeCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 8,
    padding: spacing.md,
    marginBottom: spacing.md,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    borderWidth: 1,
    borderColor: '#E5E7EB',
  },
  referralCodeHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  referralCodeIcon: {
    width: 28,
    height: 28,
    backgroundColor: '#EC4899',
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: spacing.sm,
  },
  referralCodeTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#1F2937',
  },
  referralCodeContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F9FAFB',
    borderRadius: 6,
    borderWidth: 1,
    borderColor: '#D1D5DB',
    marginBottom: spacing.xs,
  },
  referralCodeInput: {
    flex: 1,
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: '#1F2937',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.sm,
    fontFamily: 'monospace',
  },
  referralCodeCopyButton: {
    backgroundColor: '#EC4899',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderTopRightRadius: 6,
    borderBottomRightRadius: 6,
  },
  referralCodeCopyText: {
    color: '#FFFFFF',
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
  },
  referralCodeInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
  },
  referralCodeInfoText: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
    flex: 1,
  },
  
  referralStatsGrid: {
    flexDirection: 'row',
    gap: spacing.xs,
    marginBottom: spacing.md,
  },
  referralStatCard: {
    flex: 1,
    backgroundColor: '#FFFFFF',
    borderRadius: 8,
    padding: spacing.sm,
    alignItems: 'center',
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    borderWidth: 1,
    borderColor: '#E5E7EB',
  },
  referralStatIcon: {
    width: 32,
    height: 32,
    backgroundColor: '#3B82F6',
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.xs,
  },
  referralStatNumber: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.bold,
    color: '#1F2937',
    marginBottom: 3,
  },
  referralStatLabel: {
    fontSize: fontSizes.xs,
    color: '#6B7280',
    textAlign: 'center',
  },
  
  referralShareCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 8,
    padding: spacing.md,
    marginBottom: spacing.md,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    borderWidth: 1,
    borderColor: '#E5E7EB',
  },
  referralShareHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  referralShareIcon: {
    width: 28,
    height: 28,
    backgroundColor: '#10B981',
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: spacing.sm,
  },
  referralShareTitle: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: '#1F2937',
  },
  referralShareButtons: {
    flexDirection: 'row',
    gap: spacing.xs,
  },
  referralShareButton: {
    flex: 1,
    backgroundColor: '#3B82F6',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing.sm,
    borderRadius: 6,
    gap: spacing.xs,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  referralShareButtonText: {
    color: '#FFFFFF',
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.medium,
  },
  // Empty State Styles
  emptyStateContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing.xl * 2,
    paddingHorizontal: spacing.lg,
  },
  emptyStateIcon: {
    fontSize: 64,
    marginBottom: spacing.md,
  },
  emptyStateTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.gray[800],
    marginBottom: spacing.xs,
    textAlign: 'center',
  },
  emptyStateText: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
    textAlign: 'center',
    marginBottom: spacing.lg,
  },
  emptyStateButton: {
    backgroundColor: colors.primary[600],
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    borderRadius: radius.md,
  },
  emptyStateButtonText: {
    color: colors.white,
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
  },
  loadingText: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    textAlign: 'center',
    paddingVertical: spacing.xl,
  },
  
  // Password Success Modal Styles
  successModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: spacing.xl,
  },
  successModalContent: {
    backgroundColor: '#FFFFFF',
    borderRadius: 24,
    padding: spacing.xl * 1.5,
    width: '90%',
    maxWidth: 400,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.3,
    shadowRadius: 20,
    elevation: 15,
  },
  successIconContainer: {
    marginBottom: spacing.lg,
  },
  successIconCircle: {
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: '#D1FAE5',
    justifyContent: 'center',
    alignItems: 'center',
  },
  successModalTitle: {
    fontSize: fontSizes.xxl,
    fontWeight: fontWeights.bold as any,
    color: '#111827',
    marginBottom: spacing.md,
    textAlign: 'center',
  },
  successModalMessage: {
    fontSize: fontSizes.md,
    color: '#6B7280',
    textAlign: 'center',
    lineHeight: 24,
    marginBottom: spacing.xl,
    paddingHorizontal: spacing.md,
  },
  successModalButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#A43E2D',
    paddingVertical: spacing.md + 4,
    paddingHorizontal: spacing.xl * 1.5,
    borderRadius: radius.lg,
    width: '100%',
    shadowColor: '#A43E2D',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 4,
  },
  successModalButtonText: {
    color: '#FFFFFF',
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold as any,
  },
});