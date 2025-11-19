{{-- resources/views/student/permit/view.blade.php --}}
@extends('layouts.app')

@section('content')
  <div class="container mt-4">
    <div class="row">
      <div class="col-12">
        <h1 class="mb-4">View Permit PDF</h1>

        @if($permit->pdf_data)
          <div class="card">
            <div class="card-body">
              <object data="data:application/pdf;base64,{{ base64_encode($permit->pdf_data) }}" type="application/pdf"
                width="100%" height="600px">
                <p>Your browser does not support PDFs.
                  <a href="data:application/pdf;base64,{{ base64_encode($permit->pdf_data) }}"
                    download="sdso_permit_filled.pdf" class="btn btn-primary">
                    Download PDF
                  </a>
                </p>
              </object>
            </div>
          </div>
        @else
          <div class="alert alert-danger">
            No PDF data found for this permit.
          </div>
        @endif

        <div class="mt-3">
          <a href="{{ route('permit.track') }}" class="btn btn-secondary">Back to Tracking</a>
        </div>
      </div>
    </div>
  </div>
@endsection