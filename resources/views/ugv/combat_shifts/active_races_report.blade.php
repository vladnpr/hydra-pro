@extends('adminlte::page')

@section('title', 'Активні рейси НРК')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Активні рейси НРК</h1>
        <div>
            <button onclick="window.print()" class="btn btn-success">
                <i class="fas fa-print"></i> Друкувати
            </button>
            <button id="copy-report" class="btn btn-info ml-2">
                <i class="fas fa-copy"></i> Копіювати
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body p-5" id="report-content">
                    @forelse($shifts as $shift)
                        <h4 class="mb-4 text-center">Позиція: {{ $shift->position_name }}</h4>
                        @php
                            // Отримуємо рейси за сьогодні (або останні доступні)
                            $today = now()->format('Y-m-d');
                            $dayRaces = $shift->ugv_races[$today] ?? [];
                            if (empty($dayRaces) && !empty($shift->ugv_races)) {
                                $dayRaces = reset($shift->ugv_races);
                            }
                        @endphp

                        @forelse($dayRaces as $race)
                            <div class="race-report-item mb-4" style="page-break-inside: avoid;">
                                <p class="m-0 font-weight-bold">Ціль:
                                    @if(!empty($race['checkpoints']))
                                        <div class="ml-3 mt-1">
                                            @foreach($race['checkpoints'] as $checkpoint)
                                                <div>{{ $loop->iteration }}. {{ $checkpoint['position_name'] }}</div>
                                            @endforeach
                                        </div>
                                    @else
                                        {{ $race['position_name'] ?: ($race['coordinates'] ?: '-') }}
                                    @endif
                                </p>
                                @if(!empty($race['checkpoints']) && $race['coordinates'] && $race['coordinates'] !== '-')
                                    <p class="m-0 font-weight-bold">{{ $race['coordinates'] }}</p>
                                @endif
                                <p class="m-0">Час початку: {{ \Carbon\Carbon::parse($race['start_time'])->format('d.m.y H:i') }}</p>
                                <p class="m-0">Час кінця: {{ $race['end_time'] ? \Carbon\Carbon::parse($race['end_time'])->format('d.m.y H:i') : '-' }}</p>
                                <p class="m-0">Стрім: {{ $race['stream_status'] ? '+' : '-' }}</p>
                                <p class="m-0">НРК: {{ $race['drone_name'] }} ({{ $race['drone_serial'] ?? '-' }})</p>
                                <p class="m-0">Місія: {{ $race['mission_type_label'] }}</p>
                                <p class="m-0">Результат: {{ $race['result_label'] }}</p>
                                <p class="m-0">Коментар: {{ $race['comment'] }}</p>
                            </div>
                        @empty
                            <p class="text-center text-muted">Сьогодні рейсів не зафіксовано</p>
                            <hr>
                        @endforelse
                    @empty
                        <div class="text-center">
                            <p>Немає активних змін UGV.</p>
                        </div>
                    @endforelse
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

            document.getElementById('copy-report').addEventListener('click', function() {
                const items = document.querySelectorAll('.race-report-item');
                let content = '';

                items.forEach((item, index) => {
                    const paragraphs = item.querySelectorAll('p');
                    let itemText = '';

                    paragraphs.forEach((p, pIndex) => {
                        itemText += p.innerText.trim();
                        if (pIndex < paragraphs.length - 1) {
                            itemText += '\n';
                        }
                    });

                    content += itemText;

                    if (index < items.length - 1) {
                        content += '\n\n-------------------\n\n';
                    }
                });

                if (content === '') {
                    content = document.getElementById('report-content').innerText.trim();
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
        @media print {
            .main-header, .main-sidebar, .main-footer, .content-header .btn, .no-print {
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
            #report-content {
                color: #000 !important;
            }
        }
        #report-content {
            font-family: "Courier New", Courier, monospace;
            font-size: 1.1rem;
            line-height: 1.4;
        }
        .race-report-item {
            border-bottom: 1px dashed #ccc;
            padding-bottom: 15px;
            margin-bottom: 30px !important;
        }
        .race-report-item:last-child {
            border-bottom: none;
        }
    </style>
@endsection
