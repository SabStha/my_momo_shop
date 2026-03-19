import React, { useState } from 'react';
import { Image, ImageProps, ActivityIndicator, View, StyleSheet } from 'react-native';
import { imagePreloader } from '../services/ImagePreloader';

interface OptimizedImageProps extends Omit<ImageProps, 'source'> {
  source: { uri: string };
  fallbackSource?: ImageProps['source'];
  showLoadingIndicator?: boolean;
  loadingIndicatorColor?: string;
  optimized?: boolean; // Whether to use preloading optimizations
}

export default function OptimizedImage({
  source,
  fallbackSource,
  showLoadingIndicator = true,
  loadingIndicatorColor = '#FF6B35',
  optimized = true,
  style,
  ...props
}: OptimizedImageProps) {
  const [isLoading, setIsLoading] = useState(true);
  const [hasError, setHasError] = useState(false);

  const handleLoadStart = () => {
    setIsLoading(true);
    setHasError(false);
  };

  const handleLoad = () => {
    setIsLoading(false);
    setHasError(false);
    
    if (optimized) {
      console.log('🖼️ [OPTIMIZED] Image loaded instantly:', source.uri.split('/').pop());
    }
  };

  const handleError = (error: any) => {
    console.warn('🖼️ [OPTIMIZED] Image load error:', source.uri.split('/').pop(), error);
    setIsLoading(false);
    setHasError(true);
  };

  // Check if image is preloaded for instant loading
  const isPreloaded = optimized ? imagePreloader.isImagePreloaded(source.uri) : false;

  return (
    <View style={[styles.container, style]}>
      <Image
        {...props}
        source={hasError && fallbackSource ? fallbackSource : source}
        style={[style, { opacity: isLoading ? 0 : 1 }]}
        onLoadStart={handleLoadStart}
        onLoad={handleLoad}
        onError={handleError}
        fadeDuration={isPreloaded ? 0 : 200} // Instant if preloaded
      />
      
      {isLoading && showLoadingIndicator && (
        <View style={styles.loadingOverlay}>
          <ActivityIndicator 
            size="small" 
            color={loadingIndicatorColor}
          />
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    position: 'relative',
  },
  loadingOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'rgba(255, 255, 255, 0.8)',
  },
});
