import { Redirect } from 'expo-router';

export default function AppIndex() {
  // This will be handled by RouteGuard
  // For now, redirect to auth to let RouteGuard handle the logic
  return <Redirect href="/(auth)/login" />;
}
