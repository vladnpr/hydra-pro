@extends('adminlte::page')

@section('title', 'Звіт по польотах Vampire')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Звіт по польотах Vampire</h1>
        <div>
            <a href="{{ route('vampire.combat_shifts.show', $shift->id) }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Назад до деталей
            </a>
            <button onclick="window.print()" class="btn btn-success ml-2">
                <i class="fas fa-print"></i> Друкувати
            </button>
            <button id="copy-report" class="btn btn-info ml-2">
                <i class="fas fa-copy"></i> Копіювати
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3 no-print">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('vampire.combat_shifts.flights_report', $shift->id) }}" method="GET" class="form-inline">
                        <div class="form-group mr-2">
                            <label for="from" class="mr-2">З:</label>
                            <input type="datetime-local" name="from" id="from" class="form-control" value="{{ $from }}">
                        </div>
                        <div class="form-group mr-2">
                            <label for="to" class="mr-2">По:</label>
                            <input type="datetime-local" name="to" id="to" class="form-control" value="{{ $to }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Переглянути</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0 no-print">
                    <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="standard-report-tab" data-toggle="pill" href="#standard-report" role="tab" aria-controls="standard-report" aria-selected="true">Звіт</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="list-report-tab" data-toggle="pill" href="#list-report" role="tab" aria-controls="list-report" aria-selected="false">Список</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="spending-tab" data-toggle="pill" href="#spending-content" role="tab" aria-controls="spending-content" aria-selected="false">Звіт по витратам</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="remains-tab" data-toggle="pill" href="#remains-content" role="tab" aria-controls="remains-content" aria-selected="false">Звіт по залишку</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="reportTabsContent">
                        <!-- Стандартний звіт Vampire -->
                        <div class="tab-pane fade show active p-4" id="standard-report" role="tabpanel" aria-labelledby="standard-report-tab">
                            <div id="report-content-standard" class="report-printable-area">
                                @php
                                    $dronesOnShift = collect($shift->vampire_flights)->flatten(1)->groupBy('drone_id');
                                @endphp

                                @foreach($dronesOnShift as $droneId => $droneFlights)
                                    @php $firstFlight = $droneFlights->first(); @endphp
                                    <p class="m-0">{{ $firstFlight['drone_name'] }}</p>
                                    <p class="m-0">Бopт {{ $firstFlight['drone_serial'] ?? 'N/A' }}</p>
                                    <p class="mb-3">Позиція {{ $shift->position_name }}</p>
                                @endforeach

                                <p class="mb-4">
                                    @if($shift->status === 'closed')
                                        Відпрацювали заплановані цілі.
                                    @else
                                        В процесі роботи.
                                    @endif
                                </p>

                                <p class="font-weight-bold">Відпрацювали:</p>
                                @php $i = 1; @endphp
                                @foreach($workedFlights as $flight)
                                    <p class="m-0">{{ $i++ }}) {{ $flight['position_name'] }} ({{ $flight['mission_type_label'] }})</p>
                                    <p class="m-0">Час: {{ \Carbon\Carbon::parse($flight['start_time'])->format('H:i') }} - {{ $flight['end_time'] ? \Carbon\Carbon::parse($flight['end_time'])->format('H:i') : '...' }}</p>
                                    <p class="m-0">Стрім: {{ $flight['stream_status'] ? 'Так' : 'Ні' }}</p>
                                    @if($flight['coordinates'] && $flight['coordinates'] !== '-')
                                        <p class="m-0">{{ $flight['coordinates'] }}</p>
                                    @endif
                                    <p class="mb-3 flight-end">{{ $flight['comment'] ?: '-' }}</p>
                                @endforeach

                                <p class="font-weight-bold">Не відпрацювали:</p>
                                @php $j = 1; @endphp
                                @foreach($notWorkedFlights as $flight)
                                    <p class="m-0">{{ $j++ }}) {{ $flight['position_name'] }} ({{ $flight['mission_type_label'] }})</p>
                                    <p class="m-0">Час: {{ \Carbon\Carbon::parse($flight['start_time'])->format('H:i') }} - {{ $flight['end_time'] ? \Carbon\Carbon::parse($flight['end_time'])->format('H:i') : '...' }}</p>
                                    <p class="m-0">Стрім: {{ $flight['stream_status'] ? 'Так' : 'Ні' }}</p>
                                    @if($flight['coordinates'] && $flight['coordinates'] !== '-')
                                        <p class="m-0">{{ $flight['coordinates'] }}</p>
                                    @endif
                                    <p class="mb-3 flight-end">{{ $flight['comment'] ?: '-' }}</p>
                                @endforeach

                                @if(!empty($lostFlights))
                                    <p class="font-weight-bold text-danger">Втрати:</p>
                                    @php $k = 1; @endphp
                                    @foreach($lostFlights as $flight)
                                        <p class="m-0 text-danger">{{ $k++ }}) {{ $flight['position_name'] }} ({{ $flight['mission_type_label'] }})</p>
                                        <p class="m-0 text-danger">Час: {{ \Carbon\Carbon::parse($flight['start_time'])->format('H:i') }} - {{ $flight['end_time'] ? \Carbon\Carbon::parse($flight['end_time'])->format('H:i') : '...' }}</p>
                                        @if($flight['coordinates'] && $flight['coordinates'] !== '-')
                                            <p class="m-0 text-danger">{{ $flight['coordinates'] }}</p>
                                        @endif
                                        <p class="mb-3 flight-end text-danger">{{ $flight['comment'] ?: '-' }}</p>
                                    @endforeach
                                @endif

                                <p class="font-weight-bold mt-4">Екіпаж:</p>
                                @foreach($shift->crew as $member)
                                    <p class="m-0">{{ $member['callsign'] }}</p>
                                @endforeach
                            </div>
                        </div>

                        <!-- Список Vampire -->
                        <div class="tab-pane fade p-4" id="list-report" role="tabpanel" aria-labelledby="list-report-tab">
                            <div id="report-content-list" class="report-printable-area">
                                @foreach($allFlightsSorted as $flight)
                                    @if($flight['position_name'] && $flight['position_name'] !== '-')
                                        <p class="m-0">Ціль: {{ $flight['position_name'] }}</p>
                                    @endif
                                    @if($flight['coordinates'] && $flight['coordinates'] !== '-')
                                        <p class="m-0">{{ $flight['coordinates'] }}</p>
                                    @endif
                                    <p class="m-0">Час: {{ \Carbon\Carbon::parse($flight['start_time'])->format('H:i') }} - {{ $flight['end_time'] ? \Carbon\Carbon::parse($flight['end_time'])->format('H:i') : '...' }}</p>
                                    <p class="m-0">Стрім: {{ $flight['stream_status'] ? '+' : '-' }}</p>
                                    <p class="m-0">Дрон: {{ $flight['drone_name'] }} - {{ $flight['drone_serial'] ?? 'N/A' }}</p>
                                    <p class="m-0">Місія: {{ $flight['mission_type_label'] }}</p>
                                    <p class="m-0">Результат: {{ $flight['result_label'] }}</p>
                                    <p class="mb-3 flight-end">Коментар: {{ $flight['comment'] ?: '-' }}</p>
                                @endforeach
                            </div>
                        </div>

                        <!-- Вкладка Витрати -->
                        <div class="tab-pane fade p-4" id="spending-content" role="tabpanel" aria-labelledby="spending-tab">
                            <div id="report-content-spending" class="report-printable-area p-5">
                                <div class="report-header mb-4">
                                    <h4 class="mb-3">Звіт по витратах (Vampire): {{ $shift->position_name }}</h4>
                                    <p>Період: {{ \Carbon\Carbon::parse($from)->format('d.m.y H:i') }} - {{ \Carbon\Carbon::parse($to)->format('d.m.y H:i') }}</p>
                                </div>

                                <div class="spending-section">
                                    <h5 class="font-weight-bold mb-3">Статистика вильотів:</h5>
                                    <ul class="list-unstyled pl-0 mb-4">
                                        <li class="mb-1">Всього вильотів: {{ $totalFlights }}</li>
                                        <li class="mb-1">Бойових: {{ $combatFlights }}</li>
                                        <li class="mb-1">Логістика: {{ $logisticsFlights }}</li>
                                    </ul>

                                    <h5 class="font-weight-bold mb-3">Витрачено БК:</h5>
                                    <ul class="list-unstyled pl-0 mb-4">
                                        @forelse($spendingAmmunition as $name => $qty)
                                            <li class="mb-1">{{ $name }} - {{ $qty }} шт</li>
                                        @empty
                                            <li>Витрат БК не зафіксовано</li>
                                        @endforelse
                                    </ul>

                                    <h5 class="font-weight-bold mb-3">Втрачено Дронів:</h5>
                                    <ul class="list-unstyled pl-0">
                                        @forelse($lostDrones as $drone)
                                            <li class="mb-1">{{ $drone['name'] }} ({{ $drone['serial'] }}) - втрачено о {{ $drone['lost_at'] }}</li>
                                        @empty
                                            <li>Втрат дронів не зафіксовано за цей період</li>
                                        @endforelse
                                    </ul>

                                    @if(!empty($strikeCoordinates))
                                        <h5 class="font-weight-bold mb-3 mt-4">Координати ураження/патрулювання:</h5>
                                        <ul class="list-unstyled pl-0">
                                            @foreach($strikeCoordinates as $coord)
                                                <li class="mb-1">{{ $coord }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Вкладка Залишки -->
                        <div class="tab-pane fade p-4" id="remains-content" role="tabpanel" aria-labelledby="remains-tab">
                            <div id="report-content-remains" class="report-printable-area p-5">
                                <div class="report-header mb-4">
                                    <h4 class="mb-3">Поточні залишки: {{ $shift->position_name }}</h4>
                                </div>

                                <div class="crew-section mb-4">
                                    <p class="font-weight-bold mb-2">Екіпаж:</p>
                                    @foreach($shift->crew as $index => $member)
                                        <p class="mb-1">{{ $index + 1 }}. {{ $member['callsign'] }}</p>
                                    @endforeach
                                </div>

                                <div class="remains-section">
                                    <h5 class="font-weight-bold mb-3">В наявності</h5>

                                    <div class="drones-block mb-4">
                                        <p class="font-weight-bold mb-2">Дрони:</p>
                                        <ul class="list-unstyled pl-0">
                                            @forelse($shift->vampire_drones as $drone)
                                                @if($drone['status'] === 'active')
                                                    <li class="mb-1">{{ $drone['name'] }} ({{ $drone['serial_number'] }})</li>
                                                @endif
                                            @empty
                                                <li>Активні дрони відсутні</li>
                                            @endforelse
                                        </ul>
                                    </div>

                                    <div class="ammunition-block">
                                        <p class="font-weight-bold mb-2">БК:</p>
                                        <ul class="list-unstyled pl-0">
                                            @forelse($shift->ammunition as $item)
                                                @if($item['quantity'] > 0)
                                                    <li class="mb-1">{{ $item['name'] }} - {{ $item['quantity'] }} шт</li>
                                                @endif
                                            @empty
                                                <li>БК відсутнє</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            function copyToClipboard(text) {
                if (navigator.clipboard && window.isSecureContext) {
                    return navigator.clipboard.writeText(text);
                } else {
                    let textArea = document.createElement("textarea");
                    textArea.value = text;
                    textArea.style.position = "fixed";
                    textArea.style.left = "-9999px";
                    textArea.style.top = "0";
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    return new Promise((res, rej) => {
                        document.execCommand('copy') ? res() : rej();
                        textArea.remove();
                    });
                }
            }

            $('#copy-report').click(function() {
                const activeTab = $('.nav-link.active').attr('href');
                let reportText = '';

                if (activeTab === '#standard-report' || activeTab === '#list-report') {
                    let contentId = activeTab === '#standard-report' ? '#report-content-standard' : '#report-content-list';
                    // Збираємо текст з активного контейнера
                    $(contentId + ' p').each(function() {
                        reportText += $(this).text().trim() + '\n';
                        if ($(this).hasClass('flight-end')) {
                            reportText += '\n';
                        }
                    });
                } else if (activeTab === '#spending-content') {
                    reportText = $('#report-content-spending').text().trim();
                } else if (activeTab === '#remains-content') {
                    reportText = $('#report-content-remains').text().trim();
                }

                if (!reportText || reportText.trim() === '') {
                    // Запасний варіант
                    reportText = $('.tab-pane.active').text().trim();
                }

                if (!reportText || reportText.trim() === '') {
                    alert('Немає даних для копіювання');
                    return;
                }

                copyToClipboard(reportText.trim()).then(() => {
                    const btn = $(this);
                    const originalHtml = btn.html();
                    btn.html('<i class="fas fa-check"></i> Скопійовано!');
                    btn.removeClass('btn-info').addClass('btn-success');
                    setTimeout(() => {
                        btn.html(originalHtml);
                        btn.removeClass('btn-success').addClass('btn-info');
                    }, 2000);
                }).catch(err => {
                    console.error('Помилка копіювання: ', err);
                    alert('Не вдалося скопіювати текст');
                });
            });

            $('.modal').on('hidden.bs.modal', function () {
                let video = $(this).find('video')[0];
                if (video) video.pause();
            });
        });
    </script>
@endsection

@section('css')
<style>
    .report-printable-area {
        font-family: "Courier New", Courier, monospace;
        font-size: 1.1rem;
        line-height: 1.2;
    }
    .bg-black { background-color: #000; }
    @media print {
        .no-print, .nav-tabs {
            display: none !important;
        }
        .content-wrapper {
            background: white !important;
            margin-left: 0 !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .report-printable-area {
            color: #000 !important;
            display: block !important;
        }
        .tab-pane {
            display: none !important;
        }
        .tab-pane.active {
            display: block !important;
        }
    }
</style>
@endsection
