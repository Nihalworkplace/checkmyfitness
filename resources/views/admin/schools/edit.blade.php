@extends('layouts.app')
@section('title','Edit School')
@section('page-title','Edit School')

@section('sidebar-nav')
@include('admin.partials.nav')
@endsection

@section('content')
<div style="max-width:640px;">
  <div class="card">
    <div class="card-header">
      <div class="card-title">✏️ Edit — {{ $school->name }}</div>
      <a href="{{ route('admin.schools.show', $school) }}" class="btn btn-out btn-sm">← Back</a>
    </div>

    <form method="POST" action="{{ route('admin.schools.update', $school) }}">
      @csrf @method('PUT')

      <div class="form-group">
        <label class="form-label">School Name <span class="req">*</span></label>
        <input type="text" name="name" class="form-input" value="{{ old('name', $school->name) }}" required/>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">City <span class="req">*</span></label>
          <input type="text" name="city" class="form-input" value="{{ old('city', $school->city) }}" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Board <span class="req">*</span></label>
          <select name="board" class="form-input" required>
            @foreach(['CBSE','ICSE','GSEB','IB','IGCSE','State','Other'] as $b)
              <option value="{{ $b }}" {{ old('board', $school->board)===$b?'selected':'' }}>{{ $b }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Contact Person</label>
          <input type="text" name="contact_person" class="form-input" value="{{ old('contact_person', $school->contact_person) }}"/>
        </div>
        <div class="form-group">
          <label class="form-label">Contact Phone</label>
          <input type="text" name="contact_phone" class="form-input" value="{{ old('contact_phone', $school->contact_phone) }}"/>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-input">{{ old('notes', $school->notes) }}</textarea>
      </div>

      <button type="submit" class="btn btn-g btn-lg btn-full">Save Changes →</button>
    </form>
  </div>
</div>
@endsection
