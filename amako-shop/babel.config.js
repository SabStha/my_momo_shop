// babel.config.js
module.exports = function (api) {
  api.cache(true);
  return {
    presets: ['babel-preset-expo'],
    plugins: [
      // 1) Reanimated v3 uses the reanimated plugin
      'react-native-reanimated/plugin',

      // 2) If you use NativeWind/Tailwind, uncomment:
      // 'nativewind/babel',
    ],
  };
};

