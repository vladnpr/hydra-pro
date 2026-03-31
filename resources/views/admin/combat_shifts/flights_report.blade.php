@extends('adminlte::page')

@section('title', 'Звіт по польотам')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Звіт по польотам</h1>
        <div>
            <a href="{{ route('combat_shifts.show', $shift->id) }}" class="btn btn-default">
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
                    <form action="{{ route('combat_shifts.flights_report', $shift->id) }}" method="GET" class="form-inline">
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
                                    <div id="report-content-flights" class="report-printable-area">
                                        @forelse($flights as $flight)
                                            <div class="flight-report-item mb-4" style="page-break-inside: avoid;">
                                                <p class="m-0 font-weight-bold">
                                                    @if(($flight['mission'] ?? '') === 'logistics')
                                                        Назва Цілі/Позиції:
                                                    @endif
                                                    {{ $flight['coordinates'] }}
                                                </p>
                                                <p class="m-0">Час: {{ \Carbon\Carbon::parse($flight['flight_time'])->format('d.m.y H:i') }}</p>
                                                <p class="m-0">Місія:
                                                    @if(($flight['mission'] ?? '') === 'strike') Ударна
                                                    @elseif(($flight['mission'] ?? '') === 'patrol') Патруль/Ждун
                                                    @elseif(($flight['mission'] ?? '') === 'logistics') Логістика
                                                    @else {{ $flight['mission'] ?? '-' }}
                                                    @endif
                                                </p>
                                                <p class="m-0">Стрім: {{ $flight['stream'] ?: 'без стріму' }}</p>
                                                <p class="m-0">Дрон: {{ $flight['drone_name'] }} {{ $flight['drone_model'] }}</p>
                                                @if(($flight['mission'] ?? '') !== 'logistics')
                                                    <p class="m-0">БК: {{ $flight['ammunition_name'] }}</p>
                                                @endif
                                                <p class="m-0">Результат:
                                                    @if((($flight['mission'] ?? '') === 'patrol' || ($flight['mission'] ?? '') === 'logistics') && str_contains($flight['result'], 'відпрацювали'))
                                                        <span class="font-weight-bold">{{ $flight['result'] }}</span>
                                                    @else
                                                        {{ $flight['result'] }}
                                                    @endif
                                                </p>
                                                @if(($flight['mission'] ?? '') !== 'logistics')
                                                    <p class="m-0">Детонація: {{ $flight['detonation'] ?? 'ні' }}</p>
                                                @endif
                                                <p class="m-0">Коментар: {{ $flight['note'] }}</p>
                                                @if(!empty($flight['video_path']))
                                                    <p class="m-0 no-print">
                                                        <a href="{{ route('flight_operations.download', $flight['id']) }}" class="btn btn-xs btn-success mt-1">
                                                            <i class="fas fa-download"></i> Скачати відео
                                                        </a>
                                                        <button type="button" class="btn btn-xs btn-secondary mt-1 ml-1" data-toggle="modal" data-target="#videoModal{{ $flight['id'] }}">
                                                            <i class="fas fa-video"></i> Переглянути
                                                        </button>
                                                    </p>

                                                    <div class="modal fade" id="videoModal{{ $flight['id'] }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Відео вильоту ({{ \Carbon\Carbon::parse($flight['flight_time'])->format('d.m.y H:i') }})</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body text-center bg-black">
                                                                    <video width="100%" controls>
                                                                        <source src="{{ Storage::url($flight['video_path']) }}" type="video/mp4">
                                                                        Ваш браузер не підтримує відео.
                                                                    </video>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-center">
                                                <p>За обраний період вильотів не знайдено.</p>
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
                                            <h4 class="mb-3">Звіт по витратах: {{ $shift->position_name }}</h4>
                                            <p>Період: {{ \Carbon\Carbon::parse($from)->format('d.m.y H:i') }} - {{ \Carbon\Carbon::parse($to)->format('d.m.y H:i') }}</p>
                                        </div>

                                        <div class="spending-section">
                                            <h5 class="font-weight-bold mb-3">Витрачено БК:</h5>
                                            <ul class="list-unstyled pl-0 mb-4">
                                                @forelse($spendingAmmunition as $name => $qty)
                                                    <li class="mb-1">{{ $name }} - {{ $qty }} шт</li>
                                                @empty
                                                    <li>Витрат БК не зафіксовано</li>
                                                @endforelse
                                            </ul>

                                            <h5 class="font-weight-bold mb-3">Втрачено/Витрачено Дронів:</h5>
                                            <ul class="list-unstyled pl-0">
                                                @forelse($spendingDrones as $name => $qty)
                                                    <li class="mb-1">{{ $name }} - {{ $qty }} шт</li>
                                                @empty
                                                    <li>Втрат дронів не зафіксовано</li>
                                                @endforelse
                                            </ul>
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
                                            <h4 class="mb-3">Позиція {{ $shift->position_name }}</h4>
                                            <h5>День {{ $dayNumber }}</h5>
                                        </div>

                                        <div class="crew-section mb-4">
                                            @foreach($shift->crew as $index => $member)
                                                <p class="mb-1">{{ $index + 1 }}. {{ $member['callsign'] }}</p>
                                            @endforeach
                                        </div>

                                        <div class="remains-section">
                                            <h5 class="font-weight-bold mb-3">В наявності</h5>

                                            <div class="drones-block mb-4">
                                                <p class="font-weight-bold mb-2">Дрони:</p>
                                                <ul class="list-unstyled pl-0">
                                                    @forelse($shift->drones as $drone)
                                                        @if($drone['quantity'] > 0)
                                                            <li class="mb-1">{{ $drone['name'] }} {{ $drone['model'] }} - {{ $drone['quantity'] }} шт</li>
                                                        @endif
                                                    @empty
                                                        <li>Відсутні</li>
                                                    @endforelse
                                                </ul>
                                            </div>

                                            <div class="ammunition-block">
                                                <p class="font-weight-bold mb-2">БК:</p>
                                                <ol class="pl-0" style="list-style-type: none;">
                                                    @forelse($shift->ammunition as $index => $item)
                                                        @if($item['quantity'] > 0)
                                                            <li class="mb-1">{{ $index + 1 }}. {{ $item['name'] }} - {{ $item['quantity'] }} шт</li>
                                                        @endif
                                                    @empty
                                                        <li>Відсутні</li>
                                                    @endforelse
                                                </ol>
                                            </div>
                                        </div>

                                        @if(!empty($shift->damaged_drones))
                                            <div class="damaged-drones-block mb-4 mt-4">
                                                <p class="font-weight-bold mb-2">Пошкоджені дрони:</p>
                                                <ul class="list-unstyled pl-0">
                                                    @foreach($shift->damaged_drones as $item)
                                                        <li class="mb-1">{{ $item['name'] }} - {{ $item['quantity'] }} шт</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
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
            $('.modal').on('hidden.bs.modal', function () {
                let video = $(this).find('video')[0];
                if (video) video.pause();
            });

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

        document.getElementById('copy-report').addEventListener('click', function() {
            const activeTab = document.querySelector('.nav-link.active').getAttribute('href');
            let content = '';

            if (activeTab === '#flights-content') {
                const items = document.querySelectorAll('.flight-report-item');
                let flightsText = '';

                items.forEach((item, index) => {
                    const paragraphs = item.querySelectorAll('p');
                    let itemText = '';

                    paragraphs.forEach((p, pIndex) => {
                        // Ігноруємо кнопки та інші не-текстові елементи (наприклад, у .no-print)
                        const cleanText = p.innerText.replace(/Скачати відео|Переглянути/g, '').trim();
                        if (cleanText) {
                            itemText += cleanText;
                            if (pIndex < paragraphs.length - 1) {
                                itemText += '\n';
                            }
                        }
                    });

                    flightsText += itemText;

                    if (index < items.length - 1) {
                        flightsText += '\n\n-------------------\n\n';
                    }
                });
                content = flightsText;
            } else if (activeTab === '#spending-content') {
                content = document.getElementById('report-content-spending').innerText.trim();
            } else if (activeTab === '#remains-content') {
                content = document.getElementById('report-content-remains').innerText.trim();
            }

            if (content === '') {
                const reportContent = document.getElementById('report-content');
                if (reportContent) {
                    content = reportContent.innerText.trim();
                }
            }

            if (content === '') {
                 // Запасний варіант - якщо нічого не знайдено, спробувати взяти текст з активної панелі
                 content = document.querySelector('.tab-pane.active').innerText.trim();
            }

            copyToClipboard(content).then(() => {
                const btn = this;
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Скопійовано!';
                btn.classList.replace('btn-info', 'btn-success');
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.replace('btn-success', 'btn-info');
                }, 2000);
            }).catch(err => {
                console.error('Помилка копіювання: ', err);
                alert('Не вдалося скопіювати текст');
            });
        });
    });
</script>
@endsection

@section('css')
    <style>
        .bg-black { background-color: #000; }
        @media print {
            .main-header, .main-sidebar, .main-footer, .content-header .btn, .no-print, .nav-tabs {
                display: none !important;
            }
            .content-wrapper {
                margin-left: 0 !important;
                background-color: white !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            .card-body {
                padding: 0 !important;
            }
            body {
                background-color: white !important;
            }
            .report-printable-area {
                color: #000 !important;
                display: block !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
            .tab-pane {
                display: none !important;
            }
            .tab-pane.active {
                display: block !important;
            }
        }
        .report-printable-area {
            font-family: "Courier New", Courier, monospace;
            font-size: 1.1rem;
            line-height: 1.4;
        }
        .flight-report-item {
            border-bottom: 1px dashed #ccc;
            padding-bottom: 15px;
            margin-bottom: 30px !important;
        }
        .flight-report-item:last-child {
            border-bottom: none;
        }
    </style>
@endsection
