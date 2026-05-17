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
        $events   = $this->getEventsForMonth($tanggalSaatIni);

        return view('admin.calendar.index', compact('kalender', 'tanggalSaatIni', 'events', 'pendingUsers'));
    }

    public function apiIndex(Request $request)
    {
        $currentDate = Carbon::now();

        if ($request->has(['tahun', 'bulan'])) {
            $currentDate = Carbon::createFromDate($request->tahun, $request->bulan, 1);
        }

        $calendar = $this->generateKalender($currentDate);
        $events   = $this->getEventsForMonth($currentDate);

        return response()->json([
            'currentDate' => $currentDate->toDateString(),
            'calendar'    => $calendar,
            'events'      => $this->formatEventsForJson($events),
        ]);
    }

    public function apiAllMonths(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);

        $months = collect(range(1, 12))->map(function ($month) use ($year) {
            $date        = Carbon::createFromDate($year, $month, 1);
            $daysInMonth = $date->daysInMonth;

            return [
                'monthNumber'    => $month,
                'monthName'      => $date->format('F'),
                'year'           => $year,
                'totalDays'      => $daysInMonth,
                'firstDayOfMonth'=> Carbon::createFromDate($year, $month, 1)->format('Y-m-d'),
                'lastDayOfMonth' => Carbon::createFromDate($year, $month, $daysInMonth)->format('Y-m-d'),
                'calendar'       => $this->generateKalender($date),
            ];
        });

        // Kumpulkan events untuk semua bulan dalam tahun ini
        $allEvents = collect();
        foreach (range(1, 12) as $month) {
            $monthDate   = Carbon::createFromDate($year, $month, 1);
            $monthEvents = $this->getEventsForMonth($monthDate);
            $allEvents   = $allEvents->merge($monthEvents->flatten(1));
        }

        $groupedEvents = $allEvents->groupBy(function ($event) {
            return $event['date'];
        });

        return response()->json([
            'currentDate' => Carbon::now()->toDateString(),
            'year'        => $year,
            'months'      => $months,
            'events'      => $groupedEvents,
        ]);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Ambil events untuk bulan tertentu:
     * 1. Event biasa di bulan & tahun yang dipilih.
     * 2. Recurring events dari tahun-tahun sebelumnya yang jatuh di bulan yang sama,
     *    lalu tanggalnya diproyeksikan ke tahun yang dipilih.
     *    Jika tanggal proyeksi sudah ada event non-recurring, skip duplikasi.
     */
    private function getEventsForMonth(Carbon $date): \Illuminate\Support\Collection
    {
        $year  = $date->year;
        $month = $date->month;

        // 1. Event di bulan & tahun yang dipilih (recurring maupun tidak)
        $regularEvents = Event::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        // 2. Recurring events dari tahun sebelumnya (hanya jika tahun yang ditampilkan > tahun event asal)
        $recurringEvents = Event::where('is_recurring', true)
            ->whereYear('date', '<', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get()
            ->map(function (Event $event) use ($year) {
                // Klon & proyeksikan ke tahun yang sedang ditampilkan
                $projected       = $event->replicate();
                $projected->id   = $event->id;           // pertahankan id asli
                $projected->date = $event->date->copy()->setYear($year);
                return $projected;
            })
            ->filter(function (Event $projected) use ($regularEvents) {
                // Jangan tambahkan jika tanggal proyeksi sudah diisi event reguler
                $projectedKey = $projected->date->format('Y-m-d');
                return $regularEvents
                    ->filter(fn($e) => $e->date->format('Y-m-d') === $projectedKey)
                    ->isEmpty();
            });

        // Gabung & group by tanggal
        return $regularEvents->merge($recurringEvents)
            ->groupBy(fn($event) => $event->date->format('Y-m-d'));
    }

    /**
     * Format grouped events ke array JSON-friendly.
     */
    private function formatEventsForJson(\Illuminate\Support\Collection $grouped): \Illuminate\Support\Collection
    {
        return $grouped->map(function ($group) {
            return $group->map(function (Event $event) {
                return [
                    'id'           => $event->id,
                    'title'        => $event->title,
                    'description'  => $event->content,
                    'date'         => $event->date->toDateString(),
                    'is_recurring' => $event->is_recurring,
                ];
            });
        });
    }

    private function generateKalender(Carbon $tanggal): array
    {
        $hariPertama = Carbon::createFromDate($tanggal->year, $tanggal->month, 1);
        $jumlahHari  = $hariPertama->daysInMonth;

        $mingguArray = [];
        $minggu      = [];

        for ($i = 0; $i < $hariPertama->dayOfWeek; $i++) {
            $minggu[] = null;
        }

        for ($hari = 1; $hari <= $jumlahHari; $hari++) {
            $minggu[] = $hari;
            if (count($minggu) === 7) {
                $mingguArray[] = $minggu;
                $minggu        = [];
            }
        }

        while (count($minggu) < 7 && !empty($minggu)) {
            $minggu[] = null;
        }

        if (!empty($minggu)) {
            $mingguArray[] = $minggu;
        }

        return $mingguArray;
    }
}