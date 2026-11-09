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
        <h1>Денні звіти</h1>
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
        <div class="col-md-12">
            <div class="card card-primary card-outline card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0 no-print">
                    <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="daily-tab" data-toggle="pill" href="#daily-content" role="tab" aria-controls="daily-content" aria-selected="true">Денний звіт</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="flights-tab" data-toggle="pill" href="#flights-content" role="tab" aria-controls="flights-content" aria-selected="false">Звіт по польотам</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="reportTabsContent">
                        <!-- Вкладка Денний звіт -->
                        <div class="tab-pane fade show active" id="daily-content" role="tabpanel" aria-labelledby="daily-tab">
                            <div class="p-5 offset-md-2" id="report-content">
                                @foreach($presenter->getDutyReportsDTOCollection() as $reportData)
                                    @if ($reportData instanceof \App\DTOs\FPVDutyReportDTO)
                                        @include('admin.reports.duty.fpv-duty-rep', ['reportData' => $reportData])<br>
                                    @elseif($reportData instanceof ReconDutyReportDTO)
                                        @include('admin.reports.duty.recon-duty-rep', ['reportData' => $reportData])<br>
                                    @elseif($reportData instanceof \App\DTOs\ADDutyReportDTO)
                                        @include('admin.reports.duty.ad-duty-rep', ['reportData' => $reportData])<br>
                                    @elseif($reportData instanceof \App\DTOs\VampireDutyReportDTO)
                                        @include('admin.reports.duty.vampire-duty-rep', ['reportData' => $reportData])<br>
                                    @elseif($reportData instanceof \App\DTOs\UGVDutyReportDTO)
                                        @include('admin.reports.duty.ugv-duty-rep', ['reportData' => $reportData])<br>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- Вкладка Звіт по польотам -->
                        <div class="tab-pane fade" id="flights-content" role="tabpanel" aria-labelledby="flights-tab">
                            <div class="p-5 offset-md-2" id="report-content-flights">
                                @foreach($presenter->getDutyReportsDTOCollection() as $reportData)
                                    @if ($reportData instanceof \App\DTOs\FPVDutyReportDTO)
                                        @include('admin.reports.duty.fpv-flight-rep', ['reportData' => $reportData])<br>
                                    @elseif($reportData instanceof ReconDutyReportDTO)
                                        @include('admin.reports.duty.recon-flight-rep', ['reportData' => $reportData])<br>
                                    @elseif($reportData instanceof \App\DTOs\ADDutyReportDTO)
                                        @include('admin.reports.duty.ad-flight-rep', ['reportData' => $reportData])<br>
                                    @elseif($reportData instanceof \App\DTOs\VampireDutyReportDTO)
                                        @include('admin.reports.duty.vampire-flight-rep', ['reportData' => $reportData])<br>
                                    @elseif($reportData instanceof \App\DTOs\UGVDutyReportDTO)
                                        @include('admin.reports.duty.ugv-race-rep', ['reportData' => $reportData])<br>
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
            const activeTab = document.querySelector('.nav-link.active')?.getAttribute('href');
            let content = '';

            if (activeTab === '#flights-content') {
                const flightsContent = document.getElementById('report-content-flights');
                if (flightsContent) {
                    content = flightsContent.innerText;
                }
            } else if (activeTab === '#daily-content') {
                const reportContent = document.getElementById('report-content');
                if (reportContent) {
                    content = reportContent.innerText;
                }
            }

            if (!content) {
                const activePane = document.querySelector('.tab-pane.active');
                if (activePane) {
                    content = activePane.innerText;
                } else {
                    const fallbackContent = document.getElementById('report-content');
                    if (fallbackContent) {
                        content = fallbackContent.innerText;
                    }
                }
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
    </script>
@endsection

@section('css')
    <style>
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

            #report-content, #report-content-flights {
                color: #000 !important;
            }

            .tab-pane {
                display: none !important;
            }

            .tab-pane.active {
                display: block !important;
            }
        }

        #report-content, #report-content-flights {
            font-family: "Courier New", Courier, monospace;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .report-header h4, .report-header h5 {
            font-weight: bold;
        }
    </style>
@endsection

