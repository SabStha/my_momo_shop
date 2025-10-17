import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { client } from './client';

export interface UserProfile {
  id: string;
  name: string;
  email: string;
  phone?: string;
  city?: string;
  ward_number?: string;
  area_locality?: string;
  building_name?: string;
  detailed_directions?: string;
}

export interface UpdateProfileData {
  name?: string;
  email?: string;
  phone?: string;
  city?: string;
  address?: string; // Combined address for checkout compatibility
  deliveryInstructions?: string;
}

// Fetch user profile
const fetchUserProfile = async (): Promise<UserProfile> => {
  try {
    console.log('👤 Fetching user profile...');
    const response = await client.get('/user/profile');
    console.log('👤 User profile response:', response.data);
    
    if (response.data?.success && response.data?.data) {
      return response.data.data;
    }
    
    throw new Error('Invalid profile response');
  } catch (error) {
    console.error('👤 Error fetching profile:', error);
    throw error;
  }
};

// Update user profile
const updateUserProfile = async (data: UpdateProfileData): Promise<UserProfile> => {
  try {
    console.log('👤 Updating user profile:', data);
    
    // Transform combined address into detailed fields if provided
    const profileData: any = {
      name: data.name,
      email: data.email,
      phone: data.phone,
      city: data.city,
    };
    
    // If address is provided, save it as area_locality
    if (data.address) {
      profileData.area_locality = data.address;
    }
    
    // If deliveryInstructions provided, save as detailed_directions
    if (data.deliveryInstructions) {
      profileData.detailed_directions = data.deliveryInstructions;
    }
    
    const response = await client.put('/user/profile', profileData);
    console.log('👤 Profile update response:', response.data);
    
    if (response.data?.success && response.data?.data) {
      return response.data.data;
    }
    
    throw new Error('Invalid profile update response');
  } catch (error) {
    console.error('👤 Error updating profile:', error);
    throw error;
  }
};

// Hook to fetch user profile
export function useUserProfile() {
  return useQuery({
    queryKey: ['userProfile'],
    queryFn: fetchUserProfile,
    staleTime: 1000 * 60 * 5, // 5 minutes
    retry: 1,
    // Don't throw errors, just return undefined - form will work without auto-fill
    useErrorBoundary: false,
  });
}

// Hook to update user profile
export function useUpdateUserProfile() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: updateUserProfile,
    onSuccess: (data) => {
      console.log('👤 Profile updated successfully:', data);
      // Invalidate and refetch user profile
      queryClient.invalidateQueries({ queryKey: ['userProfile'] });
    },
    onError: (error) => {
      console.error('👤 Profile update failed:', error);
    },
  });
}

