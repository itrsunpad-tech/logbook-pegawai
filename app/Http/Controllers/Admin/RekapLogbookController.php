<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FingerprintLog;
use App\Models\Logbook;
use App\Models\User;
use App\Support\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RekapLogbookController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $kata = trim($filter['q'] ?? '');
        $instalasi = $request->query('instalasi');
        $daftarInstalasi = User::query()
            ->where('role', User::ROLE_PEGAWAI)
            ->whereNotNull('employee_instalasi')
            ->where('employee_instalasi', '!=', '')
            ->when(!$instalasi && $kata !== '', fn ($query) => $query->where('employee_instalasi', 'like', "%{$kata}%"))
            ->selectRaw('employee_instalasi, COUNT(*) as total_pegawai')
            ->groupBy('employee_instalasi')
            ->orderBy('employee_instalasi')
            ->get();

        $pegawai = null;
        if ($instalasi !== null && $daftarInstalasi->contains('employee_instalasi', $instalasi)) {
            $pegawai = User::query()
                ->where('role', User::ROLE_PEGAWAI)
                ->where('employee_instalasi', $instalasi)
                ->when($kata !== '', fn ($query) => $query->where(function ($search) use ($kata) {
                    $search->where('name', 'like', "%{$kata}%")
                        ->orWhere('employee_jabatan', 'like', "%{$kata}%")
                        ->orWhere('email', 'like', "%{$kata}%");
                }))
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString();
        }

        return view('admin.rekap.index', compact('daftarInstalasi', 'instalasi', 'pegawai', 'kata'));
    }

    public function pegawai(User $user): View
    {
        abort_unless($user->role === User::ROLE_PEGAWAI, 404);

        $data = $this->dataRekap($user);

        return view('admin.rekap.pegawai', ['user' => $user] + $data);
    }

    public function excel(User $user): Response
    {
        abort_unless($user->role === User::ROLE_PEGAWAI, 404);

        $data = $this->dataRekap($user);
        $namaFile = 'rekap-logbook-' . Str::slug($user->name) . '.xlsx';

        return response($this->buatWorkbookExcel($user, $data), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $namaFile . '"',
            'Cache-Control' => 'max-age=0, must-revalidate',
        ]);
    }

    /** Buat workbook XLSX sederhana dengan tabel ringkasan bergaya tanpa dependensi eksternal. */
    private function buatWorkbookExcel(User $user, array $data): string
    {
        $cell = static function (string $alamat, mixed $nilai, int $style = 0): string {
            if ($nilai === null || $nilai === '') {
                return '<c r="' . $alamat . '" s="' . $style . '"/>';
            }

            $teks = htmlspecialchars((string) $nilai, ENT_XML1 | ENT_QUOTES, 'UTF-8');

            return '<c r="' . $alamat . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . $teks . '</t></is></c>';
        };
        $row = static function (int $nomor, array $nilai, int $style = 0, ?float $tinggi = null) use ($cell): string {
            $attrs = $tinggi ? ' ht="' . $tinggi . '" customHeight="1"' : '';
            $xml = '<row r="' . $nomor . '"' . $attrs . '>';
            foreach ($nilai as $kolom => $isi) {
                $xml .= $cell($kolom . $nomor, $isi, $style);
            }

            return $xml . '</row>';
        };

        $sheetRows = [];
        $sheetRows[] = $row(1, ['A' => 'Rekap Logbook Pegawai'], 1, 30);
        $sheetRows[] = $row(2, ['A' => 'Nama Pegawai', 'B' => $user->name], 2, 22);
        $sheetRows[] = $row(3, ['A' => 'Instalasi', 'B' => $user->employee_instalasi ?: 'Belum diisi'], 2, 22);
        $sheetRows[] = $row(4, ['A' => 'Dibuat', 'B' => now()->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') . ' WIB'], 2, 22);
        $sheetRows[] = $row(5, [], 0, 9);
        $sheetRows[] = $row(6, ['A' => 'Ringkasan', 'B' => 'Diisi', 'C' => 'Approved', 'D' => 'Total Durasi', 'E' => 'Durasi Approved'], 3, 26);

        foreach ($data['ringkasan'] as $index => $baris) {
            $nomor = 7 + $index;
            $sheetRows[] = $row($nomor, [
                'A' => $baris['nama'],
                'B' => $baris['diisi'] . ' ' . $baris['satuan'],
                'C' => $baris['approved'] . ' ' . $baris['satuan'],
                'D' => $this->formatDurasi($baris['menit']),
                'E' => $this->formatDurasi($baris['approved_menit']),
            ], $index === 0 ? 6 : 4, 25);
        }

        $sheetRows[] = $row(10, [
            'A' => 'Total Keterlambatan',
            'B' => $data['totalTerlambat'] . ' hari',
            'C' => '—',
            'D' => '—',
            'E' => '—',
        ], 4, 25);

        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<dimension ref="A1:E10"/><sheetViews><sheetView workbookViewId="0"><pane ySplit="6" topLeftCell="A7" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<cols><col min="1" max="1" width="30" customWidth="1"/><col min="2" max="3" width="17" customWidth="1"/><col min="4" max="5" width="24" customWidth="1"/></cols>'
            . '<sheetData>' . implode('', $sheetRows) . '</sheetData>'
            . '<autoFilter ref="A6:E10"/><mergeCells count="4"><mergeCell ref="A1:E1"/><mergeCell ref="B2:E2"/><mergeCell ref="B3:E3"/><mergeCell ref="B4:E4"/></mergeCells>'
            . '<pageMargins left="0.3" right="0.3" top="0.5" bottom="0.5" header="0.2" footer="0.2"/><pageSetup orientation="landscape" fitToWidth="1" fitToHeight="1"/></worksheet>';

        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="4"><font><sz val="10"/><name val="Arial"/><color rgb="FF24364B"/></font><font><b/><sz val="16"/><name val="Arial"/><color rgb="FF17263D"/></font><font><b/><sz val="10"/><name val="Arial"/><color rgb="FF52647E"/></font><font><b/><sz val="10"/><name val="Arial"/><color rgb="FF53657E"/></font></fonts>'
            . '<fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFF"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF2F6FC"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left/><right/><top/><bottom style="thin"><color rgb="FFDCE5F0"/></bottom><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="7"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="2" borderId="1" xfId="0" applyFill="1" applyBorder="1"/></cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';

        return $this->buatZip([
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Rekap Pegawai" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            'xl/worksheets/sheet1.xml' => $sheet,
            'xl/styles.xml' => $styles,
        ]);
    }

    /** Pack file entries into a minimal, uncompressed ZIP container for the XLSX package. */
    private function buatZip(array $files): string
    {
        $data = '';
        $directory = '';
        $offset = 0;

        foreach ($files as $nama => $isi) {
            $namaPanjang = strlen($nama);
            $isiPanjang = strlen($isi);
            $crc = crc32($isi);
            $data .= pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, 0, 33, $crc, $isiPanjang, $isiPanjang, $namaPanjang, 0) . $nama . $isi;
            $directory .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, 0, 33, $crc, $isiPanjang, $isiPanjang, $namaPanjang, 0, 0, 0, 0, 0, $offset) . $nama;
            $offset = strlen($data);
        }

        $jumlah = count($files);

        return $data . $directory . pack('VvvvvVVv', 0x06054b50, 0, 0, $jumlah, $jumlah, strlen($directory), strlen($data), 0);
    }

    private function dataRekap(User $user): array
    {

        $logbooks = Logbook::query()->where('user_id', $user->id)->with('shift')->orderBy('tanggal')->orderBy('id')->get();
        $hariKerja = $logbooks->filter(fn (Logbook $item) => $item->jenis === Logbook::HARIAN && !$item->is_cuti && !$item->is_izin_sakit);
        $hariKerjaApproved = $hariKerja->where('status', Logbook::STATUS_DISETUJUI);
        $approvedDates = $hariKerjaApproved->map(fn (Logbook $item) => $item->tanggal->toDateString())->unique()->flip();
        $shiftHarianPerTanggal = $hariKerja->keyBy(fn (Logbook $item) => $item->tanggal->toDateString());
        $jadwalKerjaPegawai = WorkSchedule::jadwalKerja($user);

        $absensiPerHari = collect();
        if ($user->fingerprint_id) {
            $absensiPerHari = FingerprintLog::query()
                ->where('fingerprint_id', $user->fingerprint_id)
                ->whereIn('punch_state', [0, 1])
                ->orderBy('punched_at')
                ->get()
                ->groupBy(fn (FingerprintLog $log) => $log->punched_at->copy()->setTimezone(FingerprintLog::TZ_TAMPIL)->toDateString())
                ->map(function ($logs, $tanggal) use ($user, $shiftHarianPerTanggal, $jadwalKerjaPegawai) {
                    $masuk = $logs->first(fn (FingerprintLog $log) => (int) $log->punch_state === 0);
                    $durasi = WorkSchedule::durasiDariTap(
                        $user,
                        $logs,
                        $shiftHarianPerTanggal->get($tanggal)?->shift,
                        $jadwalKerjaPegawai
                    );

                    return [
                        'tanggal' => $tanggal,
                        'menit' => $durasi,
                        'terlambat' => $masuk && $masuk->punched_at->copy()->setTimezone(FingerprintLog::TZ_TAMPIL)->format('H:i:s') > '08:00:00',
                    ];
                });
        }

        $jamKerjaMenit = (int) $absensiPerHari->sum('menit');
        $jamKerjaApprovedMenit = (int) $absensiPerHari
            ->filter(fn (array $hari) => $approvedDates->has($hari['tanggal']))
            ->sum('menit');
        $totalTerlambat = $absensiPerHari->filter(fn (array $hari) => $hari['terlambat'])->count();
        $lembur = $logbooks->where('jenis', Logbook::LEMBUR);
        $lemburApproved = $lembur->where('status', Logbook::STATUS_DISETUJUI);
        $oncall = $logbooks->where('jenis', Logbook::ONCALL);
        $oncallApproved = $oncall->where('status', Logbook::STATUS_DISETUJUI);
        $durasi = fn ($items) => (int) $items->sum(function (Logbook $item) {
            if (!$item->jam_mulai || !$item->jam_selesai) return 0;
            $mulai = Carbon::parse($item->jam_mulai);
            $selesai = Carbon::parse($item->jam_selesai);

            return $selesai->gt($mulai) ? (int) $mulai->diffInMinutes($selesai) : 0;
        });

        $ringkasan = [
            ['nama' => 'Logbook Harian', 'diisi' => $hariKerja->pluck('tanggal')->unique()->count(), 'approved' => $hariKerjaApproved->pluck('tanggal')->unique()->count(), 'menit' => $jamKerjaMenit, 'approved_menit' => $jamKerjaApprovedMenit, 'satuan' => 'hari'],
            ['nama' => 'Lembur', 'diisi' => $lembur->count(), 'approved' => $lemburApproved->count(), 'menit' => $durasi($lembur), 'approved_menit' => $durasi($lemburApproved), 'satuan' => 'hari'],
            ['nama' => 'On Call', 'diisi' => $oncall->count(), 'approved' => $oncallApproved->count(), 'menit' => $durasi($oncall), 'approved_menit' => $durasi($oncallApproved), 'satuan' => 'kali'],
        ];

        return compact('ringkasan', 'totalTerlambat');
    }

    private function formatDurasi(int $menit): string
    {
        return intdiv($menit, 60) . ' jam ' . str_pad((string) ($menit % 60), 2, '0', STR_PAD_LEFT) . ' menit';
    }
}
