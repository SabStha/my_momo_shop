import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert } from 'react-native';
import { colors, spacing, fontSizes, fontWeights } from '../src/ui/tokens';
import { useSession } from '../src/session/SessionProvider';
import { MenuService } from '../src/api/menu';
import { ENV_CONFIG } from '../src/config/environment';
import { BASE_URL } from '../src/config/api';
import { ConnectionDoctor, useConnectionDiagnostic } from '../src/utils/connectionDoctor';

export default function DebugScreen() {
  const { loading: sessionLoading, token, user, resetAuthState } = useSession();
  const { diagnostic, isLoading: connectionLoading, recommendations, retry } = useConnectionDiagnostic();
  const [apiStatus, setApiStatus] = useState<string>('Testing...');
  const [menuData, setMenuData] = useState<any>(null);
  const [isTesting, setIsTesting] = useState(false);

  const testApiConnection = async () => {
    setIsTesting(true);
    setApiStatus('Testing Laravel API connection...');
    
    try {
      const startTime = Date.now();
      const data = await MenuService.getMenu();
      const endTime = Date.now();
      
      setMenuData(data);
      setApiStatus(`✅ Laravel API Connected (${endTime - startTime}ms)`);
    } catch (error: any) {
      setApiStatus(`❌ Laravel API Failed: ${error.message}`);
      console.error('Laravel API Test Error:', error);
    } finally {
      setIsTesting(false);
    }
  };

  const testLaravelHealth = async () => {
    setIsTesting(true);
    setApiStatus('Testing Laravel health endpoint...');
    
    try {
      const response = await fetch(`${BASE_URL.replace('/api', '')}/health`, {
        method: 'GET',
        timeout: 5000,
      });
      
      if (response.ok) {
        setApiStatus('✅ Laravel server is running');
      } else {
        setApiStatus(`❌ Laravel server responded with ${response.status}`);
      }
    } catch (error: any) {
      setApiStatus(`❌ Laravel server unreachable: ${error.message}`);
    } finally {
      setIsTesting(false);
    }
  };

  const testFallbackData = () => {
    try {
      const fallbackData = MenuService.getFallbackData();
      setMenuData(fallbackData);
      setApiStatus('✅ Using Fallback Data');
    } catch (error: any) {
      setApiStatus(`❌ Fallback Failed: ${error.message}`);
    }
  };

  const clearCorruptedToken = async () => {
    try {
      await resetAuthState();
      Alert.alert('Success', 'Corrupted token cleared. Please restart the app.');
    } catch (error: any) {
      Alert.alert('Error', `Failed to clear token: ${error.message}`);
    }
  };

  useEffect(() => {
    testApiConnection();
  }, []);

  return (
    <ScrollView style={styles.container}>
      <Text style={styles.title}>Debug Information</Text>
      
      {/* Environment Info */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Environment</Text>
        <Text style={styles.info}>Dev Server URL: {ENV_CONFIG.API_URL}</Text>
        <Text style={styles.info}>API Base URL: {BASE_URL}</Text>
        <Text style={styles.info}>Environment: {ENV_CONFIG.ENV}</Text>
        <Text style={styles.info}>Development: {__DEV__ ? 'Yes' : 'No'}</Text>
      </View>

      {/* Session Info */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Session</Text>
        <Text style={styles.info}>Loading: {sessionLoading ? 'Yes' : 'No'}</Text>
        <Text style={styles.info}>Has Token: {token ? 'Yes' : 'No'}</Text>
        <Text style={styles.info}>User: {user?.name || 'None'}</Text>
      </View>

      {/* Connection Status */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Dev Server Connection</Text>
        {connectionLoading ? (
          <Text style={styles.info}>Testing connection...</Text>
        ) : diagnostic ? (
          <>
            <Text style={styles.info}>
              Status: {diagnostic.isReachable ? '✅ Connected' : '❌ Disconnected'}
            </Text>
            {diagnostic.responseTime && (
              <Text style={styles.info}>Response Time: {diagnostic.responseTime}ms</Text>
            )}
            {diagnostic.error && (
              <Text style={[styles.info, styles.errorText]}>Error: {diagnostic.error}</Text>
            )}
            {recommendations.length > 0 && (
              <View style={styles.recommendationsContainer}>
                <Text style={styles.recommendationsTitle}>Recommendations:</Text>
                {recommendations.map((rec, index) => (
                  <Text key={index} style={styles.recommendationText}>
                    • {rec}
                  </Text>
                ))}
              </View>
            )}
          </>
        ) : (
          <Text style={styles.info}>Connection test failed</Text>
        )}
        
        <TouchableOpacity 
          style={styles.button} 
          onPress={retry}
        >
          <Text style={styles.buttonText}>Retry Connection Test</Text>
        </TouchableOpacity>
      </View>

      {/* API Status */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Menu API Status</Text>
        <Text style={styles.info}>{apiStatus}</Text>
        
        <View style={styles.buttonRow}>
          <TouchableOpacity 
            style={[styles.button, isTesting && styles.buttonDisabled]} 
            onPress={testLaravelHealth}
            disabled={isTesting}
          >
            <Text style={styles.buttonText}>Test Laravel</Text>
          </TouchableOpacity>
          
          <TouchableOpacity 
            style={[styles.button, isTesting && styles.buttonDisabled]} 
            onPress={testApiConnection}
            disabled={isTesting}
          >
            <Text style={styles.buttonText}>Test API</Text>
          </TouchableOpacity>
        </View>
        
        <TouchableOpacity 
          style={styles.button} 
          onPress={testFallbackData}
        >
          <Text style={styles.buttonText}>Test Fallback Data</Text>
        </TouchableOpacity>
      </View>

      {/* Menu Data */}
      {menuData && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Menu Data</Text>
          <Text style={styles.info}>Categories: {menuData.categories?.length || 0}</Text>
          <Text style={styles.info}>Items: {menuData.items?.length || 0}</Text>
        </View>
      )}

      {/* Quick Actions */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Quick Actions</Text>
        <TouchableOpacity 
          style={[styles.button, { backgroundColor: colors.error }]} 
          onPress={clearCorruptedToken}
        >
          <Text style={styles.buttonText}>Clear Corrupted Token</Text>
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.button} 
          onPress={() => Alert.alert('Info', 'This is a debug screen to help diagnose loading issues.')}
        >
          <Text style={styles.buttonText}>Show Help</Text>
        </TouchableOpacity>
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.white,
    padding: spacing.lg,
  },
  title: {
    fontSize: fontSizes['2xl'],
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    marginBottom: spacing.lg,
    textAlign: 'center',
  },
  section: {
    backgroundColor: colors.white,
    padding: spacing.md,
    borderRadius: 12,
    marginBottom: spacing.md,
    borderWidth: 1,
    borderColor: colors.brand.primary,
  },
  sectionTitle: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    marginBottom: spacing.sm,
  },
  info: {
    fontSize: fontSizes.sm,
    color: colors.momo.mocha,
    marginBottom: spacing.xs,
  },
  buttonRow: {
    flexDirection: 'row',
    gap: spacing.sm,
    marginTop: spacing.sm,
  },
  button: {
    backgroundColor: colors.brand.primary,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: 8,
    flex: 1,
  },
  buttonDisabled: {
    backgroundColor: colors.gray[400],
  },
  buttonText: {
    color: colors.white,
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    textAlign: 'center',
  },
  errorText: {
    color: colors.error,
    fontWeight: fontWeights.medium,
  },
  recommendationsContainer: {
    marginTop: spacing.sm,
    padding: spacing.sm,
    backgroundColor: colors.white,
    borderRadius: 8,
    borderLeftWidth: 3,
    borderLeftColor: colors.brand.primary,
  },
  recommendationsTitle: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.bold,
    color: colors.brand.primary,
    marginBottom: spacing.xs,
  },
  recommendationText: {
    fontSize: fontSizes.xs,
    color: colors.momo.mocha,
    marginBottom: spacing.xs,
    lineHeight: 16,
  },
});
