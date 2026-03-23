@extends('adminlte::page')

@section('title', 'Додати виліт ППО')

@section('content_header')
    <h1>Додати виліт ППО</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <form action="{{ route('air-defence.races.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="position_id">Позиція</label>
                                    <select name="position_id" id="position_id" class="form-control @error('position_id') is-invalid @enderror" required>
                                        <option value="">Оберіть позицію</option>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                                {{ $position->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('position_id')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="coordinates">Координати</label>
                                    <input type="text" name="coordinates" id="coordinates" class="form-control @error('coordinates') is-invalid @enderror" value="{{ old('coordinates') }}" placeholder="напр. 37U CP 63595 53223">
                                    @error('coordinates')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_time">Час початку</label>
                                    <input type="datetime-local" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time') }}">
                                    @error('start_time')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_time">Час кінця</label>
                                    <input type="datetime-local" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time') }}">
                                    @error('end_time')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="air_defence_drone_id">Дрон</label>
                                    <select name="air_defence_drone_id" id="air_defence_drone_id" class="form-control @error('air_defence_drone_id') is-invalid @enderror" required>
                                        <option value="">Оберіть дрон</option>
                                        @foreach($drones as $drone)
                                            <option value="{{ $drone->id }}" {{ old('air_defence_drone_id') == $drone->id ? 'selected' : '' }}>
                                                {{ $drone->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('air_defence_drone_id')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="air_defence_ammunition_id">БК</label>
                                    <select name="air_defence_ammunition_id" id="air_defence_ammunition_id" class="form-control @error('air_defence_ammunition_id') is-invalid @enderror" required>
                                        <option value="">Оберіть БК</option>
                                        @foreach($ammunition as $item)
                                            <option value="{{ $item->id }}" {{ old('air_defence_ammunition_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('air_defence_ammunition_id')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="result">Результат</label>
                                    <input type="text" name="result" id="result" class="form-control @error('result') is-invalid @enderror" value="{{ old('result') }}" placeholder="напр. влучання">
                                    @error('result')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stream">Стрім</label>
                                    <input type="text" name="stream" id="stream" class="form-control @error('stream') is-invalid @enderror" value="{{ old('stream') }}" placeholder="напр. -">
                                    @error('stream')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="detonation" id="detonation" value="1" {{ old('detonation') ? 'checked' : '' }}>
                                <label for="detonation" class="custom-control-label">Детонація</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="comment">Коментар</label>
                            <textarea name="comment" id="comment" class="form-control @error('comment') is-invalid @enderror" rows="3">{{ old('comment') }}</textarea>
                            @error('comment')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('air-defence.races.index') }}" class="btn btn-default">Скасувати</a>
                        <button type="submit" class="btn btn-primary float-right">Зберегти</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
