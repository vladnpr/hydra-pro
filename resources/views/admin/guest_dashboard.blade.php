@extends('adminlte::page')

@section('title', 'Доступ обмежений')

@section('content_header')
    <h1>Вітаємо у системі Hydra Pro</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle mr-1 text-warning"></i>
                        Потрібна активація облікового запису
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Ваш обліковий запис очікує підтвердження ролі.</h5>
                        <p>Наразі у вас встановлена роль <strong>Гість</strong>, що обмежує доступ до основних модулів системи.</p>
                    </div>

                    <div class="callout callout-warning mt-4">
                        <h5>Що потрібно зробити?</h5>
                        <p>Будь ласка, зв'яжіться з одним із адміністраторів для надання відповідних прав доступу:</p>
                        <ul>
                            @foreach($admins as $admin)
                                <li><strong>{{ $admin }}</strong></li>
                            @endforeach
                        </ul>
                        <p>Повідомте адміністратору наступну інформацію:</p>
                        <ul>
                            <li>Ваш <strong>позивний</strong></li>
                            <li><strong>Напрямок</strong>, в якому ви працюєте (наприклад: "розвідка", "fpv", "НРК" і так далі)</li>
                        </ul>
                    </div>

                    <div class="text-center mt-4">
                        <p class="text-muted">Дякуємо за розуміння! Разом до перемоги!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
