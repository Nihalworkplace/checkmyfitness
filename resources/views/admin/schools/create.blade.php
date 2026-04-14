@extends('layouts.app')
@section('title','Add School')
@section('page-title','Add Partner School')

@section('sidebar-nav')
@include('admin.partials.nav')
@endsection

@section('content')
<div style="max-width:640px;">
  <div class="card">
    <div class="card-header">
      <div class="card-title">🏫 Add Partner School</div>
      <a href="{{ route('admin.schools.index') }}" class="btn btn-out btn-sm">← Back</a>
    </div>

    <form method="POST" action="{{ route('admin.schools.store') }}">
      @csrf

      <div class="form-grid">
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">School Name <span class="req">*</span></label>
          <input type="text" name="name" class="form-input" placeholder="DPS Vadodara" value="{{ old('name') }}" required/>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">City <span class="req">*</span></label>
          <input type="text" name="city" class="form-input" placeholder="Vadodara" value="{{ old('city') }}" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Board <span class="req">*</span></label>
          <select name="board" class="form-input" required>
            @foreach(['CBSE','ICSE','GSEB','IB','IGCSE','State','Other'] as $b)
              <option value="{{ $b }}" {{ old('board','CBSE')===$b?'selected':'' }}>{{ $b }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Contact Person</label>
          <input type="text" name="contact_person" class="form-input" placeholder="Principal / Coordinator" value="{{ old('contact_person') }}"/>
        </div>
        <div class="form-group">
          <label class="form-label">Contact Phone</label>
          <input type="text" name="contact_phone" class="form-input" placeholder="+91 98765 43210" value="{{ old('contact_phone') }}"/>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-input" placeholder="Any notes about this school partnership…">{{ old('notes') }}</textarea>
      </div>

      <button type="submit" class="btn btn-g btn-lg btn-full">Add School →</button>
    </form>
  </div>
</div>
@endsection
