<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Local Green Diary entries written in Indonesian only, to try the "Translate
 * missing English" button in the CMS. Like most diaries in production, half of
 * them have the Indonesian text copied into the English fields, the other half
 * leave English empty. Running it again replaces these entries; other diaries
 * are left alone.
 *
 *   php artisan db:seed --class=DummyDiaryIndonesianSeeder
 */
class DummyDiaryIndonesianSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment('production')) {
            $this->command->error('DummyDiaryIndonesianSeeder is for local development only.');
            return;
        }

        // [date, title, image description, img]
        $entries = [
            ['2026-10-21', 'Patroli sekat bakar jelang puncak kemarau', 'Tim menyusuri sekat bakar selebar 30 meter di sisi timur area restorasi dan membersihkan ilalang yang mulai tumbuh kembali.', 'kebakaran.jpg'],
            ['2026-10-19', 'Jejak gajah liar melintas di Blok 2', 'Kawanan gajah liar melintas semalam. Beberapa tanaman muda rusak terinjak, tapi sebagian besar bibit puspa selamat.', 'gajah.jpg'],
            ['2026-10-16', 'Menyulam tanaman mati di Blok 3', 'Sebanyak 850 bibit bungur dan medang ditanam untuk menggantikan tanaman yang mati akibat kemarau panjang.', 'Tanaman.jpg'],
            ['2026-10-14', 'Kabut asap menyelimuti Rawa Kadut', 'Asap dari kebakaran di luar kawasan terbawa angin hingga ke camp. Tim siaga dan memantau titik api setiap dua jam.', 'kabut.jpg'],
            ['2026-10-10', 'Menebas ilalang di sekitar bibit', 'Ilalang setinggi pinggang ditebas agar bibit mendapat cahaya. Tanpa bantuan ini, bibit kalah bersaing dengan ilalang.', 'ilalang-2.jpg'],
            ['2026-10-07', 'Mengukur tinggi pohon di plot pemantauan', 'Pohon jambon yang ditanam pada 2015 kini tingginya mencapai 9 meter. Data ini dicatat untuk menilai pertumbuhan hutan.', 'hutan.jpg'],
            ['2026-10-03', 'Pelatihan pemadaman api bagi warga desa', 'Dua puluh warga desa penyangga belajar memakai pompa air dan gepyok untuk memadamkan api di padang ilalang.', 'ilalang-3.jpg'],
            ['2026-09-29', 'Memetakan ulang batas blok tanam', 'Dengan GPS, tim menandai ulang batas blok tanam agar rencana penanaman pada musim hujan nanti lebih tepat.', 'peta.jpg'],
        ];

        $photos = storage_path('app/public/files/photos');
        File::ensureDirectoryExists($photos);
        $now = Carbon::now('Asia/Jakarta');

        DB::table('greendiary')->whereIn('slugID', array_map(fn ($entry) => Str::slug($entry[1], '-'), $entries))->delete();
        foreach ($entries as $i => [$date, $title, $description, $img]) {
            $copied = $i % 2 === 1;
            if (!File::exists($photos.'/'.$img)) {
                File::copy(public_path('assets/'.$img), $photos.'/'.$img);
            }
            DB::table('greendiary')->insert([
                'titleID' => $title,
                'titleEN' => $copied ? $title : '',
                'slugID' => Str::slug($title, '-'),
                'slugEN' => $copied ? Str::slug($title, '-') : '',
                'imgDescID' => $description,
                'imgDescEN' => $copied ? $description : '',
                'img' => $img,
                'publishdate' => $date,
                'isActive' => 1,
                'created_at' => $now,
            ]);
        }
    }
}
