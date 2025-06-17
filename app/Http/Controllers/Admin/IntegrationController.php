<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerSegment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IntegrationController extends Controller
{
    public function getMailchimpLists()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.mailchimp.api_key')
            ])->get('https://' . config('services.mailchimp.datacenter') . '.api.mailchimp.com/3.0/lists');

            return response()->json([
                'lists' => $response->json()['lists']
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching Mailchimp lists: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch Mailchimp lists'], 500);
        }
    }

    public function syncWithMailchimp(Request $request)
    {
        $request->validate([
            'segment_id' => 'required|exists:customer_segments,id',
            'list_id' => 'required|string'
        ]);

        try {
            $segment = CustomerSegment::findOrFail($request->segment_id);
            $customers = $segment->customers;

            // Prepare batch operation for Mailchimp
            $batch = [];
            foreach ($customers as $customer) {
                $batch[] = [
                    'email_address' => $customer->email,
                    'status' => 'subscribed',
                    'merge_fields' => [
                        'FNAME' => $customer->first_name,
                        'LNAME' => $customer->last_name
                    ]
                ];
            }

            // Send batch to Mailchimp
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.mailchimp.api_key')
            ])->post('https://' . config('services.mailchimp.datacenter') . '.api.mailchimp.com/3.0/lists/' . $request->list_id . '/members', [
                'members' => $batch,
                'update_existing' => true
            ]);

            return response()->json([
                'message' => 'Successfully synced segment with Mailchimp list'
            ]);
        } catch (\Exception $e) {
            Log::error('Error syncing with Mailchimp: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to sync with Mailchimp'], 500);
        }
    }

    public function getTwilioGroups()
    {
        try {
            $response = Http::withBasicAuth(
                config('services.twilio.account_sid'),
                config('services.twilio.auth_token')
            )->get('https://messaging.twilio.com/v1/Services');

            return response()->json([
                'groups' => $response->json()['services']
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching Twilio groups: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch Twilio groups'], 500);
        }
    }

    public function syncWithTwilio(Request $request)
    {
        $request->validate([
            'segment_id' => 'required|exists:customer_segments,id',
            'group_id' => 'required|string'
        ]);

        try {
            $segment = CustomerSegment::findOrFail($request->segment_id);
            $customers = $segment->customers;

            // Prepare batch operation for Twilio
            foreach ($customers as $customer) {
                if ($customer->phone) {
                    Http::withBasicAuth(
                        config('services.twilio.account_sid'),
                        config('services.twilio.auth_token')
                    )->post('https://messaging.twilio.com/v1/Services/' . $request->group_id . '/PhoneNumbers', [
                        'phone_number' => $customer->phone
                    ]);
                }
            }

            return response()->json([
                'message' => 'Successfully synced segment with Twilio group'
            ]);
        } catch (\Exception $e) {
            Log::error('Error syncing with Twilio: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to sync with Twilio'], 500);
        }
    }
} 