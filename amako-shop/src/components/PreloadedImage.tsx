import React, { useState, useEffect } from 'react';
import { Image, ImageProps, ActivityIndicator, View, StyleSheet } from 'react-native';
import { imagePreloader } from '../services/ImagePreloader';

interface PreloadedImageProps extends Omit<ImageProps, 'source'> {
  source: { uri: string };
  fallbackSource?: ImageProps['source'];
  showLoadingIndicator?: boolean;
  loadingIndicatorColor?: string;
}

export default function PreloadedImage({
  source,
  fallbackSource,
  showLoadingIndicator = true,
  loadingIndicatorColor = '#FF6B35',
  style,
  ...props
}: PreloadedImageProps) {
  const [isLoading, setIsLoading] = useState(true);
  const [hasError, setHasError] = useState(false);
  const [isPreloaded, setIsPreloaded] = useState(false);

  useEffect(() => {
    // Check if image is already preloaded
    const preloaded = imagePreloader.isImagePreloaded(source.uri);
    setIsPreloaded(preloaded);
    
    if (preloaded) {
      console.log('🖼️ [PRELOADED] Using cached image:', source.uri.split('/').pop());
      setIsLoading(false);
    }
  }, [source.uri]);

  const handleLoadStart = () => {
    setIsLoading(true);
    setHasError(false);
  };

  const handleLoad = () => {
    setIsLoading(false);
    setHasError(false);
    console.log('🖼️ [PRELOADED] Image loaded:', source.uri.split('/').pop());
  };

  const handleError = (error: any) => {
    console.warn('🖼️ [PRELOADED] Image load error:', source.uri.split('/').pop(), error);
    setIsLoading(false);
    setHasError(true);
  };

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
