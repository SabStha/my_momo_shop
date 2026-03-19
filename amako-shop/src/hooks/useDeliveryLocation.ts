import { useState } from 'react';
import { Alert } from 'react-native';
import * as Location from 'expo-location';

/**
 * Hook to handle delivery location fetching via GPS and reverse geocoding.
 * 
 * @param setValue - react-hook-form's setValue function to auto-fill address fields
 */
export const useDeliveryLocation = (setValue: (name: any, value: any, config?: any) => void) => {
    const [location, setLocation] = useState<{
        latitude: number;
        longitude: number;
        address?: string;
    } | null>(null);
    const [isLoadingLocation, setIsLoadingLocation] = useState(false);
    const [locationError, setLocationError] = useState<string | null>(null);

    const handleGetLocation = async () => {
        setIsLoadingLocation(true);
        setLocationError(null);
        try {
            // Request permission
            const { status } = await Location.requestForegroundPermissionsAsync();

            if (status !== 'granted') {
                const errorMsg = 'Location permission denied';
                setLocationError(errorMsg);
                Alert.alert(
                    'Permission Denied',
                    'Please enable location permissions in your device settings to use GPS location for delivery.',
                    [{ text: 'OK' }]
                );
                setIsLoadingLocation(false);
                return;
            }

            // Get current location
            const result = await Location.getCurrentPositionAsync({
                accuracy: Location.Accuracy.High,
            });

            console.log('📍 GPS Location obtained:', result.coords);

            // Try to reverse geocode to get address
            try {
                const reverseGeocode = await Location.reverseGeocodeAsync({
                    latitude: result.coords.latitude,
                    longitude: result.coords.longitude,
                });

                if (reverseGeocode && reverseGeocode.length > 0) {
                    const addressData = reverseGeocode[0];
                    const addressString = [
                        addressData.street,
                        addressData.district,
                        addressData.city,
                        addressData.region,
                    ].filter(Boolean).join(', ');

                    console.log('📍 Reverse geocoded address:', addressString);

                    setLocation({
                        latitude: result.coords.latitude,
                        longitude: result.coords.longitude,
                        address: addressString,
                    });

                    // Auto-fill address fields if available
                    if (addressData.city) {
                        setValue('city', addressData.city, { shouldValidate: true });
                    }
                    if (addressString) {
                        setValue('address', addressString, { shouldValidate: true });
                    }

                    Alert.alert(
                        'Location Captured',
                        `GPS coordinates saved! Your location will be shared with the delivery driver.\n\nCoordinates: ${result.coords.latitude.toFixed(6)}, ${result.coords.longitude.toFixed(6)}`,
                        [{ text: 'OK' }]
                    );
                } else {
                    // No address found, just save coordinates
                    setLocation({
                        latitude: result.coords.latitude,
                        longitude: result.coords.longitude,
                    });

                    Alert.alert(
                        'Location Captured',
                        `GPS coordinates saved successfully!\n\nLat: ${result.coords.latitude.toFixed(6)}\nLng: ${result.coords.longitude.toFixed(6)}\n\nPlease enter your address manually below.`,
                        [{ text: 'OK' }]
                    );
                }
            } catch (geocodeError) {
                console.log('⚠️ Reverse geocoding failed:', geocodeError);

                // Save coordinates anyway
                setLocation({
                    latitude: result.coords.latitude,
                    longitude: result.coords.longitude,
                });

                Alert.alert(
                    'Location Captured',
                    `GPS coordinates saved!\n\nLat: ${result.coords.latitude.toFixed(6)}\nLng: ${result.coords.longitude.toFixed(6)}\n\nPlease enter your address manually.`,
                    [{ text: 'OK' }]
                );
            }
        } catch (error: any) {
            console.error('❌ Error getting location:', error);
            setLocationError(error.message || 'Error getting location');
            Alert.alert(
                'Location Error',
                'Unable to get your location. Please make sure GPS is enabled and try again, or enter your address manually.',
                [{ text: 'OK' }]
            );
        } finally {
            setIsLoadingLocation(false);
        }
    };

    return {
        location,
        isLoadingLocation,
        handleGetLocation,
        locationError
    };
};
