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
                        <label for="date" class="mr-2">Оберіть дату:</label>
                        <select name="date" id="date" class="form-control mr-2" onchange="this.form.submit()">
                            @foreach(array_keys($shift->flights) as $flightDate)
                                <option value="{{ $flightDate }}" {{ $date == $flightDate ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($flightDate)->format('d.m.Y') }}
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
                    @forelse($flights as $flight)
                        <div class="flight-report-item mb-4" style="page-break-inside: avoid;">
                            <p class="m-0 font-weight-bold">{{ $flight['coordinates'] }}</p>
                            <p class="m-0">Час: {{ \Carbon\Carbon::parse($flight['flight_time'])->format('d.m.y H:i') }}</p>
                            <p class="m-0">Стрім: {{ $flight['stream'] ?: 'без стріму' }}</p>
                            <p class="m-0">Дрон: {{ $flight['drone_name'] }} {{ $flight['drone_model'] }}</p>
                            <p class="m-0">БК: {{ $flight['ammunition_name'] }}</p>
                            <p class="m-0">Результат: {{ $flight['result'] }}</p>
                            <p class="m-0">Детонація: {{ $flight['detonation'] ?? 'ні' }}</p>
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
                            <p>За обрану дату вильотів не знайдено.</p>
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
            const items = document.querySelectorAll('.flight-report-item');
            let content = '';

            items.forEach((item, index) => {
                // Отримуємо всі параграфи всередині елемента
                const paragraphs = item.querySelectorAll('p');
                let itemText = '';

                paragraphs.forEach((p, pIndex) => {
                    itemText += p.innerText.trim();
                    if (pIndex < paragraphs.length - 1) {
                        itemText += '\n'; // Одинарний перенос між рядками одного вильоту
                    }
                });

                content += itemText;

                if (index < items.length - 1) {
                    content += '\n\n-------------------\n\n'; // Подвійний перенос та розділювач між різними вильотами
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
        .bg-black { background-color: #000; }
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
        }
        #report-content {
            font-family: "Courier New", Courier, monospace;
            font-size: 1.1rem;
            line-height: 1.4;
            color: #000;
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
