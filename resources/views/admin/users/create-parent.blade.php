@extends('layouts.app')
@section('title','Add Parent')
@section('page-title','Add New Parent')
@section('sidebar-nav')@include('admin.partials.nav')@endsection

@section('content')
<div style="max-width:600px;">
  <div class="card">
    <div class="card-header">
      <div class="card-title">👪 Add New Parent</div>
      <a href="{{ route('admin.parents') }}" class="btn btn-out btn-sm">← Back</a>
    </div>

    <form method="POST" action="{{ route('admin.parents.store') }}">
      @csrf

      <div class="form-group">
        <label class="form-label">Full Name <span class="req">*</span></label>
        <input type="text" name="name" class="form-input" placeholder="Rajesh Shah" value="{{ old('name') }}" required/>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Email Address <span class="req">*</span></label>
          <input type="email" name="email" class="form-input" placeholder="rajesh@email.com" value="{{ old('email') }}" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Phone</label>
          <input type="tel" name="phone" class="form-input" placeholder="+91 98765 43210" value="{{ old('phone') }}"/>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Password <span class="req">*</span></label>
        <input type="password" name="password" class="form-input" placeholder="Min 8 characters" required/>
        <div class="form-hint">Parent will use this to log in. They can also log in using their child's reference code.</div>
      </div>

      <button type="submit" class="btn btn-g btn-lg btn-full">Create Parent Account →</button>
    </form>
  </div>
</div>
@endsection
