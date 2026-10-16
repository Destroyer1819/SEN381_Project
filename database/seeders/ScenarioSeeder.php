<?php

namespace Database\Seeders;

use DateTimeImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ScenarioSeeder extends Seeder
{
    private DateTimeImmutable $now;

    /**
     * 8 named users and 10 hand-made requests, one scenario each (R1 to R10).
     * Reference numbers 2026000001 to 2026000010 make them easy to find.
     */
    public function run(): void
    {
        $this->now = new DateTimeImmutable(date('Y-m-d H:i:s'));

        // Hashed with Laravel's settings (BCRYPT_ROUNDS=12 in .env, NFR-001).
        $password = Hash::make('password');
        $joined = $this->ago('400 days');

        // ---- Users: 4 requesters, 3 staff, 1 manager ----
        $users = [
            [1, 'Thandi Nkosi',         'thandi.nkosi@example.com',   'requestor'],
            [2, 'Pieter van der Merwe', 'pieter.vdmerwe@example.com', 'requestor'],
            [3, 'Aisha Patel',          'aisha.patel@example.com',    'requestor'],
            [4, 'Sipho Dlamini',        'sipho.dlamini@example.com',  'requestor'],
            [5, 'Lerato Mokoena',       'lerato.mokoena@example.com', 'staff'],
            [6, 'Johan Botha',          'johan.botha@example.com',    'staff'],
            [7, 'Nomsa Khumalo',        'nomsa.khumalo@example.com',  'staff'],
            [8, 'Grace Naidoo',         'grace.naidoo@example.com',   'management'],
        ];

        DB::table('users')->insert(array_map(fn ($u) => [
            'id' => $u[0],
            'name' => $u[1],
            'email' => $u[2],
            'role' => $u[3],
            'password' => $password,
            'email_verified_at' => $joined,
            'created_at' => $joined,
            'updated_at' => $joined,
        ], $users));

        // ---- Requests: [id, requestor, location, area, description, status, assigned_to, version, created, updated, resolved, category] ----
        $requests = [
            // R1: brand new, open
            [1, 1, 'Library, ground floor computer lab', 'Central', 'Three computers in the lab will not start up.', 'open', null, 0, '2 hours', '2 hours', null, 2],
            // R2: open for 10 days, never picked up (overdue example)
            [2, 2, 'Community hall, main entrance', 'North', 'The automatic door at the main entrance is stuck open.', 'open', null, 0, '10 days', '10 days', null, 1],
            // R3: assigned, but the staff member has not accepted the offer yet
            [3, 4, 'Sports field, east side', 'East', 'An irrigation sprinkler is broken and flooding the field.', 'assigned', 5, 1, '3 days', '2 days', null, 3],
            // R4: in progress
            [4, 3, 'Fence along Oak Street', 'South', 'A section of the boundary fence has collapsed.', 'in_progress', 6, 2, '5 days', '3 days', null, 3],
            // R5: resolved (staff member self-assigned)
            [5, 4, 'Parking lot B', 'West', 'The security gate boom is not closing at night.', 'resolved', 7, 3, '8 days', '5 days', '5 days', 5],
            // R6: full lifecycle, closed
            [6, 1, 'Clinic waiting room', 'Central', 'Two waiting room chairs are broken and unsafe to sit on.', 'closed', 5, 4, '20 days', '15 days', '16 days', 6],
            // R7: resolved, then reopened by the requester (back to in progress)
            [7, 2, 'Community hall, kitchen', 'North', 'The roof is leaking above the kitchen when it rains.', 'in_progress', 6, 4, '12 days', '7 days', null, 1],
            // R8: first staff member declined, second accepted
            [8, 3, 'Municipal office, reception', 'Central', 'The reception printer shows a paper jam error that cannot be cleared.', 'assigned', 5, 1, '6 days', '4 days 18 hours', null, 2],
            // R9: old, closed request in the 'Other' category
            [9, 4, 'Park next to Pine Avenue', 'East', 'Loud music from the park late at night.', 'closed', 6, 4, '60 days', '55 days', '56 days', 7],
            // R10: open, lost property
            [10, 3, 'Bus stop on Main Road', 'South', 'Found a black backpack left at the bus stop.', 'open', null, 0, '1 day', '1 day', null, 4],
        ];

        DB::table('service_requests')->insert(array_map(fn ($r) => [
            'id' => $r[0],
            'reference_number' => 2026000000 + $r[0],
            'requestor_id' => $r[1],
            'location' => $r[2],
            'area' => $r[3],
            'description' => $r[4],
            'status' => $r[5],
            'assigned_to' => $r[6],
            'version' => $r[7],
            'created_at' => $this->ago($r[8]),
            'updated_at' => $this->ago($r[9]),
            'resolved_at' => $r[10] === null ? null : $this->ago($r[10]),
            'category_id' => $r[11],
        ], $requests));

        // ---- Status history: [request, old, new, changed_by, when] (only allowed moves) ----
        $history = [
            [1, null, 'open', 1, '2 hours'],
            [2, null, 'open', 2, '10 days'],
            [3, null, 'open', 4, '3 days'],
            [3, 'open', 'assigned', 8, '2 days'],
            [4, null, 'open', 3, '5 days'],
            [4, 'open', 'assigned', 8, '4 days'],
            [4, 'assigned', 'in_progress', 6, '3 days'],
            [5, null, 'open', 4, '8 days'],
            [5, 'open', 'assigned', 7, '7 days'],
            [5, 'assigned', 'in_progress', 7, '6 days'],
            [5, 'in_progress', 'resolved', 7, '5 days'],
            [6, null, 'open', 1, '20 days'],
            [6, 'open', 'assigned', 8, '19 days'],
            [6, 'assigned', 'in_progress', 5, '18 days'],
            [6, 'in_progress', 'resolved', 5, '16 days'],
            [6, 'resolved', 'closed', 8, '15 days'],
            [7, null, 'open', 2, '12 days'],
            [7, 'open', 'assigned', 8, '11 days'],
            [7, 'assigned', 'in_progress', 6, '10 days'],
            [7, 'in_progress', 'resolved', 6, '9 days'],
            [7, 'resolved', 'in_progress', 2, '7 days'],
            [8, null, 'open', 3, '6 days'],
            [8, 'open', 'assigned', 8, '4 days 18 hours'],
            [9, null, 'open', 4, '60 days'],
            [9, 'open', 'assigned', 8, '59 days'],
            [9, 'assigned', 'in_progress', 6, '58 days'],
            [9, 'in_progress', 'resolved', 6, '56 days'],
            [9, 'resolved', 'closed', 8, '55 days'],
            [10, null, 'open', 3, '1 day'],
        ];

        DB::table('request_status_history')->insert(array_map(fn ($h) => [
            'request_id' => $h[0],
            'old_status' => $h[1],
            'new_status' => $h[2],
            'changed_by' => $h[3],
            'changed_at' => $this->ago($h[4]),
        ], $history));

        // ---- Assignments: [request, assigned_to, offered_by, state, created, responded] ----
        $assignments = [
            [3, 5, 8, 'offered', '2 days', null],
            [4, 6, 8, 'accepted', '4 days', '3 days 20 hours'],
            [5, 7, 7, 'self_assigned', '7 days', null],
            [6, 5, 8, 'accepted', '19 days', '18 days 22 hours'],
            [7, 6, 8, 'accepted', '11 days', '10 days 23 hours'],
            [8, 7, 8, 'declined', '5 days', '4 days 22 hours'],
            [8, 5, 8, 'accepted', '4 days 20 hours', '4 days 18 hours'],
            [9, 6, 8, 'accepted', '59 days', '58 days 22 hours'],
        ];

        DB::table('request_assignments')->insert(array_map(fn ($a) => [
            'request_id' => $a[0],
            'assigned_to' => $a[1],
            'offered_by' => $a[2],
            'state' => $a[3],
            'created_at' => $this->ago($a[4]),
            'responded_at' => $a[5] === null ? null : $this->ago($a[5]),
        ], $assignments));

        // ---- Comments: [request, author, text, is_resolution, when] ----
        $comments = [
            [2, 2, 'This door is still stuck open and is becoming a security risk.', false, '3 days'],
            [4, 6, 'Inspected the fence. Replacement posts ordered, repair planned for this week.', false, '3 days'],
            [5, 7, 'Gate motor reset and limit switch replaced. The gate now closes correctly.', true, '5 days'],
            [5, 4, 'Thank you, I will confirm tonight.', false, '4 days 20 hours'],
            [6, 1, 'Is there any update on the chairs?', false, '17 days'],
            [6, 5, 'Both chairs have been replaced with new ones.', true, '16 days'],
            [7, 6, 'Sealed the damaged roof sheets.', true, '9 days'],
            [7, 2, 'It rained last night and the roof is leaking again in the same spot.', false, '7 days'],
            [7, 6, 'I will return with a roofer to replace the sheets entirely.', false, '6 days 20 hours'],
            [8, 7, 'I am unable to take this one, I am on leave this week.', false, '4 days 22 hours'],
            [9, 6, 'Spoke to the event organisers; music now ends at 22:00.', true, '56 days'],
        ];

        DB::table('request_comments')->insert(array_map(fn ($c) => [
            'request_id' => $c[0],
            'author_id' => $c[1],
            'comment' => $c[2],
            'is_resolution' => $c[3],
            'created_at' => $this->ago($c[4]),
        ], $comments));
    }

    /** A timestamp the given amount of time before now, e.g. ago('4 days 18 hours'). */
    private function ago(string $interval): string
    {
        $parts = preg_split('/\s+/', trim($interval));
        $t = $this->now;
        for ($i = 0; $i < count($parts); $i += 2) {
            $t = $t->modify("-{$parts[$i]} {$parts[$i + 1]}");
        }

        return $t->format('Y-m-d H:i:s');
    }
}
