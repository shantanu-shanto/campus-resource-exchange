<?php

// database/seeders/ItemSeeder.php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Seeds realistic campus marketplace items for demo/testing purposes,
     * including enough category variety for the recommendation feature
     * to have something meaningful to work with.
     *
     * Assumes:
     *  - University id 1 ("Bits Pilani") already exists
     *  - Users id 3 ("Rohit kumar") and id 4 ("Tya Tycoon") already exist
     *    and belong to that university
     */
    public function run(): void
    {
        $universityId = 1;
        $userIds = [3, 4];

        $pickupLocations = [
            'Main Library, Ground Floor',
            'Boys Hostel B, Room 214',
            'Girls Hostel A, Common Room',
            'Academic Block 3, Near Cafeteria',
            'Sports Complex Reception',
            'Department of CSE, Room 108',
            'Central Canteen',
        ];

        // [title, description, category, preferred availability_mode, price range or null]
        $items = [
            ['Engineering Mathematics Vol. 2', 'Textbook covering differential equations and linear algebra, 3rd edition. Minimal highlighting, all pages intact.', 'books', 'lend', [150, 300]],
            ['Data Structures and Algorithms (Cormen)', 'Classic DSA reference. Some notes in margins but very readable.', 'books', 'sell', [400, 700]],
            ['Digital Signal Processing Notes', 'Handwritten notes covering the full semester syllabus, well organized by topic.', 'notes', 'share', null],
            ['Organic Chemistry Lab Manual', 'Lab manual with procedure sheets for all 12 experiments this semester.', 'books', 'lend', [100, 200]],
            ['Scientific Calculator FX-991EX', 'Casio scientific calculator, barely used, works perfectly.', 'electronics', 'sell', [800, 1200]],
            ['Wireless Mouse (Logitech)', 'Compact wireless mouse, good battery life, includes USB receiver.', 'electronics', 'sell', [300, 500]],
            ['Lab Safety Goggles', 'Chemistry lab safety goggles, anti-fog coating, one size fits all.', 'lab_equipment', 'lend', [50, 100]],
            ['Digital Multimeter', 'Basic digital multimeter for electronics lab work. Includes probes.', 'lab_equipment', 'lend', [200, 400]],
            ['Drafting Kit (Full Set)', 'Complete drafting kit with compass, set squares, and scale for engineering drawing.', 'stationery', 'lend', [100, 250]],
            ['Graphing Calculator TI-84', 'Texas Instruments graphing calculator, great for calculus and stats courses.', 'electronics', 'lend', [300, 600]],
            ['Physics Practical Notebook (Filled)', 'Completed practical file with all experiments, diagrams, and observations.', 'notes', 'share', null],
            ['Study Table Lamp', 'LED desk lamp with adjustable brightness, USB powered.', 'furniture', 'sell', [250, 400]],
            ['Badminton Racket Set', 'Two rackets with shuttlecocks, lightly used, good grip condition.', 'sports', 'lend', [150, 300]],
            ['Acoustic Guitar', 'Half-size acoustic guitar, good for beginners. Comes with a spare string set.', 'musical_instruments', 'rent', [200, 350]],
            ['Operating Systems Concepts (Galvin)', 'Standard OS textbook, used for one semester, no markings.', 'books', 'sell', [350, 550]],
            ['Whiteboard Markers (Pack of 8)', 'Assorted colors, barely used, great for group study sessions.', 'stationery', 'share', null],
            ['USB-C Hub (7-in-1)', 'Multiport USB-C hub with HDMI, USB 3.0, and SD card reader.', 'electronics', 'sell', [700, 1000]],
            ['Chemistry Molecular Model Kit', 'Ball-and-stick model kit for organic chemistry structures.', 'lab_equipment', 'lend', [150, 300]],
            ['Cricket Bat (Kashmir Willow)', 'Full-size cricket bat, well maintained, good for casual matches.', 'sports', 'lend', [400, 700]],
            ['Engineering Drawing Sheets (Pack)', 'Unused A2 drawing sheets, pack of 20.', 'stationery', 'sell', [150, 250]],
            ['Thermodynamics Textbook (Cengel)', 'Mechanical engineering thermodynamics textbook with solved examples.', 'books', 'lend', [200, 400]],
            ['Bluetooth Speaker (Portable)', 'Small portable speaker, good bass, USB-C charging.', 'electronics', 'rent', [100, 200]],
            ['Semester 4 Notes Bundle (CSE)', 'Complete notes for DBMS, OS, and Computer Networks, photocopied and organized.', 'notes', 'sell', [200, 350]],
            ['Study Chair (Foldable)', 'Foldable study chair, easy to store, decent cushioning.', 'furniture', 'lend', [150, 300]],
            ['Table Tennis Paddle Set', 'Two paddles and three balls, good for hostel common room games.', 'sports', 'share', null],
            ['Resistor and Capacitor Kit', 'Assorted electronic components kit for circuits lab.', 'lab_equipment', 'lend', [150, 250]],
            ['Highlighters (Set of 6)', 'Assorted color highlighters, half used but still good.', 'stationery', 'share', null],
            ['Keyboard (Mechanical, Used)', 'Compact mechanical keyboard, blue switches, minor wear on keycaps.', 'electronics', 'sell', [1200, 1800]],
            ['Calculus Early Transcendentals', 'Popular calculus textbook, good condition, some solved problems in margins.', 'books', 'lend', [200, 400]],
            ['Yoga Mat', 'Non-slip yoga mat, lightly used, easy to roll and carry.', 'sports', 'sell', [200, 350]],
        ];

        $statusPool = ['available', 'available', 'available', 'available', 'borrowed', 'sold', 'reserved'];

        foreach ($items as $index => [$title, $description, $category, $preferredMode, $priceRange]) {
            $mode = $preferredMode === 'lend' && random_int(0, 4) === 0 ? 'both' : $preferredMode;

            $price = match (true) {
                $mode === 'share' => null,
                $priceRange !== null => random_int($priceRange[0], $priceRange[1]),
                default => null,
            };

            $lendingDuration = in_array($mode, ['lend', 'both', 'rent'], true)
                ? [3, 5, 7, 10, 14][array_rand([3, 5, 7, 10, 14])]
                : null;

            Item::create([
                'user_id' => $userIds[$index % count($userIds)],
                'university_id' => $universityId,
                'title' => $title,
                'description' => $description,
                'category' => $category,
                'availability_mode' => $mode === 'rent' ? 'lend' : $mode, // schema only supports share/lend/sell/both
                'price' => $price,
                'lending_duration_days' => $lendingDuration,
                'status' => $statusPool[array_rand($statusPool)],
                'pickup_location' => $pickupLocations[array_rand($pickupLocations)],
                'image_path' => null,
            ]);
        }

        $this->command?->info('Seeded ' . count($items) . ' items for university_id ' . $universityId . '.');
    }
}