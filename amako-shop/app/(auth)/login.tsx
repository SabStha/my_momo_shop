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
  Dimensions,
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
import { useLogin } from '../../src/api/auth-hooks';
import { Button, Card, spacing, fontSizes, fontWeights, colors, radius } from '../../src/ui';
import LoadingSpinner from '../../src/components/LoadingSpinner';

const { width } = Dimensions.get('window');

type AnimationState = 'hello' | 'eye-closing' | 'typing';

export default function LoginScreen() {
  const [emailOrPhone, setEmailOrPhone] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [rememberMe, setRememberMe] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [isPasswordFocused, setIsPasswordFocused] = useState(false);
  const [isEmailFocused, setIsEmailFocused] = useState(false);
  const [animationState, setAnimationState] = useState<AnimationState>('hello');
  const [hasVideoError, setHasVideoError] = useState(false);
  const [isImageLoading, setIsImageLoading] = useState(true);
  const [preloadedImages, setPreloadedImages] = useState<{[key: string]: any}>({});
  
  const passwordInputRef = useRef<RNTextInput>(null);
  const videoRef = useRef<Video>(null);
  const scrollViewRef = useRef<ScrollView>(null);
  const loginMutation = useLogin();
  
  // Get animation source URI for expo-av
  const getAnimationSourceUri = () => {
    try {
      let source;
      let fileName;
      let preloadKey;
      
      // Use preloaded images if available, otherwise fallback to require
      if (animationState === 'eye-closing') {
        preloadKey = 'close';
        source = preloadedImages[preloadKey] || require('../../assets/animations/close.gif');
        fileName = 'close.gif';
      } else if (animationState === 'typing') {
        preloadKey = 'typing';
        source = preloadedImages[preloadKey] || require('../../assets/animations/Welcoome.mp4');
        fileName = 'Welcoome.mp4';
      } else {
        preloadKey = 'welcome';
        source = preloadedImages[preloadKey] || require('../../assets/animations/welcome.gif');
        fileName = 'welcome.gif';
      }
      
      console.log('🎬 Animation State:', animationState);
      console.log('🎬 Loading file:', fileName);
      console.log('🎬 Preload key:', preloadKey);
      console.log('🎬 Using preloaded:', !!preloadedImages[preloadKey]);
      console.log('🎬 Raw source:', source);
      
      return source;
    } catch (error) {
      console.log('Animation file not found, using fallback:', error);
      return null;
    }
  };

  const animationSource = getAnimationSourceUri();

  // Debug logging
  console.log('🎬 Animation source:', animationSource);
  console.log('🎬 Animation state:', animationState);

  // Video player setup - using stable expo-av
  const videoPlayer = null; // Not needed for expo-av

  // Animation values
  const characterScale = useSharedValue(1);
  const characterRotation = useSharedValue(0);
  const characterTranslateY = useSharedValue(0);
  const emailInputGlow = useSharedValue(0);
  const passwordInputGlow = useSharedValue(0);
  const buttonScale = useSharedValue(1);
  const buttonPressed = useSharedValue(0);
  const screenOpacity = useSharedValue(0);
  const formTranslateY = useSharedValue(50);

  // Preload all GIF images on component mount
  useEffect(() => {
    const preloadImages = async () => {
      try {
        console.log('🎬 Starting image preloading...');
        
        const imagesToPreload = {
          'welcome': require('../../assets/animations/welcome.gif'),
          'close': require('../../assets/animations/close.gif'),
          'loading': require('../../assets/animations/loading.mp4'),
          'typing': require('../../assets/animations/Welcoome.mp4'),
        };

        setPreloadedImages(imagesToPreload);
        setIsImageLoading(false);
        console.log('🎬 Images preloaded successfully');
        console.log('🎬 Preloaded images:', Object.keys(imagesToPreload));
      } catch (error) {
        console.log('🎬 Error preloading images:', error);
        setIsImageLoading(false);
      }
    };

    preloadImages();
  }, []);

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
      // When loading, keep character in normal state
      // LoadingSpinner overlay will show instead
      setAnimationState('hello');
      characterRotation.value = withSpring(0);
      characterScale.value = withSpring(1);
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

  // Input glow animations
  useEffect(() => {
    if (isEmailFocused) {
      emailInputGlow.value = withTiming(1, { duration: 300 });
    } else {
      emailInputGlow.value = withTiming(0, { duration: 300 });
    }
  }, [isEmailFocused]);

  useEffect(() => {
    if (isPasswordFocused) {
      passwordInputGlow.value = withTiming(1, { duration: 300 });
    } else {
      passwordInputGlow.value = withTiming(0, { duration: 300 });
    }
  }, [isPasswordFocused]);

  // Replay video when animation changes - using stable expo-av
  useEffect(() => {
    if (videoRef.current && !hasVideoError && animationSource) {
      try {
        videoRef.current.replayAsync().catch(() => {
          console.log('🎬 Video replay failed');
          setHasVideoError(true);
        });
        console.log('🎬 Video playing:', animationState);
      } catch (error) {
        console.log('🎬 Video play error:', error);
        setHasVideoError(true);
      }
    }
  }, [animationState, hasVideoError, animationSource]);

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

  const emailInputAnimatedStyle = useAnimatedStyle(() => {
    const glowOpacity = interpolate(emailInputGlow.value, [0, 1], [0, 0.3]);
    const glowScale = interpolate(emailInputGlow.value, [0, 1], [1, 1.02]);
    
    return {
      transform: [{ scale: glowScale }],
      shadowOpacity: glowOpacity,
      shadowColor: colors.primary[500],
      shadowOffset: { width: 0, height: 0 },
      shadowRadius: 8,
      elevation: emailInputGlow.value * 4,
    };
  });

  const passwordInputAnimatedStyle = useAnimatedStyle(() => {
    const glowOpacity = interpolate(passwordInputGlow.value, [0, 1], [0, 0.3]);
    const glowScale = interpolate(passwordInputGlow.value, [0, 1], [1, 1.02]);
    
    return {
      transform: [{ scale: glowScale }],
      shadowOpacity: glowOpacity,
      shadowColor: colors.primary[500],
      shadowOffset: { width: 0, height: 0 },
      shadowRadius: 8,
      elevation: passwordInputGlow.value * 4,
    };
  });

  const buttonAnimatedStyle = useAnimatedStyle(() => {
    return {
      transform: [
        { scale: buttonScale.value },
        { scaleY: withTiming(buttonPressed.value === 1 ? 0.95 : 1, { duration: 100 }) },
      ],
    };
  });

  const formAnimatedStyle = useAnimatedStyle(() => {
    return {
      transform: [{ translateY: formTranslateY.value }],
      opacity: screenOpacity.value,
    };
  });

  // Button gesture handling
  const buttonGesture = Gesture.Tap()
    .onBegin(() => {
      buttonPressed.value = 1;
      buttonScale.value = withSpring(0.95, { damping: 15 });
    })
    .onFinalize(() => {
      buttonPressed.value = 0;
      buttonScale.value = withSpring(1, { damping: 15 });
    });

  const handleLogin = async () => {
    if (!emailOrPhone.trim() || !password.trim()) {
      Alert.alert('Error', 'Please fill in all fields');
      return;
    }

    setIsLoading(true);
    
    try {
      await loginMutation.mutateAsync({
        emailOrPhone: emailOrPhone.trim(),
        password: password.trim(),
      });
      
      // Success - navigate to home
      router.replace('/(tabs)/home');
    } catch (error) {
      console.error('Login error:', error);
      Alert.alert('Login Failed', 'Invalid credentials. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  const togglePasswordVisibility = () => {
    setShowPassword(!showPassword);
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
    <View style={styles.container}>
      <KeyboardAvoidingView
        style={styles.keyboardAvoidingView}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={0}
      >
        <ScrollView
          ref={scrollViewRef}
          contentContainerStyle={styles.scrollContent}
          keyboardShouldPersistTaps="handled"
          keyboardDismissMode="on-drag"
          showsVerticalScrollIndicator={false}
          bounces={true}
          scrollEventThrottle={16}
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

                {/* Loading overlay handled separately - no spinning video here */}
              </View>
            ) : (
              // Fallback to emoji if animation not available or loading
              <View style={styles.fallbackCharacter}>
                <Text style={styles.fallbackEmoji}>
                  {isLoading ? '⚔️' : isPasswordFocused ? '👁️' : isEmailFocused ? '📧' : '👋'}
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
              <Text style={styles.formTitle}>Sign In</Text>

              <View style={styles.form}>
                {/* Email/Phone Input */}
                <View style={styles.inputContainer}>
                  <Text style={styles.label}>Email or Phone</Text>
                  <Animated.View style={[styles.inputWrapper, emailInputAnimatedStyle]}>
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
                      onSubmitEditing={() => passwordInputRef.current?.focus()}
                      blurOnSubmit={false}
                      importantForAutofill="yes"
                      onFocus={() => setIsEmailFocused(true)}
                      onBlur={() => setIsEmailFocused(false)}
                    />
                  </Animated.View>
                </View>

                {/* Password Input */}
                <View style={styles.inputContainer}>
                  <Text style={styles.label}>Password</Text>
                  <Animated.View style={[styles.inputWrapper, passwordInputAnimatedStyle]}>
                    <Ionicons name="lock-closed-outline" size={20} color={colors.gray[400]} style={styles.inputIcon} />
                    <RNTextInput
                      ref={passwordInputRef}
                      style={styles.textInput}
                      placeholder="Enter your password"
                      value={password}
                      onChangeText={setPassword}
                      onFocus={handlePasswordFocus}
                      onBlur={handlePasswordBlur}
                      secureTextEntry={!showPassword}
                      autoCapitalize="none"
                      autoCorrect={false}
                      returnKeyType="done"
                      placeholderTextColor={colors.gray[400]}
                      onSubmitEditing={handleLogin}
                      blurOnSubmit={true}
                      importantForAutofill="yes"
                    />
                    <TouchableOpacity onPress={togglePasswordVisibility} style={styles.eyeIcon}>
                      <Ionicons
                        name={showPassword ? 'eye-outline' : 'eye-off-outline'}
                        size={20}
                        color={colors.gray[400]}
                      />
                    </TouchableOpacity>
                  </Animated.View>
                </View>

                {/* Remember Me */}
                <TouchableOpacity 
                  style={styles.rememberMeContainer}
                  onPress={() => setRememberMe(!rememberMe)}
                  activeOpacity={0.7}
                >
                  <View style={[styles.checkbox, rememberMe && styles.checkboxChecked]}>
                    {rememberMe && (
                      <Ionicons name="checkmark" size={14} color={colors.white} />
                    )}
                  </View>
                  <Text style={styles.rememberMeText}>Remember me</Text>
                </TouchableOpacity>

                {/* Forgot Password */}
                <TouchableOpacity
                  onPress={() => Alert.alert('Forgot Password', 'Password reset feature coming soon!')}
                  activeOpacity={0.7}
                  style={styles.forgotPasswordContainer}
                >
                  <Text style={styles.forgotPasswordText}>Forgot Password?</Text>
                </TouchableOpacity>

                {/* Sign In Button */}
                <GestureDetector gesture={buttonGesture}>
                  <Animated.View style={buttonAnimatedStyle}>
                    <TouchableOpacity
                      style={[
                        styles.signInButton,
                        isLoading && styles.signInButtonDisabled
                      ]}
                      onPress={handleLogin}
                      disabled={isLoading}
                    >
                      <Text style={styles.signInButtonText}>
                        {isLoading ? 'Signing In...' : 'Sign In'}
                      </Text>
                    </TouchableOpacity>
                  </Animated.View>
                </GestureDetector>

                {/* Sign Up Link */}
                <View style={styles.signUpContainer}>
                  <Text style={styles.signUpText}>Don't have an account? </Text>
                  <TouchableOpacity onPress={() => router.push('/(auth)/register')}>
                    <Text style={styles.signUpLink}>Sign Up</Text>
                  </TouchableOpacity>
                </View>
              </View>
          </View>
        </Animated.View>
        </ScrollView>
      </KeyboardAvoidingView>
      
      {/* Full Screen Loading Overlay */}
      {isLoading && (
        <View style={styles.loadingOverlay}>
          <LoadingSpinner 
            size="large" 
            text="Signing in..."
          />
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    // White background
    backgroundColor: colors.white,
  },
  keyboardAvoidingView: {
    flex: 1,
  },
  scrollContent: {
    flexGrow: 1,
    paddingHorizontal: 0,
    paddingTop: Platform.OS === 'ios' ? spacing.lg : spacing.md,
    paddingBottom: spacing.xl * 3,
    justifyContent: 'flex-start',
    alignItems: 'stretch',
  },
  welcomeHeader: {
    alignItems: 'center',
    marginTop: 8,
    marginBottom: 0, // Reduced from 8 to 4
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
    width: width * 0.805, // Increased by 15% (0.7 * 1.15 = 0.805)
    height: width * 0.69, // Increased by 15% (0.6 * 1.15 = 0.69)
    maxWidth: 437, // Increased by 15% (380 * 1.15 = 437)
    maxHeight: 368, // Increased by 15% (320 * 1.15 = 368)
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
    maxWidth: undefined, // Remove tablet cap for true full width
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
    borderColor: colors.primary[200], // Blue border
    borderRadius: radius.lg,
    backgroundColor: colors.gray[100], // Light gray background
    paddingHorizontal: spacing.md,
    minHeight: 52,
    overflow: 'hidden',
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
    padding: spacing.xs,
    marginLeft: spacing.xs,
  },
  rememberMeContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  forgotPasswordContainer: {
    marginBottom: spacing.md,
  },
  checkbox: {
    width: 20,
    height: 20,
    borderWidth: 2,
    borderColor: colors.primary[600],
    borderRadius: 4,
    marginRight: spacing.sm,
    backgroundColor: colors.white,
    justifyContent: 'center',
    alignItems: 'center',
  },
  checkboxChecked: {
    backgroundColor: colors.primary[600],
    borderColor: colors.primary[600],
  },
  rememberMeText: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
  },
  forgotPasswordText: {
    fontSize: fontSizes.sm,
    color: colors.primary[600],
    fontWeight: fontWeights.medium,
  },
  signInButton: {
    backgroundColor: colors.primary[600], // Blue color like your nav
    borderRadius: radius.lg,
    paddingVertical: spacing.md,
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: 52,
  },
  signInButtonDisabled: {
    backgroundColor: colors.gray[400],
  },
  signInButtonText: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.white,
  },
  signUpContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
  },
  signUpText: {
    fontSize: fontSizes.sm,
    color: colors.gray[600],
  },
  signUpLink: {
    fontSize: fontSizes.sm,
    color: colors.primary[600],
    fontWeight: fontWeights.semibold,
  },
  loadingOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.7)',
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 9999,
  },
});