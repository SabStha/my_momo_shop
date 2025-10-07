import React, { useState, useRef } from 'react';
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
} from 'react-native';
import { router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useLogin } from '../../src/api/auth-hooks';
import { Button, Card, spacing, fontSizes, fontWeights, colors, radius } from '../../src/ui';

export default function LoginScreen() {
  const [emailOrPhone, setEmailOrPhone] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  
  const passwordInputRef = useRef<RNTextInput>(null);
  const loginMutation = useLogin();

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
    } catch (error: any) {
      Alert.alert('Login Failed', error.message || 'Please check your credentials and try again.');
    } finally {
      setIsLoading(false);
    }
  };

  const navigateToRegister = () => {
    router.push('/(auth)/register');
  };

  const togglePasswordVisibility = () => {
    setShowPassword(!showPassword);
  };

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      keyboardVerticalOffset={Platform.OS === 'ios' ? 0 : 0}
    >
      <ScrollView
        contentContainerStyle={styles.scrollContent}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}
        bounces={false}
        scrollEventThrottle={16}
        automaticallyAdjustKeyboardInsets={Platform.OS === 'ios'}
      >
        <View style={styles.header}>
          <View style={styles.logoContainer}>
            <Text style={styles.logo}>🍽️</Text>
            <Text style={styles.appName}>Amako Shop</Text>
          </View>
          <Text style={styles.subtitle}>Welcome back! Please sign in to continue.</Text>
        </View>

        <Card style={styles.formCard} padding="lg" radius="lg" shadow="medium">
          <Text style={styles.formTitle}>Sign In</Text>

          <View style={styles.form}>
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
                  onSubmitEditing={() => passwordInputRef.current?.focus()}
                  blurOnSubmit={false}
                  importantForAutofill="yes"
                />
              </View>
            </View>

            {/* Password Input */}
            <View style={styles.inputContainer}>
              <Text style={styles.label}>Password</Text>
              <View style={styles.inputWrapper}>
                <Ionicons name="lock-closed-outline" size={20} color={colors.gray[400]} style={styles.inputIcon} />
                <RNTextInput
                  ref={passwordInputRef}
                  style={styles.textInput}
                  placeholder="Enter your password"
                  value={password}
                  onChangeText={setPassword}
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
                    name={showPassword ? 'eye-off-outline' : 'eye-outline'}
                    size={20}
                    color={colors.gray[400]}
                  />
                </TouchableOpacity>
              </View>
            </View>

            {/* Submit Button */}
            <Button
              title="Sign In"
              onPress={handleLogin}
              variant="solid"
              size="lg"
              disabled={isLoading}
              loading={isLoading}
              style={styles.submitButton}
            />

            {/* Forgot Password */}
            <TouchableOpacity style={styles.forgotPassword}>
              <Text style={styles.forgotPasswordText}>Forgot your password?</Text>
            </TouchableOpacity>
          </View>
        </Card>

        {/* Register Link */}
        <View style={styles.footer}>
          <Text style={styles.footerText}>Don't have an account? </Text>
          <TouchableOpacity onPress={navigateToRegister}>
            <Text style={styles.registerLink}>Sign Up</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.gray[50],
  },
  scrollContent: {
    flexGrow: 1,
    padding: spacing.lg,
    justifyContent: 'center',
  },
  header: {
    alignItems: 'center',
    marginBottom: spacing.xl,
  },
  logoContainer: {
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  logo: {
    fontSize: 64,
    marginBottom: spacing.sm,
  },
  appName: {
    fontSize: fontSizes.xxl,
    fontWeight: fontWeights.bold,
    color: colors.gray[900],
  },
  subtitle: {
    fontSize: fontSizes.md,
    color: colors.gray[600],
    textAlign: 'center',
    lineHeight: fontSizes.md * 1.4,
  },
  formCard: {
    marginBottom: spacing.xl,
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
    paddingHorizontal: 0,
    margin: 0,
    textAlignVertical: 'center',
  },
  eyeIcon: {
    marginLeft: spacing.sm,
  },
  submitButton: {
    marginTop: spacing.md,
  },
  forgotPassword: {
    alignItems: 'center',
    marginTop: spacing.sm,
  },
  forgotPasswordText: {
    fontSize: fontSizes.sm,
    color: colors.primary[600],
    textDecorationLine: 'underline',
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
  registerLink: {
    fontSize: fontSizes.md,
    fontWeight: fontWeights.semibold,
    color: colors.primary[600],
    textDecorationLine: 'underline',
  },
});
