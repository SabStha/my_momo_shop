import React from 'react';
import { View, Image, Text, StyleSheet, ViewStyle, TextStyle } from 'react-native';
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
  
  return (
    <View style={[styles.container, style]}>
      {/* Smaller size with blur background to hide glitch */}
      <BlurView 
        intensity={80}
        tint="light"
        style={[
          styles.imageWrapper, 
          { 
            width: imageSize * 1.2, // Smaller but still visible
            height: imageSize * 1.2,
            borderRadius: (imageSize * 1.2) / 2,
          }
        ]}
      >
        <Image
          source={require('../../assets/animations/loading.gif')}
          style={[styles.image, { 
            width: imageSize * 1.2, 
            height: imageSize * 1.2,
          }]}
          resizeMode="cover"
        />
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

