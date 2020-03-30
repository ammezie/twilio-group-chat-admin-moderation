@extends('layouts.app')

@section('content')
    <chat-room :user="{{ auth()->user() }}"></chat-room>
@endsection
