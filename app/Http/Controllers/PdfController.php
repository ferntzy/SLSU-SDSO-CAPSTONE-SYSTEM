<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
  // Show editable HTML form
  public function showForm()
  {
    return view('pdf.editable_form');
  }

  // Generate PDF from HTML input
  public function generatePDF(Request $request)
  {


    $signer = 'John Doe';
    $email = 'johndoe@slsu.edu.ph';
    $address = 'Sogod, Southern Leyte';
    $notes = 'Certified true copy of transcript';

    // ✅ Store the signature image under public/signatures/
    $signaturePath = 'uploads/signatures/signature.png'; // relative to public/

    $data = [
      'name' => $signer,
      'email' => $email,
      'address' => $address,
      'notes' => $notes,
      'signer' => $signer,
      'signature' => $signaturePath
    ];
    $pdf = Pdf::loadView('pdf.slsu_document', $data)->setPaper('A4', 'portrait');
    return $pdf->stream('SLSU-Official-Document.pdf');
  }
}
