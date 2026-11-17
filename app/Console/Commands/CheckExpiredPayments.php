<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\Member;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckExpiredPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired payments and create new pending payments for members';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting expired payments check...');
        
        $today = Carbon::today();
        $processedCount = 0;
        $newPaymentsCount = 0;

        // Get all active members
        $activeMembers = Member::where('status', 1)->get();

        foreach ($activeMembers as $member) {
            // Get the latest payment for this member
            $latestPayment = $member->payments()
                ->orderBy('created_at', 'desc')
                ->first();

            $shouldCreateNewPayment = false;

            if ($latestPayment) {
                // Check if the latest payment has expired
                if ($latestPayment->end_date && Carbon::parse($latestPayment->end_date)->lt($today)) {
                    $shouldCreateNewPayment = true;
                    $this->info("Member {$member->name} (ID: {$member->id}) - Latest payment expired on {$latestPayment->end_date}");
                }
            } else {
                // No payment exists, create one based on member's package
                $shouldCreateNewPayment = true;
                $this->info("Member {$member->name} (ID: {$member->id}) - No payment history found, creating first payment");
            }

            if ($shouldCreateNewPayment && $member->package) {
                $newPayment = $this->createNewPayment($member, $latestPayment);
                if ($newPayment) {
                    $newPaymentsCount++;
                    $this->info("Created new pending payment for member {$member->name} - Amount: \${$newPayment->amount}");
                }
            }

            $processedCount++;
        }

        $this->info("Process completed!");
        $this->info("Processed {$processedCount} members");
        $this->info("Created {$newPaymentsCount} new pending payments");

        // Log the activity
        Log::info("Payment scheduler completed", [
            'processed_members' => $processedCount,
            'new_payments_created' => $newPaymentsCount,
            'timestamp' => now()
        ]);

        return 0;
    }

    /**
     * Create a new pending payment for a member
     *
     * @param Member $member
     * @param Payment|null $previousPayment
     * @return Payment|null
     */
    private function createNewPayment($member, $previousPayment = null)
    {
        try {
            $package = $member->package;
            if (!$package) {
                $this->warn("Member {$member->name} has no package assigned");
                return null;
            }

            // Calculate start and end dates
            $startDate = $today = Carbon::today();
            
            // Calculate end date based on package duration
            $endDate = $this->calculateEndDate($startDate, $package);

            // Create new payment
            $payment = new Payment();
            $payment->member_id = $member->id;
            $payment->user_id = $member->user_id;
            $payment->package_id = $package->id;
            $payment->amount = $package->price;
            $payment->start_date = $startDate->format('Y-m-d');
            $payment->end_date = $endDate->format('Y-m-d');
            $payment->payment_method = 'pending';
            $payment->payment_status = 'pending';
            $payment->note = 'Auto-generated payment from scheduler';
            $payment->save();

            return $payment;

        } catch (\Exception $e) {
            $this->error("Error creating payment for member {$member->name}: " . $e->getMessage());
            Log::error("Error creating payment", [
                'member_id' => $member->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Calculate end date based on package duration
     *
     * @param Carbon $startDate
     * @param Package $package
     * @return Carbon
     */
    private function calculateEndDate($startDate, $package)
    {
        // Use the package duration field (assuming it's in days)
        $durationInDays = $package->duration ?? 30; // Default to 30 days if not set
        return $startDate->copy()->addDays($durationInDays);
    }
} 