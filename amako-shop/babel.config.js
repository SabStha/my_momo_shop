// babel.config.js
module.exports = function (api) {
  api.cache(true);
  return {
    presets: ['babel-preset-expo'],
    plugins: [
      // 1) Required for Expo Router (must be before worklets)
      'expo-router/babel',

      // 2) Reanimated v3 migration uses Worklets plugin (not reanimated/plugin)
      'react-native-worklets/plugin',

      // 3) If you use NativeWind/Tailwind, uncomment:
      // 'nativewind/babel',
    ],
  };
};
