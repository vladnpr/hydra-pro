@extends('adminlte::page')

@section('title', 'Звіт по витратах НРК')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Звіт по витратах НРК</h1>
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
                    <form action="{{ route('ugv.combat_shifts.spending_report', $shift->id) }}" method="GET" class="form-inline">
                        <label for="date" class="mr-2">Оберіть дату:</label>
                        <select name="date" id="date" class="form-control mr-3" onchange="this.form.submit()">
                            @php
                                $dates = array_keys($shift->ugv_races);
                                if ($date && !in_array($date, $dates)) {
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
            <div class="card">
                <div class="card-body p-5" id="report-content">
                    <div class="report-header mb-4">
                        <h4 class="mb-3">Позиція "{{ $shift->position_name }}"</h4>
                        <p class="m-0">Дата: {{ \Carbon\Carbon::parse($date)->format('d.m.Y') }} (08:00) - {{ \Carbon\Carbon::parse($date)->addDay()->format('d.m.Y') }} (08:00)</p>
                        <br/>
                        <h5>Витрати</h5>
                    </div>

                    <div class="spending-section">
                        <div class="ammunition-block mb-4">
                            <p class="font-weight-bold mb-2">БК:</p>
                            <ul class="list-unstyled pl-0">
                                @forelse($spendingAmmunition as $name => $count)
                                    <li class="mb-1">{{ $name }} - {{ $count }} шт</li>
                                @empty
                                    <li>Відсутні</li>
                                @endforelse
                            </ul>
                        </div>

                        <div class="drones-block">
                            <p class="font-weight-bold mb-2">Втрачені НРКи:</p>
                            <ul class="list-unstyled pl-0">
                                @forelse($lostDrones as $drone)
                                    <li class="mb-1">{{ $drone['name'] }} {{ $drone['serial'] ? '(' . $drone['serial'] . ')' : '' }} - Час: {{ $drone['lost_at'] }}</li>
                                @empty
                                    <li>Відсутні</li>
                                @endforelse
                            </ul>
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

                // Збираємо текст з report-content
                $('#report-content').find('h4, p, h5, li').each(function() {
                    let text = $(this).text().trim();
                    if ($(this).is('h4') || $(this).is('h5')) {
                        reportText += '\n' + text.toUpperCase() + '\n';
                    } else if ($(this).is('li')) {
                        reportText += '- ' + text + '\n';
                    } else {
                        reportText += text + '\n';
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
        });
    </script>
@endsection

@section('css')
    <style>
        #report-content {
            font-family: "Courier New", Courier, monospace;
            font-size: 1.1rem;
            line-height: 1.4;
        }
        .report-header h4, .report-header h5 {
            font-weight: bold;
        }
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
            #report-content {
                color: #000 !important;
            }
        }
    </style>
@endsection
