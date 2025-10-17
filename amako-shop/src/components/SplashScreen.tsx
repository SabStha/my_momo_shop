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
    console.log('🎬 Splash screen mounted - playing opening video');
    startTimeRef.current = Date.now();

    // Start playing the video
    player.play();

    // Fallback timeout - hide after 10 seconds maximum to allow video to load and play
    const fallbackTimer = setTimeout(() => {
      console.log('🎬 Splash fallback timeout reached (10s), finishing splash');
      onFinish();
    }, 10000);

    return () => {
      const elapsed = Math.round((Date.now() - startTimeRef.current) / 1000);
      console.log(`🎬 Splash screen unmounting - displayed for ${elapsed}s`);
      clearTimeout(fallbackTimer);
    };
  }, [onFinish, player]);

  // Handle video finish
  const handleVideoEnd = () => {
    const elapsed = Date.now() - startTimeRef.current;
    const minDisplayTime = 3000; // Minimum 3 seconds display
    
    console.log(`🎬 Video playback finished after ${Math.round(elapsed / 1000)}s`);
    
    // Ensure splash shows for at least 3 seconds
    if (elapsed < minDisplayTime) {
      const remainingTime = minDisplayTime - elapsed;
      console.log(`🎬 Waiting ${Math.round(remainingTime / 1000)}s more to meet minimum display time`);
      setTimeout(() => {
        console.log('🎬 Minimum display time met, transitioning to app');
        onFinish();
      }, remainingTime);
    } else {
      console.log('🎬 Transitioning to app immediately');
      onFinish();
    }
  };

  // Handle video load
  const handleVideoLoad = () => {
    if (!videoLoaded) {
      setVideoLoaded(true);
      const loadTime = Math.round((Date.now() - startTimeRef.current) / 1000);
      console.log(`🎬 Video loaded after ${loadTime}s`);
    }
  };

  // Handle video error
  const handleVideoError = (error: any) => {
    console.log('🎬 Opening video error:', error);
    // Show fallback for 2 seconds then finish
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
    flex: 1,
    backgroundColor: colors.white,
    zIndex: 9999,
  },
  video: {
    width: width,
    height: height,
  },
});

