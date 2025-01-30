<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $pendingUsers = User::where('status', 'Pending')->count();

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

        return view('admin.calendar.index', compact('kalender', 'tanggalSaatIni', 'events', 'pendingUsers'));
    }

    public function apiIndex(Request $request)
    {
        $currentDate = Carbon::now();

        // Jika request memiliki parameter 'tahun' dan 'bulan', gunakan tanggal tersebut
        if ($request->has(['tahun', 'bulan'])) {
            $currentDate = Carbon::createFromDate($request->tahun, $request->bulan, 1);
        }

        // Generate kalender untuk bulan yang dipilih
        $calendar = $this->generateKalender($currentDate);

        // Ambil event berdasarkan tahun dan bulan yang dipilih
        $events = Event::whereYear('date', $currentDate->year)
            ->whereMonth('date', $currentDate->month)
            ->orderBy('date')
            ->get()
            ->groupBy(function ($event) {
                return $event->date->format('Y-m-d');
            });

        // Format response JSON
        return response()->json([
            'currentDate' => $currentDate->toDateString(),
            'calendar' => $calendar,
            'events' => $events->map(function ($group) {
                return $group->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'description' => $event->description,
                        'date' => $event->date->toDateString(),
                    ];
                });
            }),
        ]);
    }

    public function apiAllMonths(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);

        $months = collect(range(1, 12))->map(function ($month) use ($year) {
            $date = Carbon::createFromDate($year, $month, 1);
            $daysInMonth = $date->daysInMonth;

            $days = collect(range(1, $daysInMonth))->map(function ($day) use ($year, $month) {
                $date = Carbon::createFromDate($year, $month, $day);
                return [
                    'date' => $date->format('Y-m-d'),
                    'day' => $day,
                ];
            });


            return [
                'monthNumber' => $month,
                'monthName' => $date->format('F'),
                'year' => $year,
                'totalDays' => $daysInMonth,
                'firstDayOfMonth' => Carbon::createFromDate($year, $month, 1)->format('Y-m-d'),
                'lastDayOfMonth' => Carbon::createFromDate($year, $month, $daysInMonth)->format('Y-m-d'),
                'calendar' => $this->generateKalender($date),
            ];
        });

        // Get events for the entire year
        $events = Event::whereYear('date', $year)
            ->orderBy('date')
            ->get()
            ->groupBy(function ($event) {
                return $event->date->format('Y-m-d');
            });

        $currentDate = Carbon::now();

        return response()->json([
            'currentDate' => $currentDate->toDateString(),
            'year' => $year,
            'months' => $months,
            'events' => $events->map(function ($group) {
                return $group->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'description' => $event->description,
                        'date' => $event->date->toDateString(),
                    ];
                });
            }),
        ]);
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
