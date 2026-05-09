import 'dotenv/config';

export default ({ config }) => {
  return {
    ...config,
    extra: {
      ...config.extra,
      apiUrl: 'https://amakomomo.com/api',
      eas: {
        projectId: '49bf83d7-b943-4e7d-8cba-ad689654326b',
      },
    },
    ios: {
      ...config.ios,
      config: {
        ...config.ios?.config,
        googleMapsApiKey: process.env.GOOGLE_MAPS_API_KEY,
      },
    },
    android: {
      ...config.android,
      config: {
        ...config.android?.config,
        googleMaps: {
          apiKey: process.env.GOOGLE_MAPS_API_KEY,
        },
      },
    },
  };
};
