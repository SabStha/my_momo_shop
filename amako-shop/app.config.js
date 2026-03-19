import 'dotenv/config';
export default ({ config }) => {
  return {
    ...config,
    extra: {
      apiUrl: 'http://192.168.0.10:8000/api',
    },
    ios: {
      ...config.ios,
      config: {
        ...config.ios?.config,
        googleMapsApiKey: process.env.GOOGLE_MAPS_API_KEY
      }
    },
    android: {
      ...config.android,
      config: {
        ...config.android?.config,
        googleMaps: {
          apiKey: process.env.GOOGLE_MAPS_API_KEY
        }
      }
    }
  };
};
