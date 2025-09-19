import React, { Component, ErrorInfo, ReactNode } from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';

interface Props {
  children: ReactNode;
  fallback?: ReactNode;
}

interface State {
  hasError: boolean;
  error?: Error;
  errorInfo?: ErrorInfo;
}

export class ErrorBoundary extends Component<Props, State> {
  constructor(props: Props) {
    super(props);
    this.state = { hasError: false };
  }

  static getDerivedStateFromError(error: Error): State {
    return { hasError: true, error };
  }

  componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    console.error('ErrorBoundary caught an error:', error, errorInfo);
    
    // Check for infinite loop errors
    if (error.message.includes('Maximum update depth exceeded')) {
      console.error('🚨 INFINITE LOOP DETECTED! This usually means:');
      console.error('1. A component is calling setState in render');
      console.error('2. useEffect dependencies are causing infinite re-renders');
      console.error('3. Context values are changing on every render');
      console.error('4. Route navigation is causing infinite redirects');
    }
    
    // Only set state if we don't already have an error to prevent infinite loops
    if (!this.state.error) {
      this.setState({
        error,
        errorInfo,
      });
    }
  }

  handleRetry = () => {
    this.setState({ hasError: false, error: undefined, errorInfo: undefined });
  };

  handleReset = () => {
    // Force a complete reset by clearing state and potentially reloading
    this.setState({ hasError: false, error: undefined, errorInfo: undefined });
    
    // If it's an infinite loop error, suggest a reload
    if (this.state.error?.message.includes('Maximum update depth exceeded')) {
      if (typeof window !== 'undefined') {
        window.location.reload();
      }
    }
  };

  render() {
    if (this.state.hasError) {
      if (this.props.fallback) {
        return this.props.fallback;
      }

      const isInfiniteLoop = this.state.error?.message.includes('Maximum update depth exceeded');

      return (
        <View style={styles.container}>
          <Text style={styles.title}>
            {isInfiniteLoop ? '🚨 Infinite Loop Detected' : 'Something went wrong'}
          </Text>
          
          <Text style={styles.message}>
            {isInfiniteLoop 
              ? 'The app detected an infinite loop and stopped to prevent crashes. This usually happens when components keep re-rendering endlessly.'
              : 'We encountered an unexpected error. Please try again.'
            }
          </Text>
          
          {isInfiniteLoop && (
            <View style={styles.infiniteLoopInfo}>
              <Text style={styles.infoTitle}>Common Causes:</Text>
              <Text style={styles.infoText}>• setState called in render</Text>
              <Text style={styles.infoText}>• useEffect with missing dependencies</Text>
              <Text style={styles.infoText}>• Context values changing on every render</Text>
              <Text style={styles.infoText}>• Route navigation loops</Text>
            </View>
          )}
          
          <View style={styles.buttonContainer}>
            <TouchableOpacity style={styles.retryButton} onPress={this.handleRetry}>
              <Text style={styles.retryButtonText}>Try Again</Text>
            </TouchableOpacity>
            
            {isInfiniteLoop && (
              <TouchableOpacity style={styles.resetButton} onPress={this.handleReset}>
                <Text style={styles.resetButtonText}>Reset App</Text>
              </TouchableOpacity>
            )}
          </View>
        </View>
      );
    }

    return this.props.children;
  }
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 24,
    backgroundColor: '#ffffff',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#111827',
    marginBottom: 16,
    textAlign: 'center',
  },
  message: {
    fontSize: 16,
    color: '#6b7280',
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 24,
  },
  infiniteLoopInfo: {
    backgroundColor: '#fef3c7',
    padding: 16,
    borderRadius: 8,
    marginBottom: 24,
    borderWidth: 1,
    borderColor: '#f59e0b',
  },
  infoTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#92400e',
    marginBottom: 8,
  },
  infoText: {
    fontSize: 14,
    color: '#92400e',
    marginBottom: 4,
  },
  buttonContainer: {
    width: '100%',
    gap: 12,
  },
  retryButton: {
    backgroundColor: '#3b82f6',
    padding: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  retryButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '600',
  },
  resetButton: {
    backgroundColor: '#5a2e22', // AmaKo brown1
    padding: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  resetButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '600',
  },
});

// HOC to wrap components with error boundary
export function withErrorBoundary<P extends object>(
  Component: React.ComponentType<P>,
  fallback?: ReactNode
) {
  return function WrappedComponent(props: P) {
    return (
      <ErrorBoundary fallback={fallback}>
        <Component {...props} />
      </ErrorBoundary>
    );
  };
}
