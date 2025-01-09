<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', now()->year);
        $bulan = $request->get('bulan', now()->month);

        $tanggalSaatIni = Carbon::createFromDate($tahun, $bulan, 1);
        $hariDalamBulan = $tanggalSaatIni->daysInMonth;
        $awalBulan = $tanggalSaatIni->startOfMonth()->dayOfWeek;
        $akhirBulan = $tanggalSaatIni->endOfMonth()->dayOfWeek;

        $kalender = [];
        $minggu = [];

        // Isi hari kosong di awal
        for ($i = 0; $i < $awalBulan; $i++) {
            $minggu[] = null;
        }

        // Isi hari dalam bulan
        for ($hari = 1; $hari <= $hariDalamBulan; $hari++) {
            $minggu[] = $hari;

            if (count($minggu) === 7) {
                $kalender[] = $minggu;
                $minggu = [];
            }
        }

        // Isi hari kosong di akhir
        for ($i = count($minggu); $i < 7; $i++) {
            $minggu[] = null;
        }

        $kalender[] = $minggu;

        return view('admin.calendar.index', [
            'kalender' => $kalender,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'tanggalSaatIni' => $tanggalSaatIni,
        ]);
    }
}
