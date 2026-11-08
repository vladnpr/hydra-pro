@extends('adminlte::page')

@section('title', 'Дашборд')

@php
    /** @var \App\Presenters\Reports\DutyReportsListPresenter $presenter */
@endphp

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Денний звіт</h1>
        <div class="no-print">
            <a href="{{ route('home') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
            <button type="button" class="btn btn-primary ml-2" onclick="printReport()">
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
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body p-5" id="printableArea">

                    <div class="report-content">

                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .report-content {
            font-family: "Source Sans Pro", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            font-size: 1.1rem;
            line-height: 1.5;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .content-wrapper {
                background: white !important;
            }
            .main-header, .main-sidebar, .main-footer {
                display: none !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            .p-5 {
                padding: 0 !important;
            }
            .report-content {
                color: #000 !important;
            }
        }
    </style>
@endsection

@section('js')
    <script>
        function printReport() {
            window.print();
        }

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
            copyReport(this);
        });

        function copyReport(btn) {
            const content = document.getElementById('printableArea').innerText;
            copyToClipboard(content).then(() => {
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
        }
    </script>
@endsection

