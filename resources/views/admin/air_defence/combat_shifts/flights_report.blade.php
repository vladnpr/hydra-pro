@extends('adminlte::page')

@section('title', 'Звіт по польотах ППО')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Звіт по польотах ППО</h1>
        <div>
            <a href="{{ route('air-defence.combat_shifts.show', $shift->id) }}" class="btn btn-default">
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
                    <form action="{{ route('air-defence.combat_shifts.flights_report', $shift->id) }}" method="GET" class="form-inline">
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
                            <a class="nav-link active" id="flights-tab" data-toggle="pill" href="#flights-content" role="tab" aria-controls="flights-content" aria-selected="true">Звіт по польотам</a>
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
                        <!-- Вкладка Польоти -->
                        <div class="tab-pane fade show active" id="flights-content" role="tabpanel" aria-labelledby="flights-tab">
                            <div class="row">
                                <div class="col-md-8 offset-md-2">
                                    <div id="report-content-flights" class="report-printable-area p-4">
                                        <p class="text-center mb-4">Період: {{ \Carbon\Carbon::parse($from)->format('d.m.Y H:i') }} - {{ \Carbon\Carbon::parse($to)->format('d.m.Y H:i') }}</p>
                                        @forelse($flights as $flight)
                                            <div class="flight-report-item mb-4" style="page-break-inside: avoid;">
                                                @if($flight->coordinates)
                                                    <p class="m-0 font-weight-bold">{{ $flight->coordinates }}</p>
                                                @endif
                                                <p class="m-0">Час: {{ $flight->start_time ? $flight->start_time->format('d.m.y H:i') : '-' }} - {{ $flight->end_time ? $flight->end_time->format('H:i') : '-' }}</p>
                                                <p class="m-0">Стрім: {{ $flight->stream }}</p>
                                                <p class="m-0">Дрон: {{ $flight->drone ? $flight->drone->name . ' ' . $flight->drone->model : 'Невідомий' }}</p>
                                                <p class="m-0">БК: {{ $flight->ammunition ? $flight->ammunition->name : 'Невідоме' }}</p>
                                                <p class="m-0">Результат: {{ $flight->result }}</p>
                                                <p class="m-0">Детонація: {{ $flight->detonation ? 'так' : 'ні' }}</p>
                                                <p class="mb-3 flight-end">Коментар: {{ $flight->comment ?: '-' }}</p>
                                            </div>
                                        @empty
                                            <div class="text-center py-5">
                                                <p class="text-muted">За обраний період вильотів не знайдено.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Вкладка Витрати -->
                        <div class="tab-pane fade" id="spending-content" role="tabpanel" aria-labelledby="spending-tab">
                            <div class="row">
                                <div class="col-md-8 offset-md-2">
                                    <div id="report-content-spending" class="report-printable-area p-5">
                                        <div class="report-header mb-4">
                                            <h4 class="mb-3">Звіт по витратах (ППО): {{ $shift->position_name }}</h4>
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
                                                @forelse($spendingDrones as $name => $qty)
                                                    <li class="mb-1">{{ $name }} - {{ $qty }} шт</li>
                                                @empty
                                                    <li>Втрат дронів не зафіксовано</li>
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
                            </div>
                        </div>

                        <!-- Вкладка Залишки -->
                        <div class="tab-pane fade" id="remains-content" role="tabpanel" aria-labelledby="remains-tab">
                            <div class="row">
                                <div class="col-md-8 offset-md-2">
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
                                                    @forelse($shift->airDefenceDrones as $drone)
                                                        @if($drone['quantity'] > 0)
                                                            <li class="mb-1">{{ $drone['name'] }} {{ $drone['model'] }} - {{ $drone['quantity'] }} шт</li>
                                                        @endif
                                                    @empty
                                                        <li>Активні дрони відсутні</li>
                                                    @endforelse
                                                </ul>
                                            </div>

                                            <div class="ammunition-block">
                                                <p class="font-weight-bold mb-2">БК:</p>
                                                <ul class="list-unstyled pl-0">
                                                    @forelse($shift->airDefenceAmmunition as $item)
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

                if (activeTab === '#flights-content') {
                    // Збираємо текст з контейнера польотів
                    $('#report-content-flights p').each(function() {
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
@stop

@section('css')
<style>
    .report-printable-area {
        font-family: "Courier New", Courier, monospace;
        font-size: 1.1rem;
        line-height: 1.2;
    }
    .flight-report-item {
        margin-bottom: 1.5rem !important;
        border-bottom: 1px dashed #ccc;
        padding-bottom: 10px;
    }
    .flight-report-item p {
        margin-bottom: 0 !important;
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
@stop
