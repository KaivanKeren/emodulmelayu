<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $tanggalSaatIni = Carbon::now();

        if ($request->has(['tahun', 'bulan'])) {
            $tanggalSaatIni = Carbon::createFromDate($request->tahun, $request->bulan, 1);
        }

        $kalender = $this->generateKalender($tanggalSaatIni);

        // Fetch events for the entire month
        $events = Event::whereYear('date', $tanggalSaatIni->year)
            ->whereMonth('date', $tanggalSaatIni->month)
            ->orderBy('date')
            ->get()
            ->groupBy(function ($event) {
                return $event->date->format('Y-m-d');
            });

        return view('admin.calendar.index', compact('kalender', 'tanggalSaatIni', 'events'));
    }

    private function generateKalender($tanggal)
    {
        $tahun = $tanggal->year;
        $bulan = $tanggal->month;

        $hariPertama = Carbon::createFromDate($tahun, $bulan, 1);
        $jumlahHari = $hariPertama->daysInMonth;

        $mingguArray = [];
        $minggu = [];

        // Fill in empty days before the first day of the month
        for ($i = 0; $i < $hariPertama->dayOfWeek; $i++) {
            $minggu[] = null;
        }

        // Fill in the days of the month
        for ($hari = 1; $hari <= $jumlahHari; $hari++) {
            $minggu[] = $hari;

            if (count($minggu) == 7) {
                $mingguArray[] = $minggu;
                $minggu = [];
            }
        }

        // Fill in empty days after the last day of the month
        while (count($minggu) < 7 && !empty($minggu)) {
            $minggu[] = null;
        }

        if (!empty($minggu)) {
            $mingguArray[] = $minggu;
        }

        return $mingguArray;
    }
}
