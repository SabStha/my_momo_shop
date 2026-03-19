import { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';

/**
 * Zod validation schema for the checkout form
 */
export const checkoutSchema = z.object({
    name: z.string().min(2, 'Name must be at least 2 characters'),
    email: z.string().email('Please enter a valid email address'),
    phone: z.string().min(10, 'Phone number must be at least 10 digits'),
    address: z.string().min(10, 'Please enter a complete address'),
    city: z.string().min(2, 'Please enter a valid city'),
    deliveryInstructions: z.string().optional(),
});

export type CheckoutFormData = z.infer<typeof checkoutSchema>;

/**
 * Hook to manage checkout form state, validation, and auto-fill logic.
 * 
 * @param user - Current session user
 * @param userProfile - User profile data from API
 */
export const useCheckoutForm = (user: any, userProfile: any) => {
    const form = useForm<CheckoutFormData>({
        resolver: zodResolver(checkoutSchema),
        mode: 'onBlur',
        defaultValues: {
            name: '',
            email: '',
            phone: '',
            address: '',
            city: '',
            deliveryInstructions: '',
        },
    });

    const { setValue, trigger } = form;

    // Auto-fill form from user profile
    useEffect(() => {
        if (userProfile) {
            console.log('📝 Auto-filling checkout form with user data:', userProfile);

            // Auto-fill from session user (name and email)
            if (user?.name) {
                setValue('name', user.name, { shouldValidate: true });
            }
            if (user?.email) {
                setValue('email', user.email, { shouldValidate: true });
            }
            if (user?.phone || (userProfile as any)?.phone) {
                setValue('phone', user.phone || (userProfile as any)?.phone || '', { shouldValidate: true });
            }

            // Auto-fill delivery address from profile
            if ((userProfile as any)?.city) {
                setValue('city', (userProfile as any).city, { shouldValidate: true });
            }
            if ((userProfile as any)?.area_locality) {
                setValue('address', (userProfile as any).area_locality, { shouldValidate: true });
            }
            if ((userProfile as any)?.detailed_directions) {
                setValue('deliveryInstructions', (userProfile as any).detailed_directions, { shouldValidate: true });
            }

            // Trigger validation for the entire form after auto-fill
            trigger();
        }
    }, [userProfile, user, setValue, trigger]);

    return form;
};
