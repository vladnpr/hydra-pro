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
                        <label for="date" class="mr-2">Оберіть дату:</label>
                        <select name="date" id="date" class="form-control mr-3" onchange="this.form.submit()">
                            @forelse($availableDates as $flightDate)
                                <option value="{{ $flightDate }}" {{ $date == $flightDate ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($flightDate)->format('d.m.Y') }}
                                </option>
                            @empty
                                <option value="">Немає вильотів</option>
                            @endforelse
                        </select>
                        <button type="submit" class="btn btn-primary">Переглянути</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card card-primary card-outline">
                <div class="card-body p-4">
                    <div id="report-content">
                        @foreach($flights as $flight)
                            @if($flight->coordinates)
                                <p class="m-0">Координати: <span class="font-weight-bold">{{ $flight->coordinates }}</span></p>
                            @endif
                            <p class="m-0">Час початку: {{ $flight->start_time ? $flight->start_time->format('d.m.y H:i') : '-' }}</p>
                            <p class="m-0">Час кінця: {{ $flight->end_time ? $flight->end_time->format('d.m.y H:i') : '-' }}</p>
                            <p class="m-0">Стрім: {{ $flight->stream }}</p>
                            <p class="m-0">Дрон: {{ $flight->drone ? $flight->drone->name . ' ' . $flight->drone->model : 'Невідомий' }}</p>
                            <p class="m-0">БК: {{ $flight->ammunition ? $flight->ammunition->name : 'Невідоме' }}</p>
                            <p class="m-0">Результат: {{ $flight->result }}</p>
                            <p class="m-0">Детонація: {{ $flight->detonation ? 'так' : 'ні' }}</p>
                            <p class="@if(empty($flight->video_path)) mb-3 flight-end @else m-0  @endif">Коментар: {{ $flight->comment ?: '-' }}</p>
                            @if(!empty($flight->video_path))
                                <div class="mb-3 flight-end no-print">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-xs btn-secondary" data-toggle="modal" data-target="#videoModal{{ $flight->id }}" title="Переглянути">
                                            <i class="fas fa-video"></i> Переглянути
                                        </button>
                                        <a href="{{ route('air-defence.flights.download', $flight->id) }}" class="btn btn-xs btn-success ml-1" title="Скачати">
                                            <i class="fas fa-download"></i> Скачати відео
                                        </a>
                                    </div>

                                    <div class="modal fade" id="videoModal{{ $flight->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-dark">Відео вильоту ({{ $flight->start_time ? $flight->start_time->format('H:i') : '-' }})</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body text-center bg-black p-0">
                                                    <video width="100%" controls>
                                                        <source src="{{ Storage::url($flight->video_path) }}" type="video/mp4">
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
                let contentId = '#report-content';

                // Збираємо текст з контейнера
                $(contentId + ' p').each(function() {
                    reportText += $(this).text().trim() + '\n';
                    if ($(this).hasClass('flight-end')) {
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
@stop

@section('css')
<style>
    #report-content {
        font-family: "Courier New", Courier, monospace;
        font-size: 1.1rem;
        line-height: 1.2;
        color: #000;
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
    }
</style>
@stop
