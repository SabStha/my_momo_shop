// Metro configuration for React Native
const { getDefaultConfig } = require('expo/metro-config');

const config = getDefaultConfig(__dirname);

// Configure source maps to reduce InternalBytecode.js errors
config.symbolicator = {
  ...config.symbolicator,
  customizeFrame: (frame) => {
    // Skip InternalBytecode frames in stack traces
    if (frame.file && frame.file.includes('InternalBytecode.js')) {
      return null;
    }
    return frame;
  },
};

module.exports = config;

