import { Platform } from 'react-native';
import Constants from 'expo-constants';

interface ConnectionDiagnostic {
  isReachable: boolean;
  error?: string;
  responseTime?: number;
  serverUrl?: string;
  platform: string;
}

/**
 * Connection Doctor - Diagnoses dev server connectivity issues
 * Helps identify why the app might be stuck in loading loops
 */
export class ConnectionDoctor {
  private static readonly TIMEOUT = 5000; // 5 seconds
  private static readonly RETRY_ATTEMPTS = 2;

  /**
   * Test if the development server is reachable
   */
  static async diagnoseConnection(): Promise<ConnectionDiagnostic> {
    const platform = Platform.OS;
    const serverUrl = this.getServerUrl();
    
    if (__DEV__) {
      console.log('🔍 ConnectionDoctor: Testing connection to:', serverUrl);
    }

    try {
      const startTime = Date.now();
      
      // Try to reach the dev server
      const response = await this.pingServer(serverUrl);
      const responseTime = Date.now() - startTime;
      
      if (__DEV__) {
        console.log('✅ ConnectionDoctor: Server reachable in', responseTime, 'ms');
      }
      
      return {
        isReachable: true,
        responseTime,
        serverUrl,
        platform,
      };
    } catch (error: any) {
      const errorMessage = this.normalizeError(error);
      
      if (__DEV__) {
        console.error('❌ ConnectionDoctor: Server unreachable:', errorMessage);
      }
      
      return {
        isReachable: false,
        error: errorMessage,
        serverUrl,
        platform,
      };
    }
  }

  /**
   * Get the development server URL based on platform
   */
  private static getServerUrl(): string {
    const manifest = Constants.expoConfig;
    const debuggerHost = Constants.expoConfig?.hostUri;
    
    if (debuggerHost) {
      // Use the actual debugger host from Expo
      return `http://${debuggerHost}`;
    }
    
    // Fallback to localhost for web/emulator
    return 'http://localhost:8081';
  }

  /**
   * Ping the development server with retries
   */
  private static async pingServer(url: string): Promise<Response> {
    let lastError: Error | null = null;
    
    for (let attempt = 1; attempt <= this.RETRY_ATTEMPTS; attempt++) {
      try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), this.TIMEOUT);
        
        const response = await fetch(`${url}/status`, {
          method: 'GET',
          signal: controller.signal,
          headers: {
            'Accept': 'application/json',
          },
        });
        
        clearTimeout(timeoutId);
        
        if (response.ok) {
          return response;
        }
        
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      } catch (error: any) {
        lastError = error;
        
        if (__DEV__) {
          console.warn(`ConnectionDoctor: Attempt ${attempt} failed:`, error.message);
        }
        
        // Wait before retry (except on last attempt)
        if (attempt < this.RETRY_ATTEMPTS) {
          await new Promise(resolve => setTimeout(resolve, 1000));
        }
      }
    }
    
    throw lastError || new Error('Connection failed after all retries');
  }

  /**
   * Normalize error messages for better debugging
   */
  private static normalizeError(error: any): string {
    if (error.name === 'AbortError') {
      return 'Connection timeout - dev server may be unreachable';
    }
    
    if (error.message?.includes('Network request failed')) {
      return 'Network error - check your connection and dev server status';
    }
    
    if (error.message?.includes('fetch')) {
      return 'Fetch error - dev server may not be running';
    }
    
    if (error.message?.includes('192.168.56.')) {
      return 'VirtualBox Host-Only network detected - use tunnel mode instead';
    }
    
    return error.message || 'Unknown connection error';
  }

  /**
   * Get connection recommendations based on the diagnostic
   */
  static getRecommendations(diagnostic: ConnectionDiagnostic): string[] {
    const recommendations: string[] = [];
    
    if (!diagnostic.isReachable) {
      if (diagnostic.error?.includes('VirtualBox')) {
        recommendations.push('Use tunnel mode: npm run start:tunnel');
        recommendations.push('Or disable VirtualBox Host-Only adapter');
      } else if (diagnostic.error?.includes('timeout')) {
        recommendations.push('Try tunnel mode: npm run start:tunnel');
        recommendations.push('Check if dev server is running');
      } else if (diagnostic.error?.includes('Network')) {
        recommendations.push('Check your Wi-Fi connection');
        recommendations.push('Try USB mode: npm run start:usb');
      } else {
        recommendations.push('Try tunnel mode: npm run start:tunnel');
        recommendations.push('Restart the dev server');
      }
    }
    
    return recommendations;
  }

  /**
   * Log a comprehensive connection report
   */
  static async logConnectionReport(): Promise<void> {
    if (!__DEV__) return;
    
    console.log('🔍 ConnectionDoctor: Running connection diagnostic...');
    
    const diagnostic = await this.diagnoseConnection();
    const recommendations = this.getRecommendations(diagnostic);
    
    console.log('📊 ConnectionDoctor Report:');
    console.log('  Platform:', diagnostic.platform);
    console.log('  Server URL:', diagnostic.serverUrl);
    console.log('  Reachable:', diagnostic.isReachable ? '✅' : '❌');
    
    if (diagnostic.responseTime) {
      console.log('  Response Time:', diagnostic.responseTime + 'ms');
    }
    
    if (diagnostic.error) {
      console.log('  Error:', diagnostic.error);
    }
    
    if (recommendations.length > 0) {
      console.log('  Recommendations:');
      recommendations.forEach((rec, index) => {
        console.log(`    ${index + 1}. ${rec}`);
      });
    }
  }
}

/**
 * Hook to use connection diagnostics in React components
 */
export function useConnectionDiagnostic() {
  const [diagnostic, setDiagnostic] = React.useState<ConnectionDiagnostic | null>(null);
  const [isLoading, setIsLoading] = React.useState(true);

  React.useEffect(() => {
    const runDiagnostic = async () => {
      setIsLoading(true);
      try {
        const result = await ConnectionDoctor.diagnoseConnection();
        setDiagnostic(result);
      } catch (error) {
        setDiagnostic({
          isReachable: false,
          error: 'Diagnostic failed',
          platform: Platform.OS,
        });
      } finally {
        setIsLoading(false);
      }
    };

    runDiagnostic();
  }, []);

  const recommendations = diagnostic ? ConnectionDoctor.getRecommendations(diagnostic) : [];

  return {
    diagnostic,
    isLoading,
    recommendations,
    retry: () => {
      setIsLoading(true);
      ConnectionDoctor.diagnoseConnection().then(setDiagnostic).finally(() => setIsLoading(false));
    },
  };
}

// Import React for the hook
import React from 'react';
