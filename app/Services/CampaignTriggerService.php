<?php

namespace App\Services;

use App\Models\CampaignTrigger;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class CampaignTriggerService
{
    public function processTriggers()
    {
        $triggers = CampaignTrigger::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('next_scheduled_at')
                    ->orWhere('next_scheduled_at', '<=', now());
            })
            ->get();

        foreach ($triggers as $trigger) {
            try {
                if ($this->shouldProcessTrigger($trigger)) {
                    $this->processTrigger($trigger);
                }
            } catch (\Exception $e) {
                Log::error('Error processing campaign trigger: ' . $e->getMessage(), [
                    'trigger_id' => $trigger->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    protected function shouldProcessTrigger(CampaignTrigger $trigger)
    {
        // Check if trigger is in cooldown period
        if ($trigger->last_triggered_at && 
            $trigger->last_triggered_at->addHours($trigger->cooldown_period) > now()) {
            return false;
        }

        // Check if trigger should activate based on its type
        switch ($trigger->trigger_type) {
            case 'behavioral':
                return $trigger->shouldTrigger();
            case 'scheduled':
                return $trigger->next_scheduled_at <= now();
            case 'segment':
                return $trigger->checkSegmentConditions();
            default:
                return false;
        }
    }

    protected function processTrigger(CampaignTrigger $trigger)
    {
        // Get users matching the trigger conditions
        $users = $trigger->getUsersMatchingCondition();

        if ($users->isEmpty()) {
            Log::info('No users found matching trigger conditions', [
                'trigger_id' => $trigger->id
            ]);
            return;
        }

        // Process campaign based on type
        switch ($trigger->campaign_type) {
            case 'email':
                $this->sendEmailCampaign($trigger, $users);
                break;
            case 'sms':
                $this->sendSmsCampaign($trigger, $users);
                break;
            case 'push':
                $this->sendPushCampaign($trigger, $users);
                break;
        }

        // Update trigger status
        $trigger->last_triggered_at = now();
        $trigger->updateNextScheduledTime();
        $trigger->save();

        Log::info('Campaign trigger processed successfully', [
            'trigger_id' => $trigger->id,
            'users_count' => $users->count()
        ]);
    }

    protected function sendEmailCampaign(CampaignTrigger $trigger, $users)
    {
        foreach ($users as $user) {
            try {
                Mail::to($user->email)->send(new \App\Mail\CampaignEmail(
                    $trigger->campaign_template,
                    $user
                ));
            } catch (\Exception $e) {
                Log::error('Error sending campaign email: ' . $e->getMessage(), [
                    'trigger_id' => $trigger->id,
                    'user_id' => $user->id
                ]);
            }
        }
    }

    protected function sendSmsCampaign(CampaignTrigger $trigger, $users)
    {
        foreach ($users as $user) {
            try {
                if ($user->phone) {
                    // Implement your SMS sending logic here
                    // Example: SMS::send($user->phone, $trigger->campaign_template);
                }
            } catch (\Exception $e) {
                Log::error('Error sending campaign SMS: ' . $e->getMessage(), [
                    'trigger_id' => $trigger->id,
                    'user_id' => $user->id
                ]);
            }
        }
    }

    protected function sendPushCampaign(CampaignTrigger $trigger, $users)
    {
        foreach ($users as $user) {
            try {
                if ($user->device_token) {
                    // Implement your push notification logic here
                    // Example: PushNotification::send($user->device_token, $trigger->campaign_template);
                }
            } catch (\Exception $e) {
                Log::error('Error sending campaign push notification: ' . $e->getMessage(), [
                    'trigger_id' => $trigger->id,
                    'user_id' => $user->id
                ]);
            }
        }
    }
} 