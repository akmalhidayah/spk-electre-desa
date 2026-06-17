<?php

namespace Database\Seeders;

use App\Models\StrukturOrganisasiDesa;
use App\Models\WelcomeDesaSetting;
use Illuminate\Database\Seeder;

class WelcomeDesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $misi = implode("\n", [
            '1. Menciptakan tata kelola pemerintahan yang baik berdasarkan demokratisasi, transparansi, penegakan aturan berkeadilan, kesetaraan gender dan mengutamakan pelayanan kepada masyarakat.',
            '2. Meningkatkan pembangunan di bidang pendidikan dan budaya untuk mendorong peningkatan kualitas sumber daya manusia agar memiliki kecerdasan dan daya saing yang lebih baik.',
            '3. Mengupayakan pelestarian sumber daya alam untuk memenuhi kebutuhan dan pemerataan pembangunan guna meningkatkan perekonomian.',
            '4. Meningkatkan pembangunan di bidang kesehatan untuk mendorong derajat kesehatan masyarakat agar dapat bekerja lebih optimal dan memiliki harapan hidup yang lebih panjang.',
            '5. Meningkatkan pembangunan ekonomi dengan mendorong semakin tumbuh dan berkembangnya pembangunan di bidang pertanian, perkebunan, peternakan, perikanan, industri dan perdagangan.',
            '6. Meningkatkan pembangunan infrastruktur yang mendukung perekonomian desa.',
            '7. Meningkatkan pembangunan dan penataan pariwisata dengan mengoptimalkan sumber daya alam lokal dan berkolaborasi dengan BUMDes menuju pendapatan asli desa.',
            '8. Mendorong terciptanya ketenteraman masyarakat dengan semangat gotong royong dalam membangun desa.',
        ]);

        $setting = WelcomeDesaSetting::query()->updateOrCreate(
            ['nama_desa' => 'Desa Barambang'],
            [
                'kecamatan' => 'Kecamatan Sinjai Borong',
                'kabupaten' => 'Kabupaten Sinjai',
                'provinsi' => 'Sulawesi Selatan',
                'alamat' => "Desa Barambang\nKec. Sinjai Borong, Kab. Sinjai\nSulawesi Selatan",
                'email' => 'desabarambang@gmail.com',
                'telepon' => '021-000-xxxx',
                'judul_welcome' => 'Selamat Datang di Website Resmi Desa Barambang',
                'deskripsi_welcome' => 'Website resmi Desa Barambang sebagai media informasi desa dan pendukung transparansi pelayanan pemerintahan desa.',
                'visi' => 'Terwujudnya Desa Barambang yang mandiri, berkeadilan, religius melalui peningkatan sumber daya manusia (SDM) yang unggul dan berdaya saing untuk mencapai masyarakat yang sehat, cerdas, tenteram dan berakhlakul karimah.',
                'misi' => $misi,
                'judul_infografis' => 'Infografis Desa',
                'deskripsi_infografis' => 'Informasi wilayah, peta, dan gambaran umum Desa Barambang.',
                'status_aktif' => true,
            ]
        );

        $items = [
            [
                'jabatan' => 'Kepala Desa',
                'nama' => 'BOHARI, SE',
                'urutan' => 1,
                'deskripsi' => 'Pimpinan Pemerintah Desa Barambang.',
            ],
            [
                'jabatan' => 'Sekretaris Desa',
                'nama' => 'AHMAD',
                'urutan' => 2,
                'deskripsi' => 'Membantu Kepala Desa dalam bidang administrasi pemerintahan desa.',
            ],
            [
                'jabatan' => 'Kepala Seksi Pemerintahan',
                'nama' => 'JUNARTI',
                'urutan' => 3,
                'deskripsi' => 'Bertugas membantu pelaksanaan urusan pemerintahan desa.',
            ],
            [
                'jabatan' => 'Kepala Seksi Kesejahteraan',
                'nama' => 'MADE. K',
                'urutan' => 4,
                'deskripsi' => 'Bertugas membantu pelaksanaan urusan kesejahteraan masyarakat desa.',
            ],
            [
                'jabatan' => 'Kepala Seksi Pelayanan',
                'nama' => 'SALNATI',
                'urutan' => 5,
                'deskripsi' => 'Bertugas membantu pelaksanaan pelayanan kepada masyarakat desa.',
            ],
            [
                'jabatan' => 'Kaur Tata Usaha dan Umum',
                'nama' => 'RISNAH',
                'urutan' => 6,
                'deskripsi' => 'Bertugas membantu urusan tata usaha, administrasi, dan umum.',
            ],
            [
                'jabatan' => 'Kaur Keuangan',
                'nama' => 'ZULKARNAIN',
                'urutan' => 7,
                'deskripsi' => 'Bertugas membantu pengelolaan administrasi keuangan desa.',
            ],
            [
                'jabatan' => 'Kaur Perencanaan',
                'nama' => 'IRHAM FERDANI',
                'urutan' => 8,
                'deskripsi' => 'Bertugas membantu penyusunan perencanaan pembangunan desa.',
            ],
            [
                'jabatan' => 'Kepala Dusun Katute',
                'nama' => 'UMAR',
                'urutan' => 9,
                'deskripsi' => 'Kepala wilayah Dusun Katute.',
            ],
            [
                'jabatan' => 'Kepala Dusun Balang',
                'nama' => 'LUKMAN',
                'urutan' => 10,
                'deskripsi' => 'Kepala wilayah Dusun Balang.',
            ],
            [
                'jabatan' => 'Kepala Dusun Bonto Manai',
                'nama' => 'SUPRIADI',
                'urutan' => 11,
                'deskripsi' => 'Kepala wilayah Dusun Bonto Manai.',
            ],
            [
                'jabatan' => 'Kepala Dusun Batu Massompo',
                'nama' => 'MUHAMMAD ILYAS',
                'urutan' => 12,
                'deskripsi' => 'Kepala wilayah Dusun Batu Massompo.',
            ],
        ];

        foreach ($items as $item) {
            StrukturOrganisasiDesa::query()->updateOrCreate(
                [
                    'welcome_desa_setting_id' => $setting->id,
                    'jabatan' => $item['jabatan'],
                ],
                [
                    'nama' => $item['nama'],
                    'deskripsi' => $item['deskripsi'],
                    'urutan' => $item['urutan'],
                    'status_aktif' => true,
                ]
            );
        }
    }
}
