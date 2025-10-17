import React, { useEffect, useState, useRef } from 'react';
import { View, Text, StyleSheet, Dimensions, Image } from 'react-native';
import { Redirect } from 'expo-router';
import { Video, ResizeMode } from 'expo-av';
import { colors } from '../src/ui/tokens';

const { width, height } = Dimensions.get('window');

export default function AppIndex() {
  const [showSplash, setShowSplash] = useState(true);
  const [animationFinished, setAnimationFinished] = useState(false);
  const [hasVideoError, setHasVideoError] = useState(false);
  const [videoLoaded, setVideoLoaded] = useState(false);
  const videoRef = useRef<Video>(null);

  // Use open.mp4 for splash screen animation
  const splashSource = require('../assets/animations/open.mp4');

  useEffect(() => {
    console.log('🎬 Splash screen mounted');
    
    // Auto-hide splash after 8 seconds as fallback (longer for video)
    const timer = setTimeout(() => {
      console.log('🎬 Splash timeout reached, hiding splash');
      setShowSplash(false);
    }, 8000);

    return () => {
      console.log('🎬 Splash screen unmounted');
      clearTimeout(timer);
    };
  }, []);

  // Handle video finish
  const handleVideoEnd = () => {
    console.log('🎬 Opening animation finished');
    // Wait at least 2 seconds before allowing splash to end
    setTimeout(() => {
      setAnimationFinished(true);
    }, 2000);
  };

  // Handle video load
  const handleVideoLoad = (status: any) => {
    if (status.isLoaded && !videoLoaded) {
      setVideoLoaded(true);
      const duration = status.durationMillis;
      const durationSeconds = Math.round(duration / 1000);
      console.log(`🎬 Opening video loaded - Duration: ${durationSeconds}s`);
    }
  };

  // Handle video error
  const handleVideoError = (error: any) => {
    console.log('🎬 Opening video error:', error);
    setHasVideoError(true);
    // Still hide splash after error
    setTimeout(() => {
      setShowSplash(false);
    }, 1000);
  };

  // If splash is done, redirect
  if (!showSplash || animationFinished) {
    console.log('🎬 Redirecting to login - showSplash:', showSplash, 'animationFinished:', animationFinished);
    return <Redirect href="/(auth)/login" />;
  }

  // Show splash screen with opening animation
  return (
    <View style={styles.container}>
      <View style={styles.videoContainer}>
        {!hasVideoError ? (
          <>
            <Video
              ref={videoRef}
              source={splashSource}
              style={styles.video}
              resizeMode={ResizeMode.CONTAIN}
              shouldPlay
              isLooping={false}
              isMuted
              useNativeControls={false}
              onPlaybackStatusUpdate={(status) => {
                if (status.isLoaded) {
                  handleVideoLoad(status);
                  if (status.didJustFinish) {
                    handleVideoEnd();
                  }
                }
              }}
              onError={handleVideoError}
            />
            {!videoLoaded && (
              <View style={styles.loadingOverlay}>
                <Text style={styles.loadingText}>🎬</Text>
                <Text style={styles.loadingSubtext}>Loading...</Text>
              </View>
            )}
          </>
        ) : (
          <View style={styles.fallbackContainer}>
            <Text style={styles.fallbackText}>🎬</Text>
            <Text style={styles.fallbackSubtext}>Amako Shop</Text>
          </View>
        )}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.white,
    justifyContent: 'center',
    alignItems: 'center',
  },
  videoContainer: {
    width: width * 0.8,
    height: height * 0.5,
    maxWidth: 400,
    maxHeight: 400,
    justifyContent: 'center',
    alignItems: 'center',
  },
  video: {
    width: '100%',
    height: '100%',
  },
  fallbackContainer: {
    width: '100%',
    height: '100%',
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: colors.gray[100],
    borderRadius: 12,
  },
  fallbackText: {
    fontSize: 80,
    marginBottom: 16,
  },
  fallbackSubtext: {
    fontSize: 24,
    fontWeight: 'bold',
    color: colors.gray[700],
  },
  loadingOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: colors.white,
  },
  loadingText: {
    fontSize: 60,
    marginBottom: 16,
  },
  loadingSubtext: {
    fontSize: 18,
    color: colors.gray[600],
  },
});
