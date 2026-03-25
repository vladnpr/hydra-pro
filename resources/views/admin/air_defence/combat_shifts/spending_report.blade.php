@extends('adminlte::page')

@section('title', 'Звіт по витратам ППО')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Звіт по витратам ППО ({{ $shift->position_name }})</h1>
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
                    <form action="{{ route('air-defence.combat_shifts.spending_report', $shift->id) }}" method="GET" class="form-inline">
                        <label for="date" class="mr-2">Оберіть дату:</label>
                        <select name="date" id="date" class="form-control mr-3" onchange="this.form.submit()">
                            @forelse($availableDates as $flightDate)
                                <option value="{{ $flightDate }}" {{ $date == $flightDate ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($flightDate)->format('d.m.Y') }}
                                </option>
                            @empty
                                <option value="{{ $date }}">{{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</option>
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
                        <p class="text-center font-weight-bold mb-4">ЗВІТ ПО ВИТРАТАМ ЗА {{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</p>

                        <p class="font-weight-bold mb-1">Витрачено БК:</p>
                        @forelse($spendingAmmunition as $name => $qty)
                            <p class="m-0">- {{ $name }}: {{ $qty }} шт.</p>
                        @empty
                            <p class="m-0">Витрат БК не зафіксовано</p>
                        @endforelse

                        <p class="font-weight-bold mt-4 mb-1">Витрачено засобів:</p>
                        @forelse($spendingDrones as $name => $qty)
                            <p class="m-0">- {{ $name }}: {{ $qty }} шт.</p>
                        @empty
                            <p class="m-0">Витрат засобів не зафіксовано</p>
                        @endforelse
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
@stop

@section('css')
<style>
    #report-content {
        font-family: "Courier New", Courier, monospace;
        font-size: 1.2rem;
        line-height: 1.5;
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
            background: #fff !important;
        }
    }
</style>
@endsection
