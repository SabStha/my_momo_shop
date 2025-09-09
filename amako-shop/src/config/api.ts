import { Platform } from 'react-native';

const LAN_IP = '192.168.2.130'; // dev: set to your PC LAN IP for physical device testing

export const BASE_URL =
  __DEV__
    ? Platform.select({
        android: 'http://10.0.2.2:8000/api', // emulator
        ios: 'http://localhost:8000/api',
        default: `http://${LAN_IP}:8000/api`,
      })!
    : 'https://api.example.com/api'; // TODO: prod base

export const API_CONFIG = {
  BASE_URL,
  TIMEOUT: 15000,
  ENV: __DEV__ ? 'development' : 'production',
} as const;
