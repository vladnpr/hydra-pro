@extends('adminlte::page')

@section('title', 'Звіт по польотах розвідки')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Звіт по польотах розвідки</h1>
        <div>
            <a href="{{ route('recon.combat_shifts.show', $shift->id) }}" class="btn btn-default">
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
                    <form action="{{ route('recon.combat_shifts.flights_report', $shift->id) }}" method="GET" class="form-inline">
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
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body p-5" id="report-content">
                    <h3 class="text-center mb-4">Звіт по польотам розвідки</h3>
                    <p class="text-center text-muted no-copy" style="margin-top: -1.5rem; margin-bottom: 2rem;">
                        (Період: {{ \Carbon\Carbon::parse($from)->format('d.m.Y H:i') }} - {{ \Carbon\Carbon::parse($to)->format('d.m.Y H:i') }})
                    </p>

                    @if(count($dayFlights) > 0)
                        <h4 class="border-bottom pb-2 mb-3"><i class="fas fa-sun text-warning"></i> Денна зміна</h4>
                        @foreach($dayFlights as $flight)
                            <div class="flight-report-item mb-4" style="page-break-inside: avoid;">
                                <p class="m-0 font-weight-bold">{{ $flight['mission_type'] === 'delivery' ? $flight['target_name'] : $flight['coordinates'] }}</p>
                                <p class="m-0">Час вильоту: {{ \Carbon\Carbon::parse($flight['flight_time'])->format('d.m.y H:i') }}
                                    @if(!empty($flight['landing_time']))
                                        - Час посадки: {{ \Carbon\Carbon::parse($flight['landing_time'])->format('H:i') }}
                                    @endif
                                </p>
                                <p class="m-0">Стрім: {{ $flight['stream_status'] ? '+' : '-' }}</p>
                                <p class="m-0">Дрон: {{ $flight['drone_name'] }}</p>
                                <p class="m-0">Місія: {{ $flight['mission_type_label'] }}</p>
                                <p class="m-0">Результат: {{ $flight['result_label'] }}</p>
                                <p class="m-0">Коментар: {{ $flight['description'] ?: '-' }}</p>
                                @if(!empty($flight['video_path']))
                                    <div class="mt-2 no-print">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-xs btn-secondary" data-toggle="modal" data-target="#videoModal{{ $flight['id'] }}" title="Переглянути">
                                                <i class="fas fa-video"></i> Переглянути
                                            </button>
                                            <a href="{{ route('recon.flights.download', $flight['id']) }}" class="btn btn-xs btn-success ml-1" title="Скачати">
                                                <i class="fas fa-download"></i> Скачати відео
                                            </a>
                                        </div>

                                        <div class="modal fade" id="videoModal{{ $flight['id'] }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Відео польоту #{{ $flight['id'] }}</h5>
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
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif

                    @if(count($nightFlights) > 0)
                        <h4 class="border-bottom pb-2 mb-3 mt-4"><i class="fas fa-moon text-secondary"></i> Нічна зміна</h4>
                        @foreach($nightFlights as $flight)
                            <div class="flight-report-item mb-4" style="page-break-inside: avoid;">
                                <p class="m-0 font-weight-bold">{{ $flight['mission_type'] === 'delivery' ? $flight['target_name'] : $flight['coordinates'] }}</p>
                                <p class="m-0">Час вильоту: {{ \Carbon\Carbon::parse($flight['flight_time'])->format('d.m.y H:i') }}
                                    @if(!empty($flight['landing_time']))
                                        - Час посадки: {{ \Carbon\Carbon::parse($flight['landing_time'])->format('H:i') }}
                                    @endif
                                </p>
                                <p class="m-0">Стрім: {{ $flight['stream_status'] ? '+' : '-' }}</p>
                                <p class="m-0">Дрон: {{ $flight['drone_name'] }}</p>
                                <p class="m-0">Місія: {{ $flight['mission_type_label'] }}</p>
                                <p class="m-0">Результат: {{ $flight['result_label'] }}</p>
                                <p class="m-0">Коментар: {{ $flight['description'] ?: '-' }}</p>
                                @if(!empty($flight['video_path']))
                                    <div class="mt-2 no-print">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-xs btn-secondary" data-toggle="modal" data-target="#videoModal{{ $flight['id'] }}" title="Переглянути">
                                                <i class="fas fa-video"></i> Переглянути
                                            </button>
                                            <a href="{{ route('recon.flights.download', $flight['id']) }}" class="btn btn-xs btn-success ml-1" title="Скачати">
                                                <i class="fas fa-download"></i> Скачати відео
                                            </a>
                                        </div>

                                        <div class="modal fade" id="videoModal{{ $flight['id'] }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Відео польоту #{{ $flight['id'] }}</h5>
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
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif

                    @if(count($dayFlights) == 0 && count($nightFlights) == 0)
                        <div class="text-center py-5">
                            <p class="text-muted">За обраний період вильотів не знайдено.</p>
                        </div>
                    @endif
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
                // Тимчасово приховуємо елементи з класом no-copy перед копіюванням
                $('.no-copy').hide();

                // Збираємо текст вручну для точного контролю пробілів
                let reportText = '';
                const title = $('#report-content h3').text().trim();
                reportText += title + '\n\n';

                const shiftTitle = $('#report-content h4').text().trim();
                if (shiftTitle) {
                    reportText += shiftTitle + '\n\n';
                }

                $('.flight-report-item').each(function(index) {
                    $(this).find('p').each(function() {
                        reportText += $(this).text().trim() + '\n';
                    });
                    reportText += '\n'; // Додаємо порожній рядок між польотами
                });

                $('.no-copy').show();

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
    #report-content {
        font-family: "Courier New", Courier, monospace;
        font-size: 1.1rem;
        line-height: 1.2;
    }
    .flight-report-item {
        margin-bottom: 1.5rem !important;
    }
    .flight-report-item p {
        margin-bottom: 0 !important;
    }
    .bg-black { background-color: #000; }
    @media print {
        .no-print, .no-copy {
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
