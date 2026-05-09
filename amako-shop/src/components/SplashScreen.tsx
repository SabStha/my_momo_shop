import React, { useEffect, useState, useRef } from 'react';
import { View, Text, StyleSheet, Dimensions } from 'react-native';
import { VideoView, useVideoPlayer } from 'expo-video';
import { colors } from '../ui/tokens';

const { width, height } = Dimensions.get('window');

interface SplashScreenProps {
  onFinish: () => void;
}

export function SplashScreen({ onFinish }: SplashScreenProps) {
  const [videoLoaded, setVideoLoaded] = useState(false);
  const startTimeRef = useRef<number>(Date.now());

  // Use open.mp4 for splash screen animation
  const splashSource = require('../../assets/animations/open.mp4');
  
  // Create video player with expo-video
  const player = useVideoPlayer(splashSource, (player) => {
    player.loop = false;
    player.muted = true;
  });

  useEffect(() => {
    startTimeRef.current = Date.now();

    // Start playing the video
    player.play();

    // Fallback timeout - hide after 10 seconds maximum (reduced by 3s)
    const fallbackTimer = setTimeout(() => {
      onFinish();
    }, 10000);

    return () => {
      clearTimeout(fallbackTimer);
    };
  }, [onFinish, player]);

  // Handle video finish
  const handleVideoEnd = () => {
    const elapsed = Date.now() - startTimeRef.current;
    const minDisplayTime = 3000; // Minimum 3 seconds display for premium feel

    // Ensure splash shows for at least 3 seconds
    if (elapsed < minDisplayTime) {
      const remainingTime = minDisplayTime - elapsed;
      setTimeout(() => {
        onFinish();
      }, remainingTime);
    } else {
      onFinish();
    }
  };

  // Handle video load
  const handleVideoLoad = () => {
    if (!videoLoaded) {
      setVideoLoaded(true);
    }
  };

  // Handle video error
  const handleVideoError = (error: any) => {
    // Show fallback for 2 seconds then finish (fast for premium feel)
    setTimeout(() => {
      onFinish();
    }, 2000);
  };

  return (
    <View style={styles.container}>
      <VideoView
        player={player}
        style={styles.video}
        nativeControls={false}
        contentFit="cover"
        onLoadStart={handleVideoLoad}
        onPlaybackStatusUpdate={(status) => {
          if (status.isLoaded && status.didJustFinish) {
            handleVideoEnd();
          }
        }}
        onError={handleVideoError}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    width: '100%',
    height: '100%',
    backgroundColor: colors.white,
    zIndex: 9999,
  },
  video: {
    width: '100%',
    height: '100%',
    position: 'absolute',
    top: 0,
    left: 0,
  },
});

