<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\MemberWorkout;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\SubscribtionHistory;
use App\Models\SubscriptionPackage;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DummyDataSeeder extends Seeder
{
    /**
     * Seed realistic Demo Gym records for every table.
     *
     * @return void
     */
    public function run()
    {
        if (User::where('email', 'ali@example.com')->exists()) {
            return;
        }

        $admin = User::where('email', 'demo@example.com')->first()
            ?? User::where('email', 'admin@domain.com')->first();
        $password = Hash::make('gym12345');

        $businesses = [
            ['name' => 'Ali Khan', 'email' => 'ali@example.com'],
            ['name' => 'Sana Iqbal', 'email' => 'sana@example.com'],
            ['name' => 'Hamza Sheikh', 'email' => 'hamza@example.com'],
            ['name' => 'Ayesha Malik', 'email' => 'ayesha@example.com'],
            ['name' => 'Usman Raza', 'email' => 'usman@example.com'],
        ];

        $businessUsers = [];
        foreach ($businesses as $business) {
            $businessUsers[] = User::create([
                'name' => $business['name'],
                'email' => $business['email'],
                'is_admin' => 1,
                'super_admin' => 0,
                'active' => 1,
                'password' => $password,
            ]);
        }

        $staff = [
            ['name' => 'Hina Farooq', 'email' => 'hina@example.com', 'role' => 'Receptionist'],
            ['name' => 'Bilal Ahmed', 'email' => 'bilal.trainer@example.com', 'role' => 'Trainer'],
            ['name' => 'Nida Shah', 'email' => 'nida@example.com', 'role' => 'Accountant'],
            ['name' => 'Omar Javed', 'email' => 'omar@example.com', 'role' => 'Floor Manager'],
            ['name' => 'Zara Qureshi', 'email' => 'zara@example.com', 'role' => 'Sales Executive'],
        ];

        $rolePermissions = [
            'Receptionist' => ['member-list', 'member-create', 'member-edit', 'payment-list', 'payment-create'],
            'Trainer' => ['member-list', 'package-list'],
            'Accountant' => ['payment-list', 'payment-create', 'payment-edit', 'member-list', 'package-list'],
            'Floor Manager' => ['member-list', 'member-edit', 'package-list', 'payment-list'],
            'Sales Executive' => ['member-list', 'member-create', 'package-list', 'payment-list', 'payment-create'],
        ];

        $roles = [];
        foreach ($rolePermissions as $roleName => $permissionNames) {
            $role = Role::create([
                'name' => $roleName,
                'guard_name' => 'web',
                'user_id' => $admin->id,
            ]);
            $role->syncPermissions(
                Permission::whereIn('name', $permissionNames)->pluck('name')->all()
            );
            $roles[$roleName] = $role;
        }

        foreach ($staff as $person) {
            $user = User::create([
                'name' => $person['name'],
                'email' => $person['email'],
                'is_admin' => 1,
                'super_admin' => 0,
                'user_id' => $admin->id,
                'active' => 1,
                'password' => $password,
            ]);
            $user->assignRole($roles[$person['role']]);
        }

        $settings = [
            'site_title' => 'Demo Gym',
            'meta_keywords' => 'gym, fitness, city gold, karachi, personal training',
            'meta_desc' => 'Demo Gym membership, training and payments.',
            'auth_page_heading' => 'Welcome to Demo Gym',
            'copy_right' => 'Copyright Demo Gym',
            'phone' => '03001234567',
            'email' => 'info@example.com',
        ];

        $settings['branch_name'] = 'Demo Gym Gulshan';
        $settings['opening_hours'] = '6:00 AM - 11:00 PM';
        $settings['whatsapp'] = '03001234567';
        $settings['city'] = 'Karachi';
        $settings['membership_note'] = 'Walk-in fee is charged separately from the package.';

        foreach ($settings as $name => $value) {
            Setting::firstOrCreate(
                ['name' => $name],
                ['value' => $value]
            );
        }

        $packageRows = [
            ['Monthly Basic', 3500, 30],
            ['Monthly Premium', 5500, 30],
            ['Quarterly Gold', 14000, 90],
            ['Half Yearly', 25000, 180],
            ['Yearly Elite', 45000, 365],
            ['Student Monthly', 2500, 30],
            ['Couple Monthly', 9000, 30],
            ['Personal Training', 18000, 30],
        ];

        $packages = [];
        foreach ($packageRows as $row) {
            $packages[] = Package::create([
                'name' => $row[0],
                'price' => $row[1],
                'duration' => $row[2],
                'user_id' => $admin->id,
            ]);
        }

        $membersData = [
            ['Ahmed Raza', 'ahmed.raza@gmail.com', 'CG-1001', '03011111101', 'House 12, Block 5, Gulshan-e-Iqbal', 'Karachi', 0, '2026-08-01', 1],
            ['Fatima Noor', 'fatima.noor@gmail.com', 'CG-1002', '03011111102', 'Flat 4, Sea View Apartments, Clifton', 'Karachi', 1, '2026-09-10', 1],
            ['Hassan Ali', 'hassan.ali@gmail.com', 'CG-1003', '03011111103', 'Street 8, DHA Phase 6', 'Karachi', 2, '2026-07-15', 1],
            ['Ayesha Khan', 'ayesha.khan@gmail.com', 'CG-1004', '03011111104', 'Plot 21, Bahadurabad', 'Karachi', 4, '2026-01-20', 1],
            ['Usman Tariq', 'usman.tariq@gmail.com', 'CG-1005', '03011111105', 'House 9, Model Town', 'Lahore', 3, '2026-06-01', 1],
            ['Sana Malik', 'sana.malik@gmail.com', 'CG-1006', '03011111106', 'Lane 3, F-10 Markaz', 'Islamabad', 5, '2026-10-05', 1],
            ['Imran Qureshi', 'imran.qureshi@gmail.com', 'CG-1007', '03011111107', 'Shop 2, Tariq Road', 'Karachi', 6, '2026-09-01', 1],
            ['Hira Shah', 'hira.shah@gmail.com', 'CG-1008', '03011111108', 'House 44, PECHS Block 2', 'Karachi', 7, '2026-11-01', 1],
            ['Daniyal Ahmed', 'daniyal.ahmed@gmail.com', 'CG-1009', '03011111109', 'Street 14, North Nazimabad', 'Karachi', 0, '2026-04-12', 0],
            ['Maryam Siddiqui', 'maryam.siddiqui@gmail.com', 'CG-1010', '03011111110', 'Flat 11, Gulistan-e-Johar', 'Karachi', 1, '2026-10-18', 1],
        ];

        $members = [];
        foreach ($membersData as $row) {
            $package = $packages[$row[6]];
            $members[] = Member::create([
                'name' => $row[0],
                'email' => $row[1],
                'code' => $row[2],
                'password' => Hash::make('member123'),
                'phone' => $row[3],
                'address' => $row[4],
                'city' => $row[5],
                'package_id' => $package->id,
                'start_date' => $row[7],
                'status' => $row[8],
                'user_id' => $admin->id,
            ]);
        }

        $methods = ['cash', 'card', 'bank_transfer', 'cash', 'card', 'bank_transfer', 'cash', 'card', 'cash', 'bank_transfer'];
        $statuses = ['paid', 'paid', 'paid', 'pending', 'paid', 'paid', 'failed', 'paid', 'pending', 'paid'];

        foreach ($members as $index => $member) {
            $package = $packages[$membersData[$index][6]];
            $start = Carbon::parse($member->start_date);
            $createdAt = $index < 4
                ? Carbon::create(2026, 10, 5 + $index, 11, 0, 0)
                : Carbon::create(2026, 11, 2 + $index, 16, 30, 0);

            $payment = Payment::create([
                'member_id' => $member->id,
                'package_id' => $package->id,
                'amount' => $package->price,
                'start_date' => $start->toDateString(),
                'end_date' => $start->copy()->addDays($package->duration)->toDateString(),
                'payment_method' => $methods[$index],
                'payment_status' => $statuses[$index],
                'note' => $statuses[$index] === 'paid'
                    ? 'Membership fee received at front desk.'
                    : ($statuses[$index] === 'pending' ? 'Invoice sent, waiting for payment.' : 'Card declined, member will retry.'),
                'user_id' => $admin->id,
            ]);
            $payment->created_at = $createdAt;
            $payment->updated_at = $createdAt;
            $payment->save();
        }

        $subscriptionPlans = [
            ['Starter', 'Front desk, members and basic payments for a single gym.', 4999, 30, "Up to 100 members\nPayment tracking\nEmail support"],
            ['Growth', 'Packages, staff logins and workout plans for a busy gym.', 9999, 30, "Up to 400 members\nStaff roles\nWorkout library"],
            ['Pro', 'Reports, pending dues and multi staff access.', 14999, 90, "Unlimited members\nRevenue reports\nPriority support"],
            ['Elite Yearly', 'Full gym software with a yearly discount.', 99999, 365, "Everything in Pro\nYearly billing\nOnboarding call"],
            ['Studio', 'Small studio plan for personal trainers.', 2999, 30, "Up to 40 members\nWorkout assignments\nBasic reports"],
            ['Chain', 'For owners running more than one branch.', 24999, 30, "Multi branch ready\nStaff permissions\nDedicated support"],
        ];

        $subscriptionPackages = [];
        foreach ($subscriptionPlans as $index => $plan) {
            $subscriptionPackages[] = SubscriptionPackage::create([
                'name' => $plan[0],
                'description' => $plan[1],
                'price' => $plan[2],
                'duration' => $plan[3],
                'features' => $plan[4],
                'active' => $index === 5 ? 0 : 1,
            ]);
        }

        foreach ($businessUsers as $index => $owner) {
            $plan = $subscriptionPackages[$index % 5];
            $start = Carbon::create(2026, 10, 1)->addDays($index * 3);
            SubscribtionHistory::create([
                'user_id' => $owner->id,
                'package_id' => $plan->id,
                'start_date' => $start->toDateString(),
                'end_date' => $start->copy()->addDays($plan->duration)->toDateString(),
                'price' => $plan->price,
            ]);

            if ($index < 3) {
                $oldPlan = $subscriptionPackages[0];
                $oldStart = Carbon::create(2026, 6, 1)->addDays($index);
                SubscribtionHistory::create([
                    'user_id' => $owner->id,
                    'package_id' => $oldPlan->id,
                    'start_date' => $oldStart->toDateString(),
                    'end_date' => $oldStart->copy()->addDays($oldPlan->duration)->toDateString(),
                    'price' => $oldPlan->price,
                ]);
            }
        }

        $typeNames = ['Chest', 'Back', 'Legs', 'Shoulders', 'Arms', 'Cardio'];
        $workoutTypes = [];
        foreach ($typeNames as $typeName) {
            $workoutTypes[] = WorkoutType::create([
                'name' => $typeName,
                'user_id' => $admin->id,
            ]);
        }

        $workoutRows = [
            ['Bench Press', 'Flat barbell bench press, 4 sets of 8 reps. Keep shoulder blades tight.', 0],
            ['Incline Dumbbell Press', '30 degree incline, controlled lower, 3 sets of 10.', 0],
            ['Lat Pulldown', 'Wide grip, pull to the upper chest, 4 sets of 12.', 1],
            ['Barbell Row', 'Hinge at the hips and row to the waist, 4 sets of 8.', 1],
            ['Back Squat', 'High bar squat below parallel, 5 sets of 5.', 2],
            ['Romanian Deadlift', 'Soft knees, feel the hamstrings, 3 sets of 10.', 2],
            ['Overhead Press', 'Standing barbell press, brace the core, 4 sets of 6.', 3],
            ['Lateral Raise', 'Light dumbbells, no swinging, 3 sets of 15.', 3],
            ['Barbell Curl', 'Elbows pinned, full range, 3 sets of 10.', 4],
            ['Treadmill Intervals', '1 minute fast, 1 minute easy, repeat for 20 minutes.', 5],
        ];

        foreach ($workoutRows as $row) {
            Workout::create([
                'name' => $row[0],
                'description' => $row[1],
                'user_id' => $admin->id,
                'workout_type_id' => $workoutTypes[$row[2]]->id,
            ]);
        }

        $assignments = [
            [0, 0, '2026-11-10', 'Push day. Start with bench press.'],
            [1, 2, '2026-11-11', 'Leg day. Squat depth is the focus.'],
            [2, 1, '2026-11-12', 'Pull day. Add a pause on the row.'],
            [3, 5, '2026-11-08', 'Easy cardio, knee friendly pace.'],
            [4, 3, '2026-11-13', 'Shoulder volume, stop if pain starts.'],
            [5, 4, '2026-11-14', 'Arms finisher after the main lifts.'],
            [6, 0, '2026-11-09', 'Couple plan, train together on chest.'],
            [7, 2, '2026-11-15', 'Personal training lower body session.'],
            [9, 1, '2026-11-16', 'Back thickness, leave one rep in reserve.'],
            [1, 5, '2026-11-17', 'Finish the week with intervals.'],
        ];

        foreach ($assignments as $row) {
            MemberWorkout::create([
                'member_id' => $members[$row[0]]->id,
                'workout_type_id' => $workoutTypes[$row[1]]->id,
                'assigned_date' => $row[2],
                'notes' => $row[3],
                'user_id' => $admin->id,
            ]);
        }

        $resetEmails = [
            'ahmed.raza@gmail.com',
            'fatima.noor@gmail.com',
            'hassan.ali@gmail.com',
            'hina@example.com',
            'ali@example.com',
        ];
        foreach ($resetEmails as $email) {
            DB::table('password_resets')->insert([
                'email' => $email,
                'token' => Hash::make(Str::random(40)),
                'created_at' => Carbon::now()->subDays(2),
            ]);
        }

        for ($i = 1; $i <= 5; $i++) {
            DB::table('failed_jobs')->insert([
                'uuid' => (string) Str::uuid(),
                'connection' => 'database',
                'queue' => 'default',
                'payload' => json_encode([
                    'displayName' => 'App\\Jobs\\SendMemberReminder',
                    'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
                    'data' => ['member_code' => 'CG-100' . $i],
                ]),
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: Dummy reminder job failed after 3 attempts.',
                'failed_at' => Carbon::now()->subDays($i),
            ]);
        }

        for ($i = 1; $i <= 5; $i++) {
            DB::table('personal_access_tokens')->insert([
                'tokenable_type' => User::class,
                'tokenable_id' => $admin->id,
                'name' => 'Demo Gym Mobile ' . $i,
                'token' => hash('sha256', 'city-gold-dummy-token-' . $i),
                'abilities' => json_encode(['*']),
                'last_used_at' => Carbon::now()->subHours($i),
                'created_at' => Carbon::now()->subDays($i),
                'updated_at' => Carbon::now()->subHours($i),
            ]);
        }
    }
}
