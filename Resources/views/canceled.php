@extends('layouts.front.app')

@section('content')
    <div class="error_block">
        <div class="container">
            <div class="main_block">
                <div class="text_page">Success</div>
                <div>
                    <a class="home-btn" href="{{ url('/') }}">Home</a>
                </div>
            </div>
        </div>
    </div>
@endsection

