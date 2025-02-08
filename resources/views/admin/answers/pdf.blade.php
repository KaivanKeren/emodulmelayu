<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $assessment->title }} - Jawaban Siswa</title>
    <link rel="stylesheet" href="/assets/quill.css">
    <!-- Add MathJax Support -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.9/MathJax.js?config=TeX-AMS_HTML"></script>
    <script type="text/x-mathjax-config">
        MathJax.Hub.Config({
            tex2jax: {
                inlineMath: [['$$','$$']],
                displayMath: [['\\[','\\]']],
                processEscapes: true,
                processEnvironments: true
            },
            displayAlign: 'left'
        });
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .progress-bar {
            width: 100px;
            height: 10px;
            background-color: #eee;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background-color: #4299e1;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #666;
            padding: 10px 0;
        }

        .page-break {
            page-break-after: always;
        }

        .correct {
            color: #059669;
        }

        .incorrect {
            color: #dc2626;
        }

        /* Add styles for math display */
        .math-content {
            margin: 8px 0;
        }

        /* Ensure MathJax equations are properly aligned */
        .MathJax_Display {
            margin: 0.5em 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">{{ $assessment->title }}</div>
        <div class="subtitle">Laporan Jawaban Siswa</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>Nilai</th>
                <th>Soal Dijawab</th>
                <th>Penyelesaian</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($respondents as $respondent)
                <tr>
                    <td>{{ $respondent['user']->name }}</td>
                    <td>{{ number_format($respondent['total_score'], 2) }}</td>
                    <td>{{ $respondent['answered_questions'] }} / {{ $totalQuestions }}</td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $respondent['completion_percentage'] }}%"></div>
                        </div>
                        {{ number_format($respondent['completion_percentage'], 1) }}%
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    @foreach ($respondents as $respondent)
        <div class="header">
            <div class="title" style="font-size: 18px;">Detail Jawaban: {{ $respondent['user']->name }}</div>
            <div class="subtitle">
                Nilai Total: {{ number_format($respondent['total_score'], 2) }} |
                Soal Dijawab: {{ $respondent['answered_questions'] }} / {{ $totalQuestions }}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pertanyaan</th>
                    <th>Tipe</th>
                    <th>Jawaban</th>
                    <th>Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($respondent['questions_detail'] as $index => $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="ql-viewer math-content">{!! $detail['question']->content !!}</td>
                        <td>{{ $detail['question']->question_type === 'single_choice' ? 'Pilihan Tunggal' : 'Pilihan Ganda' }}
                        </td>
                        <td class="ql-viewer math-content">
                            @foreach ($detail['selected_options'] as $option)
                                <div class="{{ $option->is_correct ? 'correct' : 'incorrect' }}">
                                    <div class="flex flex-row">
                                        • {!! $option->content !!}
                                    </div>
                                </div>
                            @endforeach
                        </td>
                        <td>{{ number_format($detail['score'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
    </div>

    <script>
        // Force MathJax to reprocess the page after load
        window.onload = function() {
            MathJax.Hub.Queue(["Typeset", MathJax.Hub]);
        };
    </script>
</body>

</html>
