<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambahan email pegawai dari data referensi Database Email Pegawai RS Unpad.
     * Pencocokan dilakukan berbasis NAMA pegawai (dan fingerprint_id), bukan ID auto-increment.
     */
    private array $data = [
        [
            'name'           => 'Aa Wasailut Thoibin',
            'fingerprint_id' => 306,
            'email'          => 'aawasailutthoibin7@gmail.com',
        ],
        [
            'name'           => 'Acep Setia Firmansyah',
            'fingerprint_id' => 289,
            'email'          => 'acepsetiaf@gmail.com',
        ],
        [
            'name'           => 'Aditya Nugraha',
            'fingerprint_id' => 270,
            'email'          => '8055dit@gmail.com',
        ],
        [
            'name'           => 'Agniya nur Rohmah',
            'fingerprint_id' => 316,
            'email'          => 'agniyanurrohmah@gmail.com',
        ],
        [
            'name'           => 'Ai Siti Maesaroh',
            'fingerprint_id' => 272,
            'email'          => 'aiisitimaesaroh519@gmail.com',
        ],
        [
            'name'           => 'Alya Anissa Fitrie',
            'fingerprint_id' => 293,
            'email'          => 'safieyaaly@gmail.com',
        ],
        [
            'name'           => 'Andreas Recki Prasetyo',
            'fingerprint_id' => 15,
            'email'          => 'andreas@unpad.ac.id',
        ],
        [
            'name'           => 'Angela Vanessa Amabel Od',
            'fingerprint_id' => 296,
            'email'          => 'angela18004@mail.unpad.ac.id',
        ],
        [
            'name'           => 'Angga Rahayu',
            'fingerprint_id' => 276,
            'email'          => 'anggaabyan28@gmail.com',
        ],
        [
            'name'           => 'Anisah Istiqomah',
            'fingerprint_id' => 312,
            'email'          => '12dbkc.anisahistiqomah@gmail.com',
        ],
        [
            'name'           => 'Anjani Sekar Syafana',
            'fingerprint_id' => 317,
            'email'          => 'gyunyuu06@gmail.com',
        ],
        [
            'name'           => 'Annisa Salma nur Fauziya',
            'fingerprint_id' => 314,
            'email'          => 'aesfile8@gmail.com',
        ],
        [
            'name'           => 'Arfiah Azzahra Nissa Sur',
            'fingerprint_id' => 253,
            'email'          => 'azzahraarfiah@gmail.com',
        ],
        [
            'name'           => 'Asri Amalul Ihsan',
            'fingerprint_id' => 36,
            'email'          => 'asriamalul10@gmail.com',
        ],
        [
            'name'           => 'Aurel Avrilia',
            'fingerprint_id' => 267,
            'email'          => 'aurellavriliaa05@gmail.com',
        ],
        [
            'name'           => 'Budi Utami',
            'fingerprint_id' => 20,
            'email'          => 'casemixrsunpad@gmail.com',
        ],
        [
            'name'           => 'Diah NUR anggraini',
            'fingerprint_id' => 319,
            'email'          => 'diahnuranggraini23@gmail.com',
        ],
        [
            'name'           => 'Dianti Apriliani',
            'fingerprint_id' => 304,
            'email'          => 'diantiapriliani12@gmail.com',
        ],
        [
            'name'           => 'Diki Gunawan',
            'fingerprint_id' => 315,
            'email'          => 'dikigunawan214@gmail.com',
        ],
        [
            'name'           => 'Dinal',
            'fingerprint_id' => 274,
            'email'          => 'dinalminul123@gmail.com',
        ],
        [
            'name'           => 'Dinda putri arianto',
            'fingerprint_id' => 318,
            'email'          => 'dindaaputrii1818@gmail.com',
        ],
        [
            'name'           => 'Dini Apriyani',
            'fingerprint_id' => 327,
            'email'          => 'dini.lulu92@gmail.com',
        ],
        [
            'name'           => 'Eva Hardiana',
            'fingerprint_id' => 67,
            'email'          => 'dianaevahardiana@gmail.com',
        ],
        [
            'name'           => 'Febriana Wahyuni Nazwa',
            'fingerprint_id' => 258,
            'email'          => 'febriananazwa40@gmail.com',
        ],
        [
            'name'           => 'Fitri Apiyah Afini',
            'fingerprint_id' => 294,
            'email'          => 'fitriapiyah95@gmail.com',
        ],
        [
            'name'           => 'Ghina Nur Alya',
            'fingerprint_id' => 298,
            'email'          => 'ghinanuralya13@gmail.com',
        ],
        [
            'name'           => 'Gwendy Dale Henry',
            'fingerprint_id' => 324,
            'email'          => 'gwendydale@gmail.com',
        ],
        [
            'name'           => 'Hari Azhar',
            'fingerprint_id' => 308,
            'email'          => 'hariazhar3@gmail.com',
        ],
        [
            'name'           => 'Hari Muhamad Raya',
            'fingerprint_id' => 290,
            'email'          => 'harimuhamadraya@gmail.com',
        ],
        [
            'name'           => 'Ika Siska',
            'fingerprint_id' => 305,
            'email'          => 'siskaika505@gmail.com',
        ],
        [
            'name'           => 'Iklima Mardiah',
            'fingerprint_id' => 285,
            'email'          => 'iklimamardiahh@gmail.com',
        ],
        [
            'name'           => 'Indra Rohmatullah',
            'fingerprint_id' => 300,
            'email'          => 'indrarohmatullah@gmail.com',
        ],
        [
            'name'           => 'Indrawan Mustofa',
            'fingerprint_id' => 281,
            'email'          => 'indrawandm3006@gmail.com',
        ],
        [
            'name'           => 'Jajang Nurjaman',
            'fingerprint_id' => 279,
            'email'          => 'nabasyam04@gmail.com',
        ],
        [
            'name'           => 'Lisa Setyowati',
            'fingerprint_id' => 309,
            'email'          => 'tyowatilisa@gmail.com',
        ],
        [
            'name'           => 'Lita Rizki Aurelia',
            'fingerprint_id' => 295,
            'email'          => 'litaaarzky@gmail.com',
        ],
        [
            'name'           => 'M nur Indrawan',
            'fingerprint_id' => 286,
            'email'          => 'indrawanur@gmail.com',
        ],
        [
            'name'           => 'Muhammad Akbar Syuhada',
            'fingerprint_id' => 313,
            'email'          => 'muhammadakbarsyuhada@gmail.com',
        ],
        [
            'name'           => 'Muhammad Sri Kurniawan D',
            'fingerprint_id' => 326,
            'email'          => 'irwan.mskurniawan@gmail.com',
        ],
        [
            'name'           => 'Nadia Sari',
            'fingerprint_id' => 278,
            'email'          => 'nanatnadia@gmail.com',
        ],
        [
            'name'           => 'Nadia Sinta Wulandari',
            'fingerprint_id' => 271,
            'email'          => 'nadiasinw@gmail.com',
        ],
        [
            'name'           => 'Neni Ermawati',
            'fingerprint_id' => 291,
            'email'          => 'ermawatineni6@gmail.com',
        ],
        [
            'name'           => 'Nia Kurniati',
            'fingerprint_id' => 299,
            'email'          => 'niakurniati625@gmail.com',
        ],
        [
            'name'           => 'Puput Widiawati',
            'fingerprint_id' => 320,
            'email'          => 'nyomanacu93@gmail.com',
        ],
        [
            'name'           => 'Ramdani',
            'fingerprint_id' => 292,
            'email'          => 'rmdn0450@gmail.com',
        ],
        [
            'name'           => 'Reina Rofilla',
            'fingerprint_id' => 119,
            'email'          => 'reihanarofilla525@gmail.com',
        ],
        [
            'name'           => 'Ridwan Zulpan',
            'fingerprint_id' => 268,
            'email'          => 'zulpanridwan3@gmail.com',
        ],
        [
            'name'           => 'Rima Mutiarawati',
            'fingerprint_id' => 325,
            'email'          => 'rimamutiarawati30@gmail.com',
        ],
        [
            'name'           => 'Rima Siti Hamidah',
            'fingerprint_id' => 302,
            'email'          => 'rimasiti363@gmail.com',
        ],
        [
            'name'           => 'Risa Rahmawati',
            'fingerprint_id' => 52,
            'email'          => 'risarahma205@gmail.com',
        ],
        [
            'name'           => 'Ristia Gustiani',
            'fingerprint_id' => 322,
            'email'          => 'ristiag3@gmail.com',
        ],
        [
            'name'           => 'Rizka Hadi Lestari',
            'fingerprint_id' => 328,
            'email'          => 'rizkaahadi@gmail.com',
        ],
        [
            'name'           => 'Santi Aulia Rahmah',
            'fingerprint_id' => 307,
            'email'          => 'santiaulia2000@gmail.com',
        ],
        [
            'name'           => 'Sara Safira',
            'fingerprint_id' => 321,
            'email'          => 'sarasafirajan26@gmail.com',
        ],
        [
            'name'           => 'Sari Syifa Andini',
            'fingerprint_id' => 284,
            'email'          => 'ssyifaaan@gmail.com',
        ],
        [
            'name'           => 'Selviana Budiani',
            'fingerprint_id' => 134,
            'email'          => 'selvinaby4business@gmail.com',
        ],
        [
            'name'           => 'Sholeh Ramdan',
            'fingerprint_id' => 288,
            'email'          => 'sholehramdan64@gmail.com',
        ],
        [
            'name'           => 'Silvi putri Mutiara Meil',
            'fingerprint_id' => 283,
            'email'          => 'silvimeiliza15smp.belajar.id@gmail.com',
        ],
        [
            'name'           => 'Siti Juariah',
            'fingerprint_id' => 269,
            'email'          => 'shienju5@gmail.com',
        ],
        [
            'name'           => 'Sri Dewi Rahmasari',
            'fingerprint_id' => 311,
            'email'          => 'sridewirahmasari@gmail.com',
        ],
        [
            'name'           => 'Sri Dian Harianti',
            'fingerprint_id' => 254,
            'email'          => 'sridianh504@gmail.com',
        ],
        [
            'name'           => 'Syifa Nurul Fauzania',
            'fingerprint_id' => 145,
            'email'          => 'syifanurulfauzania@gmail.com',
        ],
        [
            'name'           => 'Tira Kurniasih',
            'fingerprint_id' => 247,
            'email'          => 'tirakurniasih127@gmail.com',
        ],
        [
            'name'           => 'Violletha Shefierra Sept',
            'fingerprint_id' => 22,
            'email'          => 'violletha1109@gmail.com',
        ],
        [
            'name'           => 'Yohana Novrida',
            'fingerprint_id' => 282,
            'email'          => 'yohananovrida9@gmail.com',
        ],
        [
            'name'           => 'Yolanfa Chelsea Salsa',
            'fingerprint_id' => 297,
            'email'          => 'yolanfachelsea@gmail.com',
        ],
        [
            'name'           => 'Yulia Alya Rohyana',
            'fingerprint_id' => 275,
            'email'          => 'yuliarohyana9@gmail.com',
        ],
        [
            'name'           => 'bani Farriz',
            'fingerprint_id' => 303,
            'email'          => 'banifarriz01@gmail.com',
        ],
        [
            'name'           => 'devi Rosmiati',
            'fingerprint_id' => 287,
            'email'          => 'dedevirosmiati@gmail.com',
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->data as $item) {
            $user = DB::table('users')->where('name', $item['name'])->first();

            if (!$user) {
                $user = DB::table('users')->whereRaw('TRIM(name) = ?', [trim($item['name'])])->first();
            }

            if (!$user && !empty($item['fingerprint_id'])) {
                $user = DB::table('users')->where('fingerprint_id', $item['fingerprint_id'])->first();
            }

            if ($user) {
                DB::table('users')->where('id', $user->id)->update([
                    'email'      => $item['email'],
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $emails = array_column($this->data, 'email');
        DB::table('users')
            ->whereIn('email', $emails)
            ->update([
                'email'      => null,
                'updated_at' => now(),
            ]);
    }
};
