<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Bulk test data for performance testing (NFR-004: at most 1 second per 100 returned entries).
 *
 * Generates SEED_REQUESTS service requests (default 300) spread over the last year, each with a
 * complete, valid status history (only allowed transitions), assignments and comments.
 * Older requests are mostly closed; recent ones are spread over all statuses, like a real system.
 *
 * The random generator uses a fixed seed (381), so everyone on the team gets the same data
 * (only the dates shift, because they are relative to "now").
 *
 * Change the amount with SEED_REQUESTS in .env, e.g. SEED_REQUESTS=1000
 */
class VolumeSeeder extends Seeder
{
    private const FIRST_USER_ID = 9;      // users 1 to 8 come from ScenarioSeeder
    private const FIRST_REQUEST_ID = 11;  // requests 1 to 10 come from ScenarioSeeder
    private const REQUESTORS = 40;
    private const STAFF = 5;
    private const MANAGERS = 1;
    private const CHUNK = 200;            // rows per INSERT statement

    private int $now;
    private array $buffer = [];

    private const FIRST_NAMES = ['Thabo', 'Naledi', 'Ruan', 'Zanele', 'Kagiso', 'Annelie', 'Mandla', 'Priya', 'Bongani', 'Chantel',
        'Lwazi', 'Marike', 'Tshepo', 'Fatima', 'Neo', 'Charl', 'Ayanda', 'Kirsten', 'Sibusiso', 'Refilwe',
        'Dewald', 'Nandi', 'Yusuf', 'Palesa', 'Wikus', 'Lindiwe', 'Rajesh', 'Mpho', 'Elsabe', 'Themba'];

    private const LAST_NAMES = ['Mahlangu', 'Pretorius', 'Ndlovu', 'Govender', 'Van Wyk', 'Molefe', 'Jacobs', 'Zulu', 'Venter', 'Maseko',
        'Pillay', 'Steyn', 'Mthembu', 'Adams', 'Coetzee', 'Sithole', 'Fourie', 'Ntuli', 'Moodley', 'Kruger',
        'Radebe', 'Smit', 'Mokoena', 'Hendricks', 'Nel', 'Cele', 'Reddy', 'Baloyi', 'Visser', 'Shabalala'];

    private const AREAS = ['North', 'South', 'East', 'West', 'Central'];

    private const LOCATIONS = ['Community hall', 'Library', 'Clinic', 'Municipal office', 'Sports field', 'Swimming pool',
        'Parking lot A', 'Parking lot B', 'Bus terminus', 'Taxi rank', 'Recreation centre', 'Primary school',
        'Park on Church Street', 'Main Road', 'Oak Street', 'Pine Avenue', 'Market square', 'Fire station',
        'Police station entrance', 'Cemetery gate', 'Train station', 'Youth centre', 'Old age home', 'Stadium'];

    private const SPOTS = ['main entrance', 'ground floor', 'first floor', 'back entrance', 'kitchen', 'bathrooms',
        'east side', 'west side', 'reception', 'storeroom', 'corridor', 'outside area'];

    /** Description templates per category id (see CategorySeeder). */
    private const DESCRIPTIONS = [
        1 => ['The lights are not working.', 'The door lock is broken.', 'Water is leaking from the ceiling.',
              'The toilets are blocked.', 'The air conditioner is not cooling.', 'A window is cracked.'],
        2 => ['The public Wi-Fi is not working.', 'A computer will not start up.', 'The printer is offline.',
              'The projector shows no picture.', 'The booking kiosk screen is frozen.'],
        3 => ['A section of the fence has fallen over.', 'The grass has not been cut in weeks.', 'There is a large pothole.',
              'The paint is peeling badly.', 'A tree branch is hanging over the path.', 'The gutter has come loose.'],
        4 => ['Found a set of keys.', 'Found a wallet with cards inside.', 'Lost a child\'s school bag.',
              'Found a mobile phone.', 'Lost a blue umbrella.'],
        5 => ['The security light is off at night.', 'The gate is left open after hours.', 'There is broken glass on the walkway.',
              'Someone has been sleeping in the storeroom.', 'The alarm goes off for no reason.'],
        6 => ['A bench is broken and unsafe.', 'The playground swing chain has snapped.', 'A table leg is broken.',
              'The water fountain is damaged.', 'The ramp railing is loose.'],
        7 => ['Loud music late at night.', 'Illegal dumping next to the road.', 'Stray dogs in the area.',
              'A suggestion for more rubbish bins.', 'Strong smell coming from a drain.'],
    ];

    private const FOLLOW_UPS = ['Is there any update on this?', 'This is still a problem.', 'It is getting worse, please help.',
        'Can someone please look at this soon?', 'Just checking that this was received.'];

    private const PROGRESS = ['Inspected the problem, parts have been ordered.', 'Contractor booked for later this week.',
        'Started the repair, will finish tomorrow.', 'Waiting on the supplier for a replacement.', 'Checked on site, more work needed.'];

    private const RESOLUTIONS = ['Repaired and tested, working correctly now.', 'Replaced the broken part.',
        'Item returned to the owner.', 'Cleaned up and area made safe.', 'Issue fixed, no further action needed.',
        'Referred to the responsible department and resolved.'];

    private const REOPENS = ['The problem came back.', 'This is not fixed, it happened again.', 'Still not working properly.'];

    public function run(): void
    {
        mt_srand(381);
        $this->now = strtotime(date('Y-m-d H:i:00'));
        $total = max(0, (int) env('SEED_REQUESTS', 300));

        [$requestors, $staff, $managers] = $this->seedUsers();

        // The named users from ScenarioSeeder join in, so logging in as them shows plenty of data.
        $requestors = array_merge([1, 2, 3, 4], $requestors);
        $staff = array_merge([5, 6, 7], $staff);
        $managers = array_merge([8], $managers);

        $referenceCounters = [];

        for ($n = 0; $n < $total; $n++) {
            $this->seedRequest(self::FIRST_REQUEST_ID + $n, $requestors, $staff, $managers, $referenceCounters);
        }

        $this->flushAll();
        $this->command?->info("VolumeSeeder: {$total} requests generated.");
    }

    /** Extra requesters, staff and managers. Returns their IDs per role. */
    private function seedUsers(): array
    {
        $password = Hash::make('password');
        $joined = $this->time($this->now - 400 * 86400);
        $ids = ['requestor' => [], 'staff' => [], 'management' => []];
        $id = self::FIRST_USER_ID;

        foreach (['requestor' => self::REQUESTORS, 'staff' => self::STAFF, 'management' => self::MANAGERS] as $role => $count) {
            for ($i = 0; $i < $count; $i++, $id++) {
                $first = self::FIRST_NAMES[mt_rand(0, count(self::FIRST_NAMES) - 1)];
                $last = self::LAST_NAMES[mt_rand(0, count(self::LAST_NAMES) - 1)];
                $email = strtolower(str_replace(' ', '', "{$first}.{$last}")).".{$id}@example.com";

                $this->add('users', [
                    'id' => $id,
                    'name' => "{$first} {$last}",
                    'email' => $email,
                    'role' => $role,
                    'password' => $password,
                    'email_verified_at' => $joined,
                    'created_at' => $joined,
                    'updated_at' => $joined,
                ]);
                $ids[$role][] = $id;
            }
        }
        $this->flush('users');

        return [$ids['requestor'], $ids['staff'], $ids['management']];
    }

    private function seedRequest(int $id, array $requestors, array $staff, array $managers, array &$referenceCounters): void
    {
        $requestor = $this->pick($requestors);
        $worker = $this->pick($staff);
        $manager = $this->pick($managers);
        $category = mt_rand(1, 100) <= 6 ? 7 : mt_rand(1, 6); // about 6% 'Other'

        // When was it reported? Anywhere in the last year.
        $ageMinutes = mt_rand(30, 365 * 24 * 60);
        $created = $this->now - $ageMinutes * 60;
        $ageDays = $ageMinutes / 1440;

        // How far did it get? 0=open 1=assigned 2=in_progress 3=resolved 4=closed
        $weights = $ageDays > 30 ? [3, 3, 4, 5, 85] : ($ageDays > 7 ? [10, 15, 25, 20, 30] : [35, 25, 25, 10, 5]);
        $stage = $this->weighted($weights);
        $selfAssigned = mt_rand(1, 100) <= 15;

        // Build the chain of allowed moves: [old, new, who, typical gap in minutes].
        $steps = [];
        if ($stage >= 1) {
            $steps[] = ['open', 'assigned', $selfAssigned ? $worker : $manager, mt_rand(30, 2880)];
        }
        if ($stage >= 2) {
            $steps[] = ['assigned', 'in_progress', $worker, mt_rand(30, 1440)];
        }
        if ($stage >= 3) {
            $steps[] = ['in_progress', 'resolved', $worker, mt_rand(60, 4320)];
            if (mt_rand(1, 100) <= 8) { // reopened by the requester, then fixed again
                $steps[] = ['resolved', 'in_progress', $requestor, mt_rand(60, 2880)];
                $steps[] = ['in_progress', 'resolved', $worker, mt_rand(60, 2880)];
            }
        } elseif ($stage === 2 && mt_rand(1, 100) <= 5) { // reopened and currently being worked on again
            $steps[] = ['in_progress', 'resolved', $worker, mt_rand(60, 2880)];
            $steps[] = ['resolved', 'in_progress', $requestor, mt_rand(60, 2880)];
        }
        if ($stage === 4) {
            $steps[] = ['resolved', 'closed', $manager, mt_rand(60, 2880)];
        }

        // Squeeze the gaps if they would run past "now".
        $gapSum = array_sum(array_column($steps, 3));
        $room = ($ageMinutes - 1) * 0.95;
        $scale = $gapSum > $room ? $room / $gapSum : 1;

        $t = $created;
        $times = [];
        foreach ($steps as $i => $step) {
            $t += (int) floor($step[3] * $scale) * 60;
            $times[$i] = $t;
        }

        $status = $steps === [] ? 'open' : end($steps)[1];
        $resolvedAt = null;
        if (in_array($status, ['resolved', 'closed'], true)) {
            foreach ($steps as $i => $step) {
                if ($step[1] === 'resolved') {
                    $resolvedAt = $times[$i]; // the last time it was resolved
                }
            }
        }

        $year = (int) date('Y', $created);
        $referenceCounters[$year] = ($referenceCounters[$year] ?? 100) + 1;

        $this->add('service_requests', [
            'id' => $id,
            'reference_number' => $year * 1000000 + $referenceCounters[$year],
            'requestor_id' => $requestor,
            'category_id' => $category,
            'location' => $this->pick(self::LOCATIONS).', '.$this->pick(self::SPOTS),
            'area' => $this->pick(self::AREAS),
            'description' => $this->pick(self::DESCRIPTIONS[$category]),
            'status' => $status,
            'assigned_to' => $stage >= 1 ? $worker : null,
            'version' => count($steps),
            'created_at' => $this->time($created),
            'updated_at' => $this->time($steps === [] ? $created : end($times)),
            'resolved_at' => $resolvedAt === null ? null : $this->time($resolvedAt),
        ]);

        // History: the first entry is always NULL -> open by the requester.
        $this->add('request_status_history', [
            'request_id' => $id, 'old_status' => null, 'new_status' => 'open',
            'changed_by' => $requestor, 'changed_at' => $this->time($created),
        ]);
        foreach ($steps as $i => $step) {
            $this->add('request_status_history', [
                'request_id' => $id, 'old_status' => $step[0], 'new_status' => $step[1],
                'changed_by' => $step[2], 'changed_at' => $this->time($times[$i]),
            ]);
        }

        // Assignments.
        if ($stage >= 1) {
            $assignedAt = $times[0];
            if (! $selfAssigned && count($staff) > 1 && mt_rand(1, 100) <= 10) {
                // Someone else was offered it first and declined.
                do {
                    $other = $this->pick($staff);
                } while ($other === $worker);
                $span = $assignedAt - $created;
                $this->add('request_assignments', [
                    'request_id' => $id, 'assigned_to' => $other, 'offered_by' => $manager, 'state' => 'declined',
                    'created_at' => $this->time($created + (int) ($span * 0.3)),
                    'responded_at' => $this->time($created + (int) ($span * 0.6)),
                ]);
            }

            if ($selfAssigned) {
                $state = 'self_assigned';
                $respondedAt = null;
            } elseif ($stage >= 2) {
                $state = 'accepted';
                $respondedAt = $assignedAt + (int) (($times[1] - $assignedAt) / 2);
            } else {
                $state = 'offered'; // still waiting for the staff member to respond
                $respondedAt = null;
            }

            $this->add('request_assignments', [
                'request_id' => $id, 'assigned_to' => $worker,
                'offered_by' => $selfAssigned ? $worker : $manager, 'state' => $state,
                'created_at' => $this->time($assignedAt),
                'responded_at' => $respondedAt === null ? null : $this->time($respondedAt),
            ]);
        }

        // Comments.
        $lastActivity = $steps === [] ? $this->now : end($times);
        if (mt_rand(1, 100) <= 30) {
            $this->comment($id, $requestor, $this->pick(self::FOLLOW_UPS), false, mt_rand($created, max($created, $lastActivity)));
        }
        if ($stage >= 2 && mt_rand(1, 100) <= 50) {
            $from = $times[1];
            $this->comment($id, $worker, $this->pick(self::PROGRESS), false, mt_rand($from, max($from, $lastActivity)));
        }
        foreach ($steps as $i => $step) {
            if ($step[1] === 'resolved') {
                $this->comment($id, $worker, $this->pick(self::RESOLUTIONS), true, $times[$i]);
            } elseif ($step[0] === 'resolved' && $step[1] === 'in_progress') {
                $this->comment($id, $requestor, $this->pick(self::REOPENS), false, $times[$i]);
            }
        }
    }

    private function comment(int $requestId, int $author, string $text, bool $isResolution, int $at): void
    {
        $this->add('request_comments', [
            'request_id' => $requestId, 'author_id' => $author, 'comment' => $text,
            'is_resolution' => $isResolution, 'created_at' => $this->time($at),
        ]);
    }

    // ---- Small helpers ----

    /** Queue a row and insert in chunks. Parents are written before children to satisfy foreign keys. */
    private function add(string $table, array $row): void
    {
        $this->buffer[$table][] = $row;
        if (count($this->buffer[$table]) >= self::CHUNK) {
            if ($table !== 'service_requests' && $table !== 'users') {
                $this->flush('service_requests');
            }
            $this->flush($table);
        }
    }

    private function flush(string $table): void
    {
        if (! empty($this->buffer[$table])) {
            DB::table($table)->insert($this->buffer[$table]);
            $this->buffer[$table] = [];
        }
    }

    private function flushAll(): void
    {
        foreach (['users', 'service_requests', 'request_status_history', 'request_assignments', 'request_comments'] as $table) {
            $this->flush($table);
        }
    }

    private function pick(array $items): mixed
    {
        return $items[mt_rand(0, count($items) - 1)];
    }

    private function weighted(array $weights): int
    {
        $roll = mt_rand(1, array_sum($weights));
        foreach ($weights as $index => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
                return $index;
            }
        }

        return count($weights) - 1;
    }

    private function time(int $timestamp): string
    {
        return date('Y-m-d H:i:s', $timestamp);
    }
}
