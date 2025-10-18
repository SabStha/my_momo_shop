import React, { useEffect, useRef, useState } from 'react';
import { View, StyleSheet, Platform, Dimensions, Image, TouchableOpacity } from 'react-native';
import MapView, { Marker, PROVIDER_GOOGLE, AnimatedRegion, Polyline } from 'react-native-maps';
import { Ionicons } from '@expo/vector-icons';
import { Easing } from 'react-native';
import DeliveryNotificationService from '../../services/DeliveryNotificationService';
import { DeliveryNotificationData } from '../../services/NativeNotificationService';

const { width } = Dimensions.get('window');
const ASPECT_RATIO = width / Dimensions.get('window').height;
const LATITUDE_DELTA = 0.01;
const LONGITUDE_DELTA = LATITUDE_DELTA * ASPECT_RATIO;

// REQUIRED: Calculate bearing from previous to next point
const calculateBearing = (from: {latitude: number, longitude: number}, to: {latitude: number, longitude: number}) => {
  const r = Math.PI / 180;
  const y = Math.sin((to.longitude - from.longitude) * r) * Math.cos(to.latitude * r);
  const x = Math.cos(from.latitude * r) * Math.cos(to.latitude * r) - Math.sin(from.latitude * r) * Math.sin(to.latitude * r) * Math.cos((to.longitude - from.longitude) * r);
  return (Math.atan2(y, x) / r + 360) % 360;
};

// Calculate distance between two coordinates in meters (Haversine formula)
const getDistance = (from: {latitude: number, longitude: number}, to: {latitude: number, longitude: number}): number => {
  const R = 6371e3; // Earth's radius in meters
  const φ1 = from.latitude * Math.PI / 180;
  const φ2 = to.latitude * Math.PI / 180;
  const Δφ = (to.latitude - from.latitude) * Math.PI / 180;
  const Δλ = (to.longitude - from.longitude) * Math.PI / 180;

  const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
            Math.cos(φ1) * Math.cos(φ2) *
            Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

  return R * c; // Distance in meters
};

// Google Navigation style - Detailed turn-by-turn view
const CUSTOM_MAP_STYLE = [
  // Hide POI labels and clutter
  {
    "featureType": "poi",
    "elementType": "labels",
    "stylers": [{ "visibility": "off" }]
  },
  {
    "featureType": "poi.business",
    "stylers": [{ "visibility": "off" }]
  },
  {
    "featureType": "transit",
    "stylers": [{ "visibility": "off" }]
  },
  // Enhanced roads for navigation view
  {
    "featureType": "road",
    "elementType": "geometry",
    "stylers": [
      { "color": "#ffffff" },
      { "weight": 2.5 },
      { "visibility": "on" }
    ]
  },
  {
    "featureType": "road",
    "elementType": "geometry.stroke",
    "stylers": [
      { "color": "#cfcdca" },
      { "weight": 1 }
    ]
  },
  // Road labels - clearly visible for navigation
  {
    "featureType": "road",
    "elementType": "labels.text.fill",
    "stylers": [
      { "color": "#2c2c2c" },
      { "weight": 0.5 }
    ]
  },
  {
    "featureType": "road",
    "elementType": "labels.text.stroke",
    "stylers": [
      { "color": "#ffffff" },
      { "weight": 2 }
    ]
  },
  // Highways - prominent for navigation
  {
    "featureType": "road.highway",
    "elementType": "geometry",
    "stylers": [
      { "color": "#ffde5a" },
      { "weight": 3 }
    ]
  },
  {
    "featureType": "road.highway",
    "elementType": "geometry.stroke",
    "stylers": [
      { "color": "#e6a200" },
      { "weight": 1.5 }
    ]
  },
  {
    "featureType": "road.highway",
    "elementType": "labels.text.fill",
    "stylers": [{ "color": "#2c2c2c" }]
  },
  {
    "featureType": "road.highway",
    "elementType": "labels.text.stroke",
    "stylers": [
      { "color": "#ffffff" },
      { "weight": 3 }
    ]
  },
  // Arterial roads - clear for directions
  {
    "featureType": "road.arterial",
    "elementType": "geometry",
    "stylers": [
      { "color": "#ffffff" },
      { "weight": 2 }
    ]
  },
  {
    "featureType": "road.arterial",
    "elementType": "labels.text.fill",
    "stylers": [{ "color": "#2c2c2c" }]
  },
  // Local roads
  {
    "featureType": "road.local",
    "elementType": "geometry",
    "stylers": [
      { "color": "#ffffff" },
      { "weight": 1.5 }
    ]
  },
  {
    "featureType": "road.local",
    "elementType": "labels.text.fill",
    "stylers": [{ "color": "#646464" }]
  },
  // Water - subtle
  {
    "featureType": "water",
    "elementType": "geometry",
    "stylers": [{ "color": "#c8ddf5" }]
  },
  // Background - light for navigation
  {
    "featureType": "landscape",
    "elementType": "geometry",
    "stylers": [{ "color": "#f5f5f5" }]
  },
  // Parks - subtle green
  {
    "featureType": "poi.park",
    "elementType": "geometry.fill",
    "stylers": [{ "color": "#e5f3d6" }]
  },
  // Buildings - very subtle
  {
    "featureType": "landscape.man_made",
    "elementType": "geometry.fill",
    "stylers": [{ "color": "#ebebeb" }]
  },
  // Administrative - hide for clean view
  {
    "featureType": "administrative",
    "elementType": "labels",
    "stylers": [{ "visibility": "off" }]
  }
];

interface LiveTrackingMapProps {
  driverLocation: {
    latitude: number;
    longitude: number;
  } | null;
  deliveryLocation: {
    latitude: number;
    longitude: number;
  } | null;
  routeCoordinates?: Array<{
    latitude: number;
    longitude: number;
  }>;
  onMapReady?: () => void;
  onRouteInfoUpdate?: (info: { distance: string; duration: string; eta: string }) => void;
}

export default function LiveTrackingMap({
  driverLocation,
  deliveryLocation,
  routeCoordinates,
  onMapReady,
  onRouteInfoUpdate,
}: LiveTrackingMapProps) {
  const mapRef = useRef<MapView>(null);
  const [realRouteCoordinates, setRealRouteCoordinates] = useState<Array<{latitude: number, longitude: number}>>([]);
  
  // Store initial region ONCE and never change it
  const initialRegionRef = useRef(
    driverLocation
      ? {
          latitude: driverLocation.latitude,
          longitude: driverLocation.longitude,
          latitudeDelta: LATITUDE_DELTA,
          longitudeDelta: LONGITUDE_DELTA,
        }
      : deliveryLocation
      ? {
          latitude: deliveryLocation.latitude,
          longitude: deliveryLocation.longitude,
          latitudeDelta: LATITUDE_DELTA,
          longitudeDelta: LONGITUDE_DELTA,
        }
      : {
          latitude: 27.7172,
          longitude: 85.324,
          latitudeDelta: LATITUDE_DELTA,
          longitudeDelta: LONGITUDE_DELTA,
        }
  );
  
  // Driver marker animation with smooth movement
  const driverCoordinate = useRef(
    new AnimatedRegion({
      latitude: driverLocation?.latitude || 0,
      longitude: driverLocation?.longitude || 0,
      latitudeDelta: 0,
      longitudeDelta: 0,
    })
  ).current;
  
  // Track driver heading (bearing) for rotation
  const [driverBearing, setDriverBearing] = useState(0);
  const [isUserInteracting, setIsUserInteracting] = useState(false);
  const previousLocation = useRef<{latitude: number, longitude: number} | null>(null);
  const lastUpdateTime = useRef<number>(Date.now());
  
  // Track user interaction more precisely
  const interactionTimeoutRef = useRef<any>(null);
  const isUserZoomingRef = useRef(false);
  const userInteractionStartTime = useRef<number>(0);
  
  // Helper to set user interaction state with proper timeout handling
  const setUserInteractionWithTimeout = (interacting: boolean, delay: number = 5000) => {
    // Clear any existing timeout
    if (interactionTimeoutRef.current) {
      clearTimeout(interactionTimeoutRef.current);
      interactionTimeoutRef.current = null;
    }
    
    if (interacting) {
      setIsUserInteracting(true);
      userInteractionStartTime.current = Date.now();
    } else {
      // Set timeout to resume camera following after delay
      interactionTimeoutRef.current = setTimeout(() => {
        setIsUserInteracting(false);
        isUserZoomingRef.current = false;
        console.log('✅ User interaction timeout - resuming camera following');
      }, delay);
    }
  };
  
  // Cleanup timeout on unmount
  useEffect(() => {
    return () => {
      if (interactionTimeoutRef.current) {
        clearTimeout(interactionTimeoutRef.current);
      }
    };
  }, []);
  
  // Use the global calculateBearing function (defined at top of file)
  
  // Calculate distance between two points (in meters)
  const calculateDistance = (start: {latitude: number, longitude: number}, end: {latitude: number, longitude: number}) => {
    const R = 6371e3; // Earth radius in meters
    const φ1 = start.latitude * Math.PI / 180;
    const φ2 = end.latitude * Math.PI / 180;
    const Δφ = (end.latitude - start.latitude) * Math.PI / 180;
    const Δλ = (end.longitude - start.longitude) * Math.PI / 180;

    const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ/2) * Math.sin(Δλ/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

    return R * c; // Distance in meters
  };

  // Smooth driver movement with throttling and bearing calculation
  useEffect(() => {
    if (!driverLocation) return;
    
    // Check if location ACTUALLY changed (not just a new object)
    if (previousLocation.current && 
        previousLocation.current.latitude === driverLocation.latitude &&
        previousLocation.current.longitude === driverLocation.longitude) {
      // Location hasn't changed, don't do anything
      return;
    }
    
    console.log('🗺️ LiveTrackingMap: Driver location ACTUALLY changed:', driverLocation);
    
    const now = Date.now();
    const timeSinceLastUpdate = now - lastUpdateTime.current;
    
    // Throttle updates to 500ms
    if (timeSinceLastUpdate < 500) {
      console.log('⏸️ Throttled - too soon since last update');
      return;
    }
    
    // Check if driver moved significantly (>1 meters)
    if (previousLocation.current) {
      const distance = calculateDistance(previousLocation.current, driverLocation);
      
      if (distance < 1) {
        console.log('⏸️ Driver moved < 1m, ignoring');
        return;
      }
      
      // Calculate bearing (heading) based on movement
      const bearing = calculateBearing(previousLocation.current, driverLocation);
      setDriverBearing(bearing);
      
      console.log('🗺️ Driver moved:', { distance: Math.round(distance), bearing: Math.round(bearing) });
    }
    
    // Animate marker smoothly to new position
    const newCoordinate = {
      latitude: driverLocation.latitude,
      longitude: driverLocation.longitude,
      latitudeDelta: 0,
      longitudeDelta: 0,
    };
    
    // @ts-ignore - AnimatedRegion typing issue with react-native-maps
    driverCoordinate.timing(newCoordinate).start();
    
    // REMOVED: Camera animation - user has full control
    // The map will stay wherever the user zooms/pans it
    
    // Update tracking
    previousLocation.current = driverLocation;
    lastUpdateTime.current = now;
  }, [driverLocation?.latitude, driverLocation?.longitude, driverBearing, isUserInteracting]);

  // Get real road directions from Google Directions API
  useEffect(() => {
    const getDirections = async () => {
      if (!driverLocation || !deliveryLocation) return;
      
      try {
        // Use Google Directions API to get real road route
        const origin = `${driverLocation.latitude},${driverLocation.longitude}`;
        const destination = `${deliveryLocation.latitude},${deliveryLocation.longitude}`;
        const apiKey = 'AIzaSyCgas0A0JVwVLZefRXJ-e4qpkam1TdEf2A'; // Your Google Maps API key
        
        const response = await fetch(
          `https://maps.googleapis.com/maps/api/directions/json?origin=${origin}&destination=${destination}&mode=driving&key=${apiKey}`
        );
        
        const data = await response.json();
        
        if (data.routes && data.routes.length > 0) {
          const route = data.routes[0];
          const leg = route.legs[0];
          const points: Array<{latitude: number, longitude: number}> = [];
          
          // Extract distance and duration from the route
          const distance = leg.distance.text; // e.g., "2.5 km"
          const duration = leg.duration.text; // e.g., "8 mins"
          const durationInSeconds = leg.duration.value; // Duration in seconds
          
          // Calculate ETA
          const now = new Date();
          const eta = new Date(now.getTime() + durationInSeconds * 1000);
          const etaFormatted = eta.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true 
          });
          
          // Call callback with route info
          if (onRouteInfoUpdate) {
            onRouteInfoUpdate({
              distance,
              duration,
              eta: etaFormatted
            });
          }
          
          // Removed excessive route logging for performance
          
          // Decode the polyline to get route coordinates
          route.legs.forEach((leg: any) => {
            leg.steps.forEach((step: any) => {
              const decoded = decodePolyline(step.polyline.points);
              points.push(...decoded);
            });
          });
          
          setRealRouteCoordinates(points);
          
          // REMOVED: fitToCoordinates - let user control zoom manually
        }
      } catch (error) {
        console.error('Failed to get directions:', error);
        // Fallback to simple route if API fails
        if (driverLocation && deliveryLocation) {
          const simpleRoute = [
            driverLocation,
            deliveryLocation
          ];
          setRealRouteCoordinates(simpleRoute);
        }
      }
    };
    
    getDirections();
  }, [driverLocation, deliveryLocation]);

  // Helper function to decode Google polyline
  const decodePolyline = (encoded: string) => {
    const poly = [];
    let index = 0;
    const len = encoded.length;
    let lat = 0;
    let lng = 0;

    while (index < len) {
      let b, shift = 0, result = 0;
      do {
        b = encoded.charCodeAt(index++) - 63;
        result |= (b & 0x1f) << shift;
        shift += 5;
      } while (b >= 0x20);
      const dlat = ((result & 1) ? ~(result >> 1) : (result >> 1));
      lat += dlat;

      shift = 0;
      result = 0;
      do {
        b = encoded.charCodeAt(index++) - 63;
        result |= (b & 0x1f) << shift;
        shift += 5;
      } while (b >= 0x20);
      const dlng = ((result & 1) ? ~(result >> 1) : (result >> 1));
      lng += dlng;

      poly.push({
        latitude: lat / 1e5,
        longitude: lng / 1e5,
      });
    }
    return poly;
  };


  // Reset map to initial view
  const resetToInitialView = () => {
    if (mapRef.current && driverLocation) {
      mapRef.current.animateCamera({
        center: {
          latitude: driverLocation.latitude,
          longitude: driverLocation.longitude,
        },
        pitch: 45,
        heading: 0,
        zoom: 15,
      }, { duration: 1000 });
    }
  };

  return (
    <View style={styles.container}>
      <MapView
        ref={mapRef}
        provider={PROVIDER_GOOGLE}
        style={styles.map}
        mapType="standard"
        initialRegion={initialRegionRef.current}
        customMapStyle={CUSTOM_MAP_STYLE}
        showsUserLocation={false}
        showsMyLocationButton={false}
        showsCompass={false}
        showsScale={false}
        showsBuildings={false}
        showsTraffic={false}
        showsIndoors={false}
        showsPointsOfInterest={false}
        toolbarEnabled={false}
        loadingEnabled={true}
        loadingIndicatorColor="#A43E2D"
        loadingBackgroundColor="#FFFFFF"
        scrollEnabled={true}
        zoomEnabled={true}
        zoomControlEnabled={false}
        pitchEnabled={true}
        rotateEnabled={true}
        minZoomLevel={12}
        maxZoomLevel={20}
        onTouchStart={() => {
          // User started touching the map - PERMANENTLY DISABLE camera following
          console.log('👆 User touch start - PERMANENTLY DISABLING camera following');
          setIsUserInteracting(true);
          isUserZoomingRef.current = true;
          
          // Clear any existing timeouts - NO MORE AUTO-RESUME
          if (interactionTimeoutRef.current) {
            clearTimeout(interactionTimeoutRef.current);
            interactionTimeoutRef.current = null;
          }
        }}
        onTouchEnd={() => {
          // User finished touching
          console.log('👆 User touch end');
        }}
      >
        {/* Driver Marker - Animated and Rotated */}
        {driverLocation && (
          <Marker.Animated
            coordinate={driverCoordinate as any}
            title="🛵 Delivery Driver"
            description="Your delivery driver is on the way"
            anchor={{ x: 0.5, y: 0.5 }}
            rotation={driverBearing - 90}
            flat={true}
          >
            <View style={styles.driverMarker}>
              <View style={styles.driverMarkerInner}>
                {/* Your custom delivery scooter icon */}
                <Image 
                  source={require('../../../assets/icons/delivery-scooter.png')}
                  style={styles.deliveryIcon}
                  resizeMode="contain"
                />
              </View>
              <View style={styles.driverPulse} />
            </View>
          </Marker.Animated>
        )}

        {/* Real Road Route Line - Navigation Style */}
        {realRouteCoordinates && realRouteCoordinates.length > 1 && (
          <>
            {/* Outer glow/shadow for the route */}
            <Polyline
              coordinates={realRouteCoordinates}
              strokeColor="#1a56db"
              strokeWidth={12}
              lineCap="round"
              lineJoin="round"
              zIndex={1}
            />
            {/* Main route line - Bold like navigation */}
            <Polyline
              coordinates={realRouteCoordinates}
              strokeColor="#4285F4"
              strokeWidth={8}
              lineCap="round"
              lineJoin="round"
              zIndex={2}
            />
          </>
        )}
        
        {/* Fallback Route Line (if Google Directions fails) */}
        {(!realRouteCoordinates || realRouteCoordinates.length <= 1) && routeCoordinates && routeCoordinates.length > 1 && (
          <Polyline
            coordinates={routeCoordinates}
            strokeColor="#3B82F6"
            strokeWidth={4}
            lineDashPattern={[5, 5]}
            lineCap="round"
            lineJoin="round"
          />
        )}

        {/* Delivery Location Marker */}
        {deliveryLocation && (
          <Marker
            coordinate={{
              latitude: deliveryLocation.latitude,
              longitude: deliveryLocation.longitude,
            }}
            title="Delivery Location"
            description="Your address"
          >
            <View style={styles.deliveryMarker}>
              <Ionicons name="home" size={28} color="#EF4444" />
            </View>
          </Marker>
        )}
      </MapView>
      
      {/* Reset to Initial View Button */}
      <TouchableOpacity 
        style={styles.resetButton}
        onPress={resetToInitialView}
      >
        <Ionicons name="locate" size={24} color="#152039" />
      </TouchableOpacity>
    </View>
  );
}

// Calculate delivery progress percentage
const calculateProgress = (driverLocation: any, deliveryLocation: any): number => {
  if (!driverLocation || !deliveryLocation) return 0;
  
  // Simple distance-based progress (can be enhanced with route distance)
  const totalDistance = getDistance(
    { latitude: driverLocation.latitude, longitude: driverLocation.longitude },
    { latitude: deliveryLocation.latitude, longitude: deliveryLocation.longitude }
  );
  
  // Assume progress based on remaining distance (inverse relationship)
  // This is simplified - ideally you'd track actual route progress
  const maxDistance = 10000; // 10km max distance assumption
  const progress = Math.max(0, Math.min(100, ((maxDistance - totalDistance) / maxDistance) * 100));
  
  return Math.round(progress);
};

// Add native notification integration
const updateNativeNotifications = (driverLocation: any, orderNumber: string, status: string, routeDetails: any, orderId: number, deliveryLocation: any) => {
  if (driverLocation && orderNumber && status) {
    const progress = calculateProgress(driverLocation, deliveryLocation);
    
    // Update native notification with enhanced data
    const nativeData: DeliveryNotificationData = {
      orderId: orderId.toString(),
      orderNumber,
      status,
      distance: routeDetails?.distance,
      duration: routeDetails?.duration,
      eta: routeDetails?.eta,
      progress,
      driverName: 'Driver Name', // You can get this from your data
      driverPhone: '+1234567890', // You can get this from your data
      restaurantName: 'Amako Momo',
    };

    DeliveryNotificationService.updateNativeNotification(nativeData);
  }
};

const styles = StyleSheet.create({
  container: {
    height: 400,
    width: '100%',
    borderRadius: 16,
    overflow: 'hidden',
    marginBottom: 16,
    position: 'relative',
  },
  map: {
    ...StyleSheet.absoluteFillObject,
  },
  resetButton: {
    position: 'absolute',
    bottom: 16,
    right: 16,
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: '#FFFFFF',
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 5,
  },
  driverMarker: {
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
  },
  driverMarkerInner: {
    alignItems: 'center',
    justifyContent: 'center',
    // NO background - transparent like Uber
  },
  driverPulse: {
    position: 'absolute',
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: '#3B82F6',
    opacity: 0.15, // Very subtle pulse
  },
  bikeIcon: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 2,
  },
  deliveryScooter: {
    width: 40,
    height: 30,
    position: 'relative',
    alignItems: 'center',
    justifyContent: 'center',
  },
  scooterBody: {
    width: 24,
    height: 8,
    backgroundColor: '#FFFFFF',
    borderRadius: 4,
    position: 'absolute',
    top: 12,
  },
  scooterBox: {
    width: 12,
    height: 16,
    backgroundColor: '#FFFFFF',
    borderRadius: 2,
    position: 'absolute',
    top: 4,
    right: 2,
  },
  scooterPerson: {
    width: 8,
    height: 12,
    backgroundColor: '#FFFFFF',
    borderRadius: 4,
    position: 'absolute',
    top: 6,
    left: 8,
  },
  deliveryIcon: {
    width: 50,
    height: 50,
    // Add shadow for better visibility
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.5,
    shadowRadius: 4,
    elevation: 6,
  },
  deliveryMarker: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#FFFFFF',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 2,
    borderColor: '#EF4444',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 5,
  },
});

