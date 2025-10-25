import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ViewStyle, TextStyle, Image, ActivityIndicator } from 'react-native';
import { BlurView } from 'expo-blur';

interface LoadingSpinnerProps {
  size?: 'small' | 'medium' | 'large';
  text?: string;
  style?: ViewStyle;
  textStyle?: TextStyle;
}

export default function LoadingSpinner({ 
  size = 'medium', 
  text = 'Loading...', 
  style,
  textStyle 
}: LoadingSpinnerProps) {
  const imageSize = size === 'small' ? 40 : size === 'large' ? 120 : 80;
  const [gifLoaded, setGifLoaded] = useState(false);
  const [gifError, setGifError] = useState(false);
  
  // Debug logging
  console.log('🥟 [LOADING GIF] Component rendering:', {
    size,
    text,
    imageSize,
    calculatedSize: imageSize * 1.2,
    gifLoaded,
    gifError
  });

  // Preload GIF on mount
  useEffect(() => {
    console.log('🥟 [LOADING GIF] Attempting to preload GIF...');
    const gifSource = require('../../assets/animations/loading.gif');
    Image.prefetch(Image.resolveAssetSource(gifSource).uri)
      .then(() => {
        console.log('🥟 [LOADING GIF] ✅ GIF preloaded successfully!');
        setGifLoaded(true);
      })
      .catch((error) => {
        console.error('🥟 [LOADING GIF] ❌ GIF preload failed:', error);
        setGifError(true);
      });
  }, []);
  
  return (
    <View style={[styles.container, style]}>
      {/* Blur background wrapper */}
      <BlurView 
        intensity={80}
        tint="light"
        style={[
          styles.imageWrapper, 
          { 
            width: imageSize * 1.2,
            height: imageSize * 1.2,
            borderRadius: (imageSize * 1.2) / 2,
          }
        ]}
      >
        {/* Show GIF if loaded, spinner if loading, nothing if error (blur looks good) */}
        {gifError ? (
          <ActivityIndicator size={size === 'large' ? 'large' : 'small'} color="#FF6B35" />
        ) : !gifLoaded ? (
          <ActivityIndicator size={size === 'large' ? 'large' : 'small'} color="#FF6B35" />
        ) : (
          <Image
            source={require('../../assets/animations/loading.gif')}
            style={[styles.image, { 
              width: imageSize * 1.2, 
              height: imageSize * 1.2,
            }]}
            resizeMode="cover"
            fadeDuration={0}
            onLoadStart={() => console.log('🥟 [LOADING GIF] Image component load started')}
            onLoad={() => console.log('🥟 [LOADING GIF] ✅ Image component loaded!')}
            onError={(error) => {
              console.error('🥟 [LOADING GIF] ❌ Image component error:', error);
              setGifError(true);
            }}
          />
        )}
      </BlurView>
      {text && (
        <Text style={[styles.text, textStyle]}>{text}</Text>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    justifyContent: 'center',
    alignItems: 'center',
    padding: 0,
  },
  imageWrapper: {
    overflow: 'hidden', // Clips the edges
    justifyContent: 'center',
    alignItems: 'center',
  },
  image: {
    width: 80,
    height: 80,
  },
  text: {
    marginTop: 12,
    fontSize: 16,
    color: '#666',
    fontWeight: '500',
  },
});

