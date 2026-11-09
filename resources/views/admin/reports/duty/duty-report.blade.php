@php
    use App\Presenters\Reports\DutyReportsListPresenter;
    use App\DTOs\ReconDutyReportDTO;
@endphp
@extends('adminlte::page')

@section('title', 'Дашборд')

@php
    /** @var DutyReportsListPresenter $presenter */
@endphp

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Звіт по залишку</h1>
        <div>
            <a href="{{ route('home') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Назад
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
                    <form action="{{ route('duty-report.index') }}" method="GET" class="form-inline">
                        <div class="form-group mr-2">
                            <label for="from" class="mr-2">З:</label>
                            <input type="datetime-local" name="from" id="from" class="form-control" value="{{$presenter->getFrom()}}">
                        </div>
                        <div class="form-group mr-2">
                            <label for="to" class="mr-2">По:</label>
                            <input type="datetime-local" name="to" id="to" class="form-control" value="{{$presenter->getTo()}}">
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
                    @foreach($presenter->getDutyReportsDTOCollection() as $reportData)
                        @if ($reportData instanceof \App\DTOs\FPVDutyReportDTO)
                            @include('admin.reports.duty.fpv-duty-rep', ['reportData' => $reportData])
                        @elseif($reportData instanceof ReconDutyReportDTO)
                            @include('admin.reports.duty.recon-duty-rep', ['reportData' => $reportData])
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
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

        document.getElementById('copy-report').addEventListener('click', function () {
            const content = document.getElementById('report-content').innerText;
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
    </script>
@endsection

@section('css')
    <style>
        @media print {
            .main-header, .main-sidebar, .main-footer, .content-header .btn {
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
            line-height: 1.6;
        }

        .report-header h4, .report-header h5 {
            font-weight: bold;
        }
    </style>
@endsection

