import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { colors, spacing, fontSizes } from '../../ui/tokens';

interface NotificationDebugProps {
  data: any;
  error?: any;
  isLoading?: boolean;
}

export default function NotificationDebug({ data, error, isLoading }: NotificationDebugProps) {
  if (!__DEV__) return null;
  
  return (
    <View style={styles.container}>
      <Text style={styles.title}>🔍 Notification Debug</Text>
      
      {isLoading && <Text style={styles.text}>⏳ Loading...</Text>}
      
      {error && (
        <View style={styles.errorContainer}>
          <Text style={styles.errorTitle}>❌ Error:</Text>
          <Text style={styles.errorText}>
            {error?.message || 'Unknown error occurred'}
          </Text>
        </View>
      )}
      
      {data && (
        <View style={styles.dataContainer}>
          <Text style={styles.dataTitle}>📊 Data:</Text>
          <Text style={styles.dataText}>
            Notifications: {data?.notifications?.length || 0}
          </Text>
          <Text style={styles.dataText}>
            Pagination: {data?.pagination ? JSON.stringify(data.pagination, null, 2) : 'No pagination data'}
          </Text>
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: colors.gray[100],
    padding: spacing.md,
    margin: spacing.sm,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: colors.gray[300],
  },
  title: {
    fontSize: fontSizes.sm,
    fontWeight: 'bold',
    color: colors.gray[800],
    marginBottom: spacing.sm,
  },
  text: {
    fontSize: fontSizes.xs,
    color: colors.gray[600],
    marginBottom: spacing.xs,
  },
  errorContainer: {
    backgroundColor: '#fef2f2',
    padding: spacing.sm,
    borderRadius: 4,
    marginBottom: spacing.sm,
  },
  errorTitle: {
    fontSize: fontSizes.xs,
    fontWeight: 'bold',
    color: '#991b1b',
  },
  errorText: {
    fontSize: fontSizes.xs,
    color: '#dc2626',
    fontFamily: 'monospace',
  },
  dataContainer: {
    backgroundColor: colors.green[50],
    padding: spacing.sm,
    borderRadius: 4,
  },
  dataTitle: {
    fontSize: fontSizes.xs,
    fontWeight: 'bold',
    color: colors.green[800],
  },
  dataText: {
    fontSize: fontSizes.xs,
    color: colors.green[600],
    fontFamily: 'monospace',
  },
});
