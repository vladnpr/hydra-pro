@extends('adminlte::page')

@section('title', 'Звіт по рейсах НРК')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Звіт по рейсах НРК</h1>
        <div>
            <a href="{{ route('ugv.combat_shifts.show', $shift->id) }}" class="btn btn-default">
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
                    <form action="{{ route('ugv.combat_shifts.races_report', $shift->id) }}" method="GET" class="form-inline">
                        <label for="date" class="mr-2">Оберіть дату:</label>
                        <select name="date" id="date" class="form-control mr-3" onchange="this.form.submit()">
                            @php
                                $dates = array_keys($shift->ugv_races);
                                if (!in_array($date, $dates)) {
                                    $dates[] = $date;
                                }
                                rsort($dates);
                            @endphp
                            @foreach($dates as $raceDate)
                                <option value="{{ $raceDate }}" {{ $date == $raceDate ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($raceDate)->format('d.m.Y') }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary">Переглянути</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card card-primary card-outline card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0 no-print">
                    <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="standard-report-tab" data-toggle="pill" href="#standard-report" role="tab" aria-controls="standard-report" aria-selected="true">Звіт</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="list-report-tab" data-toggle="pill" href="#list-report" role="tab" aria-controls="list-report" aria-selected="false">Список</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="reportTabsContent">
                        <div class="tab-pane fade show active p-4" id="standard-report" role="tabpanel" aria-labelledby="standard-report-tab">
                            <div id="report-content-standard">
                                @php
                                    $dronesOnShift = collect($shift->ugv_races)->flatten(1)->groupBy('drone_id');
                                @endphp

                                @foreach($dronesOnShift as $droneId => $droneRaces)
                                    @php $firstRace = $droneRaces->first(); @endphp
                                    <p class="m-0">{{ $firstRace['drone_name'] }}</p>
                                    <p class="m-0">Бopт {{ $firstRace['drone_serial'] ?? 'N/A' }}</p>
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
                                @foreach($workedRaces as $race)
                                    <p class="m-0">{{ $i++ }})
                                        @if(!empty($race['checkpoints']))
                                            <div class="ml-3 mt-1">
                                                @foreach($race['checkpoints'] as $checkpoint)
                                                    <div class="{{ $checkpoint['status'] === 'not_worked' ? 'text-strikethrough' : '' }}">{{ $loop->iteration }}. {{ $checkpoint['position_name'] }}</div>
                                                @endforeach
                                            </div>
                                        @else
                                            {{ $race['position_name'] }}
                                        @endif
                                        ({{ $race['mission_type_label'] }})
                                    </p>
                                    <p class="m-0">Час: {{ \Carbon\Carbon::parse($race['start_time'])->format('H:i') }} - {{ $race['end_time'] ? \Carbon\Carbon::parse($race['end_time'])->format('H:i') : '...' }}</p>
                                    <p class="m-0">Стрім: {{ $race['stream_status'] ? 'Так' : 'Ні' }}</p>
                                    @if($race['coordinates'] && $race['coordinates'] !== '-')
                                        <p class="m-0">{{ $race['coordinates'] }}</p>
                                    @endif
                                    <p class="mb-3 race-end">{{ $race['comment'] ?: '-' }}</p>
                                @endforeach

                                <p class="font-weight-bold">Не відпрацювали:</p>
                                @php $j = 1; @endphp
                                @foreach($notWorkedRaces as $race)
                                    <p class="m-0">{{ $j++ }})
                                        @if(!empty($race['checkpoints']))
                                            <div class="ml-3 mt-1">
                                                @foreach($race['checkpoints'] as $checkpoint)
                                                    <div class="{{ $checkpoint['status'] === 'not_worked' ? 'text-strikethrough' : '' }}">{{ $loop->iteration }}. {{ $checkpoint['position_name'] }}</div>
                                                @endforeach
                                            </div>
                                        @else
                                            {{ $race['position_name'] }}
                                        @endif
                                        ({{ $race['mission_type_label'] }})
                                    </p>
                                    <p class="m-0">Час: {{ \Carbon\Carbon::parse($race['start_time'])->format('H:i') }} - {{ $race['end_time'] ? \Carbon\Carbon::parse($race['end_time'])->format('H:i') : '...' }}</p>
                                    <p class="m-0">Стрім: {{ $race['stream_status'] ? 'Так' : 'Ні' }}</p>
                                    @if($race['coordinates'] && $race['coordinates'] !== '-')
                                        <p class="m-0">{{ $race['coordinates'] }}</p>
                                    @endif
                                    <p class="mb-3 race-end">{{ $race['comment'] ?: '-' }}</p>
                                @endforeach

                                <p class="font-weight-bold mt-4">Екіпаж:</p>
                                @foreach($shift->crew as $member)
                                    <p class="m-0">{{ $member['callsign'] }}</p>
                                @endforeach
                            </div>
                        </div>
                        <div class="tab-pane fade p-4" id="list-report" role="tabpanel" aria-labelledby="list-report-tab">
                            <div id="report-content-list">
                                @foreach($allRacesSorted as $race)
                                    <p class="m-0">Ціль:
                                        @if(!empty($race['checkpoints']))
                                            <div class="ml-3 mt-1">
                                                @foreach($race['checkpoints'] as $checkpoint)
                                                    <div>{{ $loop->iteration }}. {{ $checkpoint['position_name'] }}</div>
                                                @endforeach
                                            </div>
                                        @else
                                            {{ $race['position_name'] ?: '-' }}
                                        @endif
                                    </p>
                                    @if($race['coordinates'] && $race['coordinates'] !== '-')
                                        <p class="m-0">{{ $race['coordinates'] }}</p>
                                    @endif
                                    <p class="m-0">Час: {{ \Carbon\Carbon::parse($race['start_time'])->format('H:i') }} - {{ $race['end_time'] ? \Carbon\Carbon::parse($race['end_time'])->format('H:i') : '...' }}</p>
                                    <p class="m-0">Стрім: {{ $race['stream_status'] ? '+' : '-' }}</p>
                                    <p class="m-0">НРК: {{ $race['drone_name'] }} - {{ $race['drone_serial'] ?? 'N/A' }}</p>
                                    <p class="m-0">Місія: {{ $race['mission_type_label'] }}</p>
                                    <p class="m-0">Результат: {{ $race['result_label'] }}</p>
                                    <p class="@if(empty($race['video_path'])) mb-3 race-end @else m-0  @endif">Коментар: {{ $race['comment'] ?: '-' }}</p>
                                    @if(!empty($race['video_path']))
                                        <div class="mb-3 race-end no-print">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-xs btn-secondary" data-toggle="modal" data-target="#videoModal{{ $race['id'] }}" title="Переглянути">
                                                    <i class="fas fa-video"></i> Переглянути
                                                </button>
                                                <a href="{{ route('ugv.races.download', $race['id']) }}" class="btn btn-xs btn-success ml-1" title="Скачати">
                                                    <i class="fas fa-download"></i> Скачати відео
                                                </a>
                                            </div>

                                            <div class="modal fade" id="videoModal{{ $race['id'] }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog modal-lg" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Відео рейсу #{{ $race['id'] }}</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body text-center bg-black">
                                                            <video width="100%" controls>
                                                                <source src="{{ Storage::url($race['video_path']) }}" type="video/mp4">
                                                                Ваш браузер не підтримує відео.
                                                            </video>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
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
                let reportText = '';
                let activeTab = $('.nav-tabs .nav-link.active').attr('href');
                let contentId = activeTab === '#standard-report' ? '#report-content-standard' : '#report-content-list';

                // Збираємо текст з активного контейнера
                $(contentId + ' p').each(function() {
                    reportText += $(this).text().trim() + '\n';
                    if ($(this).hasClass('race-end')) {
                        reportText += '\n';
                    }
                });

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
    .text-strikethrough {
        text-decoration: line-through;
    }
    #report-content-standard, #report-content-list {
        font-family: "Courier New", Courier, monospace;
        font-size: 1.1rem;
        line-height: 1.2;
    }
    .bg-black { background-color: #000; }
    @media print {
        .no-print {
            display: none !important;
        }
        .content-wrapper {
            background: white !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        #report-content-standard, #report-content-list {
            color: #000 !important;
        }
    }
</style>
@endsection
