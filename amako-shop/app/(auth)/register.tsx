import React, { useState, useRef, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
  KeyboardAvoidingView,
  Platform,
  TextInput as RNTextInput,
  Image,
} from 'react-native';
import { router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { Video, ResizeMode } from 'expo-av';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withSpring,
  withTiming,
  withSequence,
  withRepeat,
  interpolate,
  runOnJS,
  Easing,
} from 'react-native-reanimated';
import { GestureDetector, Gesture } from 'react-native-gesture-handler';
import { useRegister } from '../../src/api/auth-hooks';
import { Button, Card, spacing, fontSizes, fontWeights, colors, radius } from '../../src/ui';

type AnimationState = 'hello' | 'eye-closing' | 'katana-spinning' | 'typing';

export default function RegisterScreen() {
  const [name, setName] = useState('');
  const [emailOrPhone, setEmailOrPhone] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [isPasswordFocused, setIsPasswordFocused] = useState(false);
  const [isEmailFocused, setIsEmailFocused] = useState(false);
  const [animationState, setAnimationState] = useState<AnimationState>('hello');
  const [hasVideoError, setHasVideoError] = useState(false);
  const [isImageLoading, setIsImageLoading] = useState(true);
  
  const videoRef = useRef<Video>(null);
  const scrollViewRef = useRef<ScrollView>(null);
  const registerMutation = useRegister();

  // Animation values
  const characterScale = useSharedValue(1);
  const characterRotation = useSharedValue(0);
  const characterTranslateY = useSharedValue(0);
  const screenOpacity = useSharedValue(0);
  const formTranslateY = useSharedValue(50);

  // Welcome bounce animation on screen load
  useEffect(() => {
    // Screen entrance animation
    screenOpacity.value = withTiming(1, { duration: 800 });
    formTranslateY.value = withSpring(0, { damping: 15, stiffness: 150 });

    // Character welcome bounce
    characterScale.value = withSequence(
      withTiming(1.1, { duration: 300, easing: Easing.out(Easing.back(1.5)) }),
      withTiming(1, { duration: 200 })
    );

    // Character wave (rotation)
    characterRotation.value = withSequence(
      withTiming(-10, { duration: 400 }),
      withTiming(10, { duration: 400 }),
      withTiming(0, { duration: 400 })
    );

    // Character floating bounce
    characterTranslateY.value = withRepeat(
      withSequence(
        withTiming(-8, { duration: 1500, easing: Easing.inOut(Easing.sin) }),
        withTiming(0, { duration: 1500, easing: Easing.inOut(Easing.sin) })
      ),
      -1,
      false
    );
  }, []);

  // Update animation based on state
  useEffect(() => {
    // Reset video error when animation state changes
    setHasVideoError(false);
    
    if (isLoading) {
      setAnimationState('katana-spinning');
      // Character spinning animation
      characterRotation.value = withRepeat(
        withTiming(360, { duration: 1000, easing: Easing.linear }),
        -1,
        false
      );
    } else if (isPasswordFocused) {
      setAnimationState('eye-closing'); // This will show close.gif
      // Character tilts and covers eyes
      characterRotation.value = withSpring(-15);
      characterScale.value = withSpring(0.95);
    } else {
      // Default state - always show welcome.gif when not password focused
      setAnimationState('hello'); // This will show welcome.gif
      // Character returns to normal
      characterRotation.value = withSpring(0);
      characterScale.value = withSpring(1);
    }
  }, [isLoading, isPasswordFocused]);

  // Animated styles
  const characterAnimatedStyle = useAnimatedStyle(() => {
    return {
      transform: [
        { scale: characterScale.value },
        { rotate: `${characterRotation.value}deg` },
        { translateY: characterTranslateY.value },
      ],
      opacity: screenOpacity.value,
    };
  });

  const formAnimatedStyle = useAnimatedStyle(() => {
    return {
      transform: [{ translateY: formTranslateY.value }],
      opacity: screenOpacity.value,
    };
  });

  const handleRegister = async () => {
    if (!name.trim() || !emailOrPhone.trim() || !password.trim() || !confirmPassword.trim()) {
      Alert.alert('Error', 'Please fill in all fields');
      return;
    }

    if (password !== confirmPassword) {
      Alert.alert('Error', 'Passwords do not match');
      return;
    }

    if (password.length < 8) {
      Alert.alert('Error', 'Password must be at least 8 characters');
      return;
    }

    setIsLoading(true);
    try {
      await registerMutation.mutateAsync({
        name: name.trim(),
        emailOrPhone: emailOrPhone.trim(),
        password: password.trim(),
        password_confirmation: confirmPassword.trim(),
      });
    } catch (error: any) {
      Alert.alert('Registration Failed', error.message || 'Please check your information and try again.');
    } finally {
      setIsLoading(false);
    }
  };

  const navigateToLogin = () => {
    router.push('/(auth)/login');
  };

  const togglePasswordVisibility = () => {
    setShowPassword(!showPassword);
  };

  const toggleConfirmPasswordVisibility = () => {
    setShowConfirmPassword(!showConfirmPassword);
  };

  const handlePasswordFocus = () => {
    setIsPasswordFocused(true);
    // Scroll up slightly to show the password field without cutting off the character
    setTimeout(() => {
      scrollViewRef.current?.scrollTo({ y: 50, animated: true });
    }, 100);
  };

  const handlePasswordBlur = () => {
    setIsPasswordFocused(false);
    // Scroll back to original position
    setTimeout(() => {
      scrollViewRef.current?.scrollTo({ y: 0, animated: true });
    }, 100);
  };

  return (
    <Animated.View style={[styles.container, { opacity: screenOpacity }]}>
      <KeyboardAvoidingView
        style={styles.keyboardAvoidingView}
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 0 : 20}
      >
        <ScrollView
          ref={scrollViewRef}
          contentContainerStyle={styles.scrollContent}
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}
          bounces={false}
          scrollEventThrottle={16}
          automaticallyAdjustKeyboardInsets={false}
        >
        {/* Welcome Header */}
        <View style={styles.welcomeHeader}>
          <Text style={styles.welcomeTitle}>Welcome to</Text>
          <Text style={styles.welcomeSubtitle}>Amako Momo</Text>
        </View>

        {/* Character above the form */}
        <View style={styles.characterHugContainer}>
          <Animated.View style={[styles.characterContainer, characterAnimatedStyle]}>
            {!hasVideoError ? (
              // Render all images and show/hide based on state for instant switching
              <View style={styles.characterImageContainer}>
                {/* Welcome GIF - shown by default */}
                <Image
                  source={require('../../assets/animations/welcome.gif')}
                  style={[
                    styles.characterImage,
                    { opacity: animationState === 'hello' ? 1 : 0 }
                  ]}
                  resizeMode="contain"
                  onLoad={() => {
                    console.log('🎬 Welcome GIF loaded');
                    setIsImageLoading(false);
                  }}
                  onError={(error) => {
                    console.log('🎬 Welcome GIF error:', error);
                    setHasVideoError(true);
                  }}
                />
                
                {/* Close GIF - shown when password focused */}
                <Image
                  source={require('../../assets/animations/close.gif')}
                  style={[
                    styles.characterImage,
                    { 
                      position: 'absolute',
                      opacity: animationState === 'eye-closing' ? 1 : 0 
                    }
                  ]}
                  resizeMode="contain"
                  onLoad={() => {
                    console.log('🎬 Close GIF loaded');
                  }}
                  onError={(error) => {
                    console.log('🎬 Close GIF error:', error);
                  }}
                />

                {/* Loading Video - shown when loading */}
                {animationState === 'katana-spinning' && (
                  <Video
                    ref={videoRef}
                    source={require('../../assets/animations/loading.mp4')}
                    style={styles.characterVideo}
                    resizeMode={ResizeMode.CONTAIN}
                    shouldPlay
                    isLooping
                    isMuted
                    useNativeControls={false}
                    onError={(error) => {
                      console.log('🎬 Video error:', error);
                      setHasVideoError(true);
                    }}
                  />
                )}
              </View>
            ) : (
              // Fallback to emoji if animation not available or loading
              <View style={styles.fallbackCharacter}>
                <Text style={styles.fallbackEmoji}>
                  {isLoading ? '⚔️' : isPasswordFocused ? '👁️' : '👋'}
                </Text>
                {isImageLoading && (
                  <Text style={styles.loadingText}>Loading...</Text>
                )}
              </View>
            )}
          </Animated.View>
        </View>

        {/* Form overlaps character with negative margin - moved outside characterHugContainer */}
        <Animated.View style={formAnimatedStyle}>

        <View style={styles.formCard}>
          <Text style={styles.formTitle}>Sign Up</Text>

          <View style={styles.form}>
            {/* Name Input */}
            <View style={styles.inputContainer}>
              <Text style={styles.label}>Full Name</Text>
              <View style={styles.inputWrapper}>
                <Ionicons name="person-outline" size={20} color={colors.gray[400]} style={styles.inputIcon} />
                <RNTextInput
                  style={styles.textInput}
                  placeholder="Enter your full name"
                  value={name}
                  onChangeText={setName}
                  autoCapitalize="words"
                  autoCorrect={false}
                  returnKeyType="next"
                  placeholderTextColor={colors.gray[400]}
                />
              </View>
            </View>

            {/* Email/Phone Input */}
            <View style={styles.inputContainer}>
              <Text style={styles.label}>Email or Phone</Text>
              <View style={styles.inputWrapper}>
                <Ionicons name="mail-outline" size={20} color={colors.gray[400]} style={styles.inputIcon} />
                <RNTextInput
                  style={styles.textInput}
                  placeholder="Enter your email or phone"
                  value={emailOrPhone}
                  onChangeText={setEmailOrPhone}
                  autoCapitalize="none"
                  autoCorrect={false}
                  keyboardType="email-address"
                  returnKeyType="next"
                  placeholderTextColor={colors.gray[400]}
                />
              </View>
            </View>

            {/* Password Input */}
            <View style={styles.inputContainer}>
              <Text style={styles.label}>Password</Text>
              <View style={styles.inputWrapper}>
                <Ionicons name="lock-closed-outline" size={20} color={colors.gray[400]} style={styles.inputIcon} />
                <RNTextInput
                  style={styles.textInput}
                  placeholder="Enter your password"
                  value={password}
                  onChangeText={setPassword}
                  onFocus={handlePasswordFocus}
                  onBlur={handlePasswordBlur}
                  secureTextEntry={!showPassword}
                  autoCapitalize="none"
                  autoCorrect={false}
                  returnKeyType="next"
                  placeholderTextColor={colors.gray[400]}
                />
                <TouchableOpacity onPress={togglePasswordVisibility} style={styles.eyeIcon}>
                  <Ionicons
                    name={showPassword ? 'eye-off-outline' : 'eye-outline'}
                    size={20}
                    color={colors.gray[400]}
                  />
                </TouchableOpacity>
              </View>
            </View>

            {/* Confirm Password Input */}
            <View style={styles.inputContainer}>
              <Text style={styles.label}>Confirm Password</Text>
              <View style={styles.inputWrapper}>
                <Ionicons name="lock-closed-outline" size={20} color={colors.gray[400]} style={styles.inputIcon} />
                <RNTextInput
                  style={styles.textInput}
                  placeholder="Confirm your password"
                  value={confirmPassword}
                  onChangeText={setConfirmPassword}
                  secureTextEntry={!showConfirmPassword}
                  autoCapitalize="none"
                  autoCorrect={false}
                  returnKeyType="done"
                  placeholderTextColor={colors.gray[400]}
                />
                <TouchableOpacity onPress={toggleConfirmPasswordVisibility} style={styles.eyeIcon}>
                  <Ionicons
                    name={showConfirmPassword ? 'eye-off-outline' : 'eye-outline'}
                    size={20}
                    color={colors.gray[400]}
                  />
                </TouchableOpacity>
              </View>
            </View>

            {/* Submit Button */}
            <Button
              title="Sign Up"
              onPress={handleRegister}
              variant="solid"
              size="lg"
              disabled={isLoading}
              loading={isLoading}
              style={styles.submitButton}
            />
          </View>
        </View>

        {/* Login Link */}
        <View style={styles.footer}>
          <Text style={styles.footerText}>Already have an account? </Text>
          <TouchableOpacity onPress={navigateToLogin}>
            <Text style={styles.loginLink}>Sign In</Text>
          </TouchableOpacity>
        </View>
        </Animated.View>
      </ScrollView>
      </KeyboardAvoidingView>
    </Animated.View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.white,
  },
  keyboardAvoidingView: {
    flex: 1,
  },
  scrollContent: {
    flexGrow: 1,
    paddingHorizontal: 0, // No horizontal padding for maximum width
    paddingTop: Platform.OS === 'ios' ? spacing.sm : 4,
    paddingBottom: spacing.xl,
    justifyContent: 'center',
    alignItems: 'stretch', // Let children take full width
    minHeight: '100%',
  },
  welcomeHeader: {
    alignItems: 'center',
    marginTop: 8,
    marginBottom: 0,
    alignSelf: 'center', // Center the header container itself
    backgroundColor: colors.primary[600], // Blue background matching nav
    paddingVertical: spacing.lg,
    paddingHorizontal: spacing.lg, // More horizontal padding
    borderRadius: radius.lg, // Rounded corners around text
    shadowColor: colors.primary[600],
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 4,
    elevation: 4,
  },
  welcomeTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.medium,
    color: colors.white, // White text on blue background
    textAlign: 'center',
    marginBottom: spacing.xs,
  },
  welcomeSubtitle: {
    fontSize: fontSizes.xxl,
    fontWeight: fontWeights.bold,
    color: colors.white, // White text on blue background
    textAlign: 'center',
  },
  // Character hugging layout - like Flutter example
  characterHugContainer: {
    alignItems: 'center', // Keep centered for character
    marginTop: -18, // Move image up closer to title
    width: '100%',
    alignSelf: 'stretch', // Allow children to stretch to full width
  },
  characterContainer: {
    width: 300, // Fixed width for consistency
    height: 250, // Fixed height for consistency
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: -32, // Image overlaps card by 32px
  },
  characterVideo: {
    width: '100%',
    height: '100%',
  },
  characterImageContainer: {
    width: '100%',
    height: '100%',
    position: 'relative',
  },
  characterImage: {
    width: '100%',
    height: '100%',
  },
  fallbackCharacter: {
    width: '100%',
    height: '100%',
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'transparent',
  },
  fallbackEmoji: {
    fontSize: 120,
  },
  loadingText: {
    fontSize: fontSizes.sm,
    color: colors.gray[500],
    marginTop: spacing.sm,
    textAlign: 'center',
  },
  formCard: {
    marginBottom: spacing.xl,
    marginTop: -32, // Negative margin to overlap with character
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    shadowColor: colors.gray[900],
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 4,
    alignSelf: 'stretch', // Force full width inside ScrollView
    width: '100%', // Explicit full width
    maxWidth: undefined, // Remove any width cap
    marginHorizontal: 0, // Nuke any Card defaults
    paddingHorizontal: 20, // Inner horizontal padding
    paddingVertical: spacing.lg, // Inner vertical padding
    // Make it look like it's being hugged by the character
    borderTopLeftRadius: radius.xl,
    borderTopRightRadius: radius.xl,
  },
  formTitle: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
    textAlign: 'center',
    marginBottom: spacing.lg,
  },
  form: {
    gap: spacing.lg,
  },
  inputContainer: {
    gap: spacing.xs,
  },
  label: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: colors.gray[700],
  },
  inputWrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: colors.gray[300],
    borderRadius: radius.md,
    backgroundColor: colors.white,
    paddingHorizontal: spacing.md,
    minHeight: 48,
  },
  inputIcon: {
    marginRight: spacing.sm,
  },
  textInput: {
    flex: 1,
    fontSize: fontSizes.md,
    color: colors.gray[900],
    paddingVertical: spacing.sm,
  },
  eyeIcon: {
    marginLeft: spacing.sm,
  },
  submitButton: {
    marginTop: spacing.md,
  },
  footer: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
  },
  footerText: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
  },
  loginLink: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.primary[600],
    textDecorationLine: 'underline',
  },
});
