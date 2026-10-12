<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Member;
use Illuminate\Database\Seeder;

class LibraryDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Fiksi' => 'Novel, cerita pendek, dan karya imajinatif.',
            'Sains' => 'Pengetahuan alam, teknologi, dan penemuan.',
            'Pengembangan Diri' => 'Bacaan tentang kebiasaan, karier, dan keterampilan.',
        ];

        foreach ($categories as $name => $description) {
            Category::query()->firstOrCreate(['name' => $name], ['description' => $description]);
        }

        $fiction = Category::query()->where('name', 'Fiksi')->firstOrFail();
        $science = Category::query()->where('name', 'Sains')->firstOrFail();
        $growth = Category::query()->where('name', 'Pengembangan Diri')->firstOrFail();

        $books = [
            ['RB-DEMO-001', 'Senja di Tanah Jawa', 'Rani Anggraini', 'Pustaka Timur', 2022, 3, 'Rak A · 01', $fiction->id],
            ['RB-DEMO-002', 'Peta Bintang Nusantara', 'Dimas Pratama', 'Langit Ilmu', 2021, 2, 'Rak A · 02', $fiction->id],
            ['RB-DEMO-003', 'Kebun Kecil di Rumah', 'Maya Lestari', 'Tunas Pustaka', 2023, 4, 'Rak B · 01', $science->id],
            ['RB-DEMO-004', 'Mengenal Laut Indonesia', 'Arif Wicaksono', 'Samudra Press', 2020, 2, 'Rak B · 02', $science->id],
            ['RB-DEMO-005', 'Ritme Kerja yang Sehat', 'Nadia Putri', 'Ruang Tumbuh', 2024, 3, 'Rak C · 01', $growth->id],
            ['RB-DEMO-006', 'Langkah Kecil Setiap Hari', 'Bima Santoso', 'Ruang Tumbuh', 2022, 2, 'Rak C · 02', $growth->id],
        ];

        foreach ($books as [$code, $title, $author, $publisher, $year, $stock, $shelf, $categoryId]) {
            Book::query()->firstOrCreate(['book_code' => $code], [
                'category_id' => $categoryId,
                'title' => $title,
                'author' => $author,
                'publisher' => $publisher,
                'publication_year' => $year,
                'total_stock' => $stock,
                'available_stock' => $stock,
                'shelf_location' => $shelf,
            ]);
        }

        $members = [
            ['MB-DEMO-001', 'Nadia Rahma', 'nadia.demo@example.test', '+628123450001', 'Makassar', 'active'],
            ['MB-DEMO-002', 'Fajar Saputra', 'fajar.demo@example.test', '+628123450002', 'Gowa', 'active'],
            ['MB-DEMO-003', 'Intan Permata', 'intan.demo@example.test', '+628123450003', 'Maros', 'active'],
            ['MB-DEMO-004', 'Rizky Mahendra', 'rizky.demo@example.test', '+628123450004', 'Parepare', 'active'],
            ['MB-DEMO-005', 'Salsa Maharani', 'salsa.demo@example.test', '+628123450005', 'Takalar', 'inactive'],
        ];

        foreach ($members as [$code, $name, $email, $phone, $address, $status]) {
            Member::query()->firstOrCreate(['member_code' => $code], [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'joined_at' => today()->subMonths(4),
                'status' => $status,
            ]);
        }
    }
}
